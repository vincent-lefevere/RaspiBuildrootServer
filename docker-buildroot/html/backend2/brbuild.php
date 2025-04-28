<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 
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

 function toolchain($toolchain) {
  $tmpdc=file_get_contents("/data/tc-{$toolchain}/hardware_defconfig");
  $tmpadd=<<<EOT
BR2_TOOLCHAIN_BUILDROOT_CXX=y
BR2_TOOLCHAIN_BUILDROOT_FORTRAN=y
BR2_PACKAGE_HOST_GDB=y
BR2_DL_DIR="/tmp/dl"
BR2_HOST_DIR="/home/cross"
BR2_CCACHE=y
BR2_CCACHE_DIR="/home/.buildroot-ccache"
BR2_INIT_NONE=y
# BR2_PACKAGE_BUSYBOX is not set
# BR2_TARGET_ROOTFS_TAR is not set
EOT;

  $tmprm=<<<EOT
BR2_SYSTEM_DHCP=y
BR2_LINUX_KERNEL=y
BR2_LINUX_KERNEL_CUSTOM_TARBALL=y
BR2_LINUX_KERNEL_CUSTOM_TARBALL_LOCATION=y
BR2_LINUX_KERNEL_DEFCONFIG=y
BR2_LINUX_KERNEL_DTS_SUPPORT=y
BR2_LINUX_KERNEL_INTREE_DTS_NAME=y
BR2_LINUX_KERNEL_NEEDS_HOST_OPENSSL=y
BR2_TARGET_ROOTFS_EXT2=y
BR2_TARGET_ROOTFS_EXT2_4=y
BR2_TARGET_ROOTFS_EXT2_SIZE=y
EOT;
  file_put_contents("/data/tc-{$toolchain}/toolchain_defconfig",cfmerge($tmpdc,$tmprm,$tmpadd));
 }

 session_start();
 if (!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 if (!isset($_POST['version'])||!isset($_POST['toolchain'])||!isset($_POST['defconf'])) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $version=(int) $_POST['version'];
 $toolchain=(int) $_POST['toolchain'];
 $defconf=(int) $_POST['defconf'];
 if ($mysqli->query("SELECT 1 FROM prop WHERE idversion={$version} AND iddefconf={$defconf}")->fetch_assoc()==false) die('false');
 if ($toolchain==0) {
  if ($mysqli->query("SELECT 1 FROM prop WHERE idversion={$version} AND idtoolchain IS NOT NULL AND iddefconf={$defconf}")->fetch_assoc()) die('false');
  $mysqli->query("INSERT INTO toolchains(gcc,headers) VALUES (NULL,NULL)");
  $toolchain=$mysqli->insert_id;
  $mysqli->query("UPDATE prop SET idtoolchain={$toolchain} WHERE idversion={$version} AND iddefconf={$defconf}");
  $val=$mysqli->query("SELECT title FROM versions WHERE id={$toolchain}")->fetch_assoc();
  $title=$val['title'];
  $val=$mysqli->query("SELECT defconfig FROM defconfs WHERE id={$defconf}")->fetch_assoc();
  $defconfig=$val['defconfig'];
  mkdir("/data/tc-{$toolchain}", 0755, true);
  exec("tar -C /data/tc-{$toolchain} -xzf /data/brdl/buildroot-{$title}.tar.gz buildroot-{$title}/configs/{$defconfig}_defconfig");
  symlink("buildroot-{$title}/configs/{$defconfig}_defconfig", "/data/tc-{$toolchain}/hardware_defconfig");
  $tmp=<<<EOT
services:
  toolchain-{$toolchain}:
    restart: no
    build:
      context: ..
      dockerfile: tc-{$toolchain}/Dockerfile

EOT;
  file_put_contents("/data/tc-{$toolchain}/tc.yml",$tmp);
  $tmp=<<<EOT
FROM docker-buildroot-master:latest
USER 33
COPY --chown=33 brdl/buildroot-{$title}.tar.gz /home
COPY --chown=33 tc-{$toolchain}/toolchain_defconfig /home/buildroot-{$title}/configs/my_toolchain_defconfig
COPY --chown=33 --chmod=755 tc-{$toolchain}/build.sh /tmp/build.sh
RUN --mount=type=cache,target=/home/.buildroot-ccache,sharing=shared,from=docker-buildroot-web,source=/ccache /tmp/build.sh

EOT;
  file_put_contents("/data/tc-{$toolchain}/Dockerfile",$tmp);
  $tmp=<<<EOT
#!/bin/bash
tar -C /home -xzpf /home/buildroot-{$title}.tar.gz
rm /home/buildroot-{$title}.tar.gz
cd /home/buildroot-{$title}
make O=/home/buildroot/cross my_toolchain_defconfig
make O=/home/buildroot/cross toolchain || exit
(cd /home/buildroot/cross/build ; echo gcc-final-*) | cut -d'-' -f3 > /home/cross/.gcc.version
(cd /home/buildroot/cross/build ; echo linux-headers-*) | cut -d'-' -f3 > /home/cross/.headers.version
cd
rm -Rf /home/buildroot-{$title}
rm -Rf /home/buildroot/cross

EOT;
  file_put_contents("/data/tc-{$toolchain}/build.sh",$tmp);
 } else {
  if ($mysqli->query("SELECT 1 FROM prop WHERE idtoolchain={$toolchain} AND iddefconf={$defconf}")->fetch_assoc()==false) die('false');
  if ($mysqli->query("SELECT 1 FROM images WHERE version={$version} AND toolchain={$toolchain} AND defconf={$defconf}")->fetch_assoc()) die('false');
 }
 $mysqli->query("INSERT INTO images(version,defconf,install,toolchain) VALUES ({$version},{$defconf},0,{$toolchain})");
 $id=$mysqli->insert_id;
 $val=$mysqli->query("SELECT title FROM versions WHERE id={$version}")->fetch_assoc();
 $title=$val['title'];
 mkdir("/data/br-{$id}", 0755, true);
 exec("tar -C /data/br-{$id} -xzf /data/brdl/buildroot-{$title}.tar.gz buildroot-{$title}/configs/{$defconf}_defconfig");
 symlink("buildroot-{$title}/configs/{$defconf}_defconfig", "/data/br-{$id}/hardware_defconfig");
 $tmp=<<<EOT
services:
  master-{$id}:
    restart: no
    build:
      context: ..
      dockerfile: br-{$id}/Dockerfile

EOT;
 file_put_contents("/data/br-{$id}/br.yml",$tmp);
 $tmp=<<<EOT
FROM docker-buildroot-toolchain-{$toolchain}:latest
USER 33
COPY --chown=33 brdl/buildroot-{$title}.tar.gz /home
RUN tar -C /home -xzpf /home/buildroot-{$title}.tar.gz && \
    chmod -R ug-w /home/buildroot-{$title} && \
    rm /home/buildroot-{$title}.tar.gz
COPY --chown=33 br-{$id}/usage_defconfig /home/buildroot-{$title}/configs/my_usage_defconfig
COPY --chown=33 br-{$id}/withaddon_defconfig /home/buildroot-{$title}/configs/my_withaddon_defconfig
COPY --chown=33 --chmod=755 br-{$id}/build.sh /tmp/build.sh
RUN --mount=type=cache,target=/home/.buildroot-ccache,sharing=shared,from=docker-buildroot-web,source=ccache /tmp/build.sh
USER root

EOT;
 file_put_contents("/data/br-{$id}/Dockerfile",$tmp);
 $tmp=<<<EOT
#!/bin/bash
cd /home/buildroot-{$title}
make O=/home/buildroot/output my_withaddon_defconfig
for pkt in cmake fakeroot makedevs e2fsprogs python-cffi python-pythran flex openssl swig dosfstools genimage mtools patchelf skeleton pkgconf zlib
do 
  echo "### make O=/home/buildroot/output host-\${pkt} ###"
  make O=/home/buildroot/output host-\${pkt} >>/dev/null || exit
done
cd /home/buildroot/output
mkdir -p /home/buildroot/external/custom-rootfs
echo "### make ###"
make || exit
echo "### make python-scipy-source ###"
make python-scipy-source
echo "### make python-scipy-build ###"
make python-scipy-build
make my_usage_defconfig
rm /home/buildroot/output/images/sdcard.img
EOT;
 file_put_contents("/data/br-{$id}/build.sh",$tmp);
 toolchain($toolchain);
 exec("php /var/www/html/backend2/compile.php {$id} >/dev/null 2>&1 &");
 die('true');
?>
