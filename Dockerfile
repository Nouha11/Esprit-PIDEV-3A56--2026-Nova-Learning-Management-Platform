FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    libssl-dev \
    zlib1g-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd intl zip soap opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install dependencies (no scripts to avoid MakerBundle issue)
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts
RUN composer dump-autoload --optimize --no-dev

# Generate JWT keys directory
RUN mkdir -p config/jwt

# Set Apache document root to Symfony public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set permissions
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chown -R www-data:www-data /var/www/html/var \
    && chmod -R 775 /var/www/html/var

# Apache .htaccess support
RUN echo '<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>' \
    >> /etc/apache2/apache2.conf

ENV APP_ENV=prod
ENV APP_DEBUG=0

EXPOSE 80

# Pre-create cache directories with proper permissions
RUN mkdir -p /var/www/html/var/cache/prod/vich_uploader \
    && mkdir -p /var/www/html/var/cache/prod/pools \
    && mkdir -p /var/www/html/var/log \
    && chmod -R 777 /var/www/html/var

# Startup script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
CMD ["docker-entrypoint.sh"]