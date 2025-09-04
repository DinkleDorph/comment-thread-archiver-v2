#!/bin/bash

# Set up shared group permissions for file access
# This allows both www-data and host user to read/write files

# Set group ownership to 'shared' group (which both www-data and host user belong to)
chgrp -R shared /var/www/html

# Set permissions: 775 for directories, 664 for files
# This allows group members (www-data and host user) to read/write
find /var/www/html -type d -exec chmod 775 {} \;
find /var/www/html -type f -exec chmod 664 {} \;

# Ensure required directories exist
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Set proper permissions for Laravel directories
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Make executable files executable
chmod +x /var/www/html/artisan 2>/dev/null || true
if [ -d "/var/www/html/node_modules/.bin" ]; then
    find /var/www/html/node_modules/.bin -type f -exec chmod +x {} \;
fi

# Start Apache
exec apache2ctl -D FOREGROUND
