<?php
/**
 * mentions-legales.php — Mentions légales (éditeur, hébergement, prestataires, propriété intellectuelle).
 */
require_once __DIR__ . '/includes/init.php';

$titre = 'Mentions légales';
$description = 'Mentions légales de ConseilPlus+ : éditeur, hébergement et prestataires du cabinet.';
$page = 'autre';
include __DIR__ . '/includes/header.php';
?>

<section class="conteneur entete-page">
  <p class="etiquette">Formulaire administratif</p>
  <h1>Mentions légales</h1>
  <p class="chapo">La seule page du site qui doit être prise au sérieux.</p>
</section>

<section class="conteneur texte-long" aria-label="Mentions légales">
  <h2>Éditeur</h2>
  <p>ConseilPlus+ est un site humoristique réalisé dans le cadre d'un projet de cours (semestre 5), sans activité commerciale.
    Les « formules » et leurs prix sont fictifs : aucun paiement n'est demandé ni possible.</p>
  <p>Contact : <a href="contact.php">formulaire de contact</a> ou conseilplusplus@gmail.com.</p>

  <h2>Hébergement et prestataires</h2>
  <ul>
    <li><strong>Site</strong> : Render Services, Inc. (États-Unis), <a href="https://render.com">render.com</a>.</li>
    <li><strong>Base de données</strong> : TiDB Cloud, PingCAP (serveurs à Francfort, Allemagne), <a href="https://www.pingcap.com">pingcap.com</a>.</li>
    <li><strong>Courriels</strong> : Brevo (France), <a href="https://www.brevo.com">brevo.com</a>.</li>
    <li><strong>Intelligence artificielle de la consultation</strong> : Groq, Inc. (États-Unis), <a href="https://groq.com">groq.com</a>.</li>
  </ul>

  <h2>Propriété intellectuelle</h2>
  <p>Textes, logo et générateurs de conseils : contenus originaux du projet. Le site rend un hommage non officiel à l'humour
    absurde ; il n'est affilié à aucune émission ni à aucun artiste. Les personnalités citées dans les conseils le sont dans des
    situations inoffensives et imaginaires.</p>
  <p>Polices Schibsted Grotesk et JetBrains Mono, sous licence SIL Open Font License.</p>

  <h2>Données personnelles</h2>
  <p>Voir la <a href="confidentialite.php">politique de confidentialité</a>. Le site n'utilise ni publicité, ni outil de
    mesure d'audience, ni cookie de suivi.</p>

  <h2>Accessibilité</h2>
  <p>Voir la <a href="accessibilite.php">déclaration d'accessibilité</a>.</p>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
