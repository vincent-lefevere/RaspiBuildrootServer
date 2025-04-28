<?php
 require_once('config.inc.php');
 
 function send_mqtt_msg($topic) {
  if (!class_exists('Mosquitto\Client')) return;

  $client = new Mosquitto\Client;
  $client->onConnect( function() use ($client,$topic) {
   $client->publish($topic,'V',1);
  });
  $client->onPublish( function() use ($client) {
   $client->disconnect();
  });
  $client->connect('mosquitto',1883);
  $client->loopForever();
 }
 
 function mycmp($item1,$item2) {
  $val1=explode('=',$item1)[0];
  $val2=explode('=',$item2)[0];
  return $val1 <=> $val2;
 }

 function cfmerge($defconf,$remove,$add) {
  $tdefconf=explode("\n",$defconf);
  $tremove=explode("\n",$remove);
  $tadd=explode("\n",$add);
  return(implode("\n",array_udiff($tdefconf,$tremove,$tadd,'mycmp')).$add);
 }

function compiletoolchain($toolchain) {
 system("sudo /usr/bin/docker compose -p docker-buildroot -f /data/tc-{$toolchain}/tc.yml build >/data/tc-{$toolchain}/build.log 2>&1");
 $ll=system("sudo /usr/bin/docker image ls -q docker-buildroot-toolchain-{$toolchain}");
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 if ($ll!='') {
  system("sudo /usr/bin/docker container create --name tmp-toolchain-{$toolchain} docker-buildroot-toolchain-{$toolchain}:latest");
  system("sudo /usr/bin/docker container cp -q tmp-toolchain-{$toolchain}:/home/cross/.gcc.version - | tar -C /tmp -xf -");
  system("sudo /usr/bin/docker container cp -q tmp-toolchain-{$toolchain}:/home/cross/.headers.version - | tar -C /tmp -xf -");
  system("sudo /usr/bin/docker container rm tmp-toolchain-{$toolchain}");
  $gcc=(int) explode('.',file_get_contents("/tmp/.gcc.version"))[0];
  $headers=explode('.',file_get_contents("/tmp/.headers.version"));
  $major=(int) $headers[0];
  $minor=(int) $headers[1];
  $mysqli->query("UPDATE toolchains SET gcc='{$gcc}', headers='{$major}_{$minor}' WHERE id={$toolchain}");
 } else $mysqli->query("DELETE FROM toolchains WHERE id={$toolchain}");
 $mysqli->close();
}

