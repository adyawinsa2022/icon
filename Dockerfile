# ============================================================
# 1. Composer dependencies
# ============================================================
FROM php:8.3-cli-alpine AS composer

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


# ============================================================
# 2. Laravel PHP-FPM
# ============================================================
FROM php:8.3-fpm-alpine AS app

WORKDIR /var/www/html

# System dependencies
RUN apk add --no-cache \
    icu-libs \
    libzip

# PHP extension build dependencies
RUN apk add --no-cache --virtual .build-deps \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        pdo_mysql \
        pcntl \
        zip \
    && apk del .build-deps

# Composer dependencies
COPY --from=composer /app/vendor ./vendor

# Laravel application
COPY . .

# Laravel writable directories
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]


# ============================================================
# 3. Laravel Nginx
# ============================================================
FROM nginx:alpine AS nginx

COPY --from=app /var/www/html/public /var/www/html/public

COPY docker/nginx/default.conf \
     /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
