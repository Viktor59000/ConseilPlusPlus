<?php
/**
 * Le Bureau — amorce des pages réservées aux agents : base, session, règles métier (dont le client de l'API),
 * aides d'affichage, puis contrôle d'accès ($agent).
 */
require_once __DIR__ . '/bdd.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/metier.php';
require_once __DIR__ . '/vues.php';

$agent = exiger_agent();
