<?php
/**
 * auth/logout.php — Déconnexion de l'utilisateur.
 *
 * Détruit la session active (suppression des variables + destruction)
 * puis redirige vers l'accueil.
 */
session_start();

unset($_SESSION['user_id']);
unset($_SESSION['prenom']);

session_destroy();

header('location: ../index.php');
exit();
?>
