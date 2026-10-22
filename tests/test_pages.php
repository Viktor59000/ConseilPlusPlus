<?php
/**
 * tests/test_pages.php — Ouvre chaque page publique et vérifie le code HTTP, l'absence d'erreur PHP affichée
 * et la présence des en-têtes de sécurité envoyés par PHP (CSP).
 *
 * Usage : php tests/test_pages.php [url_de_base]   (par défaut : http://localhost/Site)
 */
$base = rtrim($argv[1] ?? 'http://localhost/Site', '/');
$pages = [
    'index.php' => 200, 'cabinet.php' => 200, 'petit-moyen.php' => 200, 'produit.php' => 200, 'guichet.php' => 301, 'messmerde.php' => 200, 'standard.php' => 200, 'diagnostic.php' => 200, 'attestation.php' => 200, 'stagiaire.php' => 200,
    'contact.php' => 200, 'login.php' => 200, 'application.php' => 200, 'plan-du-site.php' => 200,
    'accessibilite.php' => 200, 'mentions-legales.php' => 200, 'confidentialite.php' => 200,
    'mot-de-passe-oublie.php' => 200, 'developpeurs.php' => 200, 'reinitialiser.php' => 200, 'erreur-404.php' => 404,
    // pages réservées : redirection vers la connexion
    'conseil.php' => 302, 'carnet.php' => 302, 'compte.php' => 302, 'duel.php' => 302, 'bureau/statistiques.php' => 302,
    // API publique et API protégée
    'api/conseils.php' => 200, 'api/widget.php' => 200, 'api/export.php' => 401, 'api/contacts.php' => 401, 'api/statistiques.php' => 401,
    'bureau/connexion.php' => 200,
];
$echecs = 0;
$html_ok = []; // page HTML répondant 200 => son code, pour les liens morts
foreach ($pages as $page => $attendu) {
    $ch = curl_init("$base/$page");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60]);
    $reponse = (string) curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $erreur_php = preg_match('/(Fatal error|Parse error|Warning|Notice|Deprecated|Uncaught)[:<]/', $reponse);
    $html = str_ends_with($page, '.php') && !str_starts_with($page, 'api/') && $code === 200;
    $csp = !$html || stripos($reponse, 'Content-Security-Policy:') !== false;
    if ($html) {
        $html_ok[$page] = $reponse;
    }
    $ok = $code === $attendu && !$erreur_php && $csp;
    $echecs += $ok ? 0 : 1;
    printf("[%s] %-28s HTTP %d (attendu %d)%s%s\n", $ok ? 'OK' : 'ECHEC', $page, $code, $attendu,
        $erreur_php ? ' — erreur PHP affichée' : '', $csp ? '' : ' — CSP absente');
}

// Liens morts : chaque href / src local des pages HTML ci-dessus (200) doit répondre 200, 301 ou 302.
// Ignorés : ancres, mailto:, tel:, javascript:, data:, autres sites ; la requête (?…) est retirée ; chaque cible une seule fois.
$hote = parse_url($base, PHP_URL_SCHEME) . '://' . parse_url($base, PHP_URL_HOST) . (parse_url($base, PHP_URL_PORT) ? ':' . parse_url($base, PHP_URL_PORT) : '');
$cibles = []; // url => première page qui y renvoie
foreach ($html_ok as $page => $html) {
    preg_match_all('/\s(?:href|src)\s*=\s*(["\'])(.*?)\1/i', $html, $m);
    $dossier = $base . '/' . (dirname($page) === '.' ? '' : dirname($page) . '/');
    foreach ($m[2] as $lien) {
        $lien = trim(html_entity_decode($lien, ENT_QUOTES | ENT_HTML5));
        $lien = preg_replace('/[?#].*$/s', '', $lien);
        if ($lien === '' || preg_match('#^(mailto|tel|javascript|data):#i', $lien)) {
            continue;
        }
        if (preg_match('#^(https?:)?//#i', $lien)) {
            $url = str_starts_with($lien, '//') ? parse_url($base, PHP_URL_SCHEME) . ':' . $lien : $lien;
            if (!str_starts_with($url . '/', $base . '/')) {
                continue; // autre site (dont l'adresse publique, hors du site testé)
            }
        } else {
            $url = str_starts_with($lien, '/') ? $hote . $lien : $dossier . $lien;
        }
        // Normalisation des « ./ » et « ../ »
        $chemin = [];
        foreach (explode('/', (string) parse_url($url, PHP_URL_PATH)) as $segment) {
            if ($segment === '..') {
                array_pop($chemin);
            } elseif ($segment !== '.') {
                $chemin[] = $segment;
            }
        }
        $cibles[$hote . implode('/', $chemin)] ??= $page;
    }
}
$liens_morts = 0;
foreach ($cibles as $url => $page) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!in_array($code, [200, 301, 302], true)) {
        $liens_morts++;
        printf("[ECHEC] lien mort %s (HTTP %d), depuis %s\n", substr($url, strlen($hote)), $code, $page);
    }
}
printf("[%s] %d lien(s) local(aux) vérifié(s), %d mort(s)\n", $liens_morts ? 'ECHEC' : 'OK', count($cibles), $liens_morts);
$echecs += $liens_morts;

echo $echecs ? "\n$echecs page(s) en échec.\n" : "\nToutes les pages répondent correctement.\n";
exit($echecs ? 1 : 0);
