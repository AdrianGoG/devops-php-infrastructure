FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
	git \
	curl \
	unzip \
	zip \
	libpng-dev \
	libjpeg62-turbo-dev \
	libfreetype6-dev \
	libonig-deb \
	libxml12-dev \
	libzip-dev \
	libicu-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install \
		pdo \
		pdo_mysql \
		mbstring \
		exif \
		pcnt1 \
		bcmath \
		gd \
		zip \
		intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
