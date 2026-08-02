FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
	git \
	unzip \
	zip \
	curl \
	libzip-dev

RUN docker-php-ext-install \
	pdo_mysql \
	zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
