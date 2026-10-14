<?php
// bureau/releve.php — « Relever le courrier » : importe les nouvelles demandes du site via l'API (POST uniquement).
require("includes/bdd.php");
require("includes/session.php");
require("includes/metier.php");
$agent = exiger_agent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valide()) {
    header('Location: tableau.php');
    exit();
}

$r = relever_courrier($bdd, $agent['id']);
if ($r['erreurs'] > 0 && $r['importes'] === 0) {
    annoncer('Relevé incomplet : ' . $r['detail'], 'erreur');
} elseif ($r['importes'] === 0) {
    annoncer('Courrier relevé. ' . $r['detail'] . ' Aucun nouveau dossier.');
} else {
    annoncer("Courrier relevé : {$r['importes']} nouveau(x) dossier(s) ouvert(s)."
        . ($r['erreurs'] ? " {$r['erreurs']} demande(s) à re-marquer au prochain relevé." : ''));
}
header('Location: tableau.php');
exit();