function compileimage($id,$toolchain) {
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $val=$mysqli->query("SELECT versions.title, images.speedup FROM versions INNER JOIN images ON versions.id=images.version WHERE images.id={$id}")->fetch_assoc();
 $title=$val['title'];
 $speedup=$val['speedup'];
 $tmp=<<<EOT
#!/bin/bash
cd /home/buildroot-{$title}
make O=/home/buildroot/output my_withaddon_defconfig

EOT;
 $result=$mysqli->query("SELECT name FROM host_pkgs WHERE id={$speedup} ORDER BY pri ASC");
 while($val=$result->fetch_assoc()) {
  $pkg=$val['name'];
  $tmp.=<<<EOT
echo "### make host-{$pkg} ###"
make O=/home/buildroot/output host-{$pkg} >>/dev/null || exit

EOT;
 }
 $tmp.=<<<EOT
cd /home/buildroot/output
mkdir -p /home/buildroot/external/custom-rootfs
echo "### make ###"
make || exit

EOT;
 $result=$mysqli->query("SELECT name FROM pkgs WHERE id={$speedup} ORDER BY pri ASC");
 while($val=$result->fetch_assoc()) {
  $pkg=$val['name'];
  $tmp.=<<<EOT
echo "### make {$pkg}-source ###"
make {$pkg}-source
echo "### make {$pkg}-build ###"
make {$pkg}-build

EOT;
 }
 $tmp.=<<<EOT
make my_usage_defconfig
rm /home/buildroot/output/images/sdcard.img

EOT;
 file_put_contents("/data/br-{$id}/build.sh",$tmp);
 $val=$mysqli->query("SELECT gcc, headers FROM toolchains WHERE id={$toolchain}")->fetch_assoc();
 $gcc_version=$val['gcc'];
 $headers_version=$val['headers'];
 $tmpdc=file_get_contents("/data/br-{$id}/hardware_defconfig");
 $tmpadd=$tmpdc.<<<EOT
BR2_TOOLCHAIN_EXTERNAL=y
BR2_TOOLCHAIN_EXTERNAL_CUSTOM=y
BR2_TOOLCHAIN_EXTERNAL_PATH="/home/cross"
BR2_TOOLCHAIN_EXTERNAL_CUSTOM_GLIBC=y
# BR2_TOOLCHAIN_EXTERNAL_INET_RPC is not set
BR2_TOOLCHAIN_EXTERNAL_CXX=y
BR2_TOOLCHAIN_EXTERNAL_FORTRAN=y
BR2_TOOLCHAIN_EXTERNAL_GCC_{$gcc_version}=y
BR2_TOOLCHAIN_EXTERNAL_HEADERS_{$headers_version}=y
BR2_CCACHE=y
BR2_CCACHE_DIR="/home/.buildroot-ccache"
BR2_DL_DIR="/tmp/dl"
BR2_HOST_DIR="/tmp/cross"
BR2_TARGET_GENERIC_ROOT_PASSWD="Bu11dr00t"
BR2_EXTERNAL="/home/buildroot/external"
BR2_ROOTFS_OVERLAY="/home/buildroot/external/custom-rootfs/"

EOT;
 $tmprm=<<<EOT
BR2_TOOLCHAIN_BUILDROOT_CXX=y

EOT;
 $tmp=cfmerge($tmpdc,$tmprm,$tmpadd);
 file_put_contents("/data/br-{$id}/usage_defconfig",$tmp);
 $result=$mysqli->query("SELECT env FROM pkgs WHERE id={$speedup} ORDER BY pri ASC");
 while($val=$result->fetch_assoc()) {
  $env=$val['env'];
  $tmp.=<<<EOT
BR2_{$env}=y

EOT;
 }
 file_put_contents("/data/br-{$id}/withaddon_defconfig",$tmp);
 $mysqli->close();

 system("sudo /usr/bin/docker rmi docker-buildroot-master-{$id}");
 system("sudo /usr/bin/docker compose -p docker-buildroot -f /data/br-{$id}/br.yml build >/data/br-{$id}/build.log 2>&1");
 $ll=system("sudo /usr/bin/docker image ls -q docker-buildroot-master-{$id}");
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 if ($ll!='') $mysqli->query("UPDATE images SET install=2 WHERE id={$id}");
 else $mysqli->query("DELETE FROM images WHERE id={$id}");
 $mysqli->close();
}

if ($argc!=3) exit();
$toolchain=(int) $argv[1];
$image=(int) $argv[2];
$mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
while (true) {
 $mysqli->query("LOCK TABLES images WRITE, toolchains WRITE");
 $val=$mysqli->query("SELECT toolchains.id AS toolchain, 
      CASE WHEN images.id IS NULL THEN 0 ELSE images.id END AS image,
      toolchains.gcc IS NOT NULL AS flag,
      CASE WHEN images.install IS NULL THEN 1 ELSE images.install END AS install
    FROM toolchains LEFT JOIN images
    ON toolchains.id=images.toolchain 
    WHERE (toolchains.gcc IS NULL) OR (images.install IS NOT NULL AND images.install < 2)
    ORDER BY install ASC, toolchain ASC, image ASC LIMIT 1")->fetch_assoc();
 if (!$val) exit();
 if ($image==0) {
  if ($val['toolchain']==$toolchain) {
   $mysqli->query("UNLOCK TABLES");
   break;
  }
  $val=$mysqli->query("SELECT id FROM toolchains WHERE id={$toolchain}")->fetch_assoc();
  if (!$val) exit();
 } else {
  if ($val['image']==$image && $val['flag']) {
   $mysqli->query("UPDATE images SET install=0 WHERE id={$image}");
   $mysqli->query("UNLOCK TABLES");
   break;
  }
  $val=$mysqli->query("SELECT id FROM images WHERE id={$image}")->fetch_assoc();
  if (!$val) exit();
 }
 $mysqli->query("UNLOCK TABLES");
 sleep(30);
}
$mysqli->close();
if ($image==0) compiletoolchain($toolchain);
else compileimage($image,$toolchain);
send_mqtt_msg('/cnf');
?>