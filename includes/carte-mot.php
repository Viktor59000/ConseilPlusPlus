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
    // Version courte (affichée et jouée) : la scène et sa chute. Le rituel (ouverture, fausses pistes,
    // épellation, formule finale) reste disponible dans un volet, pour les amateurs.
    $rituel = [['conseiller', $ouverture, '']];
    foreach ($s['pistes'] as $i => $piste) {
        $rituel[] = ['stagiaire', $piste . ' !', ''];
        if ($i < count($s['pistes']) - 1) {
            $rituel[] = ['conseiller', 'Non.', ''];
        }
    }
    $rituel[] = ['conseiller', 'Non. ' . $mot['mot'] . '. ' . $s['epellation'], ''];
    $rituel[] = ['duo', FORMULE_FINALE, 'formule'];
    $court = [['scene', $s['scene'], '']];
    foreach ($s['repliques'] as $i => $r) {
        $court[] = [$r['qui'], $r['texte'], $i === count($s['repliques']) - 1 ? 'chute' : ''];
    }
    $noms['duo'] = 'Le conseiller et le stagiaire';
?>
  <div class="sketch" data-sketch>
    <div class="sketch-commandes" hidden>
      <button type="button" class="btn btn-tampon btn-petit" data-jouer>▶ Jouer la scène</button>
      <label class="sketch-voix"><input type="checkbox" data-voix> Avec les voix</label>
    </div>
    <div class="sketch-court">
<?php $lignes = $court; include __DIR__ . '/script-sketch.php'; ?>
<?php if (!empty($s['decoupage'])): ?>
      <p class="sketch-decoupage mono" data-qui="decoupage">Découpage officiel : <strong><?= htmlspecialchars($s['decoupage']) ?></strong></p>
<?php endif; ?>
<?php if ($s['relance'] !== ''): ?>
      <p class="sketch-relance" data-qui="relance"><span class="replique-qui mono">Le stagiaire, après coup</span> <span class="replique-texte"><?= htmlspecialchars($s['relance']) ?></span></p>
<?php endif; ?>
    </div>
    <details class="sketch-rituel">
      <summary>Le rituel intégral (ouverture, fausses pistes, épellation)</summary>
<?php $lignes = $rituel; include __DIR__ . '/script-sketch.php'; ?>
    </details>
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
