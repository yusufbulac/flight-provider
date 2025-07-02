FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libxslt1-dev \
    libicu-dev \
    unzip \
    git \
    ca-certificates \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install soap bcmath xsl intl

RUN echo "soap.wsdl_cache_enabled=0" >> /usr/local/etc/php/conf.d/soap.ini && \
    echo "soap.wsdl_cache_ttl=0" >> /usr/local/etc/php/conf.d/soap.ini

RUN echo "allow_url_fopen=1" >> /usr/local/etc/php/conf.d/docker-php.ini && \
    echo 'user_agent="curl/7.85.0"' >> /usr/local/etc/php/conf.d/user-agent.ini

WORKDIR /app

COPY composer.json composer.lock ./

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-scripts --no-progress \
    && rm composer-setup.php

COPY . ./
