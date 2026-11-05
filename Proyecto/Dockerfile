FROM php:8.2-apache

# 1. Habilitar el módulo mod_rewrite (necesario para que .htaccess funcione)
RUN a2enmod rewrite

# 2. Instalar extensiones PDO necesarias para la conexión con MySQL
RUN docker-php-ext-install pdo pdo_mysql

# 3. Reemplazar la configuración por defecto de Apache con nuestro apache.conf
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# 4. Copiar ejecutable de Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Establecer directorio de trabajo y copiar el código de la aplicación
WORKDIR /var/www/html
COPY . /var/www/html/

# 6. Instalar dependencias del proyecto (Twig, etc.)
RUN composer install --no-dev --optimize-autoloader

# 7. Asignar permisos al usuario del servidor web (www-data)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80