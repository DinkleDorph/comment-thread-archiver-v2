#!/bin/bash

# Set ownership for the web server
chown -R www-data:www-data /var/www/html

# Set permissions for Laravel
# chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Set general permissions (readable/writable for development)
chmod -R 777 /var/www/html

# Start Apache
exec apache2ctl -D FOREGROUND
