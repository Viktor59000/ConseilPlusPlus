<?php
/**
 * attestation.php — Guichet n° 5 : le Service des documents officiels du Guichet, tenu par le tampon (doyen du cabinet).
 * Avant toute attestation, Bertrand fait remplir le formulaire du jour (data/formulaires.php, tiré par date) ; le tampon
 * valide ensuite quoi qu'on y ait écrit. Le document est dessiné dans le navigateur (js/attestation.js) : ni le nom ni
 * les cases ne sont envoyés au serveur (aucun champ n'a d'attribut name). Modèles : data/attestations.php.
 * ?document=<clé> présélectionne un modèle (liens depuis les verdicts des autres guichets et Messmerde).
 * Styles : css/v2/pages/epreuves.css.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/textes.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/guichets.php';
$modeles = require __DIR__ . '/data/attestations.php';
$formulaire = formulaire_du_jour();
$g = guichet('documents');
// ?document=… : un autre service du cabinet (diagnostic, stagiaire, Messmerde) envoie vers son attestation
$choisi = isset($modeles[$_GET['document'] ?? '']) ? $_GET['document'] : null;
$donnees = [
    'modeles' => $modeles,
    'formulaire' => $formulaire['code'],
    'verdicts' => (require __DIR__ . '/data/formulaires.php')['verdicts'],
];
$types = ['texte' => 'text', 'nombre' => 'number', 'date' => 'date'];

page_debut([
    'titre' => 'Les attestations',
    'description' => 'Le Service des documents officiels de ConseilPlus+ : conseil non suivi, client moyen, avis favorable… Formulaire du jour rempli, attestation tamponnée par le doyen du cabinet.',
    'page' => 'guichet',
    'css' => ['css/v2/pages/epreuves.css'],
    'scripts' => ['js/attestation.js'],
]);
echo bandeau_guichets('documents');
?>
<section class="heros epreuve" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Les attestations</h1>
      <p class="heros__chapeau">Délivrées par le tampon, doyen du cabinet, qui valide tout ce qu'on lui présente, après remise du formulaire du jour à Bertrand, gardien du règlement intérieur. Aucune valeur légale, une valeur moyenne.</p>
      <ul class="liste-reglement">
<?php foreach ($modeles as $m): ?>
        <li><?= e($m['titre']) ?></li>
<?php endforeach; ?>
      </ul>
      <div class="epreuve__plaque"><?= plaque_guichet($g['numero'], 'Documents') ?></div>
    </div>
    <div class="epreuve__poste">
      <noscript><?= bloc_alerte('Le tampon a besoin de JavaScript pour dessiner votre attestation. Il est vieux, mais il est moderne.', 'attention', ['titre' => 'Tampon indisponible']) ?></noscript>
      <form class="feuille" data-demande hidden aria-labelledby="formulaire-titre">
        <div class="feuille__entete">
          <h2 id="formulaire-titre">Le formulaire du jour</h2>
          <p class="mono">Remis par Bertrand avant toute demande. Un formulaire par jour, le même pour tous. Les cases restent dans votre navigateur.</p>
        </div>
        <fieldset class="formulaire-jour">
          <legend><span class="mono">Formulaire <?= e($formulaire['code']) ?></span> <?= e($formulaire['titre']) ?></legend>
<?php foreach ($formulaire['cases'] as $i => $c):
    $id = 'case-' . $i;
    $numero = '<span class="case-n">Case ' . e($c['n']) . '</span> ';
    if ($c['type'] === 'choix'): ?>
          <fieldset class="case" data-case>
            <legend><?= $numero . e($c['libelle']) ?></legend>
<?php foreach ($c['options'] as $j => $option): ?>
            <label class="case-choix"><input type="checkbox" id="<?= $id . '-' . $j ?>"> <?= e($option) ?></label>
<?php endforeach; ?>
          </fieldset>
<?php elseif ($c['type'] === 'reserve'): ?>
          <div class="case case-reserve"><p><?= $numero . e($c['libelle']) ?></p></div>
<?php else: ?>
          <div class="case" data-case>
            <label for="<?= $id ?>"><?= $numero . e($c['libelle']) ?></label>
            <input id="<?= $id ?>" type="<?= $types[$c['type']] ?>" autocomplete="off"<?= $c['type'] === 'texte' ? ' maxlength="60"' : '' ?><?= $c['type'] === 'nombre' ? ' inputmode="numeric"' : '' ?>>
          </div>
<?php endif; endforeach; ?>
        </fieldset>
        <div class="feuille__entete">
          <h2 id="demande-titre">Votre demande</h2>
        </div>
        <?= champ('att-type', 'Document demandé', [], ['type' => 'select', 'options' => array_map(fn ($m) => $m['titre'], $modeles), 'valeur' => $choisi ?? '', 'attributs' => ['name' => null, 'data-type' => true]]) ?>
        <?= champ('att-nom', 'Au nom de', [], ['requis' => true, 'maxlength' => 40, 'autocomplete' => 'name', 'placeholder' => 'Prénom et nom', 'officiel' => true,
            'aide' => 'Le nom reste dans votre navigateur : le cabinet ne l\'enregistre pas, il n\'a pas de tiroir assez grand.', 'attributs' => ['name' => null, 'data-nom' => true]]) ?>
        <div class="boutons"><?= bouton('Remettre le formulaire et faire tamponner', null, ['type' => 'submit']) ?></div>
      </form>

      <div class="feuille apercu" id="apercu" data-apercu hidden tabindex="-1" aria-labelledby="apercu-titre">
        <h2 id="apercu-titre" class="visuellement-cache">Votre attestation</h2>
        <p class="visuellement-cache" role="status" data-statut></p>
        <p class="apercu__verdict" data-verdict></p>
        <div class="apercu__image" data-papier>
          <img data-image alt="" width="1600" height="1131">
          <?= tampon('Validé', ['mouvement' => 'en-attente', 'angle' => -9, 'decoratif' => true]) ?>
        </div>
        <div class="boutons">
          <?= bouton('Télécharger', null, ['type' => 'button', 'attributs' => ['data-telecharger' => true]]) ?>
          <?= bouton('Partager', null, ['type' => 'button', 'variante' => 'secondaire', 'attributs' => ['data-partager-attestation' => true]]) ?>
        </div>
      </div>
    </div>
  </div>
</section>
<script type="application/json" id="donnees-attestation"><?= json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<?php page_fin(); ?>
