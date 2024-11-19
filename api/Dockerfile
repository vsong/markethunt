FROM webdevops/php-nginx:8.2 AS base

FROM base AS release
COPY ./config /app/config
COPY ./public /app/public
COPY ./src /app/src
COPY ./template /app/template
COPY ./composer.* /app
RUN composer install -d /app
