<?php
/**
 * api/statistiques.php — Usage de l'API (journal api_logs), pour le tableau de bord du logiciel de gestion.
 *
 * GET ?jours=14 (clé requise) → 200 {
 *   jours, total, erreurs,
 *   par_jour:        [ { jour, appels }, … ]           (du plus ancien au plus récent, jours sans appel inclus)
 *   par_ressource:   [ { ressource, appels }, … ]      (les plus appelées d'abord)
 *   par_code:        [ { code, appels }, … ]
 *   par_application: [ { application, appels }, … ]
 * }
 * Aucune adresse IP n'est renvoyée : seulement des totaux.
 */
require_once __DIR__ . '/../includes/api.php';

exiger_methode('GET');
exiger_cle();

$jours = lire_entier('jours', 1, 90, 14);
$depuis = (new DateTime('today'))->modify('-' . ($jours - 1) . ' days')->format('Y-m-d');

$compter = function (string $colonne) use ($bdd, $depuis): array {
    $stmt = $bdd->prepare("SELECT $colonne AS cle, COUNT(*) AS appels FROM api_logs WHERE date_appel >= :depuis
                           GROUP BY $colonne ORDER BY appels DESC LIMIT 12");
    $stmt->execute(['depuis' => $depuis]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
};

// Appels par jour, en comptant aussi les jours sans appel
$stmt = $bdd->prepare('SELECT DATE(date_appel) AS jour, COUNT(*) AS appels FROM api_logs WHERE date_appel >= :depuis GROUP BY jour');
$stmt->execute(['depuis' => $depuis]);
$par_date = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'appels', 'jour');
$par_jour = [];
for ($d = new DateTime($depuis), $i = 0; $i < $jours; $i++, $d->modify('+1 day')) {
    $par_jour[] = ['jour' => $d->format('Y-m-d'), 'appels' => (int) ($par_date[$d->format('Y-m-d')] ?? 0)];
}

$renommer = fn(array $lignes, string $nom) => array_map(fn($l) => [$nom => $l['cle'], 'appels' => (int) $l['appels']], $lignes);
$par_code = $renommer($compter('code'), 'code');

repondre(200, [
    'jours' => $jours,
    'total' => array_sum(array_column($par_jour, 'appels')),
    'erreurs' => array_sum(array_map(fn($l) => $l['code'] >= 400 ? $l['appels'] : 0, $par_code)),
    'par_jour' => $par_jour,
    'par_ressource' => $renommer($compter('ressource'), 'ressource'),
    'par_code' => $par_code,
    'par_application' => $renommer($compter('application'), 'application'),
]);
