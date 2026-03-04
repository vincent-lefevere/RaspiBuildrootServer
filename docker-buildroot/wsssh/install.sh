#!/bin/bash
apt-get update
apt-get install -y --no-install-recommends build-essential ca-certificates wget pkg-config
apt-get install -y --no-install-recommends libxml2 libxml2-dev libwebsockets-dev libmariadb-dev
wget https://github.com/vincent-lefevere/wsssh/archive/refs/tags/v1.0.0.tar.gz
tar -xvzpf v1.0.0.tar.gz
(cd wsssh-1.0.0/src ; make install)
