FROM php:8.4-apache

# Install SQL extension
RUN apt-get update && apt-get install -y libpq-dev \
&& docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
&& docker-php-ext-install pdo pdo_pgsql pgsql

# Copy files
COPY ../public /var/www/html/public/
COPY ../uploads /var/www/html/uploads/
COPY ../composer.json /var/www/html/composer.json
COPY ../.gitignore /var/www/html/.gitignore
COPY ../.env /var/www/html/.env
COPY ../generate_file_storage_key.php /var/www/html/generate_file_storage_key.php

# Add permissions
RUN chown -R www-data:www-data /var/www/html \
&& chmod -R 755 /var/www/html \
&& chmod -R 600 /var/www/html/uploads \
&& php /var/www/html/generate_file_storage_key.php  \
&& rm /var/www/html/generate_file_storage_key.php \
&& chown root:www-data /var/www/secure_storage \
&& chmod 750 /var/www/secure_storage \
&& chown www-data:www-data /var/www/secure_storage/encryption.key   \
&& chmod 600 /var/www/secure_storage/encryption.key \
&& chmod 600 /var/www/html/.env

# Set working directory
WORKDIR /var/www/html

# run composer install
RUN apt-get update && apt-get install -y git unzip \
&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& cd /var/www/html && composer install

# Recommended: Enable Apache rewrite module
RUN a2enmod rewrite

EXPOSE 80