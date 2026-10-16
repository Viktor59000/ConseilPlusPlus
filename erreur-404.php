<?php
/**
 * erreur-404.php — Page « Dossier introuvable », affichée par Apache pour toute adresse inconnue
 * (ErrorDocument dans docker/apache.conf). L'adresse demandée peut être n'importe où : <base> remet les liens
 * et les fichiers du site (CSS, images, scripts) à la racine.
 */
session_start();
require("db.php");
require("includes/tirage.php");

http_response_code(404);
$conseil = tirer_conseil($bdd);
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

$titre = 'Dossier introuvable';
$description = 'Cette page n\'existe pas, ou plus. Le cabinet a peut-être classé votre dossier sans suite.';
$page = 'autre';
include("includes/header.php");
?>

<section class="conteneur hero">
  <div>
    <p class="etiquette">Erreur 404</p>
    <h1>Dossier introuvable.</h1>
    <p class="chapo">Cette page n'existe pas, ou plus. Elle a sans doute été classée sans suite par un conseiller moyen.</p>
    <div class="actions">
      <a class="btn btn-tampon avance" href="index.php">Retour à l'accueil</a>
      <a class="btn btn-clair" href="plan-du-site.php">Plan du site</a>
    </div>
  </div>
  <article class="fiche" aria-labelledby="fiche-titre">
    <header class="fiche-entete">
      <h2 id="fiche-titre">Conseil de consolation</h2>
      <span class="mono">Dossier n° <?= $conseil['dossier'] ?></span>
    </header>
    <p class="fiche-conseil">
      <?= htmlspecialchars($conseil['situation']) ?>
      <mark><?= htmlspecialchars($conseil['action']) ?></mark>
      <?= htmlspecialchars($conseil['resultat']) ?>
    </p>
    <span class="tampon tampon-anime" aria-hidden="true">Égaré</span>
    <p class="fiche-pied mono">Offert. Toujours pas remboursable.</p>
  </article>
</section>

<?php include("includes/footer.php"); ?>
