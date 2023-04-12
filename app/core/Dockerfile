FROM php:7.4-apache

EXPOSE 3002

RUN a2enmod rewrite headers
RUN docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

RUN pecl install xdebug-2.8.1 \
    && docker-php-ext-enable xdebug

COPY /app/core /var/www/html