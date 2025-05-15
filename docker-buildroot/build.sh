#!/bin/sh
if ! test -e conf/web/server.key -a  -e conf/web/server.cer
then
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout conf/web/server.key -out conf/web/server.cer
fi
mkdir -p bin
docker container rm tmp_wsssh
docker rmi make_wsssh:current
docker build -t make_wsssh:current wsssh
docker container create --name tmp_wsssh make_wsssh:current
docker cp -a -q tmp_wsssh:/usr/local/sbin/wsssh - | tar -C bin -xvf -
docker container rm tmp_wsssh
docker rmi make_wsssh:current
BUILDKIT_PROGRESS=plain docker compose build master web mosquitto telegraf mqtt2mysql git
rm -Rf bin
test -f conf/proftpd/custom/ssh_host_rsa_key && rm conf/proftpd/custom/ssh_host_rsa_key
ssh-keygen -q -N ""  -t rsa -b 4096 -f conf/proftpd/custom/ssh_host_rsa_key
test -d /var/tmp/.buildroot-ccache && rm -R /var/tmp/.buildroot-ccache
mkdir /var/tmp/.buildroot-ccache
chmod 777 /var/tmp/.buildroot-ccache
mkdir data/brdl
chown www-data data data/brdl
cat <<EOF >html/backend2/path.inc.php
<?php
define(PATH_ROOT_INSTALL,'`pwd`');
?>
EOF
cat <<EOF

  Remember to periodically update the "conf/web/server.key" ans "conf/web/server.cer" files,
  for ssl access to the site.
  To do this, you can delete them and run this script again, or replace them with official
  certificates.

EOF
