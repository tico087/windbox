
FROM php:8.1-fpm


RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    git \
    unzip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql zip

WORKDIR /var/www/html
