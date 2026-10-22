<?php
/**
 * Connexion à la base du site (chargée par db.php). Une seule connexion par requête, même si db.php est inclus plusieurs fois.
 * Identifiants : variables d'environnement DB_* (Render, CI), sinon valeurs WAMP par défaut (root sans mot de passe).
 * Les tables sont créées ou migrées automatiquement quand le schéma change (includes/schema.php).
 */

// Fuseau horaire du site (le « jour » du mot du jour doit être le même pour PHP et MySQL)
date_default_timezone_set('Europe/Paris');

function bdd(): PDO
{
    static $bdd = null;
    if (!$bdd) {
        try {
            $bdd = connecter_bdd();
        } catch (PDOException $e) {
            base_indisponible($e);
        }
    }
    return $bdd;
}

function connecter_bdd(): PDO
{
    $hote = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '3306';
    $base = getenv('DB_NAME') ?: 'site';
    $utilisateur = getenv('DB_USER') ?: 'root';
    $mdp = getenv('DB_PASS') ?: '';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // MySQL suit le même fuseau que PHP (un serveur distant est souvent en UTC)
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . date('P') . "'",
    ];
    // Base distante : connexion chiffrée, vérifiée avec un certificat (chemin depuis la racine du site, ou absolu :
    // /etc/ssl/certs/ca-certificates.crt pour TiDB Cloud)
    if ($ca = getenv('DB_SSL_CA')) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = str_starts_with($ca, '/') ? $ca : __DIR__ . '/../' . $ca;
    }

    try {
        $bdd = new PDO("mysql:host=$hote;port=$port;dbname=$base;charset=utf8mb4", $utilisateur, $mdp, $options);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? 0) !== 1049) { // 1049 : base inconnue (premier lancement en local)
            throw $e;
        }
        (new PDO("mysql:host=$hote;port=$port", $utilisateur, $mdp, $options))
            ->exec("CREATE DATABASE IF NOT EXISTS `$base` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $bdd = new PDO("mysql:host=$hote;port=$port;dbname=$base;charset=utf8mb4", $utilisateur, $mdp, $options);
    }

    // Schéma à jour ? Une seule petite requête par page ; l'installation complète ne tourne qu'après un changement.
    $empreinte = md5_file(__DIR__ . '/schema.php') . md5_file(__DIR__ . '/../data/fragments.php');
    try {
        $actuelle = $bdd->query("SELECT valeur FROM reglages WHERE cle = 'schema'")->fetchColumn();
    } catch (PDOException $e) {
        $actuelle = false; // table reglages absente : base neuve
    }
    if ($actuelle !== $empreinte) {
        require_once __DIR__ . '/schema.php';
        installer_schema($bdd);
        $bdd->prepare("REPLACE INTO reglages (cle, valeur) VALUES ('schema', ?)")->execute([$empreinte]);
    }
    return $bdd;
}

/** Base injoignable : page « Archives momentanément égarées » (ou JSON pour l'API) en 503, détail technique dans les journaux seulement. */
function base_indisponible(PDOException $e): never
{
    // Le détail (adresse du serveur, message MySQL) va dans les journaux, jamais dans la page publique
    error_log('ConseilPlus+ : base de données indisponible (' . $e->getMessage() . ')');
    http_response_code(503); // « service indisponible » : visible des outils de surveillance, contrairement à un 200
    header('Retry-After: 120');
    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['erreur' => 'Service momentanément indisponible. Réessayez dans quelques minutes.'], JSON_UNESCAPED_UNICODE));
    }
    header('Content-Type: text/html; charset=utf-8');
    exit('<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Archives égarées — ConseilPlus+</title><style>:root{color-scheme:light dark}body{margin:0;min-height:100vh;display:grid;'
        . 'place-items:center;background:#F5F6FA;color:#1B2440;font:17px/1.6 Arial,Helvetica,sans-serif}'
        . 'main{max-width:32rem;margin:24px;padding:32px;background:#FFF;border-radius:20px}h1{margin:0 0 12px;font-size:1.6rem;letter-spacing:-.02em}'
        . 'p{margin:0 0 10px}.mono{font-family:ui-monospace,monospace;font-size:.85rem;color:#525A73}'
        . '@media (prefers-color-scheme:dark){body{background:#0E1220;color:#F3F1EC}main{background:#171C2E}.mono{color:#A9AFC3}}</style></head>'
        . '<body><main><h1>Archives momentanément égarées</h1><p>Nos archives sont indisponibles. Le cabinet ne voit pas '
        . 'd\'autre explication.</p><p>Le classeur a été prêté à un autre service, qui ne se souvient plus lequel. '
        . 'Réessayez dans quelques minutes : il revient généralement par le courrier interne.</p>'
        . '<p class="mono">Erreur 503 — service indisponible</p></main></body></html>');
}
