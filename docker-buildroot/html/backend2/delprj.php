<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();

 if (!isset($_POST['id'])||!isset($_SESSION['login'])) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_POST['id'];
 $login=$_SESSION['login'];
 $mysqli->query("LOCK TABLES projects WRITE, act WRITE");
 if ($mysqli->query("SELECT projects.id FROM projects INNER JOIN act ON projects.id=act.id WHERE act.email='$login' AND act.id=$id AND power IS NULL AND token is NOT NULL")->fetch_assoc()) {
  $mysqli->query("DELETE FROM act WHERE id=$id");
  $mysqli->query("DELETE FROM projects WHERE id=$id");
 }
 $mysqli->query("UNLOCK TABLES");
 frontend(0);
 send_mqtt_msg("/all");
?>