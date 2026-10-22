<?php
/**
 * Schéma de la base du site : tables, migrations et données de départ.
 * Exécuté par bdd() seulement quand ce fichier ou data/fragments.php change (empreinte gardée dans `reglages`).
 */

function installer_schema(PDO $bdd): void
{
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
            statut ENUM('nouveau', 'importe', 'repondu') NOT NULL DEFAULT 'nouveau',
            date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            id_utilisateur INT NULL,
            reponse TEXT NULL,
            date_reponse DATETIME NULL,
            INDEX (id_utilisateur)
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
            etymologie VARCHAR(300) NULL,
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

        CREATE TABLE IF NOT EXISTS api_cles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(50) NOT NULL,
            empreinte CHAR(64) NOT NULL UNIQUE,
            apercu VARCHAR(12) NOT NULL,
            droits VARCHAR(50) NOT NULL DEFAULT 'lecture',
            date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            derniere_utilisation DATETIME NULL,
            revoquee TINYINT(1) NOT NULL DEFAULT 0
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

        CREATE TABLE IF NOT EXISTS duels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            joueur SMALLINT NOT NULL,
            cabinet SMALLINT NOT NULL,
            manches SMALLINT NOT NULL,
            victoire TINYINT(1) NOT NULL,
            date_partie DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX (victoire, manches)
        );

        CREATE TABLE IF NOT EXISTS reinitialisations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            jeton CHAR(64) NOT NULL UNIQUE,
            expire DATETIME NOT NULL,
            date_demande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
        );
    ");

    // Migrations : un test par colonne et une modification par instruction (TiDB refuse plusieurs changements liés
    // dans un même ALTER, et une migration interrompue reprend ainsi là où elle s'est arrêtée)
    $colonne = fn(string $table, string $nom) => $bdd->query("SHOW COLUMNS FROM $table LIKE '$nom'")->fetch(PDO::FETCH_ASSOC);

    // Migration : ajout du format « sketch » au mot du jour (bases créées avant cette version)
    if (!$colonne('mots_du_jour', 'format')) {
        $bdd->exec("ALTER TABLE mots_du_jour ADD COLUMN format ENUM('definition', 'sketch') NOT NULL DEFAULT 'definition' AFTER nature");
    }
    // Un sketch n'a ni définition, ni exemple, ni synonyme : ces colonnes deviennent facultatives
    foreach (['definition' => 'VARCHAR(300)', 'exemple' => 'VARCHAR(300)', 'synonyme' => 'VARCHAR(120)'] as $nom => $type) {
        if (($colonne('mots_du_jour', $nom)['Null'] ?? 'YES') === 'NO') {
            $bdd->exec("ALTER TABLE mots_du_jour MODIFY $nom $type NULL");
        }
    }
    if (!$colonne('mots_du_jour', 'sketch')) {
        $bdd->exec("ALTER TABLE mots_du_jour ADD COLUMN sketch JSON NULL AFTER synonyme");
    }

    // Migration : fausse étymologie (facultative) du mot du jour
    if (!$colonne('mots_du_jour', 'etymologie')) {
        $bdd->exec("ALTER TABLE mots_du_jour ADD COLUMN etymologie VARCHAR(300) NULL AFTER synonyme");
    }

    // Migration : boucle retour des demandes (client connecté, réponse du cabinet relayée par Le Bureau)
    if (!$colonne('contacts', 'id_utilisateur')) {
        $bdd->exec("ALTER TABLE contacts ADD COLUMN id_utilisateur INT NULL");
    }
    // Index à part : TiDB n'indexe pas une colonne créée par le même ALTER
    if (!in_array('id_utilisateur', array_column($bdd->query('SHOW INDEX FROM contacts')->fetchAll(PDO::FETCH_ASSOC), 'Column_name'), true)) {
        $bdd->exec("ALTER TABLE contacts ADD INDEX (id_utilisateur)");
    }
    if (!$colonne('contacts', 'reponse')) {
        $bdd->exec("ALTER TABLE contacts ADD COLUMN reponse TEXT NULL");
    }
    if (!$colonne('contacts', 'date_reponse')) {
        $bdd->exec("ALTER TABLE contacts ADD COLUMN date_reponse DATETIME NULL");
    }
    if (!str_contains($colonne('contacts', 'statut')['Type'], 'repondu')) {
        $bdd->exec("ALTER TABLE contacts MODIFY statut ENUM('nouveau', 'importe', 'repondu') NOT NULL DEFAULT 'nouveau'");
    }

    // Recharge la table fragments depuis data/fragments.php. Pas de comparaison du nombre de lignes : cette fonction ne
    // tourne que si le fichier a changé, et un texte réécrit à nombre égal doit aussi arriver en base.
    $fragments = require __DIR__ . '/../data/fragments.php';
    $bdd->exec("DELETE FROM fragments");
    $stmt = $bdd->prepare("INSERT INTO fragments (type, texte) VALUES (:type, :texte)");
    foreach ($fragments as $type => $textes) {
        foreach ($textes as $texte) {
            $stmt->execute(['type' => $type, 'texte' => $texte]);
        }
    }
}
