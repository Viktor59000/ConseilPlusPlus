<?php
/**
 * tests/test_api.php — Teste l'API de bout en bout, comme le ferait le logiciel de gestion.
 *
 * Lancer en ligne de commande (WAMP démarré) :
 *   php tests/test_api.php [url_de_base]
 * Par défaut : http://localhost/Site
 *
 * Crée une demande de test, la lit, la passe en « importe » puis « repondu », puis la supprime.
 * Modération du mot de demain : en local (base accessible), le mot de demain est sauvegardé avant les tests
 * (ou un mot provisoire est créé) puis remis exactement dans son état d'origine.
 */
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/http.php';

$base = rtrim($argv[1] ?? 'http://localhost/Site', '/') . '/api';
$cle = ['X-API-Key: ' . API_KEY];
$echecs = 0;

function verifier(string $test, int $attendu, array $reponse): void
{
    global $echecs;
    $ok = $reponse['code'] === $attendu;
    $echecs += $ok ? 0 : 1;
    printf("[%s] %-45s HTTP %d (attendu %d)\n", $ok ? 'OK' : 'ECHEC', $test, $reponse['code'], $attendu);
}

// --- Site vitrine → API
$demande = ['nom' => 'Test API', 'email' => 'test-api@site.test', 'type_demande' => 'Réclamation', 'message' => 'Mon grille-pain me juge.'];
$r = appeler_api('POST', "$base/contacts.php", $demande);
verifier('POST demande valide', 201, $r);
$id = $r['data']['id'] ?? 0;

verifier('POST demande invalide', 422, appeler_api('POST', "$base/contacts.php", ['nom' => '']));

// --- Logiciel de gestion → API
verifier('GET sans clé', 401, appeler_api('GET', "$base/contacts.php"));
$r = appeler_api('GET', "$base/contacts.php?statut=nouveau", null, $cle);
verifier('GET ?statut=nouveau', 200, $r);
$trouve = in_array($id, array_column($r['data']['contacts'] ?? [], 'id'));
printf("[%s] %-45s\n", $trouve ? 'OK' : 'ECHEC', "la demande $id est dans la liste");
$echecs += $trouve ? 0 : 1;

verifier("GET ?id=$id", 200, appeler_api('GET', "$base/contacts.php?id=$id", null, $cle));
verifier("PATCH ?id=$id (→ importe)", 200, appeler_api('PATCH', "$base/contacts.php?id=$id", ['statut' => 'importe'], $cle));
verifier('PATCH statut invalide', 422, appeler_api('PATCH', "$base/contacts.php?id=$id", ['statut' => 'perdu'], $cle));
verifier('PATCH ?id=999999 (inexistant)', 404, appeler_api('PATCH', "$base/contacts.php?id=999999", null, $cle));
// --- Boucle retour : le Bureau dépose la réponse du cabinet, que le client lit dans son compte
verifier('PATCH repondu sans réponse', 422, appeler_api('PATCH', "$base/contacts.php?id=$id", ['statut' => 'repondu'], $cle));
$texte_reponse = 'Le cabinet a délibéré : patientez, puis patientez encore.';
verifier("PATCH ?id=$id (→ repondu + réponse)", 200, appeler_api('PATCH', "$base/contacts.php?id=$id", ['statut' => 'repondu', 'reponse' => $texte_reponse], $cle));
$r = appeler_api('GET', "$base/contacts.php?id=$id", null, $cle);
$ok = ($r['data']['statut'] ?? '') === 'repondu' && ($r['data']['reponse'] ?? '') === $texte_reponse && !empty($r['data']['date_reponse']);
printf("[%s] %-45s\n", $ok ? 'OK' : 'ECHEC', 'statut repondu, réponse et date enregistrées');
$echecs += $ok ? 0 : 1;
// --- Pagination, tri, filtres et version
$r = appeler_api('GET', "$base/contacts.php?page=1&par_page=2&tri=-date", null, $cle);
verifier('GET paginé (page 1, 2 par page)', 200, $r);
$ok = ($r['data']['par_page'] ?? 0) === 2 && count($r['data']['contacts'] ?? []) <= 2 && ($r['data']['contacts'][0]['id'] ?? 0) == $id;
printf("[%s] %-45s\n", $ok ? 'OK' : 'ECHEC', 'meta de pagination, plus récente en premier');
$echecs += $ok ? 0 : 1;
$r = appeler_api('GET', "$base/contacts.php?q=" . rawurlencode('grille-pain me juge') . '&type=' . rawurlencode('Réclamation'), null, $cle);
$ok = $r['code'] === 200 && in_array($id, array_column($r['data']['contacts'] ?? [], 'id'));
printf("[%s] %-45s\n", $ok ? 'OK' : 'ECHEC', 'recherche ?q= et filtre ?type=');
$echecs += $ok ? 0 : 1;
verifier('GET par_page=500 (invalide)', 400, appeler_api('GET', "$base/contacts.php?page=1&par_page=500", null, $cle));
verifier('GET tri inconnu', 400, appeler_api('GET', "$base/contacts.php?tri=hasard", null, $cle));
verifier("GET /v1/contacts.php?id=$id", 200, appeler_api('GET', "$base/v1/contacts.php?id=$id", null, $cle));
$r = appeler_api('GET', "$base/v1/");
verifier('GET /v1/ (description OpenAPI)', 200, $r);
printf("[%s] %-45s\n", ($r['data']['openapi'] ?? '') === '3.1.0' ? 'OK' : 'ECHEC', 'OpenAPI 3.1.0');
$echecs += ($r['data']['openapi'] ?? '') === '3.1.0' ? 0 : 1;

