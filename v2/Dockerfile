FROM php:8.3-apache

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Ativa mod_rewrite do Apache
RUN a2enmod rewrite

# Configura ServerName para evitar warning
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Configura o DocumentRoot para o diretório public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copia o código
COPY src/ /var/www/html
WORKDIR /var/www/html

# Cria diretórios necessários se não existirem
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache

# Instala dependências do Laravel
RUN composer install --no-interaction --prefer-dist

# Gera a APP_KEY do Laravel
RUN php artisan key:generate --no-interaction

# Limpa cache do Laravel
RUN php artisan config:clear \
    && php artisan cache:clear \
    && php artisan view:clear

# Configura Apache para permitir .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Habilita logs detalhados do PHP e Apache
RUN echo 'log_errors = On' >> /usr/local/etc/php/conf.d/docker-php-errors.conf \
    && echo 'error_log = /dev/stderr' >> /usr/local/etc/php/conf.d/docker-php-errors.conf \
    && echo 'display_errors = On' >> /usr/local/etc/php/conf.d/docker-php-errors.conf \
    && echo 'display_startup_errors = On' >> /usr/local/etc/php/conf.d/docker-php-errors.conf

# Configura LogLevel do Apache para debug
RUN echo 'LogLevel info' >> /etc/apache2/apache2.conf

# Ajusta permissões finais - IMPORTANTE: fazer isso por último
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache