<?php
// Attend, définis par la page :
//   $titre       titre de l'onglet
//   $description résumé pour les moteurs de recherche (meta description)
//   $base        (facultatif) dossier racine du site, pour les pages servies à une autre adresse (erreur 404)
//   $page        accueil | cabinet | petit-moyen | formules | guichet | contact | client | espace | autre
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/theme.php';
require_once __DIR__ . '/boucle.php';
require_once __DIR__ . '/textes.php';
require_once __DIR__ . '/courriel.php'; // url_publique()
// Adresse publique de la page (aperçus de partage, adresse canonique)
$url_page = url_publique() . '/' . (basename($_SERVER['SCRIPT_NAME']) === 'index.php' ? '' : basename($_SERVER['SCRIPT_NAME']));
$connecte = isset($_SESSION['user_id']);
envoyer_csp();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
<?php if (!empty($base)): ?>
  <base href="<?= htmlspecialchars($base) ?>">
<?php endif; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titre) ?> — ConseilPlus+</title>
  <meta name="description" content="<?= htmlspecialchars($description ?? 'ConseilPlus+, le conseiller moyen : des conseils absurdes, délivrés avec le plus grand sérieux.') ?>">
  <link rel="canonical" href="<?= htmlspecialchars($url_page) ?>">
  <!-- Aperçu des liens partagés (WhatsApp, Discord, réseaux sociaux…) -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ConseilPlus+">
  <meta property="og:locale" content="fr_FR">
  <meta property="og:title" content="<?= htmlspecialchars($titre) ?> — ConseilPlus+">
  <meta property="og:description" content="<?= htmlspecialchars($description ?? 'Des conseils absurdes, délivrés avec le plus grand sérieux.') ?>">
  <meta property="og:url" content="<?= htmlspecialchars($url_page) ?>">
  <meta property="og:image" content="<?= url_publique() ?>/assets/og-image.png">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="ConseilPlus+ : Des conseils. C'est déjà ça.">
  <meta name="twitter:card" content="summary_large_image">
  <link rel="icon" href="assets/icons/favicon.ico" sizes="48x48">
  <link rel="icon" href="assets/icons/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="assets/icons/apple-touch-icon.png">
  <meta name="theme-color" content="#F5F6FA">
  <link rel="manifest" href="manifest.webmanifest">
  <meta name="apple-mobile-web-app-title" content="ConseilPlus+">
<?php if ($connecte): ?>
  <meta name="csrf" content="<?= csrf_jeton() ?>">
<?php endif; ?>
  <?= theme_script() ?>

  <link rel="preload" href="assets/fonts/schibsted-grotesk.woff2" as="font" type="font/woff2" crossorigin>
  <!-- Feuilles de style intégrées à la page : un aller-retour réseau en moins avant le premier affichage -->
  <style><?= str_replace('../assets/', 'assets/', css_integre(__DIR__ . '/../css/base.css', __DIR__ . '/../css/style.css')) ?></style>
</head>
<body>
<a class="skip" href="#contenu">Aller au contenu</a>
<header class="entete">
  <div class="conteneur entete-in">
    <a class="logo" href="index.php">
      <?php $logo_id = 'logo-entete'; include __DIR__ . '/logo.php'; ?>
    </a>
    <div class="outils">
      <?= theme_bouton() ?>
      <button class="menu-bouton" type="button" aria-expanded="false" aria-controls="nav-principale">
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path class="barre barre-1" d="M4 7h16"/><path class="barre barre-2" d="M4 12h16"/><path class="barre barre-3" d="M4 17h16"/></svg>
        Menu
      </button>
    </div>
    <nav class="nav" id="nav-principale" aria-label="Navigation principale">
      <div class="nav-in">
<?php
$liens = [
    'cabinet' => ['cabinet.php', 'Le cabinet'],
    'petit-moyen' => ['petit-moyen.php', 'Le mot du jour'],
    'formules' => ['produit.php', 'Nos formules'],
    'guichet' => ['guichet.php', 'Le guichet'],
    'contact' => ['contact.php', 'Contact'],
];
foreach ($liens as $cle => [$lien, $libelle]): ?>
        <a href="<?= $lien ?>"<?= $page === $cle ? ' aria-current="page"' : '' ?>><?= $libelle ?></a>
<?php endforeach; ?>
<?php if ($connecte): ?>
        <a class="btn btn-tampon btn-petit" href="conseil.php"<?= $page === 'espace' ? ' aria-current="true"' : '' ?>>Mon espace</a>
<?php else: ?>
        <a class="btn btn-tampon btn-petit" href="login.php"<?= $page === 'client' ? ' aria-current="page"' : '' ?>>Espace client</a>
<?php endif; ?>
      </div>
    </nav>
  </div>
</header>
<main id="contenu">
