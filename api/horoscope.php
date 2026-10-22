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
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/horoscope.php';

exiger_methode('GET');

$jour = lire_date_passee('Messmerde ne prédit pas l\'avenir à l\'avance.');

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
