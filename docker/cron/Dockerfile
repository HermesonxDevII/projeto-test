FROM php:8.3-fpm-alpine

# Atualizar os pacotes e instalar dependências do sistema
RUN apk update && apk add --no-cache \
    zlib-dev \
    libpng-dev \
    git \
    curl \
    zip \
    vim \
    unzip \
    linux-headers \
    build-base

# Instalar extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_mysql exif pcntl bcmath gd sockets

# Copiar crontab para o diretório apropriado
COPY docker/cron/crontab/root /etc/crontabs/root

# Comando para iniciar o cron daemon em primeiro plano
CMD ["crond", "-f"]
