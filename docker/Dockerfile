FROM php:8.3-fpm-alpine
RUN apk add --no-cache git unzip icu-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql intl zip mbstring bcmath
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
