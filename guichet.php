<?php
/**
 * guichet.php — Le guichet du cabinet : prenez un ticket, patientez, le guichet ferme à votre tour.
 * Tout se passe dans le navigateur (js/guichet.js) ; sans JavaScript, le guichet est simplement fermé.
 */
session_start();

$titre = 'Le guichet';
$description = 'Le guichet de ConseilPlus+ : prenez un ticket et patientez. Le cabinet vous recevra dans les meilleurs délais, moyens.';
$page = 'guichet';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Accueil du public</p>
  <h1>Le guichet</h1>
  <p class="chapo">Le cabinet reçoit le public sans rendez-vous. Prenez un ticket et patientez : vous serez appelé dans l'ordre, ou presque.</p>
</section>

<section class="conteneur guichet" aria-labelledby="titre-guichet">
  <h2 id="titre-guichet" class="visuellement-cache">Salle d'attente</h2>
  <div class="guichet-ecran" aria-hidden="true">
    <p class="guichet-ecran-titre mono">Numéro appelé</p>
    <p class="guichet-numero" data-appele>----</p>
    <p class="guichet-ecran-titre mono" data-poste>Guichet —</p>
  </div>

  <div class="guichet-ticket carte-guichet">
    <noscript><p class="guichet-ferme-texte">Le guichet est actuellement fermé. Horaires d'ouverture : de 8 h 59 à 9 h 01, du mardi au mardi.</p></noscript>
    <div data-machine hidden>
      <p class="mono guichet-ticket-titre">Votre ticket</p>
      <p class="guichet-mon-numero" data-ticket>Aucun</p>
      <dl class="guichet-infos">
        <div><dt class="mono">Personnes devant vous</dt><dd data-devant>Inconnu, pour l'instant</dd></div>
        <div><dt class="mono">Attente estimée</dt><dd data-attente>Inestimable</dd></div>
      </dl>
      <button type="button" class="btn btn-tampon" data-prendre>Prendre un ticket</button>
      <p class="guichet-tampon" data-ferme hidden>Guichet fermé</p>
    </div>
  </div>

  <div class="guichet-annonces">
    <h3 class="mono">Annonces</h3>
    <ol class="guichet-journal" data-journal aria-live="polite"></ol>
  </div>
</section>

<script src="js/guichet.js" defer></script>
<?php include("includes/footer.php"); ?>
