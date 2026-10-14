<?php
/**
 * Tire un conseil : une situation, une ou deux actions et un résultat au hasard.
 * Retourne ['situation' => ..., 'action' => ..., 'resultat' => ..., 'dossier' => ...]
 */
function tirer_conseil(PDO $bdd): array
{
    $stmt = $bdd->prepare('SELECT texte FROM fragments WHERE type = :type ORDER BY RAND() LIMIT :n');
    $conseil = [];
    foreach (['situation' => 1, 'action' => 2, 'resultat' => 1] as $type => $n) {
        $stmt->bindValue('type', $type);
        $stmt->bindValue('n', $n, PDO::PARAM_INT);
        $stmt->execute();
        $conseil[$type] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    // Une fois sur deux, deux actions enchaînées (« je fais ceci et je fais cela », façon Kamoulox)
    [$action1, $action2] = $conseil['action'] + [1 => null];
    $conseil['situation'] = $conseil['situation'][0];
    $conseil['resultat'] = $conseil['resultat'][0];
    $conseil['action'] = $action2 !== null && random_int(0, 1) ? "$action1, puis $action2" : $action1;
    $conseil['dossier'] = sprintf('%04d-%s', random_int(1, 9999), chr(random_int(65, 90)));
    return $conseil;
}
