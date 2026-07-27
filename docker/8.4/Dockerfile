FROM php:8.4-cli

ARG WWWGROUP=1000

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
        git curl zip unzip ca-certificates gnupg gosu sudo postgresql-client \
        libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql gd bcmath intl pcntl zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd -g ${WWWGROUP} scback \
    && useradd -ms /bin/bash -g scback -u 1337 scback \
    && echo 'scback ALL=(ALL) NOPASSWD: ALL' > /etc/sudoers.d/scback

COPY docker/8.4/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/8.4/start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

ENTRYPOINT ["start-container"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]