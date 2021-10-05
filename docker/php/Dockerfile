FROM php:7-fpm

RUN apt-get update && \
    apt-get install -y \
      git \
      libzip-dev \
      zip && \
    docker-php-ext-install \
      zip \
      pdo \
      pdo_mysql && \
    pecl install redis && \
    docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /code

COPY ./code/composer.json /code/
RUN composer install --no-interaction --no-ansi --no-progress --optimize-autoloader

COPY ./code /code
