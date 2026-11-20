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
<header class="tete">
  <div class="tete__texte">
    <p class="tete__ref"><span>Export</span><span>CSV · UTF-8 · séparateur « ; »</span></p>
    <h1>Exporter les inscrits</h1>
    <p class="tete__sous"><?= nombre_de($total, 'inscrit') ?> dans la base du Bureau. Le fichier s'ouvre directement dans Excel ; aucune cellule n'y est prise pour une formule.</p>
  </div>
</header>

<div class="zone">
  <div class="grille-b">
    <section class="panneau s-7" aria-labelledby="titre-csv">
      <div class="panneau__tete"><h2 id="titre-csv"><?= picto_bureau('export') ?>Inscrits au guichet</h2><span class="note-b">Table <code>inscris</code></span></div>
      <div class="panneau__corps">
        <form class="formulaire-b" method="post" action="export-inscrits.php">
          <?= csrf_champ() ?>
          <div class="filtres-b">
            <div class="champ-b"><label for="statut">Statut</label>
              <select class="saisie" id="statut" name="statut">
                <option value="">Tous</option>
<?php foreach (STATUTS_INSCRIS as $cle => $libelle): ?>
                <option value="<?= $cle ?>"><?= $libelle ?></option>
<?php endforeach; ?>
              </select></div>
            <div class="champ-b"><label for="jour">Jour (facultatif)</label><input class="saisie" id="jour" name="jour" type="date"></div>
            <div class="champ-b champ-b--large"><label for="q">Nom, prénom, email ou téléphone</label><input class="saisie" id="q" name="q" type="search"></div>
          </div>
          <div class="colonnes-csv" aria-label="Colonnes du fichier">
            <span class="etiquette-b">Colonnes</span>
<?php foreach (['Id', 'remote_Id', 'Nom', 'prénom', 'email', 'Téléphone', 'jour', 'horaire', 'create_at', 'Statut'] as $colonne): ?>
            <code><?= e($colonne) ?></code>
<?php endforeach; ?>
          </div>
          <div class="boutons"><?= bouton('Télécharger le CSV', null, ['fleche' => false, 'icone_html' => picto_bureau('export')]) ?>
            <span class="note-b">inscrits-<?= date('Y-m-d') ?>-…csv</span></div>
        </form>
      </div>
    </section>

    <section class="panneau s-5" aria-labelledby="titre-autres">
      <div class="panneau__tete"><h2 id="titre-autres">Autres exports</h2><span class="note-b">Second plan</span></div>
      <ul class="lignes lignes--hautes">
        <li><span class="reste"><strong>Dossiers (réclamations)</strong><br><span class="note-b">Tous les dossiers, sans les notes internes. Pour une sélection : le registre.</span></span>
          <form class="pousse" method="post" action="export-dossiers.php"><?= csrf_champ() ?><button class="mini" type="submit">CSV</button></form></li>
        <li><span class="reste"><strong>Base du site</strong><br><span class="note-b">Sauvegarde .sql, demandée à l'API (<code>api/export.php</code>).</span></span>
          <form class="pousse" method="post" action="export.php"><?= csrf_champ() ?><button class="mini" name="base" value="site" type="submit">.sql</button></form></li>
        <li><span class="reste"><strong>Base du Bureau</strong><br><span class="note-b">Sauvegarde .sql de la base « gestion ».</span></span>
          <form class="pousse" method="post" action="export.php"><?= csrf_champ() ?><button class="mini" name="base" value="gestion" type="submit">.sql</button></form></li>
      </ul>
      <p class="vide">Les sauvegardes contiennent les adresses email et les mots de passe chiffrés : à garder en lieu sûr.</p>
    </section>
  </div>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
