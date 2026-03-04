#!/bin/bash
set -e

export APP_ENV=prod
export APP_DEBUG=0
export WKHTMLTOPDF_PATH=/usr/bin/wkhtmltopdf
export WKHTMLTOIMAGE_PATH=/usr/bin/wkhtmltoimage

# Generate JWT keys
openssl genpkey -out /var/www/html/config/jwt/private.pem -aes256 \
  -algorithm rsa -pkeyopt rsa_keygen_bits:4096 \
  -pass pass:${JWT_PASSPHRASE}

openssl pkey -in /var/www/html/config/jwt/private.pem \
  -out /var/www/html/config/jwt/public.pem -pubout \
  -passin pass:${JWT_PASSPHRASE}

chmod 644 /var/www/html/config/jwt/private.pem
chmod 644 /var/www/html/config/jwt/public.pem

# Fix permissions
mkdir -p /var/www/html/var/cache/prod
mkdir -p /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

# Install frontend assets
php bin/console importmap:install
php bin/console asset-map:compile

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Clear and warm cache
php bin/console cache:clear --env=prod --no-debug
php bin/console assets:install public --env=prod --no-debug

# Start Apache
apache2-foreground