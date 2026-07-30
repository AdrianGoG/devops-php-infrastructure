FROM php:7.4-fpm

RUN apt-get update && apt-get install -y curl \
	&& docker-php-ext-install pdo_mysql opcache \
	&& apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

CMD ["php-fpm"]
