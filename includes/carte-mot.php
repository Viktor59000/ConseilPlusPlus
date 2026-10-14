<?php
// Affiche une entrée du Petit Moyen. Attend $mot (entrée lue par lire_mot / mot_du_jour)
// et, en option, $titre_niveau (h2 par défaut). Une page feuilletée (pages_dictionnaire) n'a pas de date.
$balise = $titre_niveau ?? 'h2';
if ($mot['jour'] !== null) {
    $date = (new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris'))->format(new DateTime($mot['jour']));
    $date = mb_strtoupper(mb_substr($date, 0, 1)) . mb_substr($date, 1); // « Mercredi 14 octobre 2026 »
} else {
    $date = 'Page ' . $mot['page'];
}
$noms = ['conseiller' => 'Le conseiller', 'stagiaire' => 'Le stagiaire'];
?>
<article class="entree-mot">
  <p class="entree-date mono"><?= htmlspecialchars($date) ?></p>
  <<?= $balise ?> class="entree-titre"><?= htmlspecialchars($mot['mot']) ?> <span class="entree-nature"><?= htmlspecialchars($mot['nature']) ?></span></<?= $balise ?>>
<?php if ($mot['format'] === 'sketch' && $mot['sketch']):
    $s = $mot['sketch'];
    $ouverture = OUVERTURES[crc32($mot['jour'] ?? $mot['mot']) % count(OUVERTURES)];
?>
  <div class="sketch">
    <p><strong>Le conseiller :</strong> <?= htmlspecialchars($ouverture) ?></p>
<?php foreach ($s['pistes'] as $i => $piste): ?>
    <p><strong>Le stagiaire :</strong> <?= htmlspecialchars($piste) ?> !</p>
<?php if ($i < count($s['pistes']) - 1): ?>
    <p><strong>Le conseiller :</strong> Non.</p>
<?php endif; ?>
<?php endforeach; ?>
    <p><strong>Le conseiller :</strong> Non. <?= htmlspecialchars($mot['mot']) ?>. <?= htmlspecialchars($s['epellation']) ?></p>
    <p class="sketch-scene"><?= htmlspecialchars($s['scene']) ?></p>
<?php foreach ($s['repliques'] as $r): ?>
    <p><strong><?= $noms[$r['qui']] ?> :</strong> <?= htmlspecialchars($r['texte']) ?></p>
<?php endforeach; ?>
    <p class="sketch-formule"><strong>Le conseiller et le stagiaire :</strong> <?= htmlspecialchars(FORMULE_FINALE) ?></p>
<?php if ($s['relance'] !== ''): ?>
    <p><strong>Le stagiaire :</strong> <?= htmlspecialchars($s['relance']) ?></p>
<?php endif; ?>
  </div>
  <dl class="entree-infos mono">
    <div><dt>Certitude</dt><dd><?= (int) $mot['certitude'] ?> %</dd></div>
  </dl>
<?php else: ?>
  <p class="entree-definition"><?= htmlspecialchars($mot['definition']) ?></p>
  <p class="entree-exemple">« <?= htmlspecialchars($mot['exemple']) ?> »</p>
  <dl class="entree-infos mono">
    <div><dt>Synonyme douteux</dt><dd><?= htmlspecialchars($mot['synonyme']) ?></dd></div>
    <div><dt>Certitude</dt><dd><?= (int) $mot['certitude'] ?> %</dd></div>
  </dl>
<?php endif; ?>
</article>
