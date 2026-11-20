<?php
/**
 * standard.php — Le standard téléphonique du cabinet, tenu par Mireille (standardiste, responsable des délais) :
 * l'entrée des Réclamations (« Joindre le standard »). Un répondeur interactif sans clavier : choix cliquables, menus
 * qui tournent en rond, musique d'attente, et l'appel finit immanquablement sur la machine à café.
 * Textes : data/standard.php, transmis en JSON à js/standard.js.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';

$standard = require __DIR__ . '/data/standard.php';
$standard['delais'] = textes()['delais'];
$standard['annonces'] = textes()['annonces'];

page_debut([
    'titre' => 'Le standard',
    'description' => 'Le standard téléphonique de ConseilPlus+ : un conseil, une réclamation, le stagiaire… Votre appel est important, il sera donc mis en attente.',
    'page' => 'reclamations',
    'css' => ['css/v2/pages/reclamations.css'],
    'scripts' => ['js/standard.js'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Le standard</h1>
      <p class="heros__chapeau">Tenu par Mireille, standardiste et responsable des délais. Votre appel est important pour nous : il sera donc traité avec importance, et avec délai.</p>
      <div class="boutons">
        <?= bouton('Appeler le cabinet', '#telephone', ['icone_html' => picto('telephone')]) ?>
        <?= bouton('Écrire plutôt', 'contact.php#formulaire', ['variante' => 'secondaire']) ?>
      </div>
    </div>
    <div class="heros__visuel">
      <?= scene('standard', 'Le standard du cabinet : un tableau de fiches rouges branchées au hasard, un combiné qui attend sur le comptoir, une horloge dont l\'aiguille pointe dans les deux sens.', ['prioritaire' => true, 'tailles' => '(min-width: 1024px) 46vw, 100vw']) ?>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="telephone" aria-labelledby="titre-telephone">
  <div class="conteneur grille standard">
    <div class="c-5">
      <h2 id="titre-telephone" class="visuellement-cache">Téléphone</h2>
      <noscript><?= bloc_alerte('Le standard est fermé. Horaires d\'ouverture : de 9 h à 9 h 05, sauf pendant la pause.', 'attention', ['titre' => 'Standard fermé']) ?></noscript>

      <div class="telephone" data-telephone hidden>
        <div class="telephone__ecran">
          <p class="telephone__etat"><span data-etat>Combiné raccroché</span> <span data-duree></span></p>
          <p class="telephone__message" data-message aria-live="polite">Décrochez pour joindre le cabinet.</p>
        </div>

        <div class="standard-choix" role="group" aria-label="Vos choix" data-choix></div>

        <div class="telephone__commandes">
          <button type="button" class="bouton bouton--principal" data-decrocher>Décrocher</button>
          <label class="standard-option"><input type="checkbox" data-voix> Haut-parleur (voix)</label>
          <label class="standard-option"><input type="checkbox" data-musique> Musique d'attente</label>
        </div>
      </div>
    </div>

    <aside class="d-7 standard-journal" aria-labelledby="titre-transcription">
      <h3 id="titre-transcription" class="standard-journal__titre">Transcription de l'appel</h3>
      <ol data-journal></ol>
      <p class="standard-journal__vide muet">Aucun appel en cours. La transcription s'écrit ici, mot pour mot, par souci de traçabilité.</p>
    </aside>
  </div>
</section>

<script type="application/json" id="standard-donnees"><?= json_encode($standard, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<?php page_fin(); ?>
