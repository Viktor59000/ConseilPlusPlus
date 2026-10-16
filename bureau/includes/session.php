<?php
/**
 * Le Bureau — session des agents (distincte de celle du site), jeton CSRF et contrôle d'accès.
 */
session_name('BUREAU');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();

// Jeton CSRF : mêmes fonctions que le site (csrf_jeton, csrf_champ, csrf_valide), dans la session du Bureau
require_once __DIR__ . '/../../includes/csrf.php';

/** Agent connecté (et toujours enregistré), ou redirection vers la page de connexion. */
function exiger_agent(): array
{
    global $bdd;
    $existe = false;
    if (isset($_SESSION['agent'])) {
        $stmt = $bdd->prepare('SELECT 1 FROM agents WHERE id = ?');
        $stmt->execute([$_SESSION['agent']['id']]);
        $existe = (bool) $stmt->fetchColumn();
    }
    if (!$existe) {
        unset($_SESSION['agent']);
        header('Location: connexion.php');
        exit();
    }
    return $_SESSION['agent'];
}

/** Message à afficher une seule fois sur la page suivante. */
function annoncer(string $message, string $genre = 'ok'): void
{
    $_SESSION['annonce'] = [$message, $genre];
}

function annonce(): ?array
{
    $a = $_SESSION['annonce'] ?? null;
    unset($_SESSION['annonce']);
    return $a;
}
