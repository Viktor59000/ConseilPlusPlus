<?php
// bureau/deconnexion.php — Fin de service (POST + jeton CSRF : un lien piégé ne peut pas déconnecter l'agent).
require("includes/session.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valide()) {
    $_SESSION = [];
    session_destroy();
}
header('Location: connexion.php');
exit();
