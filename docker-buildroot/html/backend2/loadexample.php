<?php
 require_once('config.inc.php');
 session_start();

 function load() {
  global $mysqli;

  $id=(int) $_POST['id'];
  if (!$mysqli->query("SELECT 1 FROM departments WHERE id={$id}")->fetch_assoc()) return;
  exec("mkdir -p /data/examples");
  $file="/data/examples/prjbr-{$id}.tar.gz";
  if (file_exists($file)) unlink($file);
  if (isset($_FILES['upload']['tmp_name']['file'])) move_uploaded_file($_FILES['upload']['tmp_name']['file'], $file);
  die('true');
 }
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 if (isset($_SESSION['prof']) && $_SESSION['prof']!=0 && isset($_POST['id'])) load();
?>false