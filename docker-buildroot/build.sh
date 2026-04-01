#!/bin/sh
if ! test -e conf/web/server.key -a  -e conf/web/server.cer
then
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout conf/web/server.key -out conf/web/server.cer
fi
mkdir -p bin
docker build -t make_wsssh_debian12:current -f wsssh/Dockerfile-Debian12 wsssh
docker container create --name tmp_wsssh make_wsssh_debian12:current
docker cp -a -q tmp_wsssh:/usr/local/sbin/wsssh - | tar -C bin -xvf -
docker container rm tmp_wsssh
docker rmi make_wsssh_debian12:current
BUILDKIT_PROGRESS=plain docker compose build master-debian12 || exit
rm -Rf bin

mkdir -p bin
docker build -t make_wsssh_debian13:current -f wsssh/Dockerfile-Debian13 wsssh
docker container create --name tmp_wsssh make_wsssh_debian13:current
docker cp -a -q tmp_wsssh:/usr/local/sbin/wsssh - | tar -C bin -xvf -
docker container rm tmp_wsssh
docker rmi make_wsssh_debian13:current
BUILDKIT_PROGRESS=plain docker compose build master-debian13 || exit
rm -Rf bin

BUILDKIT_PROGRESS=plain docker compose build web mosquitto telegraf mqtt2mysql git || exit
test -f conf/proftpd/custom/ssh_host_rsa_key && rm conf/proftpd/custom/ssh_host_rsa_key
ssh-keygen -q -N ""  -t rsa -b 4096 -f conf/proftpd/custom/ssh_host_rsa_key
test -d /var/tmp/.buildroot-ccache && rm -R /var/tmp/.buildroot-ccache
mkdir -p /var/tmp/.buildroot-ccache
chmod 777 /var/tmp/.buildroot-ccache
mkdir -p data/brdl data/tcdl
chown www-data data data/brdl data/tcdl
cat <<EOF >html/backend2/path.inc.php
<?php
define('PATH_ROOT_INSTALL','`pwd`');
?>
EOF
cat <<EOF

  Remember to periodically update the "conf/web/server.key" ans "conf/web/server.cer" files,
  for ssl access to the site.
  To do this, you can delete them and run this script again, or replace them with official
  certificates.

EOF

