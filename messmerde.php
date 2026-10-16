<?php
/**
 * messmerde.php — Les expériences de Messmerde, mentaliste du cabinet : il « devine » des évidences
 * avec le plus grand sérieux, et tamponne « PRÉVU » à chaque fois. Tout se passe dans le navigateur
 * (js/messmerde.js) ; sans JavaScript, Messmerde l'avait évidemment prévu.
 */
session_start();

$titre = 'Les expériences de Messmerde';
$description = 'Messmerde, mentaliste du cabinet ConseilPlus+, lit dans vos pensées avec une précision moyenne. Sept expériences, sept évidences.';
$page = 'autre';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Service de divination</p>
  <h1>Les expériences de Messmerde</h1>
  <p class="chapo">Messmerde, mentaliste du cabinet, lit dans les pensées depuis qu'il a appris à lire. Sept expériences, menées avec une précision moyenne et une assurance totale.</p>
</section>

<section class="conteneur messmerde" aria-labelledby="titre-experience">
  <noscript>
    <div class="experience">
      <p class="experience-num mono">Expérience n° 0</p>
      <h2>Le navigateur</h2>
      <p class="experience-revelation">Je sens que vous avez désactivé JavaScript.</p>
      <p class="experience-tampon">Prévu</p>
    </div>
  </noscript>

  <div class="experience" data-experience hidden>
    <p class="experience-num mono" data-num>Expérience n° 1 sur 7</p>
    <h2 id="titre-experience" data-titre>…</h2>
    <p class="experience-consigne" data-consigne></p>
    <div class="experience-scene" data-scene></div>
    <p class="experience-revelation" data-revelation aria-live="polite"></p>
    <p class="experience-tampon" data-tampon hidden>Prévu</p>

    <div class="experience-avis" data-avis hidden>
      <p class="mono">Votre verdict</p>
      <div class="actions">
        <button type="button" class="btn btn-tampon" data-verdict="bluffant">Bluffant</button>
        <button type="button" class="btn btn-clair" data-verdict="moyen">Moyennement bluffant</button>
      </div>
    </div>
  </div>

  <aside class="credibilite" data-credibilite hidden aria-label="Crédibilité de Messmerde">
    <p class="mono">Indice de crédibilité</p>
    <p class="credibilite-valeur" data-score>100 %</p>
    <p class="aide" data-commentaire>Calculé par Messmerde lui-même.</p>
  </aside>
</section>

<script src="<?= fichier_versionne('js/messmerde.js') ?>" defer></script>
<?php include("includes/footer.php"); ?>
