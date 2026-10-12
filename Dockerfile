# Image de production : PHP 8.3 + Apache, extensions PDO MySQL et MongoDB
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends libssl-dev unzip git \
    && docker-php-ext-install pdo_mysql \
    && pecl install mongodb && docker-php-ext-enable mongodb \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# La racine web est /public : src/, templates/, .env et SQL ne sont jamais servis
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && echo "ServerTokens Prod\nServerSignature Off" >> /etc/apache2/conf-available/security.conf \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/expose_php = On/expose_php = Off/' "$PHP_INI_DIR/php.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-interaction
COPY . .
RUN chown -R www-data:www-data storage public/uploads

# Fly.io écoute sur le port 8080
RUN sed -i 's/80/8080/' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
EXPOSE 8080
