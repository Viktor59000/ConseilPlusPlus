<?php
// bureau/synchro.php — Synchro des inscriptions : lit par l'API les inscriptions « nouveau » du site, les range dans
// la table inscris (sans doublon, par remote_id), les marque « importe » sur le site et reporte les annulations.
require_once __DIR__ . '/includes/amorce.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exiger_post('synchro.php');
    $r = synchroniser_inscriptions($bdd, $agent['id']);
    if ($r['erreurs'] > 0 && $r['importees'] === 0 && $r['deja'] === 0) {
        rediriger('synchro.php', 'Synchro incomplète : ' . $r['detail'], 'erreur');
    }
    rediriger('synchro.php', "Synchro terminée : {$r['importees']} inscription(s) importée(s), {$r['deja']} déjà présente(s), "
        . "{$r['annulees']} annulation(s) reportée(s), {$r['erreurs']} erreur(s)."
        . ($r['erreurs'] ? ' Les inscriptions non marquées le seront à la prochaine synchro.' : ''), $r['erreurs'] ? 'erreur' : 'ok');
}

// En attente sur le site : une page d'un seul élément suffit pour lire le total
$attente = api('GET', 'inscriptions.php?statut=nouveau&page=1&par_page=1', null, true, 5);
$a_synchroniser = $attente['code'] === 200 ? (int) ($attente['data']['total'] ?? 0) : null;
$synchros = $bdd->query("SELECT r.*, a.nom AS agent FROM releves r LEFT JOIN agents a ON a.id = r.id_agent
                         WHERE r.nature = 'synchro' ORDER BY r.date_releve DESC LIMIT 15")->fetchAll();
$total_inscris = (int) $bdd->query('SELECT COUNT(*) FROM inscris')->fetchColumn();

$titre = 'Synchro';
$page = 'synchro';
require __DIR__ . '/includes/haut.php';
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Synchro</p>
    <h1>Synchroniser les inscriptions</h1>
    <p class="aide"><?= $a_synchroniser === null ? htmlspecialchars(message_erreur_api($attente)) . ' : nombre d\'inscriptions en attente inconnu.'
        : "$a_synchroniser inscription(s) en attente sur le site." ?> <?= $total_inscris ?> inscrit(s) dans la base du Bureau.</p>
  </div>
  <form method="post" action="synchro.php">
    <?= csrf_champ() ?>
    <button class="btn btn-tampon" type="submit">Lancer la synchro</button>
  </form>
</div>

<section class="carte" aria-labelledby="titre-fonctionnement">
  <h2 id="titre-fonctionnement">Ce que fait la synchro</h2>
  <ol>
    <li>Elle lit les inscriptions nouvelles du site : <code>GET /api/inscriptions.php?statut=nouveau</code> (clé de l'API).</li>
    <li>Elle les range dans la table <code>inscris</code> du Bureau, sans doublon : <code>remote_id</code> est unique.</li>
    <li>Elle les marque comme importées sur le site : <code>PATCH /api/inscriptions.php?id=…</code> avec <code>{ "statut": "importe" }</code>.</li>
    <li>Elle reporte les rendez-vous annulés par les clients sur le site (<code>GET …?statut=annule&amp;du=<?= date('Y-m-d') ?></code>).</li>
  </ol>
  <p class="aide">Une inscription dont le marquage a échoué reste « nouveau » sur le site : la synchro suivante la retrouve
    et la marque, sans la recréer. Le cabinet appelle cela de la redondance maîtrisée.</p>
</section>

<section class="carte" aria-labelledby="titre-historique">
  <h2 id="titre-historique">Dernières synchros</h2>
<?php if (!$synchros): ?>
  <p class="aide">Aucune synchro pour l'instant. Les inscriptions attendent, avec la patience des gens qui ont pris rendez-vous.</p>
<?php else: ?>
  <table class="tableau">
    <thead><tr><th>Date</th><th>Agent</th><th>Importées</th><th>Erreurs</th><th>Détail</th></tr></thead>
    <tbody>
<?php foreach ($synchros as $s): ?>
      <tr>
        <td class="mono"><?= date_courte($s['date_releve']) ?></td>
        <td><?= $s['agent'] ? htmlspecialchars($s['agent']) : '—' ?></td>
        <td class="mono"><?= (int) $s['importes'] ?></td>
        <td class="mono<?= $s['erreurs'] ? ' alerte' : '' ?>"><?= (int) $s['erreurs'] ?></td>
        <td><?= htmlspecialchars($s['detail']) ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
  <p><a class="lien-fort" href="journal.php">Tout le journal du Bureau</a></p>
<?php endif; ?>
</section>
<?php require __DIR__ . '/includes/bas.php'; ?>
