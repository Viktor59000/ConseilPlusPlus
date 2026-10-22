<?php
/**
 * bureau/export-dossiers.php — Export CSV des dossiers affichés dans dossiers.php (mêmes filtres, POST + CSRF).
 * Format Excel : BOM UTF-8, séparateur « ; ». Les notes internes ne sont pas exportées.
 */
require_once __DIR__ . '/includes/amorce.php';

$criteres = criteres_dossiers($_POST);
exiger_post('dossiers.php?' . http_build_query(array_filter($criteres)), 'Session expirée : export annulé. Recommencez.');

$dossiers = filtrer_dossiers($bdd, $criteres, $agent['id'], 5000);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="dossiers-' . date('Y-m-d-His') . '.csv"');
header('Cache-Control: no-store');
$sortie = fopen('php://output', 'w');
fwrite($sortie, "\xEF\xBB\xBF");
fputcsv($sortie, ['N°', 'Demandeur', 'Email', 'Type', 'Message', 'Reçu le', 'Statut', 'Responsable', 'Échéance',
    'Dernier intervenant', 'Réponse le', 'Envoi', 'Retirée du site le'], ';', '"', '');
foreach ($dossiers as $d) {
    fputcsv($sortie, array_map('cellule_csv', [
        numero_dossier((int) $d['id_site']), $d['nom'], $d['email'], $d['type_demande'], $d['message'],
        date_courte($d['date_demande']), STATUTS_DOSSIER[$d['statut']], $d['responsable'] ?? '',
        $d['echeance'] ? date_echeance($d['echeance']) : '', $d['intervenant'] ?? '',
        $d['date_reponse'] ? date_courte($d['date_reponse']) : '', $d['envoi'] ?? '',
        $d['date_purge'] ? date_courte($d['date_purge']) : '',
    ]), ';', '"', '');
}
fclose($sortie);
