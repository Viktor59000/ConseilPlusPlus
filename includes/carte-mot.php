<?php
// Affiche une entrée du Petit Moyen. Attend $mot (ligne de mots_du_jour) et, en option, $titre_niveau (h2 par défaut).
$balise = $titre_niveau ?? 'h2';
$date = (new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris'))->format(new DateTime($mot['jour']));
$date = mb_strtoupper(mb_substr($date, 0, 1)) . mb_substr($date, 1); // « Mercredi 14 octobre 2026 »
?>
<article class="entree-mot">
  <p class="entree-date mono"><?= htmlspecialchars($date) ?></p>
  <<?= $balise ?> class="entree-titre"><?= htmlspecialchars($mot['mot']) ?> <span class="entree-nature"><?= htmlspecialchars($mot['nature']) ?></span></<?= $balise ?>>
  <p class="entree-definition"><?= htmlspecialchars($mot['definition']) ?></p>
  <p class="entree-exemple">« <?= htmlspecialchars($mot['exemple']) ?> »</p>
  <dl class="entree-infos mono">
    <div><dt>Synonyme douteux</dt><dd><?= htmlspecialchars($mot['synonyme']) ?></dd></div>
    <div><dt>Certitude</dt><dd><?= (int) $mot['certitude'] ?> %</dd></div>
  </dl>
</article>
