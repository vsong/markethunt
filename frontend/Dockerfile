FROM webdevops/php-nginx:8.2 AS base
COPY ./*.conf /opt/docker/etc/nginx/vhost.common.d

FROM node:22 as npm-build
COPY ./ /app
RUN cd /app/www-src
RUN npm install
RUN npm run build-prod

FROM base AS release
COPY --from=npm-build /app /app
RUN composer install -d /app

ENV WEB_DOCUMENT_ROOT=/app/public