<?php
/**
 * plan-du-site.php — Plan du site : second moyen de navigation exigé par le RGAA (critère 12.1).
 * Arborescence de la nouvelle version (« Le Guichet ») : les anciennes adresses fusionnées redirigent (301) vers
 * leur nouvelle page et n'y figurent plus qu'à travers elle. À tenir à jour avec sitemap.xml.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
$connecte = isset($_SESSION['user_id']);

// [pictogramme, titre de colonne, [[adresse, libellé, précision, [sous-pages]], …]]
$plan = [
    ['guichet', 'Services', [
        ['index.php', 'Le Hall', 'Le conseil du jour, le ticket, l\'article du règlement'],
        ['guichet.php', 'Le Guichet', 'Déposer une demande, prendre rendez-vous', [
            ['stagiaire.php', 'Questions pour un stagiaire'],
            ['duel.php', 'Le Duel'],
            ['diagnostic.php', 'Quel client êtes-vous ?'],
            ['attestation.php', 'Les attestations et le formulaire du jour'],
        ]],
        $connecte
            ? ['compte.php', 'Mon dossier', 'Le suivi de vos demandes, vos conseils gardés', [['auth/logout.php', 'Déconnexion']]]
            : ['login.php', 'Mon dossier', 'Connexion et ouverture d\'un dossier', [['mot-de-passe-oublie.php', 'Mot de passe oublié']]],
    ]],
    ['tampon', 'Le cabinet', [
        ['cabinet.php', 'Le Personnel', 'L\'équipe, le circuit d\'un dossier, les valeurs'],
        ['petit-moyen.php', 'Le Petit Moyen', 'Le mot (presque) du jour, le sigle, les archives', [
            ['feuilleton.php', 'La Plume et le Tampon, le feuilleton'],
        ]],
        ['messmerde.php', 'Les expériences de Messmerde', 'Prédictions rétroactives, horoscope'],
        ['produit.php', 'Nos formules', 'Les tarifs du guichet'],
    ]],
    ['telephone', 'Réclamations', [
        ['contact.php', 'Réclamations', 'Le formulaire de demande, accueilli par Mireille'],
        ['standard.php', 'Le standard', 'Joindre le cabinet par téléphone, ou presque'],
    ]],
    ['casier', 'Pratique', [
        ['application.php', 'L\'application', 'Android, widget, notification du mot du jour'],
        ['developpeurs.php', 'L\'API du cabinet', 'Documentation pour les développeurs'],
        ['accessibilite.php', 'Accessibilité', 'Déclaration RGAA 4.1'],
        ['mentions-legales.php', 'Mentions légales', null],
        ['confidentialite.php', 'Confidentialité', null],
        ['plan-du-site.php', 'Plan du site', 'Cette page'],
    ]],
];

page_debut([
    'titre' => 'Plan du site',
    'description' => 'Plan du site de ConseilPlus+ : toutes les pages du cabinet, rangées avec un soin moyen.',
    'css' => ['css/v2/pages/document.css'],
]);
?>
<section class="doc-entete" data-enter aria-labelledby="doc-titre">
  <div class="conteneur">
    <p class="reference"><span>Classement général</span><span>Arborescence</span></p>
    <h1 id="doc-titre">Plan du site</h1>
    <p class="chapeau">Toutes les pages du cabinet, sur une seule page. Pratique, pour une fois.</p>
  </div>
</section>

<div class="document">
  <div class="conteneur">
    <div class="plan">
<?php foreach ($plan as $i => [$picto, $titre, $pages]): ?>
      <section class="plan__colonne" aria-labelledby="plan-<?= $i ?>">
        <h2 id="plan-<?= $i ?>"><?= picto($picto) ?><?= e($titre) ?></h2>
        <ul>
<?php foreach ($pages as $p): ?>
          <li>
            <a href="<?= e($p[0]) ?>"<?= $p[0] === 'plan-du-site.php' ? ' aria-current="page"' : '' ?>><?= e($p[1]) ?><?php if (!empty($p[2])): ?><small><?= e($p[2]) ?></small><?php endif; ?></a>
<?php if (!empty($p[3])): ?>
            <ul>
<?php foreach ($p[3] as [$adresse, $libelle]): ?>
              <li><a href="<?= e($adresse) ?>"><?= e($libelle) ?></a></li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
          </li>
<?php endforeach; ?>
        </ul>
      </section>
<?php endforeach; ?>
    </div>
  </div>
</div>
<?php page_fin(); ?>
