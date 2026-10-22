<?php
// bureau/dossiers.php — Liste des dossiers : filtres (statut, type, assignés à moi, en retard), recherche dans le nom,
// l'email ou le message, tri par date ou par échéance, export CSV de la sélection.
require_once __DIR__ . '/includes/amorce.php';

$criteres = criteres_dossiers($_GET);
$dossiers = filtrer_dossiers($bdd, $criteres, $agent['id']);
$types = $bdd->query('SELECT DISTINCT type_demande FROM dossiers ORDER BY type_demande')->fetchAll(PDO::FETCH_COLUMN);

$titre = 'Dossiers';
$page = 'dossiers';
require __DIR__ . '/includes/haut.php';
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Registre</p>
    <h1>Dossiers</h1>
    <p class="aide"><?= count($dossiers) ?> dossier(s) affiché(s). Classement <?= $criteres['tri'] === 'echeance'
        ? 'par échéance, la plus proche d\'abord (sans échéance à la fin)' : 'par date de réception, du plus récent au plus ancien' ?>.</p>
  </div>
  <form method="post" action="export-dossiers.php">
    <?= csrf_champ() ?>
<?php foreach ($criteres as $cle => $valeur): ?>
    <input type="hidden" name="<?= $cle ?>" value="<?= htmlspecialchars($valeur) ?>">
<?php endforeach; ?>
    <button class="btn btn-clair" type="submit">Exporter en CSV</button>
  </form>
</div>

<form class="filtres carte" method="get">
  <div>
    <label for="statut">Statut</label>
    <select id="statut" name="statut">
      <option value="">Tous</option>
<?php foreach (STATUTS_DOSSIER as $cle => $libelle): ?>
      <option value="<?= $cle ?>"<?= $criteres['statut'] === $cle ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="type">Type</label>
    <select id="type" name="type">
      <option value="">Tous</option>
<?php foreach ($types as $t): ?>
      <option<?= $criteres['type'] === $t ? ' selected' : '' ?>><?= htmlspecialchars($t) ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="vue">Sélection</label>
    <select id="vue" name="vue">
      <option value="">Tous les dossiers</option>
<?php foreach (VUES_DOSSIERS as $cle => $libelle): ?>
      <option value="<?= $cle ?>"<?= $criteres['vue'] === $cle ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="tri">Tri</label>
    <select id="tri" name="tri">
      <option value="date">Date de réception</option>
      <option value="echeance"<?= $criteres['tri'] === 'echeance' ? ' selected' : '' ?>>Échéance</option>
    </select>
  </div>
  <div class="filtres-recherche">
    <label for="q">Nom, email ou message</label>
    <input id="q" name="q" type="search" value="<?= htmlspecialchars($criteres['q']) ?>">
  </div>
  <button class="btn btn-clair" type="submit">Filtrer</button>
</form>

<section class="carte">
<?php if (!$dossiers): ?>
  <p class="aide">Aucun dossier ne correspond. Le Bureau a cherché, moyennement, mais il a cherché.</p>
<?php else: ?>
  <table class="tableau">
    <thead><tr><th>N°</th><th>Demandeur</th><th>Type</th><th>Reçu le</th><th>Statut</th><th>Responsable</th><th>Échéance</th></tr></thead>
    <tbody>
<?php foreach ($dossiers as $d): ?>
      <tr>
        <td class="mono"><a href="dossier.php?id=<?= $d['id'] ?>"><?= numero_dossier($d['id_site']) ?></a></td>
        <td><?= htmlspecialchars($d['nom']) ?><br><span class="mono petit"><?= htmlspecialchars($d['email']) ?></span></td>
        <td><?= htmlspecialchars($d['type_demande']) ?></td>
        <td class="mono"><?= date_courte($d['date_demande']) ?></td>
        <td><span class="pastille pastille-<?= $d['statut'] ?>"><?= STATUTS_DOSSIER[$d['statut']] ?></span></td>
        <td><?= $d['responsable'] ? htmlspecialchars($d['responsable']) : '—' ?></td>
        <td class="mono<?= en_retard($d) ? ' alerte' : '' ?>"><?= date_echeance($d['echeance']) ?><?= en_retard($d) ? ' (en retard)' : '' ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</section>
<?php require __DIR__ . '/includes/bas.php'; ?>
