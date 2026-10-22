<?php
// Point d'entrée de la base, gardé à cet endroit pour le code du cours (auth/ fait include("../db.php")).
// Toute la logique (connexion, tables, migrations) est versionnée dans includes/bdd.php et includes/schema.php.
require_once __DIR__ . '/includes/bdd.php';
$bdd = bdd();
