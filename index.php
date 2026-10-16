<?php
session_start();
include("db.php");
include("includes/tirage.php");
include("includes/mot-du-jour.php");

$titre = 'Accueil';
$description = 'ConseilPlus+, le conseiller moyen : un cabinet qui répond à vos problèmes par des conseils absurdes, avec le plus grand sérieux.';
$page = 'accueil';
$conseil = tirer_conseil($bdd);
$mot = mot_du_jour($bdd);
include("includes/header.php");
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
  </div>

  <article class="fiche" id="copie-du-jour" aria-labelledby="fiche-titre">
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
    <span class="tampon tampon-anime" aria-hidden="true">Validé</span>
    <p class="fiche-pied mono">Offert. Non remboursable.</p>
    <div class="fiche-actions">
      <button class="btn btn-tampon btn-petit" type="button" data-autre-conseil="#copie-du-jour" hidden>Un autre conseil</button>
      <button class="btn btn-clair btn-petit" type="button" data-partager="conseil" data-source="#copie-du-jour" hidden>Partager</button>
    </div>
  </article>
</section>

<section class="conteneur mot-du-jour" aria-labelledby="titre-mot">
  <div class="mot-du-jour-intro">
    <p class="etiquette">Le Petit Moyen</p>
    <h2 id="titre-mot">Le mot (presque) du jour</h2>
    <p>Extrait du dictionnaire officieux du cabinet. Un nouveau mot chaque jour, défini avec une précision moyenne.</p>
    <a class="btn btn-clair avance" href="petit-moyen.php">Consulter Le Petit Moyen</a>
  </div>
<?php $titre_niveau = 'h3'; include("includes/carte-mot.php"); ?>
</section>

<section class="bande">
  <div class="conteneur">
    <p class="etiquette">Procédure</p>
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
    <p class="chapo">Notre guichet est ouvert. Enfin, le formulaire.</p>
  </div>
  <a class="btn btn-tampon avance" href="contact.php">Nous contacter</a>
</section>

<script src="js/conseil.js" defer></script>
<script src="js/mot-du-jour.js" defer></script>
<script src="js/sketch.js" defer></script>
<script src="js/partage.js" defer></script>
<script src="js/votes.js" defer></script>

<?php include("includes/footer.php"); ?>
