<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');

 session_start();
 if (!isset($_POST['title'])||!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $title=$_POST['title'];
 if ('2023.' > substr($title,0,5)) die('false');
 if ($mysqli->query("SELECT 1 FROM versions WHERE title='$title'")->fetch_assoc()) die('false');
 $tar="/data/brdl/buildroot-$title.tar";
 file_put_contents($tar.'.gz',file_get_contents("https://www.buildroot.org/downloads/buildroot-$title.tar.gz"));
 if (is_dir("/data/patches/buildroot-$title")) {
  $output=null;
  $retval=null;
  exec("gunzip $tar.gz && tar -C /data/patches -rf $tar buildroot-$title && gzip -9 $tar", $output, $retval);
  if ($retval!=0) die('false');
 }
 $output=null;
 $retval=null;
 exec("tar --wildcards -tvzf $tar.gz buildroot-$title/configs/raspberrypi\\*_defconfig", $output, $retval);
 if ($retval!=0) die('false');
 $mysqli->query("INSERT INTO versions(title) VALUES ('$title')");
 $idversion=$mysqli->insert_id;
 foreach ($output as $defconfig) {
  $defconfig=explode('/',$defconfig);
  $defconfig=substr($defconfig[3],0,-10);
  if ($val=$mysqli->query("SELECT id FROM defconfs WHERE defconfig='$defconfig'")->fetch_assoc()) $iddefconf=$val['id'];
  else {
  	$mysqli->query("INSERT INTO defconfs(defconfig) VALUES ('$defconfig')");
  	$iddefconf=$mysqli->insert_id;
  }
  $mysqli->query("INSERT INTO prop(idversion,iddefconf) VALUES ($idversion,$iddefconf)");
 }
 die('true');
?>
