<?php
/**
 * api/votes.php — Votes du public sur les entrées du Petit Moyen (« Moyen » ou « Pas moyen »).
 *
 * GET  ?mots=Mirobolant,Labrador   → 200 { "Mirobolant": { moyen, pas_moyen }, … }
 * GET  ?palmares=5                 → 200 [ { mot, moyen, pas_moyen, score }, … ] (meilleurs mots, utilisé par Le Bureau)
 * POST { mot, vote }               → 200 { mot, moyen, pas_moyen } | 404 mot inconnu | 422 vote invalide
 *
 * Public, sans clé. Un vote par visiteur et par mot (modifiable) : seule une empreinte anonyme est gardée
 * (hachage de l'adresse IP, du mot et d'un secret du serveur), jamais l'adresse IP elle-même.
 * POST limité à 20 votes par minute et par visiteur.
 */
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/votes.php';

router([
    'GET' => function () use ($bdd) {
        if (isset($_GET['palmares'])) {
            $stmt = $bdd->prepare("SELECT mot, SUM(vote = 'moyen') AS moyen, SUM(vote = 'pas_moyen') AS pas_moyen,
                                          SUM(vote = 'moyen') - SUM(vote = 'pas_moyen') AS score
                                   FROM votes GROUP BY mot ORDER BY score DESC, moyen DESC LIMIT :n");
            $stmt->bindValue('n', lire_entier('palmares', 1, 20, 5), PDO::PARAM_INT);
            $stmt->execute();
            repondre(200, array_map(fn($l) => ['mot' => $l['mot'], 'moyen' => (int) $l['moyen'],
                'pas_moyen' => (int) $l['pas_moyen'], 'score' => (int) $l['score']], $stmt->fetchAll(PDO::FETCH_ASSOC)));
        }
        $mots = array_slice(array_values(array_filter(array_map('trim', explode(',', (string) ($_GET['mots'] ?? ''))),
            fn($m) => $m !== '' && mb_strlen($m) <= 80)), 0, 80);
        repondre(200, compter_votes($bdd, $mots));
    },

    'POST' => function () use ($bdd) {
        limiter(20);
        $corps = lire_corps();
        $mot = trim((string) ($corps['mot'] ?? ''));
        $vote = (string) ($corps['vote'] ?? '');
        if (!in_array($vote, CHOIX_VOTE, true) || $mot === '' || mb_strlen($mot) > 80) {
            repondre(422, ['erreur' => 'Vote invalide (« moyen » ou « pas_moyen »).']);
        }
        if (!mot_connu($bdd, $mot)) {
            repondre(404, ['erreur' => 'Ce mot n\'existe pas au Petit Moyen. Pour l\'instant.']);
        }
        $empreinte = hash('sha256', ip_client() . '|' . $mot . '|' . API_KEY);
        $bdd->prepare('INSERT INTO votes (mot, vote, empreinte) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE vote = VALUES(vote), date_vote = NOW()')
            ->execute([$mot, $vote, $empreinte]);
        repondre(200, ['mot' => $mot] + compter_votes($bdd, [$mot])[$mot]);
    },
]);
