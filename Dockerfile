# 1. On part d'une image officielle contenant PHP et Apache
FROM php:8.2-apache

# 2. On installe l'extension pour connecter PHP à MySQL
RUN docker-php-ext-install pdo pdo_mysql

# 3. On copie tout votre code projet dans le dossier du serveur web
COPY . /var/www/html/

# 4. On donne les bons droits de lecture
RUN chown -R www-data:www-data /var/www/html/