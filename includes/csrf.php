<?php
/**
 * Protection CSRF : chaque formulaire envoie un jeton secret lié à la session.
 * Un site tiers ne peut pas le connaître, donc ne peut pas soumettre le formulaire à la place de l'utilisateur.
 * (La session doit être démarrée avant d'inclure ce fichier.)
 */

/** Jeton de la session (créé au premier appel). */
function csrf_jeton(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

/** Champ caché à placer dans chaque formulaire POST. */
function csrf_champ(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_jeton() . '">';
}

/** Vérifie le jeton envoyé (champ « csrf » ou en-tête X-CSRF-Token pour les appels JavaScript). */
function csrf_valide(): bool
{
    $envoye = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return isset($_SESSION['csrf']) && is_string($envoye) && hash_equals($_SESSION['csrf'], $envoye);
}
