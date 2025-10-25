# =============================================================================
# Stage 1: Build PHP dependencies with Composer
# =============================================================================
FROM php:8.4-fpm AS php-builder

# Install system dependencies and PHP extensions for Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    libpq-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev \
    gettext-base \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    opcache \
    intl \
    zip \
    bcmath \
    soap \
    ftp \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /var/www

# Copy application code (needed for Composer scripts like package:discover)
COPY . /var/www

# Install Composer and dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

# =============================================================================
# Stage 2: Build frontend assets with Node.js
# =============================================================================
FROM node:20-slim AS asset-builder

WORKDIR /var/www

# Copy application code for asset building
COPY . /var/www

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# =============================================================================
# Stage 3: Final production image with Nginx + PHP-FPM
# =============================================================================
FROM php:8.4-fpm

# Install runtime libraries, Nginx, and Supervisor
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libfcgi-bin \
    procps \
    nginx \
    supervisor \
    ftp \
    gettext-base \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Download and install php-fpm health check script
RUN curl -o /usr/local/bin/php-fpm-healthcheck \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# Copy PHP extensions from php-builder stage
COPY --from=php-builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=php-builder /usr/local/bin/docker-php-ext-* /usr/local/bin/

# Use the production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Enable PHP-FPM status page
RUN sed -i '/\[www\]/a pm.status_path = /status' /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy application code and Composer dependencies from php-builder
COPY --from=php-builder /var/www /var/www

# Copy built frontend assets from asset-builder (overwrite the non-built public dir)
COPY --from=asset-builder /var/www/public /var/www/public

# Copy initial storage structure for initialization
COPY ./storage /var/www/storage-init

# Copy configuration files
COPY ./nginx.conf /etc/nginx/nginx.conf

# Remove default Nginx site that conflicts with our config
RUN rm -f /etc/nginx/sites-enabled/default

COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set working directory
WORKDIR /var/www

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www

# Create directory for Nginx runtime files
RUN mkdir -p /var/run/nginx && chown -R www-data:www-data /var/run/nginx

# Expose the port (Render will set $PORT environment variable)
EXPOSE 8080

# Use entrypoint script for initialization
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start Supervisor to manage both Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
