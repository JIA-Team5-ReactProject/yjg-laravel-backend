FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

COPY . /var/www/html

RUN cd && \
    sed -i 's/post_max_size = 8M/post_max_size = 128MB/' ../../usr/local/etc/php/php.ini-production && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128MB/' ../../usr/local/etc/php/php.ini-production && \
    sed -i 's/post_max_size = 8M/post_max_size = 128MB/' ../../usr/local/etc/php/php.ini-development && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128MB/' ../../usr/local/etc/php/php.ini-development

## nginx(www-data)로 소유자 변경
RUN chown -R www-data:www-data /var/www/html/storage

## update packages
RUN apk update

## install curl
RUN apk add curl

RUN apk add nodejs npm

## install pdo mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

## install gd
RUN apk add --no-cache \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

## install zip
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-install zip

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

RUN php artisan route:cache

RUN php artisan view:cache

RUN php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

RUN php artisan l5-swagger:generate

#RUN rm -rf .env

## run php-fpm
CMD ["php-fpm"]
