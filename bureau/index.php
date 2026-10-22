<?php
// bureau/index.php — Entrée du Bureau : tableau de bord si l'agent est connecté, sinon connexion.
require_once __DIR__ . '/includes/session.php';
rediriger(isset($_SESSION['agent']) ? 'tableau.php' : 'connexion.php');
