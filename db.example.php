<?php
// Copier ce fichier en db.php. En local (WAMP), les valeurs par défaut suffisent.
// En ligne (Render), les identifiants viennent des variables d'environnement DB_* (voir docs/DEPLOIEMENT.md).
// Fuseau horaire du site (le « jour » du mot du jour doit être le même pour PHP et MySQL)
date_default_timezone_set('Europe/Paris');

$servername = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'site';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
// Base distante (Aiven) : connexion chiffrée, vérifiée avec le certificat public de l'hébergeur
// DB_SSL_CA = chemin depuis la racine du site (certs/aiven-ca.pem) ou chemin absolu
// (/etc/ssl/certs/ca-certificates.crt : certificats du système, pour TiDB Cloud)
if ($ca = getenv('DB_SSL_CA')) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = str_starts_with($ca, '/') ? $ca : __DIR__ . '/' . $ca;
}

try {
    $bdd_init = new PDO("mysql:host=$servername;port=$port", $user, $pass, $options);
    // Création de la base si besoin. Sur un hébergement mutualisé (panneau Pterodactyl, cPanel…), la base est créée
    // depuis le panneau et l'utilisateur n'a pas le droit d'en créer : on continue alors avec la base existante.
    try {
        $bdd_init->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (PDOException $e) {
        // droit refusé : la base doit déjà exister (la connexion ci-dessous le vérifie)
    }
    $bdd_init = null;

    $bdd = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, $options);
    // MySQL suit le même fuseau que PHP (un serveur distant est souvent en UTC)
    $bdd->exec("SET time_zone = '" . date('P') . "'");

    $bdd->exec("
        CREATE TABLE IF NOT EXISTS utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            prenom VARCHAR(100) NOT NULL,
            nom VARCHAR(100) NOT NULL,
            mail VARCHAR(191) NOT NULL UNIQUE,
            mdp VARCHAR(255) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            type_demande VARCHAR(50) NOT NULL,
            statut ENUM('nouveau', 'importe') NOT NULL DEFAULT 'nouveau',
            date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS carnet (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            texte VARCHAR(500) NOT NULL,
            dossier VARCHAR(20) NOT NULL,
            source ENUM('generateur', 'ia', 'secours') NOT NULL DEFAULT 'generateur',
            date_classement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS mots_du_jour (
            jour DATE PRIMARY KEY,
            mot VARCHAR(80) NOT NULL,
            nature VARCHAR(80) NOT NULL,
            format ENUM('definition', 'sketch') NOT NULL DEFAULT 'definition',
            definition VARCHAR(300) NULL,
            exemple VARCHAR(300) NULL,
            synonyme VARCHAR(120) NULL,
            sketch JSON NULL,
            certitude TINYINT UNSIGNED NOT NULL,
            source ENUM('banque', 'ia') NOT NULL DEFAULT 'banque'
        );

        CREATE TABLE IF NOT EXISTS api_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date_appel DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            methode VARCHAR(10) NOT NULL,
            ressource VARCHAR(100) NOT NULL,
            code SMALLINT NOT NULL,
            ip VARCHAR(45) NOT NULL,
            application VARCHAR(50) NOT NULL,
            INDEX (ip, date_appel)
        );

        CREATE TABLE IF NOT EXISTS fragments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type ENUM('situation', 'action', 'resultat') NOT NULL,
            texte VARCHAR(255) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mot VARCHAR(80) NOT NULL,
            vote ENUM('moyen', 'pas_moyen') NOT NULL,
            empreinte CHAR(64) NOT NULL,
            date_vote DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY un_vote (mot, empreinte)
        );

        CREATE TABLE IF NOT EXISTS reglages (
            cle VARCHAR(50) PRIMARY KEY,
            valeur TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS abonnements_push (
            id INT AUTO_INCREMENT PRIMARY KEY,
            endpoint VARCHAR(500) CHARACTER SET ascii NOT NULL UNIQUE,
            p256dh VARCHAR(120) NOT NULL,
            auth VARCHAR(40) NOT NULL,
            date_abonnement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Migration : ajout du format « sketch » au mot du jour (bases créées avant cette version)
    if (!$bdd->query("SHOW COLUMNS FROM mots_du_jour LIKE 'format'")->fetch()) {
        $bdd->exec("ALTER TABLE mots_du_jour
            ADD COLUMN format ENUM('definition', 'sketch') NOT NULL DEFAULT 'definition' AFTER nature,
            MODIFY definition VARCHAR(300) NULL,
            MODIFY exemple VARCHAR(300) NULL,
            MODIFY synonyme VARCHAR(120) NULL,
            ADD COLUMN sketch JSON NULL AFTER synonyme");
    }

    // Synchronise la table fragments avec data/fragments.php (au premier lancement ou si le fichier a changé)
    $fragments = require __DIR__ . '/data/fragments.php';
    $total = array_sum(array_map('count', $fragments));
    if ($bdd->query("SELECT COUNT(*) FROM fragments")->fetchColumn() != $total) {
        $bdd->exec("DELETE FROM fragments");
        $stmt = $bdd->prepare("INSERT INTO fragments (type, texte) VALUES (:type, :texte)");
        foreach ($fragments as $type => $textes) {
            foreach ($textes as $texte) {
                $stmt->execute(['type' => $type, 'texte' => $texte]);
            }
        }
    }
} catch (PDOException $e) {
    // Le détail (adresse du serveur, message MySQL) va dans les journaux, jamais dans la page publique
    error_log('ConseilPlus+ : base de données indisponible (' . $e->getMessage() . ')');
    http_response_code(503); // « service indisponible » : visible des outils de surveillance, contrairement à un 200
    header('Retry-After: 120');
    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['erreur' => 'Service momentanément indisponible. Réessayez dans quelques minutes.'], JSON_UNESCAPED_UNICODE));
    }
    header('Content-Type: text/html; charset=utf-8');
    exit('<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Guichet fermé — ConseilPlus+</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;'
        . 'background:#F4EFE4;color:#1B2440;font:17px/1.55 system-ui,sans-serif}main{max-width:32rem;margin:24px;padding:32px;'
        . 'background:#FFFDF7;border:2px solid #1B2440;box-shadow:6px 6px 0 #1B2440}h1{margin:0 0 12px;font-size:1.6rem}'
        . 'p{margin:0 0 10px}.mono{font-family:ui-monospace,monospace;font-size:.85rem;color:#4A5270}</style></head>'
        . '<body><main><h1>Guichet momentanément fermé</h1><p>Nos archives sont indisponibles. Le cabinet ne voit pas '
        . 'd\'autre explication.</p><p>Réessayez dans quelques minutes : le guichet rouvre généralement sans prévenir.</p>'
        . '<p class="mono">Erreur 503 — service indisponible</p></main></body></html>');
}
?>
