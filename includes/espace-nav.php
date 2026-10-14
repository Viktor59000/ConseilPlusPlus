<?php
// Sous-menu de l'espace client. Attend $onglet : conseil | consultation | duel | carnet.
$onglets = [
    'conseil' => ['conseil.php', 'Mon conseil'],
    'consultation' => ['consultation.php', 'Consultation'],
    'duel' => ['duel.php', 'Le Duel'],
    'carnet' => ['carnet.php', 'Mon carnet'],
];
?>
<nav class="espace-nav" aria-label="Espace client">
  <div class="conteneur espace-nav-in">
    <span class="espace-nav-titre mono">Espace de <?= htmlspecialchars($_SESSION['prenom']) ?></span>
    <ul>
<?php foreach ($onglets as $cle => [$lien, $libelle]): ?>
      <li><a href="<?= $lien ?>"<?= $onglet === $cle ? ' aria-current="page"' : '' ?>><?= $libelle ?></a></li>
<?php endforeach; ?>
      <li><a class="espace-nav-sortie" href="auth/logout.php">Déconnexion</a></li>
    </ul>
  </div>
</nav>
