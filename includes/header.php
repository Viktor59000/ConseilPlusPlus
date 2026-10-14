<?php
// Attend $titre (titre de l'onglet) et $page (accueil | formules | contact | client) définis par la page.
$connecte = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titre) ?> — ConseilPlus+</title>
  <link rel="icon" href="assets/icons/favicon.ico" sizes="48x48">
  <link rel="icon" href="assets/icons/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="assets/icons/apple-touch-icon.png">
  <meta name="theme-color" content="#1B2440">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=IBM+Plex+Mono:wght@500&family=IBM+Plex+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
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
      <a class="btn btn-petit" href="conseil.php"<?= $page === 'client' ? ' aria-current="page"' : '' ?>>Mon conseil</a>
      <a href="auth/logout.php">Déconnexion</a>
<?php else: ?>
      <a class="btn btn-petit" href="login.php"<?= $page === 'client' ? ' aria-current="page"' : '' ?>>Espace client</a>
<?php endif; ?>
    </nav>
  </div>
</header>
<main id="contenu">
