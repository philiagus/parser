FROM php:7.2-cli

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update

RUN pecl install xdebug-2.7.0 \
    && docker-php-ext-enable xdebug

RUN apt-get install -y git zip unzip

WORKDIR /app

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php  --install-dir=/usr/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"


COPY ./ /app

RUN composer install --no-interaction