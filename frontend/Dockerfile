FROM webdevops/php-nginx:8.2 AS base
COPY ./*.conf /opt/docker/etc/nginx/vhost.common.d

FROM base AS release
COPY ./ /app
RUN composer install -d /app
ENV WEB_DOCUMENT_ROOT=/app/public