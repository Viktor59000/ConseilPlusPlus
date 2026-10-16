<?php
/**
 * plan-du-site.php — Plan du site : second moyen de navigation exigé par le RGAA (critère 12.1).
 */
session_start();
$connecte = isset($_SESSION['user_id']);

$titre = 'Plan du site';
$description = 'Plan du site de ConseilPlus+ : toutes les pages du cabinet, rangées avec un soin moyen.';
$page = 'autre';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <h1>Plan du site</h1>
  <p class="chapo">Toutes les pages du cabinet, sur une seule page. Pratique, pour une fois.</p>
</section>

<div class="conteneur plan">
  <section aria-labelledby="plan-cabinet">
    <h2 id="plan-cabinet">Le cabinet</h2>
    <ul>
      <li><a href="index.php">Accueil : le conseil du jour</a></li>
      <li><a href="cabinet.php">Le cabinet : l'équipe et les valeurs</a></li>
      <li><a href="petit-moyen.php">Le Petit Moyen : le mot (presque) du jour</a></li>
      <li><a href="produit.php">Nos formules</a></li>
      <li><a href="guichet.php">Le guichet</a></li>
      <li><a href="contact.php">Nous contacter</a></li>
    </ul>
  </section>
  <section aria-labelledby="plan-client">
    <h2 id="plan-client">Espace client</h2>
    <ul>
<?php if ($connecte): ?>
      <li><a href="conseil.php">Mon conseil</a></li>
      <li><a href="consultation.php">Consultation</a></li>
      <li><a href="duel.php">Le Duel</a></li>
      <li><a href="carnet.php">Mon carnet</a></li>
      <li><a href="compte.php">Mon compte</a></li>
      <li><a href="auth/logout.php">Déconnexion</a></li>
<?php else: ?>
      <li><a href="login.php">Connexion et création de compte</a></li>
      <li><a href="mot-de-passe-oublie.php">Mot de passe oublié</a></li>
      <li>Une fois connecté : Mon conseil, Consultation, Le Duel, Mon carnet, Mon compte</li>
<?php endif; ?>
    </ul>
  </section>
  <section aria-labelledby="plan-pratique">
    <h2 id="plan-pratique">Pratique</h2>
    <ul>
      <li><a href="application.php">L'application mobile</a></li>
      <li><a href="accessibilite.php">Accessibilité</a></li>
      <li><a href="mentions-legales.php">Mentions légales</a></li>
      <li><a href="confidentialite.php">Confidentialité</a></li>
      <li><a href="plan-du-site.php" aria-current="page">Plan du site</a></li>
    </ul>
  </section>
</div>

<?php include("includes/footer.php"); ?>
