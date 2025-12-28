FROM php:8.4-cli

# System deps needed by composer (zip/unzip) + PHP zip extension
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
  && docker-php-ext-install pdo pdo_mysql zip \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy backend code into container
COPY . /app

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-dev --prefer-dist --no-interaction

EXPOSE 8080

# Serve /public
CMD ["sh", "-lc", "php -S 0.0.0.0:${PORT:-8080} -t public"]
