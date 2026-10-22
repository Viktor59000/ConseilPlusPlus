<?php
/**
 * api/mot-du-jour.php — Le mot (presque) du jour, tiré du Petit Moyen.
 *
 * GET                    Le mot du jour                                     → 200
 * GET  ?date=2026-10-14  Le mot d'un jour passé                             → 200 | 400 | 404
 * GET  ?date=<demain>    Relecture du mot de demain (clé « lecture »)       → 200 | 401 | 403 | 404
 * POST                   Fait préparer par l'IA le sketch de demain (s'il n'existe pas encore)
 *                        → 201 créé | 200 déjà prêt ou IA indisponible (la banque prendra le relais)
 * PATCH  ?date=<demain>  Corrige les textes du mot de demain (clé « ecriture ») → 200 | 404 | 409 | 422
 * DELETE ?date=<demain>  Rejette le mot de demain (clé « ecriture »)        → 204 | 404 | 409
 *
 * GET public, sans clé (widget téléphone) : sans clé, on ne publie pas à l'avance (404 pour demain).
 * Modération : seul le mot de DEMAIN se relit, se corrige ou se rejette ; un jour publié ne se réécrit pas (409).
 * POST : clé avec le droit « ecriture » (il déclenche l'IA), 3 appels par minute.
 */
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/mot-du-jour.php';

$demain = date('Y-m-d', strtotime('+1 day'));

// PATCH et DELETE : ?date= (demain par défaut) doit être demain ; l'entrée doit exister
$entree_de_demain = function () use ($bdd, $demain): array {
    exiger_cle('ecriture');
    $jour = $_GET['date'] ?? $demain;
    if ($jour !== $demain) {
        $d = DateTime::createFromFormat('!Y-m-d', $jour);
        if (!$d || $d->format('Y-m-d') !== $jour) {
            repondre(400, ['erreur' => 'Date invalide (format AAAA-MM-JJ).']);
        }
        $jour < $demain
            ? repondre(409, ['erreur' => 'Ce jour est déjà publié : le Petit Moyen ne réécrit pas l\'histoire. Seul le mot de demain se modère.'])
            : repondre(404, ['erreur' => 'Seul le mot de demain se modère.']);
    }
    return lire_mot($bdd, $demain) ?? repondre(404, ['erreur' => 'Aucun mot préparé pour demain : la banque choisira le jour venu.']);
};

router([
    'GET' => function () use ($bdd, $demain) {
        // Demain : relecture réservée aux clés (sans clé, même réponse qu'avant : rien n'est publié à l'avance)
        if (($_GET['date'] ?? '') === $demain && ($_SERVER['HTTP_X_API_KEY'] ?? '') !== '') {
            exiger_cle('lecture');
            $entree = lire_mot($bdd, $demain);
            $entree ? repondre(200, presenter_mot($entree))
                : repondre(404, ['erreur' => 'Aucun mot préparé pour demain : la banque choisira le jour venu.']);
        }
        $jour = lire_date_passee('Le Petit Moyen ne publie pas à l\'avance.');
        if ($jour === date('Y-m-d')) {
            repondre(200, presenter_mot(mot_du_jour($bdd)));
        }
        // Jour passé : uniquement s'il a été publié (on ne réécrit pas l'histoire)
        $entree = lire_mot($bdd, $jour);
        $entree ? repondre(200, presenter_mot($entree)) : repondre(404, ['erreur' => 'Aucun mot publié ce jour-là.']);
    },

    'POST' => function () use ($bdd, $demain) {
        exiger_cle('ecriture'); // appelé chaque nuit par GitHub Actions, avec la clé (secret du dépôt)
        limiter(3);
        $cree = preparer_mot($bdd, $demain);
        repondre($cree ? 201 : 200, ['jour' => $demain, 'prepare' => $cree]);
    },

    'PATCH' => function () use ($bdd, $entree_de_demain) {
        [$entree, $erreurs] = corriger_mot($entree_de_demain(), lire_corps());
        refuser_champs($erreurs);
        enregistrer_correction($bdd, $entree);
        repondre(200, presenter_mot(lire_mot($bdd, $entree['jour'])));
    },

    'DELETE' => function () use ($bdd, $entree_de_demain) {
        rejeter_mot($bdd, $entree_de_demain()['jour']);
        repondre(204);
    },
]);
