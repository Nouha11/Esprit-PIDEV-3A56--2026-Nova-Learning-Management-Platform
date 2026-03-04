#!/bin/bash
set -e

export APP_ENV=prod
export APP_DEBUG=0

# Generate JWT keys
openssl genpkey -out /var/www/html/config/jwt/private.pem -aes256 \
  -algorithm rsa -pkeyopt rsa_keygen_bits:4096 \
  -pass pass:${JWT_PASSPHRASE}

openssl pkey -in /var/www/html/config/jwt/private.pem \
  -out /var/www/html/config/jwt/public.pem -pubout \
  -passin pass:${JWT_PASSPHRASE}

# Set JWT key permissions
chmod 644 /var/www/html/config/jwt/private.pem
chmod 644 /var/www/html/config/jwt/public.pem

export WKHTMLTOPDF_PATH=/usr/bin/wkhtmltopdf
export WKHTMLTOIMAGE_PATH=/usr/bin/wkhtmltoimage

# Clear and warm cache for prod
php bin/console cache:clear --env=prod --no-debug
php bin/console assets:install public --env=prod --no-debug

# Start Apache
apache2-foreground