FROM php:7.3-cli

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update

RUN pecl install xdebug-2.7.0 \
    && docker-php-ext-enable xdebug

RUN apt-get install -y git zip unzip

WORKDIR /app

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php  --install-dir=/usr/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"


COPY ./ /app

RUN composer install --no-interaction