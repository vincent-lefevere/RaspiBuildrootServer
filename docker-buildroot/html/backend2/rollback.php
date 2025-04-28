<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');

 function mycmp($item1,$item2) {
  $val1=explode("=",$item1)[0];
  $val2=explode("=",$item2)[0];
  return $val1 <=> $val2;
 }

 function rollback($vm,$p,$num,$version) {
  $email=$_SESSION['login'];
  $name=$_SESSION['name'];
  exec("sudo /usr/bin/docker compose -p docker-buildroot -f /data/vm-{$vm}/vm.yml down");
  if ($num!=0) {
   exec("cd /data/vm-{$vm}/external ; git reset HEAD~{$num} ; git push origin +HEAD");
  }
  exec("chmod -R +w /data/vm-{$vm}/external ; rm -R /data/vm-{$vm}/external");
  system("mkdir -p /data/vm-{$vm}/external ; git clone --branch prj{$p} git://git/projets.git /data/vm-{$vm}/external",$retval);
  if ($retval!=0) {
   exec("git clone --depth 1 git://git/projets.git /data/vm-{$vm}/external");
   exec("tar -C /data/vm-{$vm}/external -xvzpf /data/external.tar.gz");
   exec("cd /data/vm-{$vm}/external ; git config user.email {$email} ; git config user.name \"{$name}\" ; git add --all ; git commit --all -m 'create project' ; git branch -c prj{$p} ; git switch prj{$p} ; git push origin prj{$p}");
  } else exec("cd /data/vm-{$vm}/external ; mkdir -p board configs custom-rootfs packages");
  if (file_exists($name="/data/vm-{$vm}/external/configs/.idhardwareversion")) $previous=file_get_contents($name);
  else $previous=$version;
  if ($previous!=$version) {
    $def1=explode("\n",file_get_contents("/data/br-{$previous}/hardware_defconfig"));
    $def2=explode("\n",file_get_contents("/data/br-{$version}/hardware_defconfig"));
    $dcname="/data/vm-{$vm}/external/configs/manip_defconfig";
    $olddc="/data/vm-{$vm}/manip_defconfig.old";
    rename($dcname,$olddc);
    $tmp=explode("\n",file_get_contents($olddc));
    $mod=array_diff($tmp,$def1);
    $mod=array_uintersect($mod,$def1,'mycmp');
    $tmp=array_udiff($tmp,$def1,$def2,'mycmp');
    $def2=array_udiff($def2,$mod,'mycmp');
    file_put_contents($dcname,implode("\n",$tmp)."\n\n".implode("\n",$def2)."\n\n".implode("\n",$mod)."\n\n");
    file_put_contents($name, $version);
    exec("cd /data/vm-{$vm}/external ; git config user.email {$email} ; git config user.name {$email} ; git add --all ; git commit --all -m 'convert from config {$previous} to {$version}' ; git push origin prj{$p}");
  } else file_put_contents($name, $version);
  exec("sudo /usr/bin/docker compose -p docker-buildroot -f /data/vm-{$vm}/vm.yml up -d vm-{$vm}");
  exec("sudo /usr/bin/docker compose -p docker-buildroot -f /data/vm-{$vm}/vm.yml exec -T -w /home/buildroot/output -u buildroot vm-{$vm} make manip_defconfig BR2_EXTERNAL=/home/buildroot/external");
 }

 session_start();
 if (!isset($_POST['projet'])||!isset($_POST['num'])||!isset($_SESSION['login'])) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $p=(int) $_POST['projet'];
 $num=(int) $_POST['num'];
 $login=$_SESSION['login'];

 $mysqli->query("LOCK TABLES projects WRITE, act WRITE");
 if ($mysqli->query("SELECT 1 FROM act WHERE id={$p} AND token IS NOT NULL AND email='{$login}'")->fetch_assoc()) {
  if ($val=$mysqli->query("SELECT power, image FROM projects WHERE id={$p}")->fetch_assoc())
   rollback($val['power'],$p,$num,$val['image']);
 }
 $mysqli->query("UNLOCK TABLES");
 frontend($p);
 send_mqtt_msg("/all");
?>
