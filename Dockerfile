FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y unzip libzip-dev \
    && docker-php-ext-install mysqli \
    && a2enmod rewrite \
    && a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork \
    && rm -rf /var/lib/apt/lists/*

# Point Apache's docroot at /public and allow .htaccess overrides
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf \
    && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' >> /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html/public/uploads

EXPOSE 80
