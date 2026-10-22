<?php
/**
 * bureau/purge.php — Purge des demandes sur le site (DELETE /api/contacts.php), POST + CSRF + case de confirmation.
 *   id=…   → un dossier classé (depuis sa fiche) ;
 *   lot=1  → tous les dossiers classés depuis plus de 6 mois (depuis le tableau de bord).
 * Le dossier reste au Bureau (date_purge) ; chaque purge est tracée dans le journal.
 */
require_once __DIR__ . '/includes/amorce.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$retour = $id ? 'dossier.php?id=' . $id : 'tableau.php';
exiger_post($retour);
if (($_POST['confirmer'] ?? '') !== '1') {
    rediriger($retour, 'Purge annulée : cochez la case de confirmation. Le Bureau ne supprime rien sur un malentendu.', 'erreur');
}

if ($id) {
    $stmt = $bdd->prepare("SELECT * FROM dossiers WHERE id = ? AND statut = 'classe' AND date_purge IS NULL");
    $stmt->execute([$id]);
    $dossier = $stmt->fetch();
    if (!$dossier) {
        rediriger($retour, 'Seul un dossier classé, et pas encore purgé, peut être retiré du site.', 'erreur');
    }
    $numero = numero_dossier((int) $dossier['id_site']);
    $resultat = purger_du_site($bdd, $dossier);
    $detail = match ($resultat) {
        'purgee' => "Demande n° $numero supprimée du site.",
        'absente' => "Demande n° $numero déjà absente du site.",
        default => "Demande n° $numero : purge impossible, le site ne répond pas.",
    };
    journaliser_purge($bdd, $resultat === 'erreur' ? 1 : 0, $detail, $agent['id']);
    rediriger($retour, $resultat === 'erreur' ? $detail . ' Réessayez plus tard.' : $detail . ' Le dossier reste consultable au Bureau.',
        $resultat === 'erreur' ? 'erreur' : 'ok');
}

// Purge en lot : 200 dossiers au plus par passage, pour ne pas faire attendre l'agent
$dossiers = $bdd->query('SELECT * FROM dossiers WHERE ' . SQL_PURGEABLES . ' ORDER BY date_reponse LIMIT 200')->fetchAll();
if (!$dossiers) {
    rediriger($retour, 'Aucun dossier classé depuis plus de 6 mois à purger. Le site est déjà rangé.');
}
$compte = ['purgee' => 0, 'absente' => 0, 'erreur' => 0];
foreach ($dossiers as $d) {
    $compte[purger_du_site($bdd, $d)]++;
}
$detail = "Purge en lot : {$compte['purgee']} demande(s) supprimée(s) du site"
    . ($compte['absente'] ? ", {$compte['absente']} déjà absente(s)" : '')
    . ($compte['erreur'] ? ", {$compte['erreur']} en erreur" : '') . '.';
journaliser_purge($bdd, $compte['erreur'], $detail, $agent['id']);
rediriger($retour, $detail, $compte['erreur'] && !$compte['purgee'] ? 'erreur' : 'ok');
