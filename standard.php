<?php
/**
 * standard.php — Le standard téléphonique du cabinet, tenu par Mireille (responsable des délais).
 * Un répondeur interactif : clavier, menus qui tournent en rond, musique d'attente, et l'appel finit
 * immanquablement sur la machine à café. Tout se passe dans le navigateur (js/standard.js).
 */
session_start();

$titre = 'Le standard';
$description = 'Le standard téléphonique de ConseilPlus+ : tapez 1 pour un conseil, 2 pour une réclamation… Votre appel est important, il sera donc mis en attente.';
$page = 'autre';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Service téléphonique</p>
  <h1>Le standard</h1>
  <p class="chapo">Tenu par Mireille, responsable des délais. Votre appel est important pour nous : il sera donc traité avec importance, et avec délai.</p>
</section>

<section class="conteneur standard" aria-labelledby="titre-telephone">
  <h2 id="titre-telephone" class="visuellement-cache">Téléphone</h2>
  <noscript><p class="standard-ferme">Le standard est fermé. Horaires d'ouverture : de 9 h à 9 h 05, sauf pendant la pause.</p></noscript>

  <div class="telephone" data-telephone hidden>
    <div class="telephone-ecran">
      <p class="telephone-etat mono"><span data-etat>Combiné raccroché</span> <span data-duree></span></p>
      <p class="telephone-message" data-message aria-live="polite">Décrochez pour joindre le cabinet.</p>
    </div>

    <div class="clavier" role="group" aria-label="Clavier du téléphone">
<?php foreach (['1', '2', '3', '4', '5', '6', '7', '8', '9', '*', '0', '#'] as $touche): ?>
      <button type="button" class="touche" data-touche="<?= $touche ?>" disabled aria-label="Touche <?= $touche === '*' ? 'étoile' : ($touche === '#' ? 'dièse' : $touche) ?>"><?= $touche ?></button>
<?php endforeach; ?>
    </div>

    <div class="telephone-commandes">
      <button type="button" class="btn btn-tampon" data-decrocher>Décrocher</button>
      <label class="standard-option"><input type="checkbox" data-voix> Haut-parleur (voix)</label>
      <label class="standard-option"><input type="checkbox" data-musique checked> Musique d'attente</label>
    </div>
  </div>

  <aside class="standard-journal">
    <h3>Historique de l'appel</h3>
    <ol data-journal></ol>
  </aside>
</section>

<script src="<?= fichier_versionne('js/standard.js') ?>" defer></script>
<?php include("includes/footer.php"); ?>
