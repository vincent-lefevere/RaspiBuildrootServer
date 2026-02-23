<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');

 function poweroff($vm,$p) {
  exec("sudo /usr/bin/docker compose -p docker-buildroot -f /data/vm-{$vm}/vm.yml exec -T -w /home/buildroot/output -u buildroot vm-{$vm} make savedefconfig BR2_DEFCONFIG=/home/buildroot/external/configs/manip_defconfig");
  exec("sudo /usr/bin/docker compose -p docker-buildroot -f /data/vm-{$vm}/vm.yml down");
  $email=$_SESSION['login'];
  $name=$_SESSION['name'];
  exec("cd /data/vm-{$vm}/external ; git config user.email {$email} ; git config user.name \"{$name}\" ; git add --all ; git commit --all -m 'stop and save vm' ; git push origin prj{$p}");
  exec("chmod -R +w /data/vm-{$vm} ; rm -R /data/vm-{$vm}");
 }

 session_start();
 if (!isset($_POST['projet'])||!isset($_SESSION['login']))
    die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $p=(int) $_POST['projet'];
 $mysqli->query("LOCK TABLES projects WRITE, act WRITE");
 if ($mysqli->query("SELECT 1 FROM act WHERE id={$p} AND token IS NOT NULL AND email='".$_SESSION['login']."'")->fetch_assoc()) {
  if ($val=$mysqli->query("SELECT power FROM projects WHERE id={$p}")->fetch_assoc()) poweroff($val['power'],$p);
  $mysqli->query("UPDATE projects SET power=NULL WHERE id={$p}");
 }
 $mysqli->query("UNLOCK TABLES");
 frontend($p);
 send_mqtt_msg("/all");
 send_mqtt_msg("/cnf");
?>
