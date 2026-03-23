<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');

 session_start();
 if (!isset($_POST['id'])||!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 $id=(int) $_POST['id'];
 if ($id<4) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $mysqli->query("DELETE FROM host_pkgs WHERE id= {$id}");
 $mysqli->query("DELETE FROM pkgs WHERE id= {$id}");
 $mysqli->query("UPDATE speedups SET del=1 WHERE id= {$id}");
 $mysqli->query("DELETE s FROM speedups AS s LEFT JOIN images ON s.id=images.speedup WHERE speedup IS NULL AND del=1");
 send_mqtt_msg('/cnf');
 die('true');
?>