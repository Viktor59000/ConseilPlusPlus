<?php
/**
 * stagiaire.php — « Questions pour un stagiaire », jeu interne du cabinet : le conseiller présente, le stagiaire buzze
 * toujours le premier et répond faux avec aplomb, le visiteur (le jury) retient la bonne réponse ou la sienne.
 * Partie de 10 questions dans le navigateur (js/stagiaire.js) ; sans JavaScript, la question du jour.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/stagiaire.php';
$banque = stagiaire_banque();
[$q_question, $q_bonne, $q_stagiaire, $q_argument] = stagiaire_question_du_jour();

$titre = 'Questions pour un stagiaire';
$description = "Questions pour un stagiaire : le stagiaire de ConseilPlus+ buzze le premier et répond faux, avec un aplomb total. Vous êtes le jury.";
$page = 'autre';
include __DIR__ . '/includes/header.php';
?>

<section class="conteneur entete-page">
  <p class="etiquette">Jeu interne du cabinet</p>
  <h1>Questions pour un stagiaire</h1>
  <p class="chapo">Présenté par le conseiller moyen. Dix questions simples, un candidat unique : le stagiaire, qui buzze toujours le premier et répond toujours avec aplomb. Vous êtes le jury : à chaque question, vous retenez la bonne réponse, ou la sienne.</p>
  <p class="aide">Parodie non officielle d'un jeu télévisé, sans lien avec une émission existante.</p>
</section>

<section class="conteneur plateau" aria-labelledby="titre-plateau">
  <h2 id="titre-plateau" class="visuellement-cache">Le plateau</h2>
  <noscript>
    <div class="plateau-jeu">
      <p class="plateau-num mono">Question du jour</p>
      <p class="plateau-question"><?= htmlspecialchars($q_question) ?></p>
      <p class="plateau-buzz">Le stagiaire a buzzé. Le reste de la partie a besoin de JavaScript ; le stagiaire, non.</p>
      <div class="plateau-reponse">
        <p class="plateau-stagiaire"><span class="mono">Réponse du stagiaire :</span> <?= htmlspecialchars($q_stagiaire) ?></p>
        <p class="plateau-argument">« <?= htmlspecialchars($q_argument) ?> »</p>
      </div>
      <details class="plateau-details">
        <summary>Voir la bonne réponse</summary>
        <p><?= htmlspecialchars($q_bonne) ?>.</p>
      </details>
    </div>
  </noscript>

  <div class="plateau-jeu" data-jeu hidden>
    <p class="plateau-num mono" data-num>Question n° 1 sur 10</p>
    <p class="plateau-question" data-question tabindex="-1"></p>
    <p class="plateau-buzz" data-buzz></p>
    <div class="plateau-reponse" data-reponse hidden>
      <p class="plateau-stagiaire"><span class="mono">Réponse du stagiaire :</span> <span data-stagiaire></span></p>
      <p class="plateau-argument" data-argument></p>
    </div>
    <div class="plateau-jury" data-jury hidden role="group" aria-labelledby="titre-jury">
      <p class="mono" id="titre-jury">Le jury retient :</p>
      <div class="actions">
        <button type="button" class="btn btn-tampon" data-choix="bonne">Bonne réponse</button>
        <button type="button" class="btn btn-clair" data-choix="stagiaire">Réponse du stagiaire</button>
      </div>
    </div>
    <p class="plateau-verdict" data-verdict></p>
    <div class="actions">
      <button type="button" class="btn btn-tampon" data-suivante hidden>Question suivante</button>
    </div>
    <p class="visuellement-cache" aria-live="polite" data-annonce></p>
  </div>

  <aside class="plateau-score" data-score hidden aria-labelledby="titre-score">
    <p class="mono" id="titre-score">Score</p>
    <dl>
      <div><dt>Le bon sens</dt><dd data-points="bon_sens">0</dd></div>
      <div><dt>Le stagiaire</dt><dd data-points="stagiaire">0</dd></div>
    </dl>
    <p class="aide">Arbitrage : vous.</p>
  </aside>
</section>

<script type="application/json" id="stagiaire-donnees"><?= json_encode($banque, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="<?= fichier_versionne('js/stagiaire.js') ?>" defer></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
