<?php
/**
 * Le Bureau — session des agents (distincte de celle du site), jeton CSRF et contrôle d'accès.
 */
session_name('BUREAU');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();

/** Jeton CSRF de la session (créé au premier appel). */
function csrf_jeton(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

/** Champ caché à placer dans chaque formulaire POST. */
function csrf_champ(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_jeton() . '">';
}

/** Vérifie le jeton envoyé par le formulaire. */
function csrf_valide(): bool
{
    $envoye = $_POST['csrf'] ?? '';
    return isset($_SESSION['csrf']) && is_string($envoye) && hash_equals($_SESSION['csrf'], $envoye);
}

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
