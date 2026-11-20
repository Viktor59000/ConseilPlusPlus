<?php
/**
 * bureau/export-inscrits.php — Onglet Export : page de choix (GET), puis CSV des inscrits (POST + CSRF, mêmes filtres
 * que inscrits.php). Format Excel : BOM UTF-8, séparateur « ; », en-têtes = colonnes du tableau de la séance.
 */
require_once __DIR__ . '/includes/amorce.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $criteres = criteres_inscris($_POST);
    exiger_post('export-inscrits.php', 'Session expirée : export annulé. Recommencez.');
    $inscrits = filtrer_inscris($bdd, $criteres, 10000);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inscrits-' . date('Y-m-d-His') . '.csv"');
    header('Cache-Control: no-store');
    $sortie = fopen('php://output', 'w');
    fwrite($sortie, "\xEF\xBB\xBF");
    fputcsv($sortie, ['Id', 'remote_Id', 'Nom', 'prénom', 'email', 'Téléphone', 'jour', 'horaire', 'create_at', 'Statut'], ';', '"', '');
    foreach ($inscrits as $i) {
        fputcsv($sortie, array_map('cellule_csv', [
            (string) $i['id'], (string) $i['remote_id'], $i['nom'], $i['prenom'], $i['email'], $i['telephone'],
            date_echeance($i['jour']), substr($i['horaire'], 0, 5), date_courte($i['create_at']), STATUTS_INSCRIS[$i['statut']],
        ]), ';', '"', '');
    }
    fclose($sortie);
    exit();
}

$total = (int) $bdd->query('SELECT COUNT(*) FROM inscris')->fetchColumn();

$titre = 'Export';
$page = 'export';
require __DIR__ . '/includes/haut.php';
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Export</p>
    <h1>Exporter les inscrits</h1>
    <p class="aide"><?= $total ?> inscrit(s) dans la base du Bureau. Fichier CSV lisible directement par Excel
      (UTF-8 avec BOM, séparateur « ; »), colonnes de la table <code>inscris</code>.</p>
  </div>
</div>

<form class="filtres carte" method="post" action="export-inscrits.php">
  <?= csrf_champ() ?>
  <div>
    <label for="statut">Statut</label>
    <select id="statut" name="statut">
      <option value="">Tous</option>
<?php foreach (STATUTS_INSCRIS as $cle => $libelle): ?>
      <option value="<?= $cle ?>"><?= $libelle ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="jour">Jour (facultatif)</label>
    <input id="jour" name="jour" type="date">
  </div>
  <div class="filtres-recherche">
    <label for="q">Nom, prénom, email ou téléphone (facultatif)</label>
    <input id="q" name="q" type="search">
  </div>
  <button class="btn btn-tampon" type="submit">Télécharger le CSV</button>
</form>

<section class="carte" aria-labelledby="titre-autres">
  <h2 id="titre-autres">Autres exports</h2>
  <ul class="historique">
    <li><a class="lien-fort" href="dossiers.php">Dossiers (demandes de contact)</a> <span class="aide">bouton « Exporter en CSV » de la liste</span></li>
    <li><a class="lien-fort" href="tableau.php#titre-sauvegarde">Sauvegardes complètes des bases</a> <span class="aide">fichiers .sql, depuis le tableau de bord</span></li>
  </ul>
</section>
<?php require __DIR__ . '/includes/bas.php'; ?>
