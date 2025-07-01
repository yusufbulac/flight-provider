FROM php:8.3-fpm

RUN apt-get update && apt-get install -y libxml2-dev unzip git \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install soap

WORKDIR /app

COPY composer.json composer.lock ./
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-scripts --no-progress \
    && rm composer-setup.php

COPY . ./

