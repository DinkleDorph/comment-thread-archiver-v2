FROM ubuntu:24.04

# Install Apache, PHP, and Composer
RUN apt update && apt install -y \
    apache2 libapache2-mod-php \
    php8.3-xml php-curl php-mbstring php-zip php-bcmath php-json php-tokenizer \
    curl php-cli php-mysql unzip nano vim && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Install Node
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

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

# Change file permissions to allow host machine to edit files in development
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]

# Create new user with matching UID to host machine, so host user can edit
# files created by the container user (for example, files created by artisan)

# Accept the arguments from command line
ARG UID
ARG GID

RUN echo "The GID being used is: ${GID}"

# Create a new group with the host's GID
RUN groupadd -g ${GID} --non-unique developer

# Create a new user with the host's UID and GID
# Also add this user to the www-data group to ensure it can interact with web server files
RUN useradd -u ${UID} --non-unique -g developer -G www-data -m developer

# Set the working directory
WORKDIR /var/www/html

EXPOSE 8080
