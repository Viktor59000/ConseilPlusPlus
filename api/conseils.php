<?php
/**
 * api/conseils.php — API publique du générateur de conseils.
 *
 * GET            → un conseil aléatoire
 * GET ?nombre=3  → plusieurs conseils (5 maximum)
 */
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/tirage.php';

exiger_methode('GET');

$nombre = lire_entier('nombre', 1, 5, 1);

$conseils = [];
for ($i = 0; $i < $nombre; $i++) {
    $c = tirer_conseil($bdd);
    $conseils[] = [
        'dossier' => $c['dossier'],
        'conseil' => texte_conseil($c),
        'fragments' => ['situation' => $c['situation'], 'action' => $c['action'], 'resultat' => $c['resultat']],
    ];
}

repondre(200, $nombre === 1 ? $conseils[0] : ['conseils' => $conseils]);
