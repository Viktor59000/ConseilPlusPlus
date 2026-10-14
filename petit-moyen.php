<?php
/**
 * petit-moyen.php — Le Petit Moyen : archives du mot (presque) du jour.
 * Affiche le mot du jour puis les mots des jours précédents (table mots_du_jour), jamais ceux à venir.
 */
session_start();
require("db.php");
require("includes/mot-du-jour.php");

$mot_jour = mot_du_jour($bdd);
$stmt = $bdd->query('SELECT jour, mot, nature, definition, exemple, synonyme, certitude, source FROM mots_du_jour WHERE jour < CURDATE() ORDER BY jour DESC LIMIT 60');
$archives = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titre = 'Le Petit Moyen';
$description = 'Le Petit Moyen, dictionnaire officieux de ConseilPlus+ : un mot (presque) du jour, défini avec une précision moyenne.';
$page = 'petit-moyen';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Dictionnaire officieux</p>
  <h1>Le Petit Moyen</h1>
  <p class="chapo">Le dictionnaire du cabinet. Un mot par jour, parfois un jeu de mots, parfois pas, toujours défini avec une précision moyenne.</p>
</section>

<section class="conteneur" aria-labelledby="titre-aujourdhui">
  <h2 id="titre-aujourdhui">Aujourd'hui</h2>
<?php $mot = $mot_jour; $titre_niveau = 'h3'; include("includes/carte-mot.php"); ?>
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

<?php include("includes/footer.php"); ?>
