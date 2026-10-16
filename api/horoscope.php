<?php
/**
 * api/horoscope.php — L'horoscope de Messmerde (une prédiction évidente par signe et par jour).
 *
 * GET                    → 200 { date, signes: [ { cle, nom, dates, prediction, chiffre, couleur } ] }
 * GET ?signe=belier      → 200 { date, cle, nom, … } | 404 signe inconnu
 * GET ?date=AAAA-MM-JJ   → l'horoscope d'un jour passé (pas de prédiction à l'avance : même Messmerde a ses limites)
 *
 * Public, sans clé.
 */
require("../includes/api.php");
require_once("../includes/horoscope.php");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    repondre(405, ['erreur' => 'Méthode non autorisée.']);
}

$jour = $_GET['date'] ?? date('Y-m-d');
$d = DateTime::createFromFormat('!Y-m-d', $jour);
if (!$d || $d->format('Y-m-d') !== $jour) {
    repondre(400, ['erreur' => 'Date invalide (format AAAA-MM-JJ).']);
}
if ($jour > date('Y-m-d')) {
    repondre(404, ['erreur' => 'Messmerde ne prédit pas l\'avenir à l\'avance.']);
}

$horoscope = horoscope_du_jour($jour);
if (isset($_GET['signe'])) {
    foreach ($horoscope as $s) {
        if ($s['cle'] === $_GET['signe']) {
            repondre(200, ['date' => $jour] + $s);
        }
    }
    repondre(404, ['erreur' => 'Signe inconnu (belier, taureau, gemeaux, cancer, lion, vierge, balance, scorpion, sagittaire, capricorne, verseau, poissons).']);
}
header('Cache-Control: public, max-age=600');
repondre(200, ['date' => $jour, 'signes' => $horoscope]);
