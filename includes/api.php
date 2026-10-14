<?php
/**
 * Outils communs aux endpoints de l'API (dossier api/).
 * Chaque échange est journalisé dans la table api_logs (date, méthode, ressource, code, IP, application).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

/** Envoie une réponse JSON avec le code HTTP donné, journalise l'échange et arrête le script. */
function repondre(int $code, $donnees = null): void
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

/** Bloque la requête si l'en-tête X-API-Key est absent ou incorrect. */
function exiger_cle(): void
{
    $cle = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if (!hash_equals(API_KEY, $cle)) {
        repondre(401, ['erreur' => 'Clé API manquante ou invalide (en-tête X-API-Key).']);
    }
}

/** Récupère un paramètre ?id= entier positif, sinon erreur 400. */
function lire_id(): int
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        repondre(400, ['erreur' => 'Paramètre id manquant ou invalide.']);
    }
    return $id;
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

/** Qui appelle : le logiciel de gestion (clé valide), le site lui-même (appel local) ou le public. */
function application_appelante(): string
{
    if (hash_equals(API_KEY, $_SERVER['HTTP_X_API_KEY'] ?? '')) {
        return 'gestion';
    }
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true) ? 'site' : 'public';
}

/** Enregistre l'échange dans le journal (un échec de journalisation ne bloque jamais la réponse). */
function journaliser(int $code): void
{
    global $bdd;
    try {
        $stmt = $bdd->prepare('INSERT INTO api_logs (methode, ressource, code, ip, application) VALUES (:methode, :ressource, :code, :ip, :application)');
        $stmt->execute([
            'methode' => $_SERVER['REQUEST_METHOD'] ?? '?',
            'ressource' => basename($_SERVER['SCRIPT_NAME'], '.php'),
            'code' => $code,
            'ip' => ip_client(),
            'application' => application_appelante(),
        ]);
    } catch (PDOException $e) {
        error_log('ConseilPlus+ : journalisation API impossible (' . $e->getMessage() . ')');
    }
}

/** Refuse la requête (429) si cette IP a déjà fait $max appels à cette ressource dans la dernière minute. */
function limiter(int $max): void
{
    global $bdd;
    $stmt = $bdd->prepare('SELECT COUNT(*) FROM api_logs WHERE ip = :ip AND ressource = :ressource AND methode = :methode AND date_appel > NOW() - INTERVAL 1 MINUTE');
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
