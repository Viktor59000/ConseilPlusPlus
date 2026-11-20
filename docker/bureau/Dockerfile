# Le Bureau (logiciel de gestion) en ligne, comme application séparée : second service Render.
# Même dépôt que le site vitrine, mais l'application servie est bureau/ ; elle ne parle au site que par l'API
# (BUREAU_API_URL = https://conseilplus.onrender.com/api) et a sa propre base (BUREAU_DB_NAME, par défaut « gestion »).
# Sur Render : New → Web Service → ce dépôt, Dockerfile Path = docker/bureau/Dockerfile (voir docs/DEPLOIEMENT.md).
FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev ca-certificates \
    && docker-php-ext-install pdo_mysql intl \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod remoteip headers \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/bureau/apache.conf /etc/apache2/conf-enabled/bureau.conf
COPY docker/securite.ini $PHP_INI_DIR/conf.d/securite.ini
# Tout le dépôt est copié (le Bureau réutilise quelques fichiers partagés : includes/, js/, assets/, css/),
# mais seul bureau/ est la racine web ; le reste n'est servi que par les alias de apache.conf.
COPY . /var/www/app/
RUN cp /var/www/app/bureau/config.example.php /var/www/app/bureau/config.php \
    && sed -ri 's#DocumentRoot /var/www/html#DocumentRoot /var/www/app/bureau#' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
