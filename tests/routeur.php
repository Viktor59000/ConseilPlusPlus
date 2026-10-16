<?php
// Routeur du serveur de test (php -S, GitHub Actions) : reproduit la règle de api/.htaccess (/api/v1/… → /api/…).
$chemin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/api/v1(?:/([a-z-]+\.php)?)?$#', $chemin, $m)) {
    $fichier = ($m[1] ?? '') ?: 'openapi.php';
    if (!is_file(__DIR__ . "/../api/$fichier")) {
        return false;
    }
    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = "/api/$fichier";
    $_SERVER['SCRIPT_FILENAME'] = realpath(__DIR__ . "/../api/$fichier");
    chdir(dirname($_SERVER['SCRIPT_FILENAME']));
    require $_SERVER['SCRIPT_FILENAME'];
    return true;
}
return false; // tout le reste : servi normalement
