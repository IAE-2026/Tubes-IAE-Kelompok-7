FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for Docker layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

# Copy application code
COPY . .

# Create .env from .env.example (since .env is in .dockerignore)
# RUN cp .env.example .env

# Re-run autoloader with full codebase
RUN composer dump-autoload --optimize --no-dev

# Create required directories
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/api-docs \
    bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Expose port 80
EXPOSE 80

# Start script: wait for DB, then migrate, seed, generate docs, and serve
CMD bash -c "\
    php artisan key:generate --force --no-interaction && \
    echo 'Waiting for MySQL...' && \
    until php artisan migrate --force 2>/dev/null; do \
    echo 'MySQL not ready, retrying in 3s...'; \
    sleep 3; \
    done && \
    php artisan db:seed --force && \
    php artisan l5-swagger:generate && \
    php artisan serve --host=0.0.0.0 --port=80"
