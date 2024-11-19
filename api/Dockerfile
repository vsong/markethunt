FROM webdevops/php-nginx:8.2 AS base

FROM base AS release
COPY ./config/.env /app/config/.env
COPY ./public /app/public
COPY ./src /app/src
COPY ./template /app/template
COPY ./composer.* /app
RUN composer install -d /app
