<?php
/**
 * Le Bureau — connexion à sa propre base « gestion » et création automatique des tables.
 * Aucune table du site vitrine n'est lue ici : les demandes arrivent uniquement par l'API.
 */
require_once __DIR__ . '/../config.php';
date_default_timezone_set('Europe/Paris');

$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
if (BUREAU_DB_SSL_CA !== '') {
    // chemin absolu (certificats du système) ou relatif à la racine du site
    $options[PDO::MYSQL_ATTR_SSL_CA] = str_starts_with(BUREAU_DB_SSL_CA, '/') ? BUREAU_DB_SSL_CA : __DIR__ . '/../../' . BUREAU_DB_SSL_CA;
}

try {
    $serveur = new PDO('mysql:host=' . BUREAU_DB_HOST . ';port=' . BUREAU_DB_PORT, BUREAU_DB_USER, BUREAU_DB_PASS, $options);
    // Sur un hébergement mutualisé, la base est créée depuis le panneau (pas le droit d'en créer ici) : on continue
    try {
        $serveur->exec('CREATE DATABASE IF NOT EXISTS `' . BUREAU_DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    } catch (PDOException $e) {
        // la connexion à la base ci-dessous vérifie qu'elle existe
    }
    $serveur = null;

    $bdd = new PDO('mysql:host=' . BUREAU_DB_HOST . ';port=' . BUREAU_DB_PORT . ';dbname=' . BUREAU_DB_NAME . ';charset=utf8mb4',
        BUREAU_DB_USER, BUREAU_DB_PASS, $options);
    $bdd->exec("SET time_zone = '" . date('P') . "'");

    $bdd->exec("
        CREATE TABLE IF NOT EXISTS agents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(191) NOT NULL UNIQUE,
            mdp VARCHAR(255) NOT NULL,
            date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS dossiers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_site INT NOT NULL UNIQUE,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            type_demande VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            date_demande DATETIME NOT NULL,
            date_import DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            statut ENUM('a_traiter', 'en_cours', 'classe') NOT NULL DEFAULT 'a_traiter',
            reponse TEXT NULL,
            date_reponse DATETIME NULL,
            id_agent INT NULL,
            FOREIGN KEY (id_agent) REFERENCES agents(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS releves (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date_releve DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            importes SMALLINT NOT NULL DEFAULT 0,
            erreurs SMALLINT NOT NULL DEFAULT 0,
            detail VARCHAR(255) NOT NULL DEFAULT '',
            id_agent INT NULL,
            FOREIGN KEY (id_agent) REFERENCES agents(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_dossier INT NOT NULL,
            id_agent INT NULL,
            texte TEXT NOT NULL,
            date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX (id_dossier)
        );
    ");

    // Migration : suivi de l'envoi de la réponse par email (envoye | archive | echec)
    if (!$bdd->query("SHOW COLUMNS FROM dossiers LIKE 'envoi'")->fetch()) {
        $bdd->exec("ALTER TABLE dossiers ADD COLUMN envoi VARCHAR(20) NULL AFTER date_reponse");
    }
    // Migration : id_agent = dernier intervenant ; id_responsable = agent à qui le dossier est affecté, avec une échéance
    // réelle (distincte du délai affiché au client). Les dossiers déjà pris en charge gardent leur agent comme responsable.
    // Ni ici ni dans « notes » de clé étrangère : la base locale (WAMP) crée des tables MyISAM, qui n'en acceptent pas.
    if (!$bdd->query("SHOW COLUMNS FROM dossiers LIKE 'id_responsable'")->fetch()) {
        // Une colonne par instruction : TiDB refuse qu'un même ALTER place une colonne après une autre qu'il crée
        $bdd->exec("ALTER TABLE dossiers ADD COLUMN id_responsable INT NULL AFTER id_agent");
    }
    if (!$bdd->query("SHOW COLUMNS FROM dossiers LIKE 'echeance'")->fetch()) {
        $bdd->exec("ALTER TABLE dossiers ADD COLUMN echeance DATE NULL AFTER id_responsable");
        $bdd->exec("UPDATE dossiers SET id_responsable = id_agent WHERE statut <> 'a_traiter'");
    }
    // Migration : date de suppression de la demande sur le site (« Purger du site »)
    if (!$bdd->query("SHOW COLUMNS FROM dossiers LIKE 'date_purge'")->fetch()) {
        $bdd->exec("ALTER TABLE dossiers ADD COLUMN date_purge DATETIME NULL AFTER echeance");
    }
    // Migration : le journal du Bureau trace aussi les purges (nature : releve | purge)
    if (!$bdd->query("SHOW COLUMNS FROM releves LIKE 'nature'")->fetch()) {
        $bdd->exec("ALTER TABLE releves ADD COLUMN nature VARCHAR(20) NOT NULL DEFAULT 'releve' AFTER date_releve");
    }
} catch (PDOException $e) {
    error_log('Le Bureau : base de données indisponible (' . $e->getMessage() . ')');
    http_response_code(503);
    exit('Le Bureau est fermé pour cause de base de données indisponible. Réessayez plus tard.');
}
