<?php
/**
 * mentions-legales.php — Mentions légales (éditeur, hébergement, prestataires, propriété intellectuelle).
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';

$sommaire = ['editeur' => 'Éditeur', 'hebergement' => 'Hébergement et prestataires', 'propriete' => 'Propriété intellectuelle',
    'donnees' => 'Données personnelles', 'accessibilite' => 'Accessibilité'];

page_debut([
    'titre' => 'Mentions légales',
    'description' => 'Mentions légales de ConseilPlus+ : éditeur, hébergement et prestataires du cabinet.',
    'css' => ['css/v2/pages/document.css'],
]);
?>
<section class="doc-entete" data-enter aria-labelledby="doc-titre">
  <div class="conteneur">
    <p class="reference"><span>Formulaire administratif</span><span>Légal</span></p>
    <h1 id="doc-titre">Mentions légales</h1>
    <p class="chapeau">La seule page du site qui doit être prise au sérieux.</p>
  </div>
</section>

<div class="document">
  <div class="conteneur grille">
    <nav class="document__sommaire" aria-labelledby="sommaire-titre">
      <h2 id="sommaire-titre">Sommaire</h2>
      <ol>
<?php foreach ($sommaire as $ancre => $libelle): ?>
        <li><a href="#<?= $ancre ?>"><?= e($libelle) ?></a></li>
<?php endforeach; ?>
      </ol>
    </nav>
    <div class="document__texte texte">
      <h2 id="editeur">Éditeur</h2>
      <p>ConseilPlus+ est un site humoristique réalisé dans le cadre d'un projet de cours (semestre 5), sans activité commerciale.
        Les « formules » et leurs prix sont fictifs : aucun paiement n'est demandé ni possible.</p>
      <p>Contact : <a href="contact.php">formulaire de contact</a> ou conseilplusplus@gmail.com.</p>

      <h2 id="hebergement">Hébergement et prestataires</h2>
      <ul>
        <li><strong>Site</strong> : Render Services, Inc. (États-Unis), <a href="https://render.com">render.com</a>.</li>
        <li><strong>Base de données</strong> : TiDB Cloud, PingCAP (serveurs à Francfort, Allemagne), <a href="https://www.pingcap.com">pingcap.com</a>.</li>
        <li><strong>Courriels</strong> : Brevo (France), <a href="https://www.brevo.com">brevo.com</a>.</li>
        <li><strong>Intelligence artificielle de la consultation</strong> : Groq, Inc. (États-Unis), <a href="https://groq.com">groq.com</a>.</li>
      </ul>

      <h2 id="propriete">Propriété intellectuelle</h2>
      <p>Textes, logo et générateurs de conseils : contenus originaux du projet. Le site rend un hommage non officiel à l'humour
        absurde ; il n'est affilié à aucune émission ni à aucun artiste. Les personnalités citées dans les conseils le sont dans des
        situations inoffensives et imaginaires.</p>
      <p>Polices Schibsted Grotesk et JetBrains Mono, sous licence SIL Open Font License.</p>

      <h2 id="donnees">Données personnelles</h2>
      <p>Voir la <a href="confidentialite.php">politique de confidentialité</a>. Le site n'utilise ni publicité, ni outil de
        mesure d'audience, ni cookie de suivi.</p>

      <h2 id="accessibilite">Accessibilité</h2>
      <p>Voir la <a href="accessibilite.php">déclaration d'accessibilité</a>.</p>
    </div>
  </div>
</div>
<?php page_fin(); ?>
