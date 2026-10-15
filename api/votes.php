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
require("../includes/api.php");
require_once("../includes/mot-du-jour.php");

const CHOIX_VOTE = ['moyen', 'pas_moyen'];

/** Compteurs des mots demandés (0 pour un mot sans vote). */
function compter(PDO $bdd, array $mots): array
{
    $resultat = array_fill_keys($mots, ['moyen' => 0, 'pas_moyen' => 0]);
    if (!$mots) {
        return $resultat;
    }
    $marques = implode(',', array_fill(0, count($mots), '?'));
    $stmt = $bdd->prepare("SELECT mot, vote, COUNT(*) AS n FROM votes WHERE mot IN ($marques) GROUP BY mot, vote");
    $stmt->execute($mots);
    foreach ($stmt as $ligne) {
        if (isset($resultat[$ligne['mot']])) {
            $resultat[$ligne['mot']][$ligne['vote']] = (int) $ligne['n'];
        }
    }
    return $resultat;
}

/** Vrai si le mot existe au Petit Moyen (banque ou mot du jour publié) : pas de vote sur n'importe quoi. */
function mot_connu(PDO $bdd, string $mot): bool
{
    if (in_array($mot, array_column(pages_dictionnaire(), 'mot'), true)) {
        return true;
    }
    $stmt = $bdd->prepare('SELECT 1 FROM mots_du_jour WHERE mot = ?');
    $stmt->execute([$mot]);
    return (bool) $stmt->fetchColumn();
}

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        if (isset($_GET['palmares'])) {
            $n = filter_input(INPUT_GET, 'palmares', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]) ?: 5;
            $stmt = $bdd->prepare("SELECT mot, SUM(vote = 'moyen') AS moyen, SUM(vote = 'pas_moyen') AS pas_moyen,
                                          SUM(vote = 'moyen') - SUM(vote = 'pas_moyen') AS score
                                   FROM votes GROUP BY mot ORDER BY score DESC, moyen DESC LIMIT :n");
            $stmt->bindValue('n', $n, PDO::PARAM_INT);
            $stmt->execute();
            repondre(200, array_map(fn($l) => ['mot' => $l['mot'], 'moyen' => (int) $l['moyen'],
                'pas_moyen' => (int) $l['pas_moyen'], 'score' => (int) $l['score']], $stmt->fetchAll(PDO::FETCH_ASSOC)));
        }
        $mots = array_slice(array_values(array_filter(array_map('trim', explode(',', (string) ($_GET['mots'] ?? ''))),
            fn($m) => $m !== '' && mb_strlen($m) <= 80)), 0, 80);
        repondre(200, compter($bdd, $mots));

    case 'POST':
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
        repondre(200, ['mot' => $mot] + compter($bdd, [$mot])[$mot]);

    default:
        header('Allow: GET, POST');
        repondre(405, ['erreur' => 'Méthode non autorisée.']);
}
