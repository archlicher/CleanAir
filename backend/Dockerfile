# syntax=docker/dockerfile:1

FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

COPY . .

RUN rm -f bootstrap/cache/*.php \
    && composer dump-autoload --no-dev --no-scripts --optimize

FROM php:8.4-fpm-bookworm

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        libpq-dev \
        python3 \
        python3-pip \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql opcache pcntl bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && pip3 install --no-cache-dir --break-system-packages pyarrow \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

RUN chmod +x /usr/local/bin/entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
