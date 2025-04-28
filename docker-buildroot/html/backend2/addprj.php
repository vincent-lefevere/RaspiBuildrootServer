<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();

 if (!isset($_POST['title'])||!isset($_POST['id'])||!isset($_SESSION['login'])) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $title=$mysqli->real_escape_string($_POST['title']);
 $id=(int) $_POST['id'];
 $mysqli->query("LOCK TABLES projects WRITE, act WRITE, images WRITE");
 $val=$mysqli->query("SELECT id FROM images WHERE install=2 ORDER BY id")->fetch_assoc();
 if ($val) {
  $image=$val['id'];
  $mysqli->query("INSERT INTO projects(title,pub,iddep,image) VALUES ('$title', 0, $id, $image)");
  $id=$mysqli->insert_id;
  $login=$_SESSION['login'];
  $token=base64_encode(random_bytes(24));
  $mysqli->query("INSERT INTO act(id,email,token) VALUES ($id,'$login','$token')");
 }
 $mysqli->query("UNLOCK TABLES");
 frontend(0);
 send_mqtt_msg("/all");
?>