<?php
/**
 * api/mot-du-jour.php — Le mot (presque) du jour, tiré du Petit Moyen.
 *
 * GET                    Le mot du jour                                     → 200
 * GET  ?date=2026-10-14  Le mot d'un jour passé                             → 200 | 400 | 404
 * POST                   Fait préparer par l'IA le sketch de demain (s'il n'existe pas encore)
 *                        → 201 créé | 200 déjà prêt ou IA indisponible (la banque prendra le relais)
 *
 * Public, sans clé : prévu aussi pour un futur widget téléphone. POST limité à 3 appels par minute et par IP.
 */
require("../includes/api.php");
require("../includes/mot-du-jour.php");

/** Format public d'une entrée. */
function presenter(array $e): array
{
    $base = ['jour' => $e['jour'], 'mot' => $e['mot'], 'nature' => $e['nature'], 'format' => $e['format']];
    $contenu = $e['format'] === 'sketch'
        ? ['sketch' => $e['sketch'] + ['formule' => FORMULE_FINALE]]
        : ['definition' => $e['definition'], 'exemple' => $e['exemple'], 'synonyme' => $e['synonyme']];
    return $base + $contenu + ['certitude' => (int) $e['certitude'], 'source' => $e['source']];
}

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        $jour = $_GET['date'] ?? date('Y-m-d');
        $d = DateTime::createFromFormat('!Y-m-d', $jour);
        if (!$d || $d->format('Y-m-d') !== $jour) {
            repondre(400, ['erreur' => 'Date invalide (format AAAA-MM-JJ).']);
        }
        if ($jour > date('Y-m-d')) {
            repondre(404, ['erreur' => 'Le Petit Moyen ne publie pas à l\'avance.']);
        }
        if ($jour === date('Y-m-d')) {
            repondre(200, presenter(mot_du_jour($bdd)));
        }
        // Jour passé : uniquement s'il a été publié (on ne réécrit pas l'histoire)
        $entree = lire_mot($bdd, $jour);
        $entree ? repondre(200, presenter($entree)) : repondre(404, ['erreur' => 'Aucun mot publié ce jour-là.']);

    case 'POST':
        limiter(3);
        $demain = date('Y-m-d', strtotime('+1 day'));
        $cree = preparer_mot($bdd, $demain);
        repondre($cree ? 201 : 200, ['jour' => $demain, 'prepare' => $cree]);

    default:
        header('Allow: GET, POST');
        repondre(405, ['erreur' => 'Méthode non autorisée.']);
}
