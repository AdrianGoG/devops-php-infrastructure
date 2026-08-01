FROM php:8.0-fpm

RUN echo 1 > /var/lib/dpkg/info/format

RUN apt-get update && apt-get install -y --no-install-recommends \
	unzip \
	zip \
	curl \
	libzip-dev

RUN docker-php-ext-install pdo_mysql zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
