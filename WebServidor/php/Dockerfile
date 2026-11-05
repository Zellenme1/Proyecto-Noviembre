FROM php:8.2.0-apache

# Install the PHP extensions required for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy the PHP application files to the container's web root
COPY index.php /var/www/html/
