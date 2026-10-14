<?php
/**
 * Outils communs aux endpoints de l'API (dossier api/).
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

/** Envoie une réponse JSON avec le code HTTP donné et arrête le script. */
function repondre(int $code, $donnees = null): void
{
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
