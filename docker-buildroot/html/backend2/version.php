<?php
 require_once('config.inc.php');

 function get_defconfs($id) {
  global $mysqli;
  $list='';
  $result=$mysqli->query("SELECT iddefconf FROM prop WHERE idversion=$id");
  while ($val=$result->fetch_assoc()) $list.=$val['iddefconf'].',';
  $list=substr($list,0,-1);
  return($list);
 }

 try {
  $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
  if (! $mysqli) die('{ "versions":[], "defconfs":[], "images":[], "toolchains":[], "speedups":[]}');
 } catch (mysqli_sql_exception $e) {
  die('{ "versions":[], "defconfs":[], "images":[], "toolchains":[], "speedups":[]}');
 }

 $speedups='';
 $result=$mysqli->query("SELECT id, title FROM speedups ORDER BY id");
 while ($val=$result->fetch_assoc()) $speedups.='{"id":'.$val['id'].',"title":"'.$val['title'].'"},';
 $speedups=substr($speedups,0,-1);


 $defconfs='';
 $result=$mysqli->query("SELECT id, defconfig FROM defconfs ORDER BY id");
 while ($val=$result->fetch_assoc()) $defconfs.='{"id":'.$val['id'].',"defconfig":"'.$val['defconfig'].'"},';
 $defconfs=substr($defconfs,0,-1);

 $versions='';
 $result=$mysqli->query("SELECT id, title FROM versions ORDER BY id");
 while ($val=$result->fetch_assoc()) {
  $id=$val['id'];
  $title=$val['title'];
  $list=get_defconfs($id);
  $versions.='{"id":'.$id.',"title":"'.$title.'","defconfs":['.$list.']},';
 }
 $versions=substr($versions,0,-1);

 $toolchains='';
 $result=$mysqli->query("SELECT toolchains.id, prop.iddefconf, prop.idversion, toolchains.gcc IS NOT NULL AS install FROM toolchains INNER JOIN prop ON toolchains.id=prop.idtoolchain ORDER BY toolchains.id");
 while ($val=$result->fetch_assoc()) $toolchains.='{"id":'.$val['id'].',"defconfig":'.$val['iddefconf'].',"version":'.$val['idversion'].($val['install']?',"install":true},':',"install":false},');
 $toolchains=substr($toolchains,0,-1);

 $images='';
 $result=$mysqli->query("SELECT images.id, images.version, images.toolchain, images.install, prop.iddefconf FROM images INNER JOIN prop ON images.toolchain=prop.idtoolchain ORDER BY images.id");
 while ($val=$result->fetch_assoc()) $images.='{"id":'.$val['id'].',"version":'.$val['version'].',"toolchain":'.$val['toolchain'].',"defconf":'.$val['iddefconf'].($val['install']==0?',"now":true':',"now":false').($val['install']==2?',"install":true},':',"install":false},');
 $images=substr($images,0,-1);
?>{ "versions":[<?php echo $versions; ?>], "defconfs":[<?php echo $defconfs; ?>], "toolchains":[<?php echo $toolchains; ?>], "images":[<?php echo $images; ?>],"speedups":[<?php echo $speedups; ?>]}
