<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 require_once('install.inc.php');
 session_start();
 session_unset();

 function checklogin($login){
  global $mysqli;

  $result=$mysqli->query("SELECT prof, name, pwd FROM users WHERE email='{$login}'");
  $val=$result->fetch_assoc();
  if ($val && password_verify($_POST['pwd'],$val['pwd'])) {
	$pwd=crypt($_POST['newpwd'], '$1$'.substr(base64_encode(random_bytes(6)),0,6).'$');
    $mysqli->query("UPDATE users SET pwd='{$pwd}' WHERE email='{$login}'");
  } else die('false');
 }

 if (!isset($_POST['login'])||!isset($_POST['pwd'])) die('false');
 if ($_POST['login']==''||$_POST["pwd"]=='') die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 if (! $mysqli) die('false');
 $login=$mysqli->real_escape_string($_POST['login']);
 if ($login!=$_POST['login']) die('false');
 checklogin($login);
 die('true');
?>
