<?php
// Copier ce fichier en db.php et renseigner les identifiants MySQL.
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

        CREATE TABLE IF NOT EXISTS fragments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type ENUM('situation', 'action', 'resultat') NOT NULL,
            texte VARCHAR(255) NOT NULL
        );
    ");

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
