<?php
/**
 * index.php — Accueil : conseil du jour (générateur), article du règlement intérieur du jour, mot (presque) du jour, témoignages.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tirage.php';
require_once __DIR__ . '/includes/mot-du-jour.php';
require_once __DIR__ . '/includes/textes.php';

$titre = 'Accueil';
$description = 'ConseilPlus+, le conseiller moyen : un cabinet qui répond à vos problèmes par des conseils absurdes, avec le plus grand sérieux.';
$page = 'accueil';
$conseil = tirer_conseil($bdd);
$mot = mot_du_jour($bdd);
$article = article_du_jour();
include __DIR__ . '/includes/header.php';
?>

<section class="hero conteneur">
  <div class="hero-texte">
    <p class="etiquette">Cabinet de conseil agréé par personne</p>
    <h1>Des conseils.<br><span class="surligne">C'est déjà ça.</span></h1>
    <p class="chapo">Vous avez un problème ? Nous avons une solution. Elle ne concerne pas votre problème, mais elle est disponible immédiatement.</p>
    <div class="actions">
      <a class="btn btn-tampon" href="<?= isset($_SESSION['user_id']) ? 'conseil.php' : 'login.php' ?>">Demander conseil</a>
      <a class="btn btn-clair" href="produit.php">Voir nos formules</a>
    </div>
    <p class="article-jour"><strong>Règlement intérieur, art. <?= htmlspecialchars($article['numero']) ?>.</strong> <?= htmlspecialchars($article['texte']) ?></p>
  </div>

  <article class="fiche" id="copie-du-jour" aria-labelledby="fiche-titre" data-tampons="<?= htmlspecialchars(json_encode(textes()['tampons'], JSON_UNESCAPED_UNICODE)) ?>">
    <span class="fiche-note entoure" aria-hidden="true"><span>C<sup>+</sup></span><?= boucle_stylo() ?></span>
    <header class="fiche-entete">
      <h2 id="fiche-titre">Conseil du jour</h2>
      <span class="mono">Dossier n° <span data-conseil="dossier" data-dossier><?= $conseil['dossier'] ?></span></span>
    </header>
    <p class="fiche-conseil" aria-live="polite">
      <span data-conseil="situation"><?= htmlspecialchars($conseil['situation']) ?></span>
      <mark data-conseil="action"><?= htmlspecialchars($conseil['action']) ?></mark>
      <span data-conseil="resultat"><?= htmlspecialchars($conseil['resultat']) ?></span>
    </p>
    <span class="tampon tampon-anime" aria-hidden="true"><?= htmlspecialchars(texte_hasard('tampons')) ?></span>
    <p class="fiche-pied mono"><?= htmlspecialchars(texte_hasard('mentions')) ?></p>
    <div class="fiche-actions">
      <button class="btn btn-tampon btn-petit" type="button" data-autre-conseil="#copie-du-jour" hidden>Un autre conseil</button>
      <button class="btn btn-clair btn-petit" type="button" data-partager="conseil" data-source="#copie-du-jour" hidden>Partager</button>
    </div>
  </article>
</section>

<section class="conteneur mot-du-jour" aria-labelledby="titre-mot">
  <div class="mot-du-jour-intro">
    <h2 id="titre-mot">Le mot (presque) du jour</h2>
    <p>Extrait du dictionnaire officieux du cabinet. Un nouveau mot chaque jour, défini avec une précision moyenne.</p>
    <a class="btn btn-clair" href="petit-moyen.php">Consulter Le Petit Moyen</a>
  </div>
<?php $titre_niveau = 'h3'; include __DIR__ . '/includes/carte-mot.php'; ?>
</section>

<section class="conteneur temoignages" aria-labelledby="titre-temoignages">
  <h2 id="titre-temoignages">Ce que disent nos clients</h2>
  <div class="temoignages-grille">
    <?= bloc_temoignages(3) ?>
  </div>
</section>

<section class="bande">
  <div class="conteneur">
    <h2>Notre méthode, en trois étapes moyennes</h2>
    <ol class="etapes">
      <li>
        <span class="etape-num entoure" aria-hidden="true">1<?= boucle_stylo() ?></span>
        <h3>Vous avez une question</h3>
        <p>Gardez-la pour vous. Nous ne la lirons pas.</p>
      </li>
      <li>
        <span class="etape-num entoure" aria-hidden="true">2<?= boucle_stylo() ?></span>
        <h3>Nous délibérons</h3>
        <p>Un algorithme tire trois idées qui ne se connaissent pas et les présente les unes aux autres.</p>
      </li>
      <li>
        <span class="etape-num entoure" aria-hidden="true">3<?= boucle_stylo() ?></span>
        <h3>Vous repartez conseillé</h3>
        <p>Pas mieux conseillé. Juste conseillé.</p>
      </li>
    </ol>
  </div>
</section>

<section class="conteneur appel">
  <div>
    <h2>Une question ?</h2>
    <p class="chapo">Le formulaire de contact est ouvert jour et nuit. Les réponses suivent leur propre horaire.</p>
  </div>
  <a class="btn btn-tampon" href="contact.php">Nous contacter</a>
</section>

<script src="<?= fichier_versionne('js/conseil.js') ?>" defer></script>
<script src="<?= fichier_versionne('js/sketch.js') ?>" defer></script>
<script src="<?= fichier_versionne('js/partage.js') ?>" defer></script>
<script src="<?= fichier_versionne('js/votes.js') ?>" defer></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
