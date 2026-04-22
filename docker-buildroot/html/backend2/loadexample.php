<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');
 session_start();

 function load() {
  global $mysqli;

  $id=(int) $_POST['id'];
  $mysqli->query("LOCK TABLES departments WRITE");
  if (!$mysqli->query("SELECT 1 FROM departments WHERE id={$id}")->fetch_assoc()) {
    $mysqli->query("UNLOCK TABLES");
    return;
  }
  exec("mkdir -p /data/examples");
  $file="/data/examples/prjbr-{$id}.tar.gz";
  if (file_exists($file)) unlink($file);
  $mysqli->query("UPDATE departments SET example=0 WHERE id={$id}");
  if (isset($_FILES['upload']['tmp_name']['file'])) { 
    move_uploaded_file($_FILES['upload']['tmp_name']['file'], $file);
    $mysqli->query("UPDATE departments SET example=1 WHERE id={$id}");
  }
  $mysqli->query("UNLOCK TABLES");
  send_mqtt_msg("/all");
  die('true');
 }
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 if (isset($_SESSION['prof']) && $_SESSION['prof']!=0 && isset($_POST['id'])) load();
?>false
