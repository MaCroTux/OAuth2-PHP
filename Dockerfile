FROM php:7.4-apache

RUN docker-php-ext-install pdo_mysql
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN apt-get update && apt-get -y install zlib1g-dev libmemcached-dev
RUN pecl install memcached && docker-php-ext-enable memcached
RUN apt install -y libssl-dev
RUN a2enmod rewrite

