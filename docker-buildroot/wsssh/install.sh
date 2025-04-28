#!/bin/bash
apt-get update
apt-get install -y --no-install-recommends build-essential ca-certificates git pkg-config
apt-get install -y --no-install-recommends libxml2 libxml2-dev libwebsockets-dev libmariadb-dev
git clone http://github.com/vincent-lefevere/wsssh
(cd wsssh/src ; make install)

