FROM php:8.2-fpm

RUN printf 'display_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/zz-production.ini
RUN apt-get update && apt-get install -y git unzip curl libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install pdo_mysql zip gd && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
