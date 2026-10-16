<?php
/**
 * L'horoscope de Messmerde : tirage déterministe selon la date (même horoscope pour tout le monde dans la journée),
 * sans répéter une prédiction d'un signe à l'autre le même jour.
 */

/** L'horoscope d'un jour (AAAA-MM-JJ, aujourd'hui par défaut) : un élément par signe. */
function horoscope_du_jour(?string $jour = null): array
{
    $jour ??= date('Y-m-d');
    $banque = require __DIR__ . '/../data/horoscope.php';

    // Mélange reproductible des prédictions (générateur congruentiel initialisé par la date)
    $graine = crc32('horoscope-' . $jour);
    $suivant = function () use (&$graine): int {
        $graine = ($graine * 1103515245 + 12345) & 0x7fffffff;
        return $graine;
    };
    $predictions = $banque['predictions'];
    for ($i = count($predictions) - 1; $i > 0; $i--) {
        $j = $suivant() % ($i + 1);
        [$predictions[$i], $predictions[$j]] = [$predictions[$j], $predictions[$i]];
    }

    $resultat = [];
    foreach ($banque['signes'] as $i => $s) {
        $resultat[] = $s + [
            'prediction' => $predictions[$i % count($predictions)],
            'chiffre' => 1 + $suivant() % 9,
            'couleur' => $banque['couleurs'][$suivant() % count($banque['couleurs'])],
        ];
    }
    return $resultat;
}
