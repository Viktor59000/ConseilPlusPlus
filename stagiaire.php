<?php
/**
 * stagiaire.php — Guichet n° 3 : « Questions pour un stagiaire », jeu interne du cabinet. Le conseiller présente, le
 * stagiaire buzze toujours le premier et répond faux avec aplomb, le visiteur (le jury) retient la bonne réponse ou la
 * sienne. Partie de 10 questions dans le navigateur (js/stagiaire.js) ; sans JavaScript, la question du jour.
 * Se conclut par le verdict tamponné du jury et le lien vers le diplôme de patience (attestation.php?document=patience).
 * Styles : css/v2/pages/epreuves.css.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/stagiaire.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/guichets.php';
$banque = stagiaire_banque();
[$q_question, $q_bonne, $q_stagiaire, $q_argument] = stagiaire_question_du_jour();
$g = guichet('stagiaire');

page_debut([
    'titre' => 'Questions pour un stagiaire',
    'description' => 'Questions pour un stagiaire, guichet n° 3 : le stagiaire de ConseilPlus+ buzze le premier et répond faux, avec un aplomb total. Vous êtes le jury.',
    'page' => 'guichet',
    'css' => ['css/v2/pages/epreuves.css'],
    'scripts' => ['js/stagiaire.js'],
]);
echo bandeau_guichets('stagiaire');
?>
<section class="heros epreuve" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Questions pour un stagiaire</h1>
      <p class="heros__chapeau">Présenté par le conseiller moyen. Dix questions simples, un candidat unique : le stagiaire, qui buzze toujours le premier et répond toujours avec aplomb. Vous êtes le jury.</p>
      <dl class="score" style="--n: 2" data-score aria-label="Score">
        <div><dt>Le bon sens</dt><dd data-points="bon_sens">0</dd></div>
        <div><dt>Le stagiaire</dt><dd data-points="stagiaire">0</dd></div>
      </dl>
      <p class="epreuve__note">Arbitrage : vous. Parodie non officielle d'un jeu télévisé, sans lien avec une émission existante.</p>
    </div>
    <div class="epreuve__poste">
      <noscript>
        <div class="feuille plateau">
          <p class="plateau__num">Question du jour</p>
          <p class="plateau__question"><?= e($q_question) ?></p>
          <p class="plateau__buzz">Le stagiaire a buzzé. Le reste de la partie a besoin de JavaScript ; le stagiaire, non.</p>
          <div class="plateau__reponse">
            <p><span class="mono">Réponse du stagiaire :</span> <?= e($q_stagiaire) ?></p>
            <p class="plateau__argument">« <?= e($q_argument) ?> »</p>
          </div>
          <details>
            <summary>Voir la bonne réponse</summary>
            <p><?= e($q_bonne) ?>.</p>
          </details>
        </div>
      </noscript>
      <div class="feuille plateau" data-jeu hidden>
        <h2 class="visuellement-cache">Le plateau</h2>
        <p class="plateau__num" data-num>Question n° 1 sur 10</p>
        <p class="plateau__question" data-question tabindex="-1"></p>
        <p class="plateau__buzz" data-buzz></p>
        <div class="plateau__reponse" data-reponse hidden>
          <p><span class="mono">Réponse du stagiaire :</span> <span data-stagiaire></span></p>
          <p class="plateau__argument" data-argument></p>
        </div>
        <div data-jury hidden role="group" aria-labelledby="titre-jury">
          <p class="mono muet" id="titre-jury">Le jury retient :</p>
          <div class="boutons">
            <?= bouton('Bonne réponse', null, ['type' => 'button', 'fleche' => false, 'attributs' => ['data-choix' => 'bonne']]) ?>
            <?= bouton('Réponse du stagiaire', null, ['type' => 'button', 'variante' => 'secondaire', 'attributs' => ['data-choix' => 'stagiaire']]) ?>
          </div>
        </div>
        <p class="plateau__verdict" data-verdict></p>
        <div class="plateau__bilan" data-bilan data-papier hidden><?= tampon('Délibéré', ['mouvement' => 'en-attente', 'angle' => -8, 'taille' => 'grand', 'id' => 'tampon-jury', 'classe' => 'plateau__tampon', 'decoratif' => true]) ?></div>
        <div class="boutons">
          <?= bouton('Question suivante', null, ['type' => 'button', 'variante' => 'secondaire', 'attributs' => ['data-suivante' => true, 'hidden' => true]]) ?>
          <?= bouton('Retirer mon diplôme de patience', 'attestation.php?document=' . $g['document'], ['attributs' => ['data-diplome' => true, 'hidden' => true]]) ?>
        </div>
        <p class="visuellement-cache" aria-live="polite" data-annonce></p>
      </div>
    </div>
  </div>
</section>
<script type="application/json" id="stagiaire-donnees"><?= json_encode($banque, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<?php page_fin(); ?>
