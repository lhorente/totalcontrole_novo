FROM php:7.4-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mysqli

COPY src/ /var/www/html/
WORKDIR /var/www/html/

# Ativa mod_rewrite do Apache (comum em CakePHP)
RUN a2enmod rewrite

# Permissões
RUN chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www
RUN chmod -R 777 /var/www/html/app/tmp

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]