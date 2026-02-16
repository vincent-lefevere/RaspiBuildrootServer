<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');

 session_start();
 if (!isset($_POST['id'])||!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 $id=(int) $_POST['id'];
 if ($id<3) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $mysqli->query("DELETE FROM speedups WHERE id= {$id}");
 $mysqli->query("DELETE FROM host_pkgs WHERE id= {$id}");
 $mysqli->query("DELETE FROM pkgs WHERE id= {$id}");
 send_mqtt_msg('/cnf');
 die('true');
?>