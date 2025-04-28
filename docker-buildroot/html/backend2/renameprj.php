<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();

 if (!isset($_POST['title'])||!isset($_POST['projet'])||!isset($_SESSION['login'])) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $title=$mysqli->real_escape_string($_POST['title']);
 $id=(int) $_POST['projet'];
 $mysqli->query("LOCK TABLES projects WRITE, act WRITE");
 if ($mysqli->query("SELECT 1 FROM act WHERE id=$id AND token IS NOT NULL AND email='".$_SESSION['login']."'")->fetch_assoc())
  $mysqli->query("UPDATE projects SET title='$title' WHERE id=$id");
 $mysqli->query("UNLOCK TABLES");
 frontend(0);
?>