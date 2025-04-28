<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();
 if (!isset($_POST['login'])||!isset($_POST['projet'])||!isset($_SESSION['login']))
  die('false');
 $email=$_POST['login'];
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $n=(int) $_POST['projet'];
 if ($_SESSION['prof']) { // un prof peut entrer ou faire entrer quelqu'un
  $token=base64_encode(random_bytes(24));
  $mysqli->query("INSERT INTO act VALUES ($n,'$email','$token') ON DUPLICATE KEY UPDATE token='$token'");
 } else {
  $result=$mysqli->query("SELECT count(email) AS n FROM act WHERE id=$n");
  if(!$val=$result->fetch_assoc()) die('false');
  if ($val['n']==0) { // Si vide on peut y entrer ou y faire entrer quelqu'un
   $token=base64_encode(random_bytes(24));
   $mysqli->query("INSERT INTO act VALUES ($n,'$email','$token') ON DUPLICATE KEY UPDATE token='$token'");
  } else {
   $result=$mysqli->query("SELECT 1 FROM act WHERE id=$n AND token IS NOT NULL AND email='".$_SESSION['login']."'");
   $val=$result->fetch_assoc();
   if ($val) { // on est dedans, on peut faire entrer
    $token=base64_encode(random_bytes(24));
    $mysqli->query("INSERT INTO act VALUES ($n,'$email','$token') ON DUPLICATE KEY UPDATE token='$token'");
   } else {
    if ($_SESSION['login']==$email) $mysqli->query("INSERT INTO act VALUES ($n,'$email',NULL)"); else die('false');
   }
  }
 }
 frontend($n);
 send_mqtt_msg('/all');
?>
