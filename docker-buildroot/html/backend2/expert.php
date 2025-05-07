<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();

 if (!isset($_POST['expert'])||!isset($_POST['projet'])||!isset($_SESSION['prof'])||$_SESSION['prof']!=1) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_POST['projet'];
 $expert=($_POST['expert']=='true')?1:0;
 $login=$_SESSION['login'];
 if ($mysqli->query("SELECT 1 FROM act WHERE id={$id} AND token IS NOT NULL AND email='{$login}'")->fetch_assoc())
  $mysqli->query("UPDATE projects SET expert={$expert} WHERE id={$id}");
 send_mqtt_msg("/all");
 frontend(0);
?>