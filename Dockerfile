FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpng-dev libonig-dev libxml2-dev zip unzip default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite

COPY . /var/www/html/

WORKDIR /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
