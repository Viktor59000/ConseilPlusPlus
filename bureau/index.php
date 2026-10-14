<?php
// bureau/index.php — Entrée du Bureau : tableau de bord si l'agent est connecté, sinon connexion.
require("includes/session.php");
header('Location: ' . (isset($_SESSION['agent']) ? 'tableau.php' : 'connexion.php'));
exit();
