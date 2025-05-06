<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();

 if (!isset($_POST['expert'])||!isset($_POST['projet'])||!isset($_SESSION['prof'])||$_SESSION['prof']!=1) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_POST['projet'];
 $expert=($_POST['expert']=='true')?1:0;
 $mysqli->query("UPDATE projects SET expert=$expert WHERE id=$id");
 send_mqtt_msg("/all");
 frontend(0);
?>