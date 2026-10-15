<?php
/**
 * petit-moyen.php — Le Petit Moyen : archives du mot (presque) du jour.
 * Affiche le mot du jour puis les mots des jours précédents (table mots_du_jour), jamais ceux à venir.
 */
session_start();
require("db.php");
require("includes/mot-du-jour.php");

$mot_jour = mot_du_jour($bdd);
$jours = $bdd->query('SELECT jour FROM mots_du_jour WHERE jour < CURDATE() ORDER BY jour DESC LIMIT 60')->fetchAll(PDO::FETCH_COLUMN);
$archives = array_map(fn($j) => lire_mot($bdd, $j), $jours);

// Feuilleter : une page du dictionnaire (?page=N), au hasard si aucune n'est demandée
$pages = pages_dictionnaire();
$total = count($pages);
$numero = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => $total]])
    ?: random_int(1, $total);
$feuillet = $pages[$numero - 1];
$autre = ($numero + random_int(1, $total - 1) - 1) % $total + 1; // une autre page, jamais la même

$titre = 'Le Petit Moyen';
$description = 'Le Petit Moyen, dictionnaire officieux de ConseilPlus+ : un mot (presque) du jour, défini avec une précision moyenne.';
$page = 'petit-moyen';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Dictionnaire officieux</p>
  <h1>Le Petit Moyen</h1>
  <p class="chapo">Le dictionnaire du cabinet. Un mot par jour, parfois un jeu de mots, parfois pas, toujours défini avec une précision moyenne. Certains jours, le conseiller et son stagiaire tentent même une démonstration.</p>
</section>

<section class="conteneur" aria-labelledby="titre-aujourdhui">
  <h2 id="titre-aujourdhui">Aujourd'hui</h2>
<?php $mot = $mot_jour; $titre_niveau = 'h3'; include("includes/carte-mot.php"); ?>
</section>

<section class="conteneur feuilleter" id="feuilleter" aria-labelledby="titre-feuilleter">
  <h2 id="titre-feuilleter">Feuilleter le dictionnaire</h2>
  <p class="chapo">Vous ne pouvez pas attendre demain ? Le cabinet comprend. Ouvrez le Petit Moyen à n'importe quelle page.</p>
<?php $mot = $feuillet; $titre_niveau = 'h3'; include("includes/carte-mot.php"); ?>
  <div class="actions">
    <a class="btn btn-tampon" href="petit-moyen.php?page=<?= $autre ?>#feuilleter">Ouvrir à une autre page</a>
  </div>
  <details class="sommaire">
    <summary>Sommaire : les <?= $total ?> mots du dictionnaire</summary>
    <ol class="sommaire-liste">
<?php foreach ($pages as $p): ?>
      <li><a href="petit-moyen.php?page=<?= $p['page'] ?>#feuilleter"<?= $p['page'] === $numero ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($p['mot']) ?></a><?= $p['format'] === 'sketch' ? ' <span class="sommaire-sketch">(sketch)</span>' : '' ?></li>
<?php endforeach; ?>
    </ol>
  </details>
</section>

<?php if ($archives): ?>
<section class="conteneur" aria-labelledby="titre-archives">
  <h2 id="titre-archives">Les jours précédents</h2>
  <ul class="archives">
<?php foreach ($archives as $mot): ?>
    <li><?php $titre_niveau = 'h3'; include("includes/carte-mot.php"); ?></li>
<?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<script src="js/sketch.js" defer></script>
<script src="js/partage.js" defer></script>
<?php include("includes/footer.php"); ?>
