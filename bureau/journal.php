<?php
// bureau/journal.php — Journal du Bureau : relevés de courrier (imports depuis l'API du site) et purges du site.
require_once __DIR__ . '/includes/amorce.php';

$releves = $bdd->query('SELECT r.*, a.nom AS agent FROM releves r LEFT JOIN agents a ON a.id = r.id_agent
                        ORDER BY r.date_releve DESC LIMIT 100')->fetchAll();

$titre = 'Journal des relevés';
$page = 'journal';
require __DIR__ . '/includes/haut.php';
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Traçabilité</p>
    <h1>Journal des relevés</h1>
    <p class="aide">Chaque relevé interroge l'API du site (<code>GET /api/contacts.php?statut=nouveau</code>), enregistre les nouvelles demandes, puis les marque comme importées (<code>PATCH</code>).
      Chaque purge supprime des demandes classées sur le site (<code>DELETE</code>).</p>
  </div>
</div>
<section class="carte">
<?php if (!$releves): ?>
  <p class="aide">Aucun relevé pour l'instant.</p>
<?php else: ?>
  <table class="tableau">
    <thead><tr><th>Date</th><th>Opération</th><th>Agent</th><th>Nouveaux dossiers</th><th>Erreurs</th><th>Détail</th></tr></thead>
    <tbody>
<?php foreach ($releves as $r): ?>
      <tr>
        <td class="mono"><?= date_courte($r['date_releve']) ?></td>
        <td><?= $r['nature'] === 'purge' ? 'Purge du site' : 'Relevé' ?></td>
        <td><?= $r['agent'] ? htmlspecialchars($r['agent']) : '—' ?></td>
        <td class="mono"><?= $r['nature'] === 'purge' ? '—' : (int) $r['importes'] ?></td>
        <td class="mono<?= $r['erreurs'] ? ' alerte' : '' ?>"><?= (int) $r['erreurs'] ?></td>
        <td><?= htmlspecialchars($r['detail']) ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</section>
<?php require __DIR__ . '/includes/bas.php'; ?>
