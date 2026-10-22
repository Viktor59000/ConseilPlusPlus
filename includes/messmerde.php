<?php
/**
 * Les prédictions rétroactives de Messmerde (data/retroactives.php) : une prédiction sur ce qui vient d'arriver,
 * et son certificat « PRÉVU » horodaté après coup. Rendu sans JavaScript ; js/messmerde.js en tire d'autres.
 */

/** Toutes les prédictions rétroactives : [[secondes, quand, texte], …]. */
function predictions_retroactives(): array
{
    static $banque = null;
    return $banque ??= require __DIR__ . '/../data/retroactives.php';
}

/** Une durée en toutes lettres approximatives (« 3 minutes », « 1 jour »). */
function duree_lisible(int $secondes): string
{
    foreach ([[86400, 'jour'], [3600, 'heure'], [60, 'minute'], [1, 'seconde']] as [$unite, $nom]) {
        if ($secondes >= $unite) {
            $n = intdiv($secondes, $unite);
            return $n . ' ' . $nom . ($n > 1 ? 's' : '');
        }
    }
    return '0 seconde';
}

/** Une prédiction au hasard et son certificat : déposé quelques secondes après l'appel, donc après l'événement. */
function prediction_retroactive(): array
{
    [$secondes, $quand, $texte] = predictions_retroactives()[array_rand(predictions_retroactives())];
    $zone = new DateTimeZone('Europe/Paris');
    $depot = (new DateTimeImmutable('now', $zone))->modify('+3 seconds');
    $evenement = $depot->modify('-' . ($secondes + 3) . ' seconds');
    return [
        'numero' => 'PR-' . $depot->format('Y') . '-' . random_int(1000, 9999),
        'quand' => $quand,
        'texte' => $texte,
        'evenement' => $evenement->format('d/m/Y \à H \h i'),
        'depot' => $depot->format('d/m/Y \à H \h i \m\i\n s \s'),
        'ecart' => duree_lisible($secondes + 3),
    ];
}
