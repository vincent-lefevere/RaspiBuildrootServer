<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();

 if (!isset($_POST['id'])||!isset($_SESSION['prof'])||$_SESSION['prof']!=1) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_POST['id'];
 $mysqli->query("LOCK TABLES projects WRITE, departments WRITE");
 $val=$mysqli->query("SELECT count(id) as nb FROM projects WHERE iddep=$id")->fetch_assoc();
 if ($val['nb']==0) $mysqli->query("DELETE FROM departments WHERE id=$id");
 $mysqli->query("UNLOCK TABLES");
 frontend(0);
 send_mqtt_msg("/all");
?>