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
    // Le sketch sous forme de script : [qui, texte, rôle] — qui ∈ conseiller | stagiaire | duo | scene
    $lignes = [['conseiller', $ouverture, '']];
    foreach ($s['pistes'] as $i => $piste) {
        $lignes[] = ['stagiaire', $piste . ' !', ''];
        if ($i < count($s['pistes']) - 1) {
            $lignes[] = ['conseiller', 'Non.', ''];
        }
    }
    $lignes[] = ['conseiller', 'Non. ' . $mot['mot'] . '. ' . $s['epellation'], ''];
    $lignes[] = ['scene', $s['scene'], ''];
    foreach ($s['repliques'] as $i => $r) {
        $lignes[] = [$r['qui'], $r['texte'], $i === count($s['repliques']) - 1 ? 'chute' : ''];
    }
    $lignes[] = ['duo', FORMULE_FINALE, 'formule'];
    if ($s['relance'] !== '') {
        $lignes[] = ['stagiaire', $s['relance'], ''];
    }
    $noms['duo'] = 'Le conseiller et le stagiaire';
?>
  <div class="sketch" data-sketch>
    <div class="sketch-commandes" hidden>
      <button type="button" class="btn btn-tampon btn-petit" data-jouer>▶ Jouer la scène</button>
      <label class="sketch-voix"><input type="checkbox" data-voix> Avec les voix</label>
    </div>
    <ol class="sketch-script">
<?php foreach ($lignes as [$qui, $texte, $role]): ?>
<?php if ($qui === 'scene'): ?>
      <li class="sketch-scene" data-qui="scene"><?= htmlspecialchars($texte) ?></li>
<?php else: ?>
      <li class="replique replique-<?= $qui ?><?= $role ? " replique-$role" : '' ?>" data-qui="<?= $qui ?>">
        <span class="replique-qui mono"><?= $noms[$qui] ?></span>
        <span class="replique-texte"><?= htmlspecialchars($texte) ?></span>
      </li>
<?php endif; ?>
<?php endforeach; ?>
    </ol>
<?php if (!empty($s['decoupage'])): ?>
    <p class="sketch-decoupage mono" data-qui="decoupage">Découpage officiel : <strong><?= htmlspecialchars($s['decoupage']) ?></strong></p>
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
