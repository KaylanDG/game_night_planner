# syntax=docker/dockerfile:1

# PHP runtime (FrankenPHP serves /app/public out of the box)
FROM dunglas/frankenphp:1-php8.4 AS base

RUN install-php-extensions pdo_pgsql intl zip opcache pcntl

# We bind to $PORT (not 80/443), so drop the file capability that Render's sandbox refuses to exec.
RUN setcap -r /usr/local/bin/frankenphp

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Composer dependencies
FROM base AS vendor

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Frontend assets (Tailwind scans vendor pagination views, so vendor is needed)
FROM node:24-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json .npmrc ./
RUN npm ci

COPY --from=vendor /app /app
RUN npm run build

# Final image
FROM vendor AS app

COPY --from=assets /app/public/build ./public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && php artisan package:discover --ansi

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

# Render injects $PORT; caches are built at boot so they pick up the runtime env vars.
CMD ["sh", "-c", "php artisan migrate --force && php artisan optimize && SERVER_NAME=\":${PORT:-8080}\" exec frankenphp run --config /etc/frankenphp/Caddyfile"]
