<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');
 
 session_start();
 if (!isset($_POST['id'])||!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_POST['id'];
 $raw=$mysqli->query("SELECT 1 FROM prop WHERE idversion={$id} AND idtoolchain IS NOT NULL")->fetch_assoc();
 if ($raw) die('false');
 $raw=$mysqli->query("SELECT title FROM versions WHERE id={$id}")->fetch_assoc();
 if (!$raw) die('false');
 $title=$raw['title'];
 system("rm -R /data/brdl/buildroot-{$title}.tar.gz /data/br-{$id}");
 $mysqli->query("DELETE FROM prop WHERE idversion={$id}");
 $mysqli->query("DELETE FROM versions WHERE id={$id}");
 send_mqtt_msg('/cnf');
 die('true');
?>