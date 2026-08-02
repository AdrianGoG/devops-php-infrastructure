FROM php:7.4-fpm

RUN printf 'display_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/zz-production.ini
RUN apt-get update && apt-get install -y curl \
	&& docker-php-ext-install pdo_mysql \
	&& apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

CMD ["php-fpm"]
