<?php
/**
 * Début commun des pages du site : session, protection CSRF et garde de l'espace client.
 * (Le code du cours, auth/, garde son propre session_start() et n'utilise pas ce fichier.)
 */
session_start();
require_once __DIR__ . '/csrf.php';

/** Page réservée aux clients connectés : sinon, renvoi vers l'espace client. Retourne l'identifiant du client. */
function exiger_connexion(): int
{
    if (!isset($_SESSION['user_id'])) {
        header('location: login.php');
        exit();
    }
    return (int) $_SESSION['user_id'];
}
