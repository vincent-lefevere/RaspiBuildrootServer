#!/bin/sh -e
test -d /var/lib/docker && rm -R /var/lib/docker
test -d /var/lib/container.d && rm -R /var/lib/container.d
apt-get update
apt-get upgrade
apt-get -y install ca-certificates curl gnupg lsb-release
mkdir -m 0755 -p /etc/apt/keyrings
test -f /etc/apt/keyrings/docker.gpg && rm /etc/apt/keyrings/docker.gpg
curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
echo "deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian "$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
apt update
apt remove -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
mkdir -p /etc/systemd/system/docker.service.d
cat <<EOF >/etc/systemd/system/docker.service.d/env.conf
[Service]
Environment="BUILDKIT_STEP_LOG_MAX_SIZE=1073741824"

EOF
systemctl daemon-reload
systemctl restart docker

