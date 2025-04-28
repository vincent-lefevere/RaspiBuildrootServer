<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();

 if (!isset($_POST['title'])||!isset($_SESSION['prof'])||$_SESSION['prof']!=1) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $title=$mysqli->real_escape_string($_POST['title']);
 $mysqli->query("LOCK TABLES departments WRITE");
 $mysqli->query("INSERT INTO departments(title) VALUES ('$title')");
 $mysqli->query("UNLOCK TABLES");
 frontend(0);
 send_mqtt_msg("/all");
?>