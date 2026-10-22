<?php
/**
 * Outils communs aux endpoints de l'API (dossier api/).
 * Chaque échange est journalisé dans la table api_logs (date, méthode, ressource, code, IP, application).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/bdd.php';

// Application de la clé maîtresse (celle du Bureau) : nom réservé, refusé pour une clé d'application
const APPLICATION_GESTION = 'gestion';

$bdd = bdd(); // connexion partagée par les endpoints
header('Content-Type: application/json; charset=utf-8');

/** Envoie une réponse JSON avec le code HTTP donné, journalise l'échange et arrête le script. */
function repondre(int $code, $donnees = null): never
{
    journaliser($code);
    http_response_code($code);
    if ($donnees !== null) {
        echo json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
    exit();
}

/** Lit le corps de la requête : JSON (php://input) ou formulaire classique ($_POST). */
function lire_corps(): array
{
    $type = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_starts_with($type, 'application/json')) {
        $donnees = json_decode(file_get_contents('php://input'), true);
        if (!is_array($donnees)) {
            repondre(400, ['erreur' => 'Corps JSON invalide.']);
        }
        return $donnees;
    }
    return $_POST;
}

/** Refuse la requête (405) si sa méthode n'est pas permise, en annonçant les méthodes permises (en-tête Allow). */
function exiger_methode(string ...$permises): void
{
    if (!in_array($_SERVER['REQUEST_METHOD'], $permises, true)) {
        header('Allow: ' . implode(', ', $permises));
        repondre(405, ['erreur' => 'Méthode non autorisée.']);
    }
}

/** Exécute l'action prévue pour la méthode de la requête (['GET' => fn() => …, 'POST' => …]), sinon 405. */
function router(array $actions): never
{
    exiger_methode(...array_keys($actions));
    $actions[$_SERVER['REQUEST_METHOD']]();
    exit(); // chaque action se termine par repondre()
}

/** Refuse la requête (422) s'il y a des erreurs de saisie ($erreurs : champ => message). */
function refuser_champs(array $erreurs): void
{
    if ($erreurs) {
        repondre(422, ['erreur' => 'Champs invalides.', 'champs' => $erreurs]);
    }
}

/**
 * Identifie la clé de l'en-tête X-API-Key :
 * - la clé maîtresse (API_KEY, celle du Bureau) a tous les droits, y compris « admin » (gérer les clés) ;
 * - une clé d'application (table api_cles, stockée hachée) a les droits « lecture » et/ou « ecriture ».
 * Retourne ['nom' => application, 'droits' => [...]] ou null si la clé est absente, inconnue ou révoquée.
 */
function cle_presentee(): ?array
{
    static $resultat = false;
    if ($resultat !== false) {
        return $resultat;
    }
    $cle = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if ($cle === '') {
        return $resultat = null;
    }
    if (hash_equals(API_KEY, $cle)) {
        return $resultat = ['nom' => APPLICATION_GESTION, 'droits' => ['lecture', 'ecriture', 'admin']];
    }
    $bdd = bdd();
    try {
        $stmt = $bdd->prepare('SELECT id, nom, droits FROM api_cles WHERE empreinte = :empreinte AND revoquee = 0');
        $stmt->execute(['empreinte' => hash('sha256', $cle)]);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $ligne = false;
    }
    if (!$ligne) {
        return $resultat = null;
    }
    $bdd->prepare('UPDATE api_cles SET derniere_utilisation = NOW() WHERE id = :id')->execute(['id' => $ligne['id']]);
    return $resultat = ['nom' => $ligne['nom'], 'droits' => explode(',', $ligne['droits'])];
}

/** Bloque la requête si la clé est absente ou invalide (401), ou si elle n'a pas le droit demandé (403). */
function exiger_cle(string $droit = 'lecture'): void
{
    $cle = cle_presentee();
    if ($cle === null) {
        repondre(401, ['erreur' => 'Clé API manquante ou invalide (en-tête X-API-Key).']);
    }
    if (!in_array($droit, $cle['droits'], true)) {
        repondre(403, ['erreur' => "Cette clé n'a pas le droit « $droit »."]);
    }
}

/** Paramètre d'URL entier compris entre $min et $max, ou $defaut s'il est absent ou invalide. */
function lire_entier(string $nom, int $min = 1, int $max = PHP_INT_MAX, ?int $defaut = null): ?int
{
    $valeur = filter_input(INPUT_GET, $nom, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
    return is_int($valeur) ? $valeur : $defaut;
}

/** Récupère un paramètre ?id= entier positif, sinon erreur 400. */
function lire_id(): int
{
    return lire_entier('id') ?? repondre(400, ['erreur' => 'Paramètre id manquant ou invalide.']);
}

/** Paramètre ?date=AAAA-MM-JJ (aujourd'hui par défaut) : 400 si mal formé, 404 ($avenir) si elle est future. */
function lire_date_passee(string $avenir): string
{
    $jour = $_GET['date'] ?? date('Y-m-d');
    $d = DateTime::createFromFormat('!Y-m-d', $jour);
    if (!$d || $d->format('Y-m-d') !== $jour) {
        repondre(400, ['erreur' => 'Date invalide (format AAAA-MM-JJ).']);
    }
    if ($jour > date('Y-m-d')) {
        repondre(404, ['erreur' => $avenir]);
    }
    return $jour;
}

/**
 * Adresse IP du client. Quand l'appel vient du site lui-même (formulaire de contact, appel serveur),
 * l'IP du visiteur est transmise dans l'en-tête X-Client-IP ; on ne la croit que si l'appel est local.
 */
function ip_client(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $transmise = $_SERVER['HTTP_X_CLIENT_IP'] ?? '';
    if (in_array($ip, ['127.0.0.1', '::1'], true) && filter_var($transmise, FILTER_VALIDATE_IP)) {
        return $transmise;
    }
    return $ip;
}

/** Qui appelle : l'application de la clé (gestion = clé maîtresse), le site lui-même (appel local) ou le public. */
function application_appelante(): string
{
    if ($cle = cle_presentee()) {
        return mb_substr($cle['nom'], 0, 50);
    }
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true) ? 'site' : 'public';
}

/** Enregistre l'échange dans le journal (un échec de journalisation ne bloque jamais la réponse). */
function journaliser(int $code): void
{
    $bdd = bdd();
    try {
        $stmt = $bdd->prepare('INSERT INTO api_logs (methode, ressource, code, ip, application) VALUES (:methode, :ressource, :code, :ip, :application)');
        $stmt->execute([
            'methode' => $_SERVER['REQUEST_METHOD'] ?? '?',
            'ressource' => basename($_SERVER['SCRIPT_NAME'], '.php'),
            'code' => $code,
            'ip' => ip_client(),
            'application' => application_appelante(),
        ]);
        // Données personnelles (adresses IP) : le journal ne garde que 90 jours (purge de temps en temps)
        if (random_int(1, 100) === 1) {
            $bdd->exec('DELETE FROM api_logs WHERE date_appel < NOW() - INTERVAL 90 DAY');
        }
    } catch (PDOException $e) {
        error_log('ConseilPlus+ : journalisation API impossible (' . $e->getMessage() . ')');
    }
}

/** Refuse la requête (429) si cette IP a déjà fait $max appels à cette ressource dans la dernière minute. */
function limiter(int $max): void
{
    $stmt = bdd()->prepare('SELECT COUNT(*) FROM api_logs WHERE ip = :ip AND ressource = :ressource AND methode = :methode AND date_appel > NOW() - INTERVAL 1 MINUTE');
    $stmt->execute([
        'ip' => ip_client(),
        'ressource' => basename($_SERVER['SCRIPT_NAME'], '.php'),
        'methode' => $_SERVER['REQUEST_METHOD'],
    ]);
    if ($stmt->fetchColumn() >= $max) {
        header('Retry-After: 60');
        repondre(429, ['erreur' => 'Trop de requêtes. Réessayez dans une minute.']);
    }
}
