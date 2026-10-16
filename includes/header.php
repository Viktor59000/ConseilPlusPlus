<?php
// Attend, définis par la page :
//   $titre       titre de l'onglet
//   $description résumé pour les moteurs de recherche (meta description)
//   $page        accueil | petit-moyen | formules | guichet | contact | client | espace | autre
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/theme.php';
require_once __DIR__ . '/boucle.php';
$connecte = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titre) ?> — ConseilPlus+</title>
  <meta name="description" content="<?= htmlspecialchars($description ?? 'ConseilPlus+, le conseiller moyen : des conseils absurdes, délivrés avec le plus grand sérieux.') ?>">
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
