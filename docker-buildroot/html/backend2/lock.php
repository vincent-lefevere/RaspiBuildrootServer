<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();

 if (!isset($_POST['lock'])||!isset($_POST['projet'])||!isset($_SESSION['login'])) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_POST['projet'];
 $lock=($_POST['lock']=='true')?1:0;
 $mysqli->query("LOCK TABLES projects WRITE, act WRITE");
 if ($mysqli->query("SELECT 1 FROM act WHERE id=$id AND token IS NOT NULL AND email='".$_SESSION['login']."'")->fetch_assoc())
  $mysqli->query("UPDATE projects SET pub=$lock WHERE id=$id");
 $mysqli->query("UNLOCK TABLES");
 frontend(0);
?>