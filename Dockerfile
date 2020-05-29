FROM php:7.4-apache

RUN docker-php-ext-install pdo_mysql
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN apt-get update && apt-get -y install zlib1g-dev libmemcached-dev
RUN pecl install memcached && docker-php-ext-enable memcached
RUN apt install -y libssl-dev
RUN a2enmod rewrite
# Create keys
RUN mkdir /keys && cd /keys/ && openssl genrsa -out private.key 2048 && openssl rsa -in private.key -pubout -out public.key && chown www-data:www-data /keys/* && chmod 660 /keys/*
