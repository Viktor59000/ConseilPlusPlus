<?php
/**
 * api/export.php — Sauvegarde complète de la base du site (structure + données), pour le logiciel de gestion.
 *
 * GET   (clé requise)   → 200 { base, fichier, sql }
 *
 * Clé : en-tête X-API-Key. Contient des données personnelles : ne jamais exposer sans clé.
 */
require("../includes/api.php");
require("../includes/export-sql.php");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    repondre(405, ['erreur' => 'Méthode non autorisée.']);
}
exiger_cle('admin'); // sauvegarde complète : clé maîtresse uniquement

$base = $bdd->query('SELECT DATABASE()')->fetchColumn();
repondre(200, [
    'base' => $base,
    'fichier' => "conseilplus-$base-" . date('Y-m-d-His') . '.sql',
    'sql' => export_sql($bdd, 'site vitrine'),
]);
