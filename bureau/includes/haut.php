<?php
// En-tête des pages du Bureau. Attend $titre et, si un agent est connecté, $agent et $page (tableau | dossiers | journal).
$agent ??= null;
$page ??= '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($titre) ?> — Le Bureau</title>
  <link rel="icon" href="../assets/icons/favicon.svg" type="image/svg+xml">
  <meta name="theme-color" content="#1B2440">
  <link rel="stylesheet" href="css/bureau.css">
</head>
<body>
<header class="entete">
  <div class="entete-in">
    <a class="marque" href="tableau.php">
      <img src="../assets/logo-negatif.svg" alt="ConseilPlus+" width="150" height="36">
      <span class="marque-nom">Le Bureau</span>
    </a>
<?php if ($agent): ?>
    <nav class="nav" aria-label="Navigation du Bureau">
      <a href="tableau.php"<?= $page === 'tableau' ? ' aria-current="page"' : '' ?>>Tableau de bord</a>
      <a href="dossiers.php"<?= $page === 'dossiers' ? ' aria-current="page"' : '' ?>>Dossiers</a>
      <a href="journal.php"<?= $page === 'journal' ? ' aria-current="page"' : '' ?>>Journal des relevés</a>
    </nav>
    <form class="agent" method="post" action="deconnexion.php">
      <?= csrf_champ() ?>
      <span class="mono">Agent : <?= htmlspecialchars($agent['nom']) ?></span>
      <button class="lien" type="submit">Se déconnecter</button>
    </form>
<?php endif; ?>
  </div>
</header>
<main class="page">
<?php if ($a = annonce()): ?>
  <p class="annonce annonce-<?= $a[1] === 'erreur' ? 'erreur' : 'ok' ?>" role="status"><?= htmlspecialchars($a[0]) ?></p>
<?php endif; ?>
