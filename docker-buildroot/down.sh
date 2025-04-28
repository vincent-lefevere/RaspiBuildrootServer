#!/bin/sh
COMPOSE_HTTP_TIMEOUT=240 docker compose down --remove-orphans
rm -Rf data/br-* data/tc-* data/vm-* data/brdl/*
