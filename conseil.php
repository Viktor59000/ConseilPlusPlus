<?php
/**
 * conseil.php — Espace client : générateur de conseils (réservé aux connectés).
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

include("db.php");
include("includes/tirage.php");

$titre = 'Mon conseil';
$page = 'client';
$conseil = tirer_conseil($bdd);
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Formule Conseil Illimité</p>
  <h1>Bonjour <?= htmlspecialchars($_SESSION['prenom']) ?>.</h1>
  <p class="chapo">Votre dossier a été traité avec le plus grand sérieux. Voici notre recommandation.</p>
</section>

<section class="conteneur">
  <article class="fiche fiche-grande" id="recommandation" aria-labelledby="fiche-titre">
    <header class="fiche-entete">
      <h2 id="fiche-titre">Recommandation officielle</h2>
      <span class="mono">Dossier n° <span data-conseil="dossier"><?= $conseil['dossier'] ?></span></span>
    </header>
    <p class="fiche-conseil" aria-live="polite">
      <span data-conseil="situation"><?= htmlspecialchars($conseil['situation']) ?></span>
      <mark data-conseil="action"><?= htmlspecialchars($conseil['action']) ?></mark>
      <span data-conseil="resultat"><?= htmlspecialchars($conseil['resultat']) ?></span>
    </p>
    <span class="tampon tampon-anime" aria-hidden="true">Validé</span>
    <p class="fiche-pied mono">Traité par : le conseiller moyen</p>
  </article>

  <div class="actions">
    <a class="btn btn-tampon" id="autre-conseil" href="conseil.php#recommandation">Demander un autre conseil</a>
    <a class="btn btn-clair" href="consultation.php">Consultation approfondie (IA)</a>
  </div>
</section>

<script src="js/conseil.js" defer></script>

<?php include("includes/footer.php"); ?>
