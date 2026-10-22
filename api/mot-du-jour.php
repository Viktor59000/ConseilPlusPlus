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
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/mot-du-jour.php';

router([
    'GET' => function () use ($bdd) {
        $jour = lire_date_passee('Le Petit Moyen ne publie pas à l\'avance.');
        if ($jour === date('Y-m-d')) {
            repondre(200, presenter_mot(mot_du_jour($bdd)));
        }
        // Jour passé : uniquement s'il a été publié (on ne réécrit pas l'histoire)
        $entree = lire_mot($bdd, $jour);
        $entree ? repondre(200, presenter_mot($entree)) : repondre(404, ['erreur' => 'Aucun mot publié ce jour-là.']);
    },

    'POST' => function () use ($bdd) {
        limiter(3);
        $demain = date('Y-m-d', strtotime('+1 day'));
        $cree = preparer_mot($bdd, $demain);
        repondre($cree ? 201 : 200, ['jour' => $demain, 'prepare' => $cree]);
    },
]);
