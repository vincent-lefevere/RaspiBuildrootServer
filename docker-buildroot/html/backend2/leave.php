<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();
 if (!isset($_POST['login'])||!isset($_POST['projet'])||!isset($_SESSION['login']))
    die('false');
 $email=$_POST['login'];
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $p=(int) $_POST['projet'];
 if ($_SESSION['prof'] || $email==$_SESSION['login'])
  $mysqli->query("DELETE FROM act WHERE id=$p AND email='$email'");
 else {
  $result=$mysqli->query("SELECT 1 FROM act WHERE id=$p AND token IS NOT NULL AND email='".$_SESSION['login']."'");
  $val=$result->fetch_assoc();
  if ($val) $mysqli->query("DELETE FROM act WHERE id=$p AND email='$email'");
  else die('false');
 }
 $result=$mysqli->query("SELECT count(email) AS n FROM act WHERE id=$p AND token IS NOT NULL");
 $val=$result->fetch_assoc();
 if ($val['n']==0) $mysqli->query("DELETE FROM act WHERE id=$p");
 frontend($p);
 send_mqtt_msg('/all');
?>
