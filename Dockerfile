# 0. Imagen base: PHP 8.2 con Apache ya integrado.
#    Cumple el requisito del enunciado (PHP 8.0 o superior + servidor Apache).
FROM php:8.2-apache

# 1. Habilitar el módulo mod_rewrite de Apache.
#    Es necesario para que el .htaccess redirija todas las rutas a index.php
#    (front controller y URLs amigables como /api/servicios).
RUN a2enmod rewrite

# 2. Instalar unzip y git.
#    Composer los necesita para descargar y descomprimir los paquetes (Twig, etc.).
#    --no-install-recommends evita instalar paquetes innecesarios.
#    El rm final borra la caché de apt para que la imagen pese menos.
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/*

# 3. Instalar las extensiones PDO y PDO MySQL de PHP.
#    Son las que permiten conectar con la base de datos MySQL desde las clases DAO.
RUN docker-php-ext-install pdo pdo_mysql

# 4. Sustituir la configuración por defecto de Apache por la nuestra.
#    apache.conf define DocumentRoot en /var/www/html/public, de modo que
#    src, templates y vendor no son accesibles desde el navegador.
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# 5. Copiar el ejecutable de Composer desde su imagen oficial.
#    Se fija la versión 2 para que una versión mayor nueva no rompa la construcción.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6. Establecer el directorio de trabajo y copiar el código de la aplicación.
#    El .dockerignore evita copiar un vendor/ del PC y pisar el de la imagen.
WORKDIR /var/www/html
COPY . /var/www/html/

# 7. Instalar las dependencias del proyecto (Twig, etc.) leyendo composer.json.
#    --no-dev excluye paquetes solo de desarrollo.
#    No se usa --optimize-autoloader para que las clases nuevas se detecten
#    sin tener que regenerar el autoload durante el desarrollo.
RUN composer install --no-dev

# 8. Dar permisos al usuario del servidor web (www-data) sobre el proyecto.
RUN chown -R www-data:www-data /var/www/html

# 9. Documentar que el contenedor escucha en el puerto 80.
#    El puerto real que se abre en el PC se define en docker-compose.yml (8080:80).
EXPOSE 80