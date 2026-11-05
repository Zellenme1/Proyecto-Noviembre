FROM php:8.3-apache

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo_mysql mysqli

COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini