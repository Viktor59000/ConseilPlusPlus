<?php
/**
 * api/conseils.php — API publique du générateur de conseils.
 *
 * GET            → un conseil aléatoire
 * GET ?nombre=3  → plusieurs conseils (5 maximum)
 */
require("../includes/api.php");
require("../includes/tirage.php");
require("../db.php");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    repondre(405, ['erreur' => 'Méthode non autorisée.']);
}

$nombre = filter_input(INPUT_GET, 'nombre', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5, 'default' => 1]]);

$conseils = [];
for ($i = 0; $i < $nombre; $i++) {
    $c = tirer_conseil($bdd);
    $conseils[] = [
        'dossier' => $c['dossier'],
        'conseil' => "{$c['situation']} {$c['action']} {$c['resultat']}",
        'fragments' => ['situation' => $c['situation'], 'action' => $c['action'], 'resultat' => $c['resultat']],
    ];
}

repondre(200, $nombre === 1 ? $conseils[0] : ['conseils' => $conseils]);
