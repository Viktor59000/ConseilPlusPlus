<?php
// Copier ce fichier en db.php et renseigner les identifiants MySQL.
// Fuseau horaire du site (le « jour » du mot du jour doit être le même pour PHP et MySQL)
date_default_timezone_set('Europe/Paris');

$servername = "localhost";
$dbname = "site";
$user = "root";
$pass = "";

try {
    $bdd_init = new PDO("mysql:host=$servername", $user, $pass);
    $bdd_init->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $bdd_init->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $bdd = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    die("Erreur de connexion ou d'initialisation de la BDD : " . $e->getMessage());
}
?>
