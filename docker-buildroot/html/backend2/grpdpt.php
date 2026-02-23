<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');

 session_start();
 if (!isset($_POST['id'])||!isset($_POST['grp'])||!isset($_POST['flag'])||!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_POST['id'];
 $grp=$mysqli->escape_string($_POST['grp']);
 $flag=($_POST['flag']!='0');
 try { 
  if ($flag) $mysqli->query("INSERT INTO access VALUES ({$id},'{$grp}')");
  else $mysqli->query("DELETE FROM access WHERE id={$id} AND grp='{$grp}'");
 } catch (mysqli_sql_exception $e) {}
 send_mqtt_msg('/all');
 die('true');
?>