# ConseilPlus+ en ligne (Render) : PHP 8.3 + Apache, base MySQL distante (Aiven).
# Réglages et secrets : variables d'environnement définies sur Render (voir docs/DEPLOIEMENT.md).
FROM php:8.3-apache

# Extensions utilisées par le site : PDO MySQL, intl (dates en français, tri alphabétique)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev \
    && docker-php-ext-install pdo_mysql intl \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod remoteip headers \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/apache.conf /etc/apache2/conf-enabled/conseilplus.conf
COPY . /var/www/html/

# Les fichiers de configuration lisent les variables d'environnement (valeurs locales par défaut)
RUN cp /var/www/html/db.example.php /var/www/html/db.php \
    && cp /var/www/html/config.example.php /var/www/html/config.php \
    && cp /var/www/html/bureau/config.example.php /var/www/html/bureau/config.php

# Le formulaire de contact appelle l'API à l'intérieur du conteneur
ENV URL_INTERNE=http://127.0.0.1

EXPOSE 80
