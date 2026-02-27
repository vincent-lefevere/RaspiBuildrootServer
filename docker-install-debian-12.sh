#!/bin/sh -e
apt-get update
apt-get upgrade
apt-get install -y apt-transport-https ca-certificates curl gnupg-agent software-properties-common wget net-tools
curl -fsSL https://download.docker.com/linux/debian/gpg | apt-key add -
add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable"
apt-get update
apt-get remove -y docker-ce docker-ce-cli containerd.io
test -d /var/lib/docker && rm -R /var/lib/docker
apt-get install -y docker-ce docker-ce-cli containerd.io
mkdir -p /etc/systemd/system/docker.service.d
cat <<EOF >/etc/systemd/system/docker.service.d/env.conf
[Service]
Environment="BUILDKIT_STEP_LOG_MAX_SIZE=1073741824"

EOF
systemctl daemon-reload
systemctl restart docker

