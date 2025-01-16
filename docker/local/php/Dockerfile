FROM php:8.3-fpm

WORKDIR /var/www/

# Установим зависимости
RUN apt-get update && apt-get install -y \
      apt-utils \
      git \
      libpng-dev \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libzip-dev \
      zip unzip \
      libpq-dev && \
      curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
      apt-get install -y nodejs && \
      pecl install redis && \
      docker-php-ext-enable redis && \
      postgresql-server-dev-all \
      postgis && \
      docker-php-ext-install pdo pdo_pgsql && \
      docker-php-ext-install sockets && \
      docker-php-ext-configure gd --with-freetype --with-jpeg && \
      docker-php-ext-install -j$(nproc) gd && \
      docker-php-ext-install zip && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin
