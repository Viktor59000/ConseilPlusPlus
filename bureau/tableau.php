<?php
// bureau/tableau.php — Tableau de bord : chiffres du jour, relevé du courrier, dossiers à traiter.
require("includes/bdd.php");
require("includes/session.php");
require("includes/metier.php");
$agent = exiger_agent();

$compte = $bdd->query("SELECT statut, COUNT(*) AS n FROM dossiers GROUP BY statut")->fetchAll(PDO::FETCH_KEY_PAIR);
$par_type = $bdd->query("SELECT type_demande, COUNT(*) AS n FROM dossiers GROUP BY type_demande ORDER BY n DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
$a_traiter = $bdd->query("SELECT * FROM dossiers WHERE statut <> 'classe' ORDER BY date_demande LIMIT 8")->fetchAll();
$dernier = $bdd->query("SELECT * FROM releves ORDER BY date_releve DESC LIMIT 1")->fetch();
$total = array_sum($compte);

$titre = 'Tableau de bord';
$page = 'tableau';
include("includes/haut.php");
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Tableau de bord</p>
    <h1>Bonjour, <?= htmlspecialchars($agent['nom']) ?>.</h1>
    <p class="aide">Dernier relevé : <?= $dernier ? date_courte($dernier['date_releve']) . ' — ' . htmlspecialchars($dernier['detail']) : 'jamais. Le courrier s\'accumule, sereinement.' ?></p>
  </div>
  <form method="post" action="releve.php">
    <?= csrf_champ() ?>
    <button class="btn btn-tampon" type="submit">Relever le courrier</button>
  </form>
</div>

<section class="chiffres" aria-label="Chiffres du Bureau">
  <div class="chiffre"><span class="chiffre-valeur"><?= (int) ($compte['a_traiter'] ?? 0) ?></span><span class="chiffre-libelle">à traiter</span></div>
  <div class="chiffre"><span class="chiffre-valeur"><?= (int) ($compte['en_cours'] ?? 0) ?></span><span class="chiffre-libelle">en cours d'examen</span></div>
  <div class="chiffre"><span class="chiffre-valeur"><?= (int) ($compte['classe'] ?? 0) ?></span><span class="chiffre-libelle">classés</span></div>
  <div class="chiffre"><span class="chiffre-valeur"><?= $total ? round(100 * ($compte['classe'] ?? 0) / $total) : 0 ?> %</span><span class="chiffre-libelle">taux de classement (objectif : moyen)</span></div>
</section>

<div class="colonnes">
  <section class="carte">
    <h2>Dossiers en attente</h2>
<?php if (!$a_traiter): ?>
    <p class="aide">Aucun dossier en attente. Profitez-en pour ranger votre bureau, ou pas.</p>
<?php else: ?>
    <table class="tableau">
      <thead><tr><th>N°</th><th>Demandeur</th><th>Type</th><th>Reçu le</th><th>Statut</th></tr></thead>
      <tbody>
<?php foreach ($a_traiter as $d): ?>
        <tr>
          <td class="mono"><a href="dossier.php?id=<?= $d['id'] ?>"><?= sprintf('%04d', $d['id_site']) ?></a></td>
          <td><?= htmlspecialchars($d['nom']) ?></td>
          <td><?= htmlspecialchars($d['type_demande']) ?></td>
          <td class="mono"><?= date_courte($d['date_demande']) ?></td>
          <td><span class="pastille pastille-<?= $d['statut'] ?>"><?= STATUTS_DOSSIER[$d['statut']] ?></span></td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
    <p><a class="lien-fort" href="dossiers.php">Tous les dossiers</a></p>
<?php endif; ?>
  </section>

  <section class="carte">
    <h2>Répartition par type</h2>
<?php if (!$par_type): ?>
    <p class="aide">Pas encore de dossier. La répartition est donc parfaitement équilibrée.</p>
<?php else: ?>
    <ul class="barres">
<?php foreach ($par_type as $type => $n): ?>
      <li>
        <span class="barres-libelle"><?= htmlspecialchars($type) ?> <span class="mono">(<?= $n ?>)</span></span>
        <span class="barres-fond"><span class="barres-plein" style="width: <?= round(100 * $n / $total) ?>%"></span></span>
      </li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
    <p class="aide mono">Priorité de tous les dossiers : moyenne.</p>
  </section>
</div>
<?php include("includes/bas.php"); ?>
