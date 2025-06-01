#!/bin/bash

if [ "$#" -eq 0 ]; then
    services="mariadb  nginx php-windbox"
else
    services="$@"
fi

docker compose down --remove-orphans
docker compose up -d --build $services
