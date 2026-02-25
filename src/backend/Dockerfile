# Use PHP FPM for better performance
FROM php:8.4.3-fpm-bullseye

# Set environment variables
ENV PHP_MEMORY_LIMIT=512M \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    COMPOSER_ALLOW_SUPERUSER=1

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libpq-dev \
    libssl-dev \
    zlib1g-dev \
    protobuf-compiler \
    zip \
    unzip \
    nginx \
    supervisor \
    inotify-tools \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && pecl install redis grpc \
    && docker-php-ext-enable redis grpc \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Configure PHP and FPM
COPY docker-config/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker-config/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Development configuration
RUN echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.validate_timestamps=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Configure Nginx
COPY docker-config/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker-config/nginx/default.conf /etc/nginx/conf.d/default.conf

# Configure Supervisor
COPY docker-config/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.* ./

# Install composer dependencies
RUN composer update --no-scripts --no-autoloader --no-dev --ignore-platform-reqs

# Copy the rest of the application
COPY . .

RUN cp .env.example .env
RUN mkdir -p bootstrap/cache && chmod -R 775 bootstrap/cache
RUN composer update

RUN php artisan key:generate

#RUN php artisan optimize

# Generate optimized autoload files
RUN composer dump-autoload --optimize


# Set correct permissions
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    && chmod -R 777 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Create required directories and set permissions
RUN mkdir -p /var/log/nginx /var/log/php-fpm \
    && chown -R www-data:www-data /var/log/nginx /var/log/php-fpm \
    && chmod -R 777 /var/log/nginx /var/log/php-fpm

# Copy and prepare startup script
COPY docker-config/docker-entrypoint.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80



# Start services with Supervisor
# CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

CMD ["/usr/local/bin/start.sh"]
