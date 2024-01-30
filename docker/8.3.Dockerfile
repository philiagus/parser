FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y git zip unzip

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /app

COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;
