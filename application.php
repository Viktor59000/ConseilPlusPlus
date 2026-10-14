<?php
/**
 * application.php — Installer ConseilPlus+ sur son téléphone (application web installable).
 * Le bouton « Installer » n'apparaît que si le navigateur le permet (Android, Chrome, Edge) : js/pwa.js.
 */
session_start();

$titre = "L'application";
$description = "Installez ConseilPlus+ sur votre téléphone : le conseil et le mot (presque) du jour, à portée de pouce.";
$page = 'application';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Application mobile</p>
  <h1>Le cabinet, dans votre poche</h1>
  <p class="chapo">ConseilPlus+ s'installe sur votre écran d'accueil comme une application. Aucun magasin d'applications, aucune mise à jour à faire : le cabinet se met à jour tout seul, à son rythme.</p>
  <div class="actions">
    <button type="button" class="btn btn-tampon" id="installer" hidden>Installer l'application</button>
  </div>
  <p class="installation-etat" id="installation-etat" role="status"></p>
</section>

<section class="conteneur installation" aria-labelledby="titre-installation">
  <h2 id="titre-installation">Procédure d'installation</h2>
  <div class="installation-grille">
    <article class="installation-carte">
      <h3>Android</h3>
      <ol>
        <li>Ouvrez ce site dans <strong>Chrome</strong>.</li>
        <li>Touchez le bouton <strong>Installer l'application</strong> ci-dessus.<br>S'il n'apparaît pas : menu <strong>⋮</strong> en haut à droite, puis <strong>Ajouter à l'écran d'accueil</strong> → <strong>Installer</strong>.</li>
        <li>L'icône C++ apparaît avec vos autres applications.</li>
      </ol>
    </article>
    <article class="installation-carte">
      <h3>iPhone</h3>
      <ol>
        <li>Ouvrez ce site dans <strong>Safari</strong>.</li>
        <li>Touchez le bouton <strong>Partager</strong> (le carré avec une flèche vers le haut).</li>
        <li>Choisissez <strong>Sur l'écran d'accueil</strong>, puis <strong>Ajouter</strong>.</li>
      </ol>
    </article>
  </div>
  <p class="mono installation-note">Appui long sur l'icône : accès direct au mot du jour ou à votre conseil (Android). Sans réseau, l'application affiche les derniers contenus consultés.</p>
</section>

<?php include("includes/footer.php"); ?>
