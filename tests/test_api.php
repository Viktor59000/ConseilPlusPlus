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
 * Contrôle d'accès : chaque route déclarée dans data/api.php (seule source de vérité) est appelée sans clé.
 * Demandes d'autrui (en local) : deux clients provisoires, créés en base puis effacés.
 */
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/http.php';

$site = rtrim($argv[1] ?? 'http://localhost/Site', '/');
$base = "$site/api";
$cle = ['X-API-Key: ' . API_KEY];
// Écritures publiques des tests : une adresse de documentation (RFC 5737) tirée au hasard, transmise comme le fait
// contact.php, pour que la limite de débit (par IP) ne bloque pas un second passage des tests dans la minute
$ip_test = ['X-Client-IP: 198.51.100.' . random_int(1, 254)];
$echecs = 0;

function verifier(string $test, int $attendu, array $reponse): void
{
    global $echecs;
    $ok = $reponse['code'] === $attendu;
    $echecs += $ok ? 0 : 1;
    printf("[%s] %-45s HTTP %d (attendu %d)\n", $ok ? 'OK' : 'ECHEC', $test, $reponse['code'], $attendu);
}

/** Vérification libre (condition déjà calculée), avec un détail facultatif. */
function constater(string $test, bool $ok, string $detail = ''): void
{
    global $echecs;
    $echecs += $ok ? 0 : 1;
    printf("[%s] %-45s%s\n", $ok ? 'OK' : 'ECHEC', $test, $detail === '' ? '' : " $detail");
}

/** Navigateur minimal (cookies gardés d'une page à l'autre, redirections non suivies) pour les pages du site. */
function navigateur(): CurlHandle
{
    $ch = curl_init();
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 30]);
    return $ch;
}

/** Ouvre une page (GET) ou envoie un formulaire (POST, $champs) ; retourne le code, le HTML et le jeton CSRF de la page. */
function visiter(CurlHandle $ch, string $url, ?array $champs = null): array
{
    curl_setopt($ch, CURLOPT_URL, $url);
    $champs === null ? curl_setopt($ch, CURLOPT_HTTPGET, true) : curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($champs));
    $html = (string) curl_exec($ch);
    preg_match('/name="csrf" value="([0-9a-f]+)"/', $html, $m);
    return ['code' => curl_getinfo($ch, CURLINFO_HTTP_CODE), 'html' => $html, 'jeton' => $m[1] ?? ''];
}

// --- Contrôle d'accès, dérivé de data/api.php : sans clé, une route « cle » ou « admin » répond 401 (une clé
// inconnue aussi) ; une route « session » répond 401 sans connexion ; une route « public » ne répond jamais 401.
// Les écritures publiques reçoivent un corps invalide (400 ou 422 : rien n'est créé).
$inconnue = ['X-API-Key: cle-inconnue-des-tests'];
foreach (require __DIR__ . '/../data/api.php' as $routes) {
    foreach ($routes as $route) {
        $url = $base . str_replace('AAAA-MM-JJ', date('Y-m-d', strtotime('+1 day')), $route['chemin']);
        foreach (explode(' · ', $route['methode']) as $methode) {
            $corps = $methode === 'GET' ? null : [];
            $nom = "$methode {$route['chemin']}";
            if ($route['acces'] === 'public') {
                $r = appeler_api($methode, $url, $corps, $methode === 'GET' ? [] : $ip_test);
                $ok = $methode === 'GET' ? !in_array($r['code'], [0, 401], true) : in_array($r['code'], [400, 422], true);
                constater("$nom sans clé (public)", $ok, "HTTP {$r['code']}" . ($methode === 'GET' ? '' : ' (attendu 400 ou 422)'));
                continue;
            }
            verifier("$nom sans clé", 401, appeler_api($methode, $url, $corps));
            if ($route['acces'] !== 'session') {
                verifier("$nom clé inconnue", 401, appeler_api($methode, $url, $corps, $inconnue));
            }
        }
    }
}
// Route publique à usage réservé (cle_facultative) : l'envoi des notifications exige une clé
verifier('POST /notifications.php envoyer sans clé', 401, appeler_api('POST', "$base/notifications.php", ['action' => 'envoyer'], $ip_test));
verifier('POST /notifications.php envoyer clé inconnue', 401, appeler_api('POST', "$base/notifications.php", ['action' => 'envoyer'], array_merge($inconnue, $ip_test)));

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
verifier('DELETE avec clé lecture (refusé)', 403, appeler_api('DELETE', "$base/contacts.php?id=$id", null, $lecture));
verifier('POST mot-du-jour avec clé lecture (refusé)', 403, appeler_api('POST', "$base/mot-du-jour.php", null, $lecture));
verifier('POST envoyer avec clé lecture (refusé)', 403, appeler_api('POST', "$base/notifications.php", ['action' => 'envoyer'], array_merge($lecture, $ip_test)));
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

