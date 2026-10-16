<?php
// Le Bureau — configuration. Copier ce fichier en config.php (ignoré par Git).
// En ligne (Render), les valeurs viennent des variables d'environnement ; en local, les valeurs par défaut suffisent
// à condition de renseigner la même clé d'API que celle du site (config.php à la racine).

// API du site vitrine : le Bureau ne lit jamais la base du site, il passe uniquement par l'API
define('BUREAU_API_URL', getenv('BUREAU_API_URL') ?: (getenv('URL_INTERNE') ? rtrim(getenv('URL_INTERNE'), '/') . '/api' : 'http://localhost/Site/api'));
define('BUREAU_API_KEY', getenv('API_KEY') ?: 'changez-moi');

// Base de données propre au Bureau (« gestion »), sur le même serveur MySQL que le site
define('BUREAU_DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('BUREAU_DB_PORT', getenv('DB_PORT') ?: '3306');
define('BUREAU_DB_NAME', getenv('BUREAU_DB_NAME') ?: 'gestion');
// Identifiants propres au Bureau si sa base a son propre utilisateur (panneau Pterodactyl : un utilisateur par base),
// sinon les mêmes que le site
define('BUREAU_DB_USER', getenv('BUREAU_DB_USER') ?: (getenv('DB_USER') ?: 'root'));
define('BUREAU_DB_PASS', getenv('BUREAU_DB_PASS') ?: (getenv('DB_PASS') ?: ''));
define('BUREAU_DB_SSL_CA', getenv('DB_SSL_CA') ?: ''); // chemin depuis la racine du site, ou absolu (connexion chiffrée en ligne)

// Envoi des réponses par email : Brevo (API HTTPS, gratuit jusqu'à 300 mails par jour).
// Render bloque le SMTP sur l'offre gratuite, d'où l'API. Sans clé, la réponse est seulement archivée dans le dossier.
define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
define('BUREAU_EXPEDITEUR', getenv('BUREAU_EXPEDITEUR') ?: '');          // adresse vérifiée dans Brevo
define('BUREAU_EXPEDITEUR_NOM', getenv('BUREAU_EXPEDITEUR_NOM') ?: 'ConseilPlus+, le conseiller moyen');
