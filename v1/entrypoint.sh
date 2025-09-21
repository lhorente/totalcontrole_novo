#!/bin/bash
chown -R www-data:www-data /var/www
chmod -R 777 /var/www
chmod -R 777 /var/www/html/app/tmp

exec apache2-foreground