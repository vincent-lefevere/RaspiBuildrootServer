<?php
 require_once('config.inc.php');
 session_start();
 if (!isset($_GET['id'])||!isset($_SESSION['login'])) die();
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $id=(int) $_GET['id'];
 $mysqli->query("LOCK TABLES projects READ, act READ");
 $res=$mysqli->query("SELECT power FROM projects INNER JOIN act ON projects.id=act.id WHERE act.id=$id AND projects.power IS NOT NULL AND email='".$_SESSION['login']."'")->fetch_assoc();
 if ($res){
  $vm=$res['power'];
  $retval=null;
  $output=null;
  $tgz="/tmp/prjbr-{$id}.tar.gz";
  exec("tar -C /data/vm-{$vm}/external -czpf {$tgz} --exclude='.git' .",$output,$retval);
  if ($retval==0) {
   header('Content-Description: File Transfer');
   header('Content-Type: application/gzip');
   header('Content-Disposition: attachment; filename="prjbr.tar.gz"');
   header('Content-Transfer-Encoding: binary');
   header('Content-Length: '.filesize($tgz));
   header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
   header('Pragma: no-cache');
   header('Expires: 0');
   $fp = fopen($tgz,'rb');
   fpassthru($fp);
   fclose($fp);
   unlink($tgz);
  }
 }
 $mysqli->query("UNLOCK TABLES");
 die();
?>