#!/bin/bash

if [ "$#" -eq 0 ]; then
    services="mariadb  nginx laravel"
else
    services="$@"
fi

docker compose down --remove-orphans
docker compose up -d --build $services
