FROM php:8.3-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

#RUN apk update && apk add --virtual supervisor

RUN apk update && apk add --no-cache supervisor


RUN mkdir -p "/etc/supervisor/logs"

COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

CMD ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisor/supervisord.conf"]