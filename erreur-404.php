<?php
/**
 * erreur-404.php — Page « Dossier introuvable », affichée par Apache pour toute adresse inconnue
 * (ErrorDocument dans docker/apache.conf). L'adresse demandée peut être n'importe où : <base> remet les liens
 * et les fichiers du site (images, scripts) à la racine. Un conseil de consolation est remis, tamponné « Égaré ».
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tirage.php';
require_once __DIR__ . '/includes/url.php';

http_response_code(404);
$conseil = tirer_conseil($bdd);

page_debut([
    'titre' => 'Dossier introuvable',
    'description' => 'Cette page n\'existe pas, ou plus. Le cabinet a peut-être classé votre dossier sans suite.',
    'base_href' => dossier_du_site() . '/',
    'canonical' => false,
    'robots' => 'noindex',
    'css' => ['css/v2/pages/erreur.css'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Dossier introuvable</h1>
      <p class="heros__chapeau">Cette page n'existe pas, ou plus. Elle a sans doute été classée sans suite par un conseiller moyen, dans un tiroir que personne n'ouvre.</p>
      <div class="boutons">
        <?= bouton('Retour au Hall', 'index.php') ?>
        <?= bouton('Plan du site', 'plan-du-site.php', ['variante' => 'secondaire']) ?>
      </div>
    </div>
    <div class="heros__visuel erreur-visuel">
      <?= attestation([
          'titre' => 'Conseil de consolation', 'reference' => $conseil['dossier'], 'service' => 'Service des objets égarés', 'niveau' => 2,
          'corps_html' => '<p>' . e($conseil['situation']) . ' <mark>' . e($conseil['action']) . '</mark> ' . e($conseil['resultat']) . '</p>',
          'champs' => ['Page demandée' => 'introuvable', 'Prix' => 'offert, toujours pas remboursable'],
          'signataire' => 'Bertrand', 'fonction' => 'agent d\'accueil',
          'tampon' => ['texte' => 'Égaré', 'mouvement' => 'au-chargement', 'angle' => -9],
      ]) ?>
    </div>
  </div>
</section>
<?php page_fin(); ?>
