<?php
/**
 * tests/test_api.php — Teste l'API de bout en bout, comme le ferait le logiciel de gestion.
 *
 * Lancer en ligne de commande (WAMP démarré) :
 *   php tests/test_api.php [url_de_base]
 * Par défaut : http://localhost/Site
 *
 * Crée une demande de test, la lit, la passe en « importe », puis la supprime.
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
verifier("DELETE ?id=$id", 204, appeler_api('DELETE', "$base/contacts.php?id=$id", null, $cle));
verifier("DELETE ?id=$id (déjà supprimé)", 404, appeler_api('DELETE', "$base/contacts.php?id=$id", null, $cle));
verifier('PUT (méthode non autorisée)', 405, appeler_api('PUT', "$base/contacts.php"));

// --- API des conseils
$r = appeler_api('GET', "$base/conseils.php");
verifier('GET conseils.php', 200, $r);
echo "      → " . ($r['data']['conseil'] ?? '(vide)') . "\n";

echo $echecs ? "\n$echecs test(s) en échec.\n" : "\nTous les tests sont passés.\n";
exit($echecs ? 1 : 0);
