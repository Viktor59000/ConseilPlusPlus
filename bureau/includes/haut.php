<?php
// En-tête des pages du Bureau. Attend $titre et, si un agent est connecté, $agent et $page (tableau | dossiers | petit-moyen | journal | statistiques | cles).
$agent ??= null;
$page ??= '';
require_once __DIR__ . '/../../includes/theme.php';
envoyer_csp();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($titre) ?> — Le Bureau</title>
  <link rel="icon" href="../assets/icons/favicon.svg" type="image/svg+xml">
  <meta name="theme-color" content="#F5F6FA">
  <?= theme_script() ?>

  <link rel="preload" href="../assets/fonts/schibsted-grotesk.woff2" as="font" type="font/woff2" crossorigin>
  <!-- Socle commun avec le site + composants du Bureau, intégrés à la page -->
  <style><?= css_integre(__DIR__ . '/../../css/base.css', __DIR__ . '/../css/bureau.css') ?></style>
</head>
<body>
<header class="entete">
  <div class="entete-in">
    <a class="marque" href="tableau.php">
      <?php $logo_id = 'logo-bureau'; include __DIR__ . '/../../includes/logo.php'; ?>
      <span class="marque-nom">Le Bureau</span>
    </a>
    <?= theme_bouton() ?>
<?php if ($agent): ?>
    <nav class="nav" aria-label="Navigation du Bureau">
      <a href="tableau.php"<?= $page === 'tableau' ? ' aria-current="page"' : '' ?>>Tableau de bord</a>
      <a href="dossiers.php"<?= $page === 'dossiers' ? ' aria-current="page"' : '' ?>>Dossiers</a>
      <a href="petit-moyen.php"<?= $page === 'petit-moyen' ? ' aria-current="page"' : '' ?>>Petit Moyen</a>
      <a href="journal.php"<?= $page === 'journal' ? ' aria-current="page"' : '' ?>>Relevés</a>
      <a href="statistiques.php"<?= $page === 'statistiques' ? ' aria-current="page"' : '' ?>>Statistiques</a>
      <a href="cles.php"<?= $page === 'cles' ? ' aria-current="page"' : '' ?>>Clés</a>
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
