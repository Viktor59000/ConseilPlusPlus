<?php
/**
 * bureau/export.php — Téléchargement d'une sauvegarde SQL, depuis le tableau de bord (POST + CSRF).
 *   base=gestion → la base du Bureau, exportée directement
 *   base=site    → la base du site, demandée à son API (api/export.php) : le Bureau ne lit jamais la base du site
 */
require("includes/bdd.php");
require("includes/session.php");
require("includes/api.php");
require("../includes/export-sql.php");
$agent = exiger_agent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valide()) {
    annoncer('Session expirée : sauvegarde annulée. Recommencez.', 'erreur');
    header('Location: tableau.php');
    exit();
}

if (($_POST['base'] ?? '') === 'site') {
    $r = api('GET', 'export.php');
    if ($r['code'] !== 200 || !isset($r['data']['sql'])) {
        annoncer($r['code'] === 401 ? 'Clé d\'API refusée par le site : sauvegarde impossible.'
            : 'Le site ne répond pas (code ' . $r['code'] . ') : réessayez dans une minute.', 'erreur');
        header('Location: tableau.php');
        exit();
    }
    $fichier = $r['data']['fichier'];
    $sql = $r['data']['sql'];
} else {
    $base = $bdd->query('SELECT DATABASE()')->fetchColumn();
    $fichier = "conseilplus-$base-" . date('Y-m-d-His') . '.sql';
    $sql = export_sql($bdd, 'Le Bureau, exporté par ' . $agent['nom']);
}

header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9._-]/i', '-', $fichier) . '"');
header('Content-Length: ' . strlen($sql));
header('Cache-Control: no-store');
echo $sql;
