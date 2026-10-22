FROM node:18-alpine AS assets

WORKDIR /app

COPY src/package.json src/package-lock.json ./
RUN npm ci

COPY src/ .
RUN npm run production

# Versão fixa: 8.3.36 corrige CVE-2026-109618 e CVE-2026-109619 (RCE sem
# autenticação). Não voltar para uma versão anterior.
FROM php:8.3.36-apache

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd zip

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

# Copia os assets do frontend já compilados (etapa Node acima)
COPY --from=assets /app/public/js /var/www/html/public/js
COPY --from=assets /app/public/css/app.css /var/www/html/public/css/app.css
COPY --from=assets /app/public/mix-manifest.json /var/www/html/public/mix-manifest.json

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

# Erros vão só para o log (stderr), nunca para a resposta HTTP -- o Laravel
# já mostra a página de debug quando APP_DEBUG=true.
RUN echo 'log_errors = On' >> /usr/local/etc/php/conf.d/docker-php-errors.ini \
    && echo 'error_log = /dev/stderr' >> /usr/local/etc/php/conf.d/docker-php-errors.ini \
    && echo 'display_errors = Off' >> /usr/local/etc/php/conf.d/docker-php-errors.ini \
    && echo 'display_startup_errors = Off' >> /usr/local/etc/php/conf.d/docker-php-errors.ini

# Hardening: não anuncia a versão do PHP e desliga o upload progress da
# sessão (não usado pelo app; vetor do CVE-2026-109619).
RUN echo 'expose_php = Off' >> /usr/local/etc/php/conf.d/security.ini \
    && echo 'session.upload_progress.enabled = 0' >> /usr/local/etc/php/conf.d/security.ini

# Recusa no Apache corpos de requisição acima de 10 MB, antes de chegar ao
# PHP (post_max_size é 8M). Com mod_php, LimitRequestBody sozinho só trunca a
# leitura (o PHP ainda executa), então o <If> barra pelo Content-Length antes.
# Também não expõe versão do Apache/SO.
RUN echo 'LimitRequestBody 10485760' >> /etc/apache2/apache2.conf \
    && printf '<If "%%{HTTP:Content-Length} -gt 10485760">\n    Require all denied\n</If>\n' >> /etc/apache2/apache2.conf \
    && echo 'ServerTokens Prod' >> /etc/apache2/apache2.conf \
    && echo 'ServerSignature Off' >> /etc/apache2/apache2.conf

# Configura LogLevel do Apache para debug
RUN echo 'LogLevel info' >> /etc/apache2/apache2.conf

# Ajusta permissões finais - IMPORTANTE: fazer isso por último
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache