<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');
 session_start();

 function adduser(){
  global $mysqli;

  $f = @fopen($_FILES['upload']['tmp_name']['file'], "r");
  if (! $f) die('true');
  while (($buffer = fgets($f)) !== false) {
   $tab=explode(";",trim($buffer,"\n\r\t"));
   if (count($tab)<5) continue;
   $login=$tab[1];
   if ($tab[2]!='-') {
    $name=$tab[3];
    $grp=($tab[4]=='')?'NULL':'\''.$tab[4].'\'';
    $prof=(int)$tab[0];
    if ($tab[2]!='') {
     $pwd=crypt($tab[2], '$1$'.substr(base64_encode(random_bytes(6)),0,6).'$');
     $mysqli->query("INSERT INTO users VALUES ('{$login}', '{$pwd}', {$prof}, '{$name}', {$grp}) ON DUPLICATE KEY UPDATE pwd='{$pwd}', prof={$prof}, name='{$name}', grp={$grp}");
    } else $mysqli->query("UPDATE users SET prof={$prof}, name='{$name}', grp={$grp} WHERE email='{$login}'");
   } else if ($login!=$_SESSION['login']) $mysqli->query("DELETE FROM users WHERE email='{$login}'");
  }
  fclose($f);
  $mysqli->query("DELETE FROM access WHERE grp NOT IN (SELECT DISTINCT grp FROM users WHERE grp IS NOT NULL)");
  send_mqtt_msg("/all");
  die('true');
 }
 
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 if (isset($_SESSION['prof']) && $_SESSION['prof']!=0 && isset($_POST['csv'])) adduser();
?>false