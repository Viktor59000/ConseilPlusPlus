<?php
/**
 * Sauvegarde d'une base en un fichier SQL (structure + données), sans outil externe : juste PDO.
 * Utilisé par api/export.php (base du site) et bureau/export.php (base du Bureau).
 * Le fichier se réimporte dans n'importe quelle base vide : phpMyAdmin → base → Importer, ou
 * mysql -u root nom_de_la_base < fichier.sql
 */
/**
 * $filtre (facultatif) : fn(string $table, array $ligne): ?array — renvoie la ligne (éventuellement modifiée) à exporter,
 * ou null pour la laisser de côté. Sert à ne jamais sortir de secrets (empreintes de mots de passe, jetons, clé privée).
 */
function export_sql(PDO $bdd, string $origine, ?callable $filtre = null): string
{
    $base = $bdd->query('SELECT DATABASE()')->fetchColumn();
    $sql = "-- ConseilPlus+ : sauvegarde de la base « $base » ($origine)\n"
        . '-- Faite le ' . date('d/m/Y à H:i') . "\n"
        . "-- Contient des données personnelles (adresses email) : à garder hors de Git.\n\n"
        . "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tables = $bdd->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
    foreach ($tables as [$table]) {
        $creation = $bdd->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $sql .= "-- Table $table\nDROP TABLE IF EXISTS `$table`;\n$creation;\n";

        $lignes = $bdd->query("SELECT * FROM `$table`");
        $colonnes = null;
        $lot = [];
        while ($ligne = $lignes->fetch(PDO::FETCH_ASSOC)) {
            if ($filtre && ($ligne = $filtre($table, $ligne)) === null) {
                continue;
            }
            $colonnes ??= '(`' . implode('`, `', array_keys($ligne)) . '`)';
            $lot[] = '(' . implode(', ', array_map(fn($v) => valeur_sql($bdd, $v), $ligne)) . ')';
            if (count($lot) === 100) {   // insertions par paquets de 100 lignes
                $sql .= "INSERT INTO `$table` $colonnes VALUES\n" . implode(",\n", $lot) . ";\n";
                $lot = [];
            }
        }
        if ($lot) {
            $sql .= "INSERT INTO `$table` $colonnes VALUES\n" . implode(",\n", $lot) . ";\n";
        }
        $sql .= "\n";
    }
    return $sql . "SET FOREIGN_KEY_CHECKS = 1;\n";
}

/** Valeur prête pour une requête : NULL, texte échappé, ou hexadécimal pour les données binaires. */
function valeur_sql(PDO $bdd, $valeur): string
{
    if ($valeur === null) {
        return 'NULL';
    }
    $valeur = (string) $valeur;
    return mb_check_encoding($valeur, 'UTF-8') ? $bdd->quote($valeur) : '0x' . bin2hex($valeur);
}
