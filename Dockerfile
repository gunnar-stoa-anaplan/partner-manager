# ============================================================
# Partner Manager — Production Image
# ============================================================
# Multi-stage build: composer + npm → lean PHP-FPM + Nginx
# ============================================================

# ---------- Stage 1: Composer dependencies ----------
FROM composer:2 AS composer
WORKDIR /build

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --ignore-platform-reqs \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ---------- Stage 2: Frontend assets ----------
FROM node:24-alpine AS frontend
WORKDIR /build

COPY package.json package-lock.json* ./
RUN npm ci --force

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- Stage 3: Production runtime ----------
FROM php:8.4-fpm-bookworm AS runtime

# System deps + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        libzip-dev \
        libicu-dev \
        libxml2-dev \
        libonig-dev \
        libpq-dev \
        libcurl4-openssl-dev \
        ca-certificates \
        unzip \
    && docker-php-ext-configure gd \
        --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        gd \
        zip \
        bcmath \
        intl \
        opcache \
        pcntl \
        xml \
        mbstring \
        curl \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/* /tmp/*

# PHP production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/production/php-overrides.ini $PHP_INI_DIR/conf.d/99-overrides.ini

# Nginx config
RUN rm /etc/nginx/sites-enabled/default
COPY docker/production/nginx.conf /etc/nginx/sites-enabled/default

# Supervisor config
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Application code
WORKDIR /var/www/html

# Copy vendor from composer stage
COPY --from=composer /build/vendor ./vendor

# Copy built frontend assets
COPY --from=frontend /build/public/build ./public/build

# Copy application source
COPY . .

# Discover packages (regenerate cache without dev packages)
RUN php artisan package:discover --ansi

# Ensure storage & cache directories exist with correct permissions
RUN mkdir -p \
        storage/logs \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/app/public \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Remove dev files from the image
RUN rm -rf \
    tests \
    node_modules \
    .env \
    .env.example \
    docker-compose.yml \
    Dockerfile \
    .dockerignore \
    .git \
    .github

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
