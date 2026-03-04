#!/bin/bash
set -e

# Generate JWT keys
openssl genpkey -out config/jwt/private.pem -aes256 \
  -algorithm rsa -pkeyopt rsa_keygen_bits:4096 \
  -pass pass:${JWT_PASSPHRASE}

openssl pkey -in config/jwt/private.pem \
  -out config/jwt/public.pem -pubout \
  -passin pass:${JWT_PASSPHRASE}

# Clear and warm cache
php bin/console cache:clear --env=prod
php bin/console assets:install public --env=prod

# Start Apache
apache2-foreground

WKHTMLTOPDF_PATH=/usr/bin/wkhtmltopdf
WKHTMLTOIMAGE_PATH=/usr/bin/wkhtmltoimage