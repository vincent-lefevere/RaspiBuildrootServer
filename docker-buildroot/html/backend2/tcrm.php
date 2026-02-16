<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');

 session_start();
 if (!isset($_POST['id'])||!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_POST['id'];
 $mysqli->query("LOCK TABLES images WRITE, toolchains WRITE, prop WRITE");
 $raw=$mysqli->query("SELECT 1 FROM toolchains WHERE id={$id}")->fetch_assoc();
 if ($raw) {
  $raw=$mysqli->query("SELECT 1 FROM images WHERE toolchain={$id}")->fetch_assoc();
  if (! $raw) {
   system("sudo /usr/bin/docker rmi docker-buildroot-toolchain-{$id}");
   system("rm -R /data/tc-{$id}");
   $mysqli->query("DELETE FROM toolchains WHERE id={$id}");
   $mysqli->query("UPDATE prop SET idtoolchain=NULL WHERE idtoolchain={$id}");
   $mysqli->query("UNLOCK TABLES");
   send_mqtt_msg('/cnf');
   die('true');
  }
 }
 $mysqli->query("UNLOCK TABLES");
 die('false');
?>