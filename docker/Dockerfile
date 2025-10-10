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

# Add permissions
RUN chown -R www-data:www-data /var/www/html \
&& chmod -R 755 /var/www/html \
&& chmod -R 600 /var/www/html/uploads

# Set working directory
WORKDIR /var/www/html

# run composer install
RUN apt-get update && apt-get install -y git unzip \
&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& cd /var/www/html && composer install

# Recommended: Enable Apache rewrite module
RUN a2enmod rewrite

EXPOSE 80