<?php
require_once __DIR__ . '/kamoulox.php';

/**
 * Tire un conseil : une situation, une ou deux actions (dont des actions Kamoulox composées) et un résultat.
 * Retourne ['situation' => ..., 'action' => ..., 'resultat' => ..., 'dossier' => ...]
 */
function tirer_conseil(PDO $bdd): array
{
    $stmt = $bdd->prepare('SELECT texte FROM fragments WHERE type = :type ORDER BY RAND() LIMIT :n');
    $conseil = [];
    foreach (['situation' => 1, 'action' => 1, 'resultat' => 1] as $type => $n) {
        $stmt->bindValue('type', $type);
        $stmt->bindValue('n', $n, PDO::PARAM_INT);
        $stmt->execute();
        $conseil[$type] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    // Début et fin : écrits à la main ou composés par gabarit, une fois sur deux chacun
    $conseil['situation'] = random_int(0, 1) ? $conseil['situation'][0] : composer_situation();
    $conseil['resultat'] = random_int(0, 1) ? $conseil['resultat'][0] : composer_resultat();

    // 1re action : écrite à la main (banque fragments) ou composée par un gabarit Kamoulox, une fois sur deux
    $gabarit = null;
    $jargon = false;
    if (random_int(0, 1)) {
        $action = $conseil['action'][0];
    } else {
        // le gabarit pseudo-savant (D) ne s'emploie qu'en seconde action, sinon la phrase devient bancale
        [$action, $gabarit, $jargon] = composer_action(['D']);
    }
    // 2e action, deux fois sur trois : « je fais ceci et je fais cela », sans aucun lien entre les deux.
    // Un seul jargon de jeu par conseil, et une virgule avant la conclusion pour éviter « et… et… ».
    // Le coup de grandeur (E) contient déjà deux actions : pas de seconde action après lui, ni en seconde position.
    if ($gabarit !== 'E' && random_int(0, 2)) {
        [$action2] = composer_action(array_filter(['E', $gabarit]), !$jargon);
        $action .= " et $action2,";
    }
    $conseil['action'] = $action;
    $conseil['dossier'] = sprintf('%04d-%s', random_int(1, 9999), chr(random_int(65, 90)));
    return $conseil;
}

/** Le conseil tiré, en une phrase. */
function texte_conseil(array $c): string
{
    return trim("{$c['situation']} {$c['action']} {$c['resultat']}");
}
