FROM php:8.4-cli

# Install PDO + MySQL driver
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

# Copy backend code into container
COPY . /app

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies (Flight)
RUN composer install --no-dev --prefer-dist --no-interaction

EXPOSE 8080

# Start PHP built-in server, serve /public
CMD ["sh", "-lc", "php -S 0.0.0.0:${PORT:-8080} -t public"]
