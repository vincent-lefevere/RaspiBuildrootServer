<?php
 require_once('config.inc.php');

 session_start();
 if (!isset($_GET['id'])||!isset($_GET['type'])||!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 $id=(int) $_GET['id'];
 if ($_GET['type']=='br') $log="/data/br-{$id}/build.log";
 else if ($_GET['type']=='tc') $log="/data/tc-{$id}/build.log";
 else die('false');
 header('Content-Description: File Transfer');
 header('Content-Type: application/download');
 header('Content-Disposition: attachment; filename="build.log"');
 header('Content-Transfer-Encoding: binary');
 header('Content-Length: '.filesize($log));
 header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
 header('Pragma: no-cache');
 header('Expires: 0');
 $fp = fopen($log,'rb');
 fpassthru($fp);
 fclose($fp);
 die();
?>