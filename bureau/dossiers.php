<?php
// bureau/dossiers.php — Liste des dossiers, filtrable par statut et type, avec recherche par nom ou email.
require("includes/bdd.php");
require("includes/session.php");
require("includes/metier.php");
$agent = exiger_agent();

$statut = array_key_exists($_GET['statut'] ?? '', STATUTS_DOSSIER) ? $_GET['statut'] : '';
$type = trim((string) ($_GET['type'] ?? ''));
$q = trim((string) ($_GET['q'] ?? ''));

$conditions = [];
$params = [];
if ($statut !== '') {
    $conditions[] = 'statut = :statut';
    $params['statut'] = $statut;
}
if ($type !== '') {
    $conditions[] = 'type_demande = :type';
    $params['type'] = $type;
}
if ($q !== '') {
    $conditions[] = '(nom LIKE :q OR email LIKE :q)';
    $params['q'] = '%' . $q . '%';
}
$sql = 'SELECT * FROM dossiers' . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY date_demande DESC LIMIT 200';
$stmt = $bdd->prepare($sql);
$stmt->execute($params);
$dossiers = $stmt->fetchAll();
$types = $bdd->query('SELECT DISTINCT type_demande FROM dossiers ORDER BY type_demande')->fetchAll(PDO::FETCH_COLUMN);

$titre = 'Dossiers';
$page = 'dossiers';
include("includes/haut.php");
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Registre</p>
    <h1>Dossiers</h1>
    <p class="aide"><?= count($dossiers) ?> dossier(s) affiché(s). Classement par date de réception, du plus récent au plus ancien.</p>
  </div>
</div>

<form class="filtres carte" method="get">
  <div>
    <label for="statut">Statut</label>
    <select id="statut" name="statut">
      <option value="">Tous</option>
<?php foreach (STATUTS_DOSSIER as $cle => $libelle): ?>
      <option value="<?= $cle ?>"<?= $statut === $cle ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="type">Type</label>
    <select id="type" name="type">
      <option value="">Tous</option>
<?php foreach ($types as $t): ?>
      <option<?= $type === $t ? ' selected' : '' ?>><?= htmlspecialchars($t) ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div class="filtres-recherche">
    <label for="q">Nom ou email</label>
    <input id="q" name="q" type="search" value="<?= htmlspecialchars($q) ?>">
  </div>
  <button class="btn btn-clair" type="submit">Filtrer</button>
</form>

<section class="carte">
<?php if (!$dossiers): ?>
  <p class="aide">Aucun dossier ne correspond. Le Bureau a cherché, moyennement, mais il a cherché.</p>
<?php else: ?>
  <table class="tableau">
    <thead><tr><th>N°</th><th>Demandeur</th><th>Type</th><th>Reçu le</th><th>Statut</th><th>Délai estimé</th></tr></thead>
    <tbody>
<?php foreach ($dossiers as $d): ?>
      <tr>
        <td class="mono"><a href="dossier.php?id=<?= $d['id'] ?>"><?= sprintf('%04d', $d['id_site']) ?></a></td>
        <td><?= htmlspecialchars($d['nom']) ?><br><span class="mono petit"><?= htmlspecialchars($d['email']) ?></span></td>
        <td><?= htmlspecialchars($d['type_demande']) ?></td>
        <td class="mono"><?= date_courte($d['date_demande']) ?></td>
        <td><span class="pastille pastille-<?= $d['statut'] ?>"><?= STATUTS_DOSSIER[$d['statut']] ?></span></td>
        <td class="petit"><?= $d['statut'] === 'classe' ? 'Traité' : htmlspecialchars(delai_estime((int) $d['id'])) ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</section>
<?php include("includes/bas.php"); ?>
