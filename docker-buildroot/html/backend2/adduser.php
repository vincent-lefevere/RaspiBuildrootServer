<?php
 require_once('config.inc.php');
 session_start();

 function adduser(){
  global $mysqli;

  $f = @fopen($_FILES['upload']['tmp_name']['file'], "r");
  if (! $f) die('true');
  while (($buffer = fgets($f)) !== false) {
   $tab=explode(";",trim($buffer,"\n\r\t"));
   $login=$tab[1];
   if ($tab[2]!='') {
    $pwd=crypt($tab[2], '$1$'.substr(base64_encode(random_bytes(6)),0,6).'$');
	$name=$tab[3];
    $prof=(int)$tab[0];
    $mysqli->query("INSERT INTO users VALUES ('{$login}', '{$pwd}', {$prof}, '{$name}') ON DUPLICATE KEY UPDATE pwd='{$pwd}', prof={$prof}, name='{$name}'");
   } else if ($login!=$_SESSION['login']) $mysqli->query("DELETE FROM users WHERE email='{$login}'");
  }
  fclose($f);
  die('true');
 }
 
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 if (isset($_SESSION['prof']) && $_SESSION['prof']!=0 && isset($_POST['csv'])) adduser();
?>false