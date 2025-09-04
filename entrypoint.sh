#!/bin/bash

# Add www-data user to the developer group so both users can access files
usermod -a -G developer www-data

# Set ownership to www-data user but developer group
# This allows both www-data (for Apache) and developer user (host editing) to access files
chown -R www-data:developer /var/www/html

# Set permissions so both www-data and developer users can read/write
# 775 for directories (rwxrwxr-x), 664 for files (rw-rw-r--)
find /var/www/html -type d -exec chmod 775 {} \;
find /var/www/html -type f -exec chmod 664 {} \;

# Ensure storage and bootstrap/cache directories are writable by both users
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Make artisan executable for both users
chmod 775 /var/www/html/artisan 2>/dev/null || true

# Start Apache
exec apache2ctl -D FOREGROUND
