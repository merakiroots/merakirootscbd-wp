FROM php:8.3-cli-bookworm

# System tools commonly needed by coding agents
RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    ca-certificates \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
    intl \
    mbstring \
    opcache \
    zip \
    xml \
    && rm -rf /var/lib/apt/lists/*

# Composer: official Composer image is intended for dependency management in PHP.
# Copying the binary into your own PHP runtime image is the recommended pattern.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /workspace

# Agent sanity check
RUN php -v && composer --version