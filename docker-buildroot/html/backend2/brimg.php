<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 
 session_start();
 if (!isset($_SESSION['prof'])||$_SESSION['prof']==0) die('false');
 if (!isset($_POST['version'])||!isset($_POST['toolchain'])||!isset($_POST['speedup'])) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $version=(int) $_POST['version'];
 $toolchain=(int) $_POST['toolchain'];
 $speedup=(int) $_POST['speedup'];
 
 if ($mysqli->query("SELECT 1 FROM images WHERE version={$version} AND toolchain={$toolchain}")->fetch_assoc()) die('false');
 $val=$mysqli->query("SELECT iddefconf FROM prop WHERE idtoolchain={$toolchain}")->fetch_assoc();
 if ($val==false) die('false');
 $iddefconf=$val['iddefconf'];
 if (!$mysqli->query("SELECT 1 FROM prop WHERE idversion={$version} AND iddefconf={$iddefconf}")->fetch_assoc()) die('false');
 $val=$mysqli->query("SELECT defconfig FROM defconfs WHERE id={$iddefconf}")->fetch_assoc();
 $defconf=$val['defconfig'];
 $mysqli->query("INSERT INTO images(version,toolchain,speedup,install) VALUES ({$version},{$toolchain},{$speedup},1)");
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
 exec("php /var/www/html/backend2/compile.php {$toolchain} {$id} >/dev/null 2>&1 &");
 die('true');
?>
