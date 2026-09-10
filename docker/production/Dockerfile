# syntax=docker/dockerfile:1
# Production image for partner-manager: a single container running nginx +
# php-fpm + the Laravel scheduler and queue worker under supervisor.
# Build context is the repo root: docker build -f docker/production/Dockerfile .

# ---- Stage 1: build front-end assets (Vite) ----
FROM node:20-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
# npm install (not ci) so platform-specific Vite/Rollup binaries resolve for the
# build's own arch — the lockfile is generated on arm64 (Apple Silicon), which
# makes `npm ci` fail on an x86_64 builder.
RUN npm install --no-audit --no-fund
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---- Stage 2: application ----
# PHP 8.4 to match the composer.lock (Symfony 8 / laravel-ai require >=8.4).
FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx supervisor git unzip \
        libicu-dev libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql intl zip bcmath opcache pcntl gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP deps first (better layer caching); scripts run after the code is copied.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# nginx + supervisor + php config
COPY docker/production/nginx.conf /etc/nginx/sites-available/default
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/app.conf
COPY docker/production/php-overrides.ini /usr/local/etc/php/conf.d/zz-partner-manager.ini
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && mkdir -p /var/log/supervisor

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/app.conf"]
