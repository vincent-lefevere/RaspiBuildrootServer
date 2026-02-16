<?php
 require_once('config.inc.php');
 require_once('mqtt.inc.php');

 session_start();
 if (!isset($_POST['title'])||!isset($_POST['packages'])||!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $title=$mysqli->escape_string($_POST['title']);
 $packages=explode("\n",$_POST['packages']);
 $hostpri=$pri=1;
 $mysqli->query("INSERT INTO speedups(title) VALUES ('{$title}')");
 $id=$mysqli->insert_id;
 $previous=[];
 foreach ($packages as $line) {
  $line=trim($line);
  if ($line!=$mysqli->escape_string($line)) continue;
  if ($line=='' || $line[0]=='#') continue;
  $values=explode(',',$line);
  $package=trim($values[0]);
  if ($package=='') continue;
  if (in_array($package,$previous)) continue;
  $previous[]=$package;
  if (substr($package, 0, 5)=='host-') {
   $package=substr($package,5);
   $mysqli->query("INSERT INTO host_pkgs(id,name,pri) VALUES ({$id},'{$package}',{$hostpri})");
   $hostpri++;
  } else {
   if (count($values)==1) continue;
   $env=trim($values[1]);
   $mysqli->query("INSERT INTO pkgs(id,name,env,pri) VALUES ({$id},'{$package}','{$env}',{$pri})");
   $pri++;
  }
 }
 send_mqtt_msg('/cnf');
 die('true');
?>
