<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');
 session_start();

 function send_list($list){
  $result='[';
  foreach ($list as $key => $value) {
    $prof=($value[0]==1)?'true':'false';
    $name=str_replace(array('"'),array('\"'),$value[1]);
    $status=$value[2];
    $result.="{\"email\":\"{$key}\",\"prof\":{$prof},\"name\":\"{$name}\",\"status\":\"{$status}\"},";
  }
  $len=strlen($result);
  if ($len > 1) $result[$len-1]=']'; else $result.=']';
  return($result);
 }

 function adduser(){
  global $mysqli;
  $f=false;
  if (isset($_FILES['upload']['tmp_name']['file']))
   $f = @fopen($_FILES['upload']['tmp_name']['file'], "r");
  $list=[];
  $res=$mysqli->query("SELECT * FROM users ORDER BY prof DESC, email ASC");
  while($raw=$res->fetch_assoc()) $list[$raw['email']]=array($raw['prof'],$raw['name'],'');
  if (! $f) die(send_list($list));
  while (($buffer = fgets($f)) !== false) {
   $tab=explode(";",trim($buffer,"\n\r\t"));
   if (count($tab)<5) continue;
   $prof=(int)$tab[0];
   $login=$tab[1];
   $name=$mysqli->real_escape_string($tab[3]);
   if ($tab[2]!='-') {
    $grp=($tab[4]=='')?'NULL':'\''.$mysqli->real_escape_string($tab[4]).'\'';
    try {
     if ($tab[2]!='') {
      $pwd=crypt($tab[2], '$1$'.substr(base64_encode(random_bytes(6)),0,6).'$');
      $mysqli->query("INSERT INTO users VALUES ('{$login}', '{$pwd}', {$prof}, '{$name}', {$grp}) ON DUPLICATE KEY UPDATE pwd='{$pwd}', prof={$prof}, name='{$name}', grp={$grp}");
      $list[$login]=array($prof,$name,isset($list[$login])?'change':'created'); 
     } else {
      $mysqli->query("UPDATE users SET prof={$prof}, name='{$name}', grp={$grp} WHERE email='{$login}'");
      $list[$login]=array($prof,$name,'change');
     }
    } catch(mysqli_sql_exception $e) {
     $list[$login]=array($prof,$name,'error');
    }
   } else if ($login!=$_SESSION['login']) {
    $mysqli->query("DELETE FROM users WHERE email='{$login}'");
    $list[$login]=array($prof,$name,'delete');
   }
  }
  fclose($f);
  $mysqli->query("DELETE FROM access WHERE grp NOT IN (SELECT DISTINCT grp FROM users WHERE grp IS NOT NULL)");
  send_mqtt_msg("/all");
  die(send_list($list));
 }
 
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 if (isset($_SESSION['prof']) && $_SESSION['prof']!=0 && isset($_POST['csv'])) adduser();
?>false
