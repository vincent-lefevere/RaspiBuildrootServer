<?php
define('BROKER','mosquitto');
define('PORT', 1883);
define("BDDSERVEUR","mariadb");
define("BDDLOGIN","mqtt");
define("BDDPASSWD","mqtt");
define("BDDBASE","buildroot");


$client = new Mosquitto\Client('MySQL-PHP',false);
$client->onConnect('connect');
$client->onDisconnect('disconnect');
$client->onMessage('message');
$client->connect(BROKER, PORT, 60);

while (true) 
	try {
		$client->loopForever();
	} catch (Exception $e) { 
		var_dump($e->getMessage());
		sleep(1);
		$client->connect(BROKER, PORT, 60) ;
	}

function connect($r) {
	global $client;

	echo "Received response code {$r}\n";
	$client->subscribe('#',2);
	$client->subscribe('/prj/#',1);
}

function message($message) {
	if ($message->topic[0]!='/') message_telegraf($message);
	else message_term($message);
}

function message_term($message) {
	global $client;

	$id=(int) substr($message->topic, 5);
	if (!$mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE)) return;
	switch ($message->payload) {
		case '?':
			$client->publish('/prj/'.$id,($mysqli->query("SELECT cmd FROM projects WHERE id={$id} AND cmd=1")->fetch_assoc())?'on':'off',1);
			break;
		case 'on':
			$mysqli->query("UPDATE projects SET cmd=1 WHERE id={$id}");
			break;
		case 'off':
			$mysqli->query("UPDATE projects SET cmd=0 WHERE id={$id}");
			break;
		default:
			break;
	}
}

function message_telegraf($message) {
	global $client;

	$payload=str_replace('com.docker.compose.service', 'service', $message->payload);
	$obj=json_decode($payload);
	if (($obj==NULL)||!isset($obj->name)||!isset($obj->timestamp)||!isset($obj->fields)) return;
	if (!$mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE)) return;
	$timestamp=$obj->timestamp;
	$name=$obj->name;
	if (($name=='mem') || ($name=='cpu')) {
		$id=0;
		if ($name=='mem' && isset($obj->fields->used_percent) && isset($obj->fields->swap_total) && isset($obj->fields->swap_free)) {
			$mem=(int) (100*$obj->fields->used_percent);
			$swap=(int) (10000*(($obj->fields->swap_total-$obj->fields->swap_free)/$obj->fields->swap_total));
			$sql="INSERT INTO graph(timestamp,id,mem,swap) VALUES ({$timestamp},{$id},{$mem},{$swap}) ON DUPLICATE KEY UPDATE mem={$mem}, swap={$swap}";
			$mysqli->query($sql);
			$mem/=100;
			$swap/=100;
			$client->publish('/met',"{\"time\":{$timestamp},\"mem\":{$mem},\"swap\":{$swap}}",1);
		} else if ($name=='cpu' && isset($obj->fields->usage_active)) {
			$cpu=(int) (100*$obj->fields->usage_active);
			$sql="INSERT INTO graph(timestamp,id,cpu) VALUES ({$timestamp},{$id},{$cpu}) ON DUPLICATE KEY UPDATE cpu={$cpu}";
			$mysqli->query($sql);
			$cpu/=100;
			$client->publish('/met',"{\"time\":{$timestamp},\"cpu\":{$cpu}}",1);
		}
	} else if (($name=='docker_container_mem') || ($name=='docker_container_cpu')) {
		if (!isset($obj->fields->usage_percent)) return;
		$service=$obj->tags->service;
		$vm=(int) substr($service,3);
		$sql="SELECT id FROM projects WHERE power={$vm}";
		if ($row=$mysqli->query($sql)->fetch_assoc()) {
			$id=(int) $row['id'];
			if ($name=='docker_container_mem') {
				$mem=(int) (100*$obj->fields->usage_percent);
				$sql="INSERT INTO graph(timestamp,id,lmem) VALUES ({$timestamp},{$id},{$mem}) ON DUPLICATE KEY UPDATE lmem={$mem}";
				$mysqli->query($sql);
				$mem/=100;
				$client->publish('/met',"{\"time\":{$timestamp},\"id\":{$id},\"lmem\":{$mem}}",1);
			} else if ($name=='docker_container_cpu') {
				$cpu=(int) (100*$obj->fields->usage_percent);
				$sql="INSERT INTO graph(timestamp,id,lcpu) VALUES ({$timestamp},{$id},{$cpu}) ON DUPLICATE KEY UPDATE lcpu={$cpu}";
				$mysqli->query($sql);
				$cpu/=100;
				$client->publish('/met',"{\"time\":{$timestamp},\"id\":{$id},\"lcpu\":{$cpu}}",1);
			}
		}
	}
	$mysqli->query("DELETE FROM graph WHERE timestamp < ({$timestamp} - 7800)");
}

function disconnect() {
	global $client;

	echo "Disconnected\n";
}

?>