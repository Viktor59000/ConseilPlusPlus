<?php
/**
 * bureau/export.php — Téléchargement d'une sauvegarde SQL, depuis le tableau de bord (POST + CSRF).
 *   base=gestion → la base du Bureau, exportée directement
 *   base=site    → la base du site, demandée à son API (api/export.php) : le Bureau ne lit jamais la base du site
 */
require_once __DIR__ . '/includes/amorce.php';
require_once __DIR__ . '/../includes/export-sql.php';

exiger_post('tableau.php', 'Session expirée : sauvegarde annulée. Recommencez.');

if (($_POST['base'] ?? '') === 'site') {
    $r = api('GET', 'export.php');
    if ($r['code'] !== 200 || !isset($r['data']['sql'])) {
        rediriger('tableau.php', message_erreur_api($r) . ($r['code'] === 401 ? ' : sauvegarde impossible.' : ' : réessayez dans une minute.'), 'erreur');
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
