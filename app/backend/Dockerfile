FROM php:7.4-apache

EXPOSE 3000

RUN a2enmod rewrite headers
RUN docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

RUN pecl install xdebug-2.8.1 \
    && docker-php-ext-enable xdebug

COPY /app/backend /var/www/html