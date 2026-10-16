<?php
// Attend, définis par la page :
//   $titre       titre de l'onglet
//   $description résumé pour les moteurs de recherche (meta description)
//   $page        accueil | petit-moyen | formules | guichet | contact | client | espace | autre
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
  <meta name="theme-color" content="#F5F5F7" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#000000" media="(prefers-color-scheme: dark)">
  <link rel="manifest" href="manifest.webmanifest">
  <meta name="apple-mobile-web-app-title" content="ConseilPlus+">
<?php if ($connecte): ?>
  <meta name="csrf" content="<?= csrf_jeton() ?>">
<?php endif; ?>
  <!-- Feuille de style intégrée à la page : un aller-retour réseau en moins avant le premier affichage -->
  <style><?= file_get_contents(__DIR__ . '/../css/style.css') ?></style>
  <script>document.documentElement.classList.add('avec-js')</script>
</head>
<body>
<a class="skip" href="#contenu">Aller au contenu</a>
<header class="entete">
  <div class="conteneur entete-in">
    <a class="logo" href="index.php">
      <picture>
        <source srcset="assets/logo-negatif.svg" media="(prefers-color-scheme: dark)">
        <img src="assets/logo.svg" alt="ConseilPlus+, le conseiller moyen — accueil" width="152" height="36">
      </picture>
    </a>
    <button class="menu-bouton" type="button" aria-expanded="false" aria-controls="nav-principale">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
      Menu
    </button>
    <nav class="nav" id="nav-principale" aria-label="Navigation principale">
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
    </nav>
  </div>
</header>
<main id="contenu">