// --- Clés par application (droits lecture / ecriture)
$r = appeler_api('POST', "$base/cles.php", ['nom' => 'Test API', 'droits' => ['lecture']], $cle);
verifier('POST cles.php (clé lecture seule)', 201, $r);
[$id_cle, $lecture] = [$r['data']['id'] ?? 0, ['X-API-Key: ' . ($r['data']['cle'] ?? 'absente')]];
verifier('POST cles.php nom réservé', 422, appeler_api('POST', "$base/cles.php", ['nom' => 'gestion'], $cle));
verifier('GET contacts avec clé lecture', 200, appeler_api('GET', "$base/contacts.php?id=$id", null, $lecture));
verifier('PATCH avec clé lecture (refusé)', 403, appeler_api('PATCH', "$base/contacts.php?id=$id", null, $lecture));
verifier('GET cles.php avec clé lecture (refusé)', 403, appeler_api('GET', "$base/cles.php", null, $lecture));
verifier('GET export.php avec clé lecture (refusé)', 403, appeler_api('GET', "$base/export.php", null, $lecture));

// --- Modération du mot de demain (Le Bureau) : relire, corriger, rejeter ; un jour publié ne se réécrit pas
date_default_timezone_set('Europe/Paris'); // même « demain » que le site
$demain = date('Y-m-d', strtotime('+1 day'));
$mot_api = "$base/mot-du-jour.php?date=$demain";
verifier('GET mot de demain sans clé', 404, appeler_api('GET', $mot_api));
verifier('PATCH mot du jour (déjà publié)', 409, appeler_api('PATCH', "$base/mot-du-jour.php?date=" . date('Y-m-d'), ['etymologie' => ''], $cle));
verifier('DELETE mot d\'hier (déjà publié)', 409, appeler_api('DELETE', "$base/mot-du-jour.php?date=" . date('Y-m-d', strtotime('-1 day')), null, $cle));
verifier('PATCH mot de demain sans clé', 401, appeler_api('PATCH', $mot_api, ['etymologie' => '']));
verifier('PATCH mot de demain, clé lecture', 403, appeler_api('PATCH', $mot_api, ['etymologie' => ''], $lecture));
verifier('DELETE mot de demain, clé lecture', 403, appeler_api('DELETE', $mot_api, null, $lecture));
$local = (bool) preg_match('#^https?://(localhost|127\.0\.0\.1)[:/]#', $base);
if ($local) {
    // Base du site accessible : sauvegarde du mot de demain (ou mot provisoire), restauré à la fin
    require_once __DIR__ . '/../includes/bdd.php';
    $lire = bdd()->prepare('SELECT * FROM mots_du_jour WHERE jour = ?');
    $lire->execute([$demain]);
    $sauvegarde = $lire->fetch(PDO::FETCH_ASSOC) ?: null;
    if (!$sauvegarde) {
        bdd()->prepare("INSERT INTO mots_du_jour (jour, mot, nature, format, definition, exemple, synonyme, certitude, source)
                        VALUES (?, 'Provisoirement', 'adv.', 'definition', 'Mot créé par les tests, effacé aussitôt.', 'Il fut provisoirement là.', 'Bientôt', 50, 'banque')")
            ->execute([$demain]);
    }
    verifier('GET mot de demain, clé lecture', 200, appeler_api('GET', $mot_api, null, $lecture));
    verifier('PATCH mot de demain, champ vide', 422, appeler_api('PATCH', $mot_api, ['mot' => ''], $cle));
    verifier('PATCH mot de demain, champ inconnu', 422, appeler_api('PATCH', $mot_api, ['couleur' => 'beige'], $cle));
    $etymologie = 'Du latin testus, « vérifié deux fois ». Attesté en 1802 dans un registre de Pontoise.';
    $r = appeler_api('PATCH', $mot_api, ['etymologie' => $etymologie], $cle);
    verifier('PATCH mot de demain (étymologie)', 200, $r);
    $ok = ($r['data']['etymologie'] ?? '') === $etymologie;
    printf("[%s] %-45s\n", $ok ? 'OK' : 'ECHEC', 'correction relue dans la réponse');
    $echecs += $ok ? 0 : 1;
    verifier('DELETE mot de demain (rejet)', 204, appeler_api('DELETE', $mot_api, null, $cle));
    verifier('GET mot de demain rejeté', 404, appeler_api('GET', $mot_api, null, $lecture));
    verifier('DELETE mot de demain (déjà rejeté)', 404, appeler_api('DELETE', $mot_api, null, $cle));
    // Remise en état : le mot de demain redevient exactement celui d'avant les tests
    bdd()->prepare('DELETE FROM mots_du_jour WHERE jour = ?')->execute([$demain]);
    if ($sauvegarde) {
        $colonnes = array_keys($sauvegarde);
        bdd()->prepare('INSERT INTO mots_du_jour (' . implode(', ', $colonnes) . ') VALUES (' . implode(', ', array_fill(0, count($colonnes), '?')) . ')')
            ->execute(array_values($sauvegarde));
    }
} else {
    echo "      (base distante : relecture, correction et rejet du mot de demain non testés)\n";
}
verifier('DELETE cles.php (révocation)', 204, appeler_api('DELETE', "$base/cles.php?id=$id_cle", null, $cle));
verifier('GET avec clé révoquée', 401, appeler_api('GET', "$base/contacts.php?id=$id", null, $lecture));
verifier('DELETE cles.php (effacement)', 204, appeler_api('DELETE', "$base/cles.php?id=$id_cle", null, $cle));

verifier("DELETE ?id=$id", 204, appeler_api('DELETE', "$base/contacts.php?id=$id", null, $cle));
verifier("DELETE ?id=$id (déjà supprimé)", 404, appeler_api('DELETE', "$base/contacts.php?id=$id", null, $cle));
verifier('PUT (méthode non autorisée)', 405, appeler_api('PUT', "$base/contacts.php"));

// --- API des conseils
$r = appeler_api('GET', "$base/conseils.php");
verifier('GET conseils.php', 200, $r);
echo "      → " . ($r['data']['conseil'] ?? '(vide)') . "\n";

echo $echecs ? "\n$echecs test(s) en échec.\n" : "\nTous les tests sont passés.\n";
exit($echecs ? 1 : 0);
