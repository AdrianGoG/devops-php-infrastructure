FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
	git \
	unzip \
	curl

RUN docker-php-ext-install pdo_mysql opcache

WORKDIR /var/www/html

CMD ["php-fpm"]
