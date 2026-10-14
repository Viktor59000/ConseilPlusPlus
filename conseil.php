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
  <article class="fiche fiche-grande" aria-labelledby="fiche-titre">
    <header class="fiche-entete">
      <h2 id="fiche-titre">Recommandation officielle</h2>
      <span class="mono">Dossier n° <?= $conseil['dossier'] ?></span>
    </header>
    <p class="fiche-conseil">
      <?= htmlspecialchars($conseil['situation']) ?>
      <mark><?= htmlspecialchars($conseil['action']) ?></mark>
      <?= htmlspecialchars($conseil['resultat']) ?>
    </p>
    <span class="tampon tampon-anime" aria-hidden="true">Validé</span>
    <p class="fiche-pied mono">Traité par : le conseiller moyen</p>
  </article>

  <div class="actions">
    <a class="btn btn-tampon" href="conseil.php">Demander un autre conseil</a>
    <a class="btn btn-clair" href="consultation.php">Consultation approfondie (IA)</a>
  </div>
</section>

<?php include("includes/footer.php"); ?>
