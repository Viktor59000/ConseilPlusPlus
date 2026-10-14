<?php
/**
 * api/widget.php — Données prêtes à afficher pour un widget d'écran d'accueil (KWGT sur Android, Scriptable sur iPhone).
 *
 * GET  → 200 { date, mot, nature, ligne1, ligne2, conseil, dossier }
 *   ligne1 / ligne2 : définition + exemple, ou (sketch) la chute + le découpage officiel.
 *   conseil : un conseil tiré au hasard à chaque appel.
 * GET ?conseils=12 → en plus, « conseils » : [{ texte, dossier }, …] (réserve du widget Android, 20 au plus).
 *
 * Public, sans clé. Réponse gardée 10 minutes en cache par le téléphone.
 */
require("../includes/api.php");
require_once("../includes/mot-du-jour.php");
require_once("../includes/tirage.php");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    repondre(405, ['erreur' => 'Méthode non autorisée.']);
}

$mot = mot_du_jour($bdd);
if ($mot['format'] === 'sketch' && $mot['sketch']) {
    $chute = end($mot['sketch']['repliques'])['texte'];
    $ligne1 = "« $chute »";
    $ligne2 = !empty($mot['sketch']['decoupage']) ? 'Découpage officiel : ' . $mot['sketch']['decoupage'] : FORMULE_FINALE;
} else {
    $ligne1 = $mot['definition'];
    $ligne2 = '« ' . $mot['exemple'] . ' »';
}

$conseil = tirer_conseil($bdd);
// ?conseils=N (1 à 20) : une réserve de conseils, pour que le widget en change sans attendre le réseau
$nombre = filter_input(INPUT_GET, 'conseils', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);
$reserve = [];
for ($i = 0; $i < ($nombre ?: 0); $i++) {
    $c = tirer_conseil($bdd);
    $reserve[] = ['texte' => trim($c['situation'] . ' ' . $c['action'] . ' ' . $c['resultat']), 'dossier' => $c['dossier']];
}
$date = (new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', null, 'EEEE d MMMM'))->format(new DateTime($mot['jour']));

header('Cache-Control: public, max-age=600');
repondre(200, [
    'date' => mb_strtoupper(mb_substr($date, 0, 1)) . mb_substr($date, 1),
    'mot' => $mot['mot'],
    'nature' => $mot['nature'],
    'ligne1' => $ligne1,
    'ligne2' => $ligne2,
    'conseil' => trim($conseil['situation'] . ' ' . $conseil['action'] . ' ' . $conseil['resultat']),
    'dossier' => $conseil['dossier'],
] + ($reserve ? ['conseils' => $reserve] : []));
