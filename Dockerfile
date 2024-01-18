FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

COPY . /var/www/html

## nginx(www-data)로 소유자 변경
RUN chown -R www-data:www-data /var/www/html/storage

## update packages
RUN apk update

## install curl
RUN apk add curl

RUN apk add nodejs npm

## install pdo mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

## install composer
RUN curl -sS https://getcomposer.org/installer | php

## move file to /usr/bin/composer
RUN mv composer.phar /usr/bin/composer

## install packages
RUN composer install --optimize-autoloader --no-dev

RUN npm install

## use 9000 port
EXPOSE 9000

RUN chown www-data:www-data ./bootstrap

RUN npm run build

RUN php artisan config:cache

RUN php artisan route:cache

RUN php artisan view:cache

RUN rm -rf .env

### run php-fpm
CMD ["php-fpm"]