// --- « Mes demandes » (compte.php) : un client ne supprime que ses propres demandes.
// Deux clients provisoires (A et B) créés en base, avec le hachage du code du cours (auth/, sel statique + SHA-512) :
// l'inscription par le formulaire est limitée à 5 comptes par heure et par IP. Une demande par client, envoyée à l'API
// puis rattachée comme le fait contact.php (qui ne peut pas appeler sa propre API sous php -S, mono-processus).
if ($local) {
    require_once __DIR__ . '/../includes/bdd.php';
    require_once __DIR__ . '/../includes/compte.php'; // empreinte_comme_le_cours()
    $suffixe = bin2hex(random_bytes(4));
    $mdp = bin2hex(random_bytes(8));
    $clients = $demandes = [];
    try {
        foreach (['a', 'b'] as $lettre) {
            bdd()->prepare('INSERT INTO utilisateurs (prenom, nom, mail, mdp) VALUES (?, ?, ?, ?)')
                ->execute(['Test', strtoupper($lettre), "test-$lettre-$suffixe@site.test", empreinte_comme_le_cours($mdp)]);
            $clients[$lettre] = (int) bdd()->lastInsertId();
            $r = appeler_api('POST', "$base/contacts.php", ['nom' => "Test $lettre", 'email' => "test-$lettre-$suffixe@site.test",
                'type_demande' => 'Autre', 'message' => 'Demande provisoire des tests automatiques.'], $ip_test);
            $demandes[$lettre] = (int) ($r['data']['id'] ?? 0);
            bdd()->prepare('UPDATE contacts SET id_utilisateur = ? WHERE id = ?')->execute([$clients[$lettre], $demandes[$lettre]]);
        }
        constater('demandes de A et B enregistrées', $demandes['a'] > 0 && $demandes['b'] > 0);

        // Connexion de B comme dans un navigateur : page, cookie de session, jeton CSRF
        $b = navigateur();
        $page = visiter($b, "$site/login.php");
        $r = visiter($b, "$site/auth/connexion.php", ['email' => "test-b-$suffixe@site.test", 'mdp' => $mdp, 'csrf' => $page['jeton']]);
        constater('connexion du client B', $r['code'] === 302 && str_contains((string) curl_getinfo($b, CURLINFO_REDIRECT_URL), 'conseil.php'),
            "HTTP {$r['code']}");
        $page = visiter($b, "$site/compte.php");
        constater('B voit sa demande, pas celle de A', $page['code'] === 200 && str_contains($page['html'], 'value="' . $demandes['b'] . '"')
            && !str_contains($page['html'], 'value="' . $demandes['a'] . '"'));

        // B tente de supprimer la demande de A : refus affiché, la demande reste
        $r = visiter($b, "$site/compte.php", ['supprimer_demande' => $demandes['a'], 'csrf' => $page['jeton']]);
        constater('B supprime la demande de A : refusé', $r['code'] === 200 && str_contains($r['html'], 'Demande introuvable'), "HTTP {$r['code']}");
        verifier('la demande de A existe toujours', 200, appeler_api('GET', "$base/contacts.php?id={$demandes['a']}", null, $cle));
        // Témoin : B supprime bien sa propre demande (le refus ci-dessus ne vient donc pas d'un jeton invalide)
        $r = visiter($b, "$site/compte.php", ['supprimer_demande' => $demandes['b'], 'csrf' => $page['jeton']]);
        constater('B supprime sa propre demande', $r['code'] === 302, "HTTP {$r['code']}");
        verifier('la demande de B est effacée', 404, appeler_api('GET', "$base/contacts.php?id={$demandes['b']}", null, $cle));
        curl_close($b);
    } finally {
        // Nettoyage, même après un échec : demandes puis clients provisoires
        foreach ($demandes as $id_demande) {
            bdd()->prepare('DELETE FROM contacts WHERE id = ?')->execute([$id_demande]);
        }
        foreach ($clients as $id_client) {
            bdd()->prepare('DELETE FROM contacts WHERE id_utilisateur = ?')->execute([$id_client]);
            bdd()->prepare('DELETE FROM utilisateurs WHERE id = ?')->execute([$id_client]);
        }
    }
} else {
    echo "      (base distante : suppression d'une demande d'autrui non testée)\n";
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
