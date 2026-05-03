FROM wordpress:php8.3-apache

# Install Composer v2 from Composer's official binary image.
COPY --from=composer/composer:2-bin /composer /usr/local/bin/composer

# Install useful dev/system packages.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        less \
        unzip \
        zip \
        git \
        curl \
        nano \
        default-mysql-client \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        intl \
        zip \
        gd \
        mysqli \
        pdo_mysql \
        exif \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && a2enmod rewrite headers expires \
    && composer --version \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/conf.d/local-dev.ini