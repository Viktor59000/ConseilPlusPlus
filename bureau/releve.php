<?php
// bureau/releve.php — « Relever le courrier » : importe les nouvelles demandes du site via l'API (POST uniquement).
require_once __DIR__ . '/includes/amorce.php';

exiger_post('tableau.php', null);

$r = relever_courrier($bdd, $agent['id']);
if ($r['erreurs'] > 0 && $r['importes'] === 0) {
    rediriger('tableau.php', 'Relevé incomplet : ' . $r['detail'], 'erreur');
}
rediriger('tableau.php', $r['importes'] === 0
    ? 'Courrier relevé. ' . $r['detail'] . ' Aucun nouveau dossier.'
    : "Courrier relevé : {$r['importes']} nouveau(x) dossier(s) ouvert(s)."
        . ($r['erreurs'] ? " {$r['erreurs']} demande(s) à re-marquer au prochain relevé." : ''));
