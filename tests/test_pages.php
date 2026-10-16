<?php
/**
 * tests/test_pages.php — Ouvre chaque page publique et vérifie le code HTTP, l'absence d'erreur PHP affichée
 * et la présence des en-têtes de sécurité envoyés par PHP (CSP).
 *
 * Usage : php tests/test_pages.php [url_de_base]   (par défaut : http://localhost/Site)
 */
$base = rtrim($argv[1] ?? 'http://localhost/Site', '/');
$pages = [
    'index.php' => 200, 'cabinet.php' => 200, 'petit-moyen.php' => 200, 'produit.php' => 200, 'guichet.php' => 200,
    'contact.php' => 200, 'login.php' => 200, 'application.php' => 200, 'plan-du-site.php' => 200,
    'accessibilite.php' => 200, 'mentions-legales.php' => 200, 'confidentialite.php' => 200,
    'mot-de-passe-oublie.php' => 200, 'reinitialiser.php' => 200, 'erreur-404.php' => 404,
    // pages réservées : redirection vers la connexion
    'conseil.php' => 302, 'carnet.php' => 302, 'compte.php' => 302,
    // API publique et API protégée
    'api/conseils.php' => 200, 'api/widget.php' => 200, 'api/export.php' => 401, 'api/contacts.php' => 401,
    'bureau/connexion.php' => 200,
];
$echecs = 0;
foreach ($pages as $page => $attendu) {
    $ch = curl_init("$base/$page");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60]);
    $reponse = (string) curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $erreur_php = preg_match('/(Fatal error|Parse error|Warning|Notice|Deprecated|Uncaught)[:<]/', $reponse);
    $html = str_ends_with($page, '.php') && !str_starts_with($page, 'api/') && $code === 200;
    $csp = !$html || stripos($reponse, 'Content-Security-Policy:') !== false;
    $ok = $code === $attendu && !$erreur_php && $csp;
    $echecs += $ok ? 0 : 1;
    printf("[%s] %-28s HTTP %d (attendu %d)%s%s\n", $ok ? 'OK' : 'ECHEC', $page, $code, $attendu,
        $erreur_php ? ' — erreur PHP affichée' : '', $csp ? '' : ' — CSP absente');
}
echo $echecs ? "\n$echecs page(s) en échec.\n" : "\nToutes les pages répondent correctement.\n";
exit($echecs ? 1 : 0);
