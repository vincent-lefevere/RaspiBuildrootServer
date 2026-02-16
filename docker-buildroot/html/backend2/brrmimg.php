<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');

 session_start();
 if (!isset($_POST['id'])||!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_POST['id'];
 $mysqli->query("LOCK TABLES images WRITE");
 $raw=$mysqli->query("SELECT 1 FROM images WHERE id={$id} AND install=2")->fetch_assoc();
 if (!$raw) {
  $mysqli->query("UNLOCK TABLES");
  die('false');
 }
 exec("sudo /usr/bin/docker rmi docker-buildroot-master-{$id}");
 exec("rm -R /data/br-{$id}");
 $mysqli->query("DELETE FROM images WHERE id={$id}");
 $mysqli->query("UNLOCK TABLES");
 send_mqtt_msg('/cnf');
 die('true');
?>