FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql \
    && a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite headers

# PHP + Apache settings (profile photo uploads, quiet ServerName warning)
RUN { echo "upload_max_filesize=8M"; echo "post_max_size=10M"; echo "expose_php=Off"; } > /usr/local/etc/php/conf.d/app.ini \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

WORKDIR /var/www/html
COPY . /var/www/html/
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html

# Railway injects $PORT at runtime; Apache must listen on it
COPY docker-start.sh /usr/local/bin/docker-start.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-start.sh && chmod +x /usr/local/bin/docker-start.sh
CMD ["docker-start.sh"]
