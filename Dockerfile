FROM ubuntu:24.04

# Install Apache, PHP, and Composer
RUN apt update && apt install -y \
    apache2 libapache2-mod-php \
    php8.3-xml php-curl php-mbstring php-zip php-bcmath php-json php-tokenizer \
    curl php-cli php-mysql unzip nano vim && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update DocumentRoot to point to /var/www/html/public
# Create placeholder public folder to silence Apache warning (will be overwritten
# by Podman mount at runtime)
RUN mkdir -p /var/www/html/public && \
    sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Copy custom Apache config
COPY custom-apache.conf /etc/apache2/conf-available/custom-apache.conf
RUN a2enconf custom-apache

# Start Apache in foreground
# CMD ["bash", "-c", "exec apache2ctl -D FOREGROUND"]

# Change file permissions to allow host machine to edit files in development
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]

EXPOSE 8080
