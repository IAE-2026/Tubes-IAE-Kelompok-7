FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-interaction --optimize-autoloader

# Run Laravel artisan serve
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

EXPOSE 8000
