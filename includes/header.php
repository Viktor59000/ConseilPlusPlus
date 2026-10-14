<?php
// Attend, définis par la page :
//   $titre       titre de l'onglet
//   $description résumé pour les moteurs de recherche (meta description)
//   $page        accueil | formules | contact | client | espace
require_once __DIR__ . '/csrf.php';
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
  <meta name="theme-color" content="#1B2440">
  <link rel="manifest" href="manifest.webmanifest">
  <meta name="apple-mobile-web-app-title" content="ConseilPlus+">
<?php if ($connecte): ?>
  <meta name="csrf" content="<?= csrf_jeton() ?>">
<?php endif; ?>
  <!-- Polices hébergées sur le site (pas de requête vers un service tiers, affichage plus rapide) -->
  <link rel="preload" href="assets/fonts/archivo-black.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="assets/fonts/ibm-plex-sans.woff2" as="font" type="font/woff2" crossorigin>
  <!-- Feuille de style intégrée à la page : un aller-retour réseau en moins avant le premier affichage -->
  <style><?= str_replace('../assets/', 'assets/', file_get_contents(__DIR__ . '/../css/style.css')) ?></style>
</head>
<body>
<a class="skip" href="#contenu">Aller au contenu</a>
<header class="entete">
  <div class="conteneur entete-in">
    <a class="logo" href="index.php">
      <img src="assets/logo.svg" alt="ConseilPlus+, le conseiller moyen — accueil" width="300" height="71">
    </a>
    <nav class="nav" aria-label="Navigation principale">
      <a href="index.php"<?= $page === 'accueil' ? ' aria-current="page"' : '' ?>>Accueil</a>
      <a href="produit.php"<?= $page === 'formules' ? ' aria-current="page"' : '' ?>>Nos formules</a>
      <a href="contact.php"<?= $page === 'contact' ? ' aria-current="page"' : '' ?>>Contact</a>
<?php if ($connecte): ?>
      <a class="btn btn-petit" href="conseil.php"<?= $page === 'espace' ? ' aria-current="true"' : '' ?>>Mon espace</a>
<?php else: ?>
      <a class="btn btn-petit" href="login.php"<?= $page === 'client' ? ' aria-current="page"' : '' ?>>Espace client</a>
<?php endif; ?>
    </nav>
  </div>
</header>
<main id="contenu">
