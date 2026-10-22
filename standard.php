<?php
/**
 * standard.php — Le standard téléphonique du cabinet, tenu par Mireille (standardiste, responsable des délais).
 * Un répondeur interactif sans clavier : choix cliquables, menus qui tournent en rond, musique d'attente, et l'appel
 * finit immanquablement sur la machine à café. Textes : data/standard.php, transmis en JSON à js/standard.js.
 */
require_once __DIR__ . '/includes/init.php';

$titre = 'Le standard';
$description = 'Le standard téléphonique de ConseilPlus+ : un conseil, une réclamation, le stagiaire… Votre appel est important, il sera donc mis en attente.';
$page = 'autre';
include __DIR__ . '/includes/header.php';
$standard = require __DIR__ . '/data/standard.php';
$standard['delais'] = textes()['delais'];
$standard['annonces'] = textes()['annonces'];
?>

<section class="conteneur entete-page">
  <p class="etiquette">Service téléphonique</p>
  <h1>Le standard</h1>
  <p class="chapo">Tenu par Mireille, standardiste et responsable des délais. Votre appel est important pour nous : il sera donc traité avec importance, et avec délai.</p>
</section>

<section class="conteneur standard" aria-labelledby="titre-telephone">
  <h2 id="titre-telephone" class="visuellement-cache">Téléphone</h2>
  <noscript><p class="standard-ferme">Le standard est fermé. Horaires d'ouverture : de 9 h à 9 h 05, sauf pendant la pause.</p></noscript>

  <div class="telephone" data-telephone hidden>
    <div class="telephone-ecran">
      <p class="telephone-etat mono"><span data-etat>Combiné raccroché</span> <span data-duree></span></p>
      <p class="telephone-message" data-message aria-live="polite">Décrochez pour joindre le cabinet.</p>
    </div>

    <div class="standard-choix" role="group" aria-label="Vos choix" data-choix></div>

    <div class="telephone-commandes">
      <button type="button" class="btn btn-tampon" data-decrocher>Décrocher</button>
      <label class="standard-option"><input type="checkbox" data-voix> Haut-parleur (voix)</label>
      <label class="standard-option"><input type="checkbox" data-musique checked> Musique d'attente</label>
    </div>
  </div>

  <aside class="standard-journal" aria-labelledby="titre-transcription">
    <h3 id="titre-transcription">Transcription de l'appel</h3>
    <ol data-journal></ol>
  </aside>
</section>

<script type="application/json" id="standard-donnees"><?= json_encode($standard, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="<?= fichier_versionne('js/standard.js') ?>" defer></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
