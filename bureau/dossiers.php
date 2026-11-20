<?php
// bureau/dossiers.php — Registre des dossiers : filtres (statut, type, assignés à moi, en retard), recherche dans le nom,
// l'email ou le message, tri par date ou par échéance, export CSV de la sélection. (Les casiers : salle-de-tri.php.)
require_once __DIR__ . '/includes/amorce.php';

$criteres = criteres_dossiers($_GET);
$dossiers = filtrer_dossiers($bdd, $criteres, $agent['id']);
$types = $bdd->query('SELECT DISTINCT type_demande FROM dossiers ORDER BY type_demande')->fetchAll(PDO::FETCH_COLUMN);

$titre = 'Registre des dossiers';
$page = 'dossiers';
require __DIR__ . '/includes/haut.php';
?>
<header class="tete">
  <div class="tete__texte">
    <p class="tete__ref"><span>Registre</span><a href="salle-de-tri.php">Voir en casiers</a></p>
    <h1>Registre des dossiers</h1>
    <p class="tete__sous"><?= count($dossiers) ?> dossier(s) affiché(s), <?= $criteres['tri'] === 'echeance'
        ? 'par échéance, la plus proche d\'abord (sans échéance à la fin)' : 'du plus récent au plus ancien' ?>.</p>
  </div>
  <form class="tete__actions" method="post" action="export-dossiers.php">
    <?= csrf_champ() ?>
<?php foreach ($criteres as $cle => $valeur): ?>
    <input type="hidden" name="<?= $cle ?>" value="<?= e($valeur) ?>">
<?php endforeach; ?>
    <?= bouton('Exporter en CSV', null, ['variante' => 'secondaire', 'icone_html' => picto_bureau('export')]) ?>
  </form>
</header>

<div class="zone">
  <form class="filtres-b barre-filtres" method="get">
    <div class="champ-b"><label for="statut">Statut</label>
      <select class="saisie" id="statut" name="statut">
        <option value="">Tous</option>
<?php foreach (STATUTS_BUREAU as $cle => $libelle): ?>
        <option value="<?= $cle ?>"<?= $criteres['statut'] === $cle ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
      </select></div>
    <div class="champ-b"><label for="type">Type</label>
      <select class="saisie" id="type" name="type">
        <option value="">Tous</option>
<?php foreach ($types as $t): ?>
        <option<?= $criteres['type'] === $t ? ' selected' : '' ?>><?= e($t) ?></option>
<?php endforeach; ?>
      </select></div>
    <div class="champ-b"><label for="vue">Sélection</label>
      <select class="saisie" id="vue" name="vue">
        <option value="">Tous les dossiers</option>
<?php foreach (VUES_DOSSIERS as $cle => $libelle): ?>
        <option value="<?= $cle ?>"<?= $criteres['vue'] === $cle ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
      </select></div>
    <div class="champ-b"><label for="tri">Tri</label>
      <select class="saisie" id="tri" name="tri">
        <option value="date">Date de réception</option>
        <option value="echeance"<?= $criteres['tri'] === 'echeance' ? ' selected' : '' ?>>Échéance</option>
      </select></div>
    <div class="champ-b champ-b--large"><label for="q">Nom, email ou message</label>
      <input class="saisie" id="q" name="q" type="search" value="<?= e($criteres['q']) ?>"></div>
    <button class="mini" type="submit">Filtrer</button>
<?php if (array_filter($criteres, fn ($v, $k) => $k !== 'tri' && $v !== '', ARRAY_FILTER_USE_BOTH)): ?>
    <a class="lien-b lien-b--nu" href="dossiers.php">Effacer les filtres</a>
<?php endif; ?>
  </form>

  <section class="panneau" aria-labelledby="titre-registre">
    <h2 id="titre-registre" class="visuellement-cache">Dossiers</h2>
<?php if (!$dossiers): ?>
    <p class="vide">Aucun dossier ne correspond. Le Bureau a cherché, moyennement, mais il a cherché.</p>
<?php else: ?>
    <div class="defile">
      <table class="table-b" id="table-dossiers">
        <thead><tr><th scope="col">N°</th><th scope="col">Demandeur</th><th scope="col">Type</th><th scope="col">Reçu le</th><th scope="col">Statut</th><th scope="col">Responsable</th><th scope="col">Échéance</th></tr></thead>
        <tbody>
<?php foreach ($dossiers as $d): ?>
          <tr>
            <td class="mono"><a href="dossier.php?id=<?= $d['id'] ?>"><?= numero_dossier((int) $d['id_site']) ?></a></td>
            <td><span class="double"><a href="dossier.php?id=<?= $d['id'] ?>"><?= e($d['nom']) ?></a><small><?= e($d['email']) ?></small></span></td>
            <td><?= e($d['type_demande']) ?></td>
            <td class="mono muet"><?= date_courte($d['date_demande']) ?></td>
            <td><?= pastille($d['statut'], STATUTS_BUREAU[$d['statut']]) ?></td>
            <td><?= $d['responsable'] ? e($d['responsable']) : '<span class="muet">—</span>' ?></td>
            <td class="mono"><?= en_retard($d) ? pastille('retard', date_echeance($d['echeance'])) : date_echeance($d['echeance']) ?></td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
