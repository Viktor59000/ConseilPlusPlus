<?php
// bureau/journal.php — Journal du Bureau : relevés de courrier et synchros des inscriptions (imports depuis l'API du
// site), purges du site.
require_once __DIR__ . '/includes/amorce.php';

$releves = $bdd->query('SELECT r.*, a.nom AS agent FROM releves r LEFT JOIN agents a ON a.id = r.id_agent
                        ORDER BY r.date_releve DESC LIMIT 100')->fetchAll();
$operations = ['releve' => ['Relevé du courrier', 'courrier'], 'synchro' => ['Synchro des inscriptions', 'synchro'], 'purge' => ['Purge du site', 'export']];

$titre = 'Journal';
$page = 'journal';
require __DIR__ . '/includes/haut.php';
?>
<header class="tete">
  <div class="tete__texte">
    <h1>Journal du Bureau</h1>
    <p class="tete__sous">Les 100 dernières opérations. Chaque relevé interroge l'API du site (<code>GET /api/contacts.php?statut=nouveau</code>) puis marque les demandes comme importées (<code>PATCH</code>) ;
      chaque synchro fait de même pour les rendez-vous (<code>/api/inscriptions.php</code>) ; chaque purge supprime des demandes classées (<code>DELETE</code>).</p>
  </div>
</header>

<div class="zone">
  <section class="panneau" aria-labelledby="titre-journal">
    <h2 id="titre-journal" class="visuellement-cache">Opérations</h2>
<?php if (!$releves): ?>
    <p class="vide">Aucune opération pour l'instant. Le registre est prêt ; il attend qu'il se passe quelque chose.</p>
<?php else: ?>
    <div class="defile">
      <table class="table-b">
        <thead><tr><th scope="col">Date</th><th scope="col">Opération</th><th scope="col">Agent</th><th scope="col" class="num">Nouveaux</th><th scope="col" class="num">Erreurs</th><th scope="col">Détail</th></tr></thead>
        <tbody>
<?php foreach ($releves as $r):
          [$libelle, $icone] = $operations[$r['nature']] ?? $operations['releve']; ?>
          <tr>
            <td class="mono"><?= date_courte($r['date_releve']) ?></td>
            <td><span class="operation-b"><?= picto_bureau($icone) ?><?= $libelle ?></span></td>
            <td><?= $r['agent'] ? e($r['agent']) : '—' ?></td>
            <td class="mono num"><?= $r['nature'] === 'purge' ? '—' : (int) $r['importes'] ?></td>
            <td class="mono num<?= $r['erreurs'] ? ' alerte-cellule' : '' ?>"><?= (int) $r['erreurs'] ?></td>
            <td class="muet"><?= e($r['detail']) ?></td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
