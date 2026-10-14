<?php
session_start();
include("db.php");
include("includes/tirage.php");

$titre = 'Accueil';
$page = 'accueil';
$conseil = tirer_conseil($bdd);
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

  <article class="fiche" aria-labelledby="fiche-titre">
    <header class="fiche-entete">
      <h2 id="fiche-titre">Conseil du jour</h2>
      <span class="mono">Dossier n° <?= $conseil['dossier'] ?></span>
    </header>
    <p class="fiche-conseil">
      <?= htmlspecialchars($conseil['situation']) ?>
      <mark><?= htmlspecialchars($conseil['action']) ?></mark>
      <?= htmlspecialchars($conseil['resultat']) ?>
    </p>
    <span class="tampon" aria-hidden="true">Validé</span>
    <p class="fiche-pied mono">Offert. Non remboursable.</p>
  </article>
</section>

<section class="bande">
  <div class="conteneur">
    <h2>Notre méthode</h2>
    <ol class="etapes">
      <li>
        <span class="etape-num mono">01</span>
        <h3>Vous avez une question</h3>
        <p>Gardez-la pour vous. Nous ne la lirons pas.</p>
      </li>
      <li>
        <span class="etape-num mono">02</span>
        <h3>Nous délibérons</h3>
        <p>Un algorithme tire trois idées qui ne se connaissent pas et les présente les unes aux autres.</p>
      </li>
      <li>
        <span class="etape-num mono">03</span>
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
  <a class="btn btn-clair" href="contact.php">Nous contacter</a>
</section>

<?php include("includes/footer.php"); ?>
