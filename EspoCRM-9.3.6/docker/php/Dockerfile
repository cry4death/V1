FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libicu-dev libxml2-dev libonig-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mysqli gd zip intl opcache exif bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/docker-php-entrypoint.sh /usr/local/bin/docker-php-entrypoint-espo
RUN chmod +x /usr/local/bin/docker-php-entrypoint-espo

ENTRYPOINT ["docker-php-entrypoint-espo"]
CMD ["php-fpm"]
