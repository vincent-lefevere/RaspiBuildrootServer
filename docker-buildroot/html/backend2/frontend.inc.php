<?php
function send_mqtt_msg($topic) {
 if (!class_exists('Mosquitto\Client')) return;

 $client = new Mosquitto\Client;
 $client->onConnect( function() use ($client,$topic) {
  $client->publish($topic,'R',1);
 });
 $client->onPublish( function() use ($client) {
  $client->disconnect();
 });
 $client->connect('mosquitto',1883);
 $client->loopForever();
}

function history($p,$vm) {
 $history='';

 exec("cd /data/vm-{$vm}/external ; git log --pretty='format:%s (%an <%ae> - %ad GMT)' --date=local --name-status  origin/prj{$p}",$result);
 foreach ($result as $value) $history.='"'.str_replace(array('"',"\\","\t","<",">"),array('\"',"\\\\","\\t","&lt;","&gt;"),$value).'",';
 $history=substr($history,0,-1);
 return($history);
}

function get_projects($id) {
 global $mysqli;

 $list='';
 $login=$_SESSION['login'];
 if ($_SESSION['prof']) $result=$mysqli->query("SELECT id FROM projects WHERE iddep=$id ");
 else $result=$mysqli->query("SELECT projects.id FROM projects INNER JOIN act ON projects.id=act.id WHERE iddep=$id AND email='$login' UNION SELECT id FROM projects WHERE iddep=$id AND NOT pub ORDER BY id");
 while ($val=$result->fetch_assoc()) $list.=$val['id'].',';
 $list=substr($list,0,-1);
 return($list);
}

function get_users($id) {
 global $mysqli;

 $list='';
 $token='';
 $result=$mysqli->query("SELECT users.email, users.name, act.token, (act.token IS NOT NULL) AS `in` FROM users INNER JOIN act ON users.email=act.email WHERE act.id=$id");
 while ($val=$result->fetch_assoc()) {
    $list.='{"email":"'.$val['email'].'","name":"'.$val['name'].($val['in']?'","in":true},':'","in":false},');
    if ($val['email']==$_SESSION['login']) $token=$val['token'];
 }
 $list=substr($list,0,-1);
 return([$list,$token]);
}

function frontend($p) {
 global $mysqli;
 global $vm;

 $login=$_SESSION['login'];
 $projects='';
 if ($_SESSION['prof']) $result=$mysqli->query("SELECT *, (power IS NULL) AS off FROM projects");
  else $result=$mysqli->query("SELECT projects.*, (projects.power IS NULL) AS off FROM projects INNER JOIN act ON projects.id=act.id WHERE email='$login' UNION SELECT *, (power IS NULL) AS off FROM projects WHERE NOT pub ORDER BY id");
 while ($val=$result->fetch_assoc()) {
  $id=$val['id'];
  $title=$val['title'];
  list($users,$token)=get_users($id);
  $version=($val['image']==null)?'null':(int)$val['image'];
  $lock=$val['pub'];
  $power=$val['off'];
  if ($power==false) {
   $docker=$val['power'];
   $history=history($id,$docker);
   $projects.='{"id":'.$id.',"history":['.$history.'],"token":"'.$token.'","title":"'.$title.'","users":['.$users.'],"version":'.$version.($lock?',"lock":true,"power":':',"lock":false,"power":').$docker.'},';
  } else {
   $projects.='{"id":'.$id.',"history":[],"token":"'.$token.'","title":"'.$title.'","users":['.$users.'],"version":'.$version.($lock?',"lock":true,"power":false},':',"lock":false,"power":false},');
  }
 }
 $projects=substr($projects,0,-1);
 $myprj='';
 $result=$mysqli->query("SELECT projects.id FROM projects INNER JOIN act ON projects.id=act.id WHERE email='$login' AND token IS NOT NULL");
 while ($val=$result->fetch_assoc()) $myprj.=$val['id'].',';
 $myprj=substr($myprj,0,-1);

 $dpts='';
 $result=$mysqli->query("SELECT id, title FROM departments");
 while ($val=$result->fetch_assoc()) {
  $id=$val['id'];
  $title=$val['title'];
  $list=get_projects($id);
  $dpts.='{"id":'.$id.',"title":"'.$title.'","projects":['.$list.']},';
 }
 $dpts=substr($dpts,0,-1);
?>{
	"name":"<?php echo $_SESSION['name']; ?>",
	"prof":<?php echo ($_SESSION['prof'])?'true':'false'; ?>,
	"projects":[<?php echo $projects; ?>],
	"myprj":[<?php echo $myprj; ?>],
	"dpts":[<?php echo $dpts; ?>]
}<?php } ?>