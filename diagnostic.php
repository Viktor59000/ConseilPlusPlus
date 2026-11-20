<?php
/**
 * diagnostic.php — Guichet n° 1 : « Quel client êtes-vous ? », le diagnostic du conseiller moyen.
 * Formulaire GET (aucune donnée enregistrée), résultat calculé ici : fonctionne sans JavaScript.
 * Se conclut par le verdict tamponné et le lien vers le certificat de client moyen (attestation.php?document=client-moyen).
 * Contenus : data/diagnostic.php. Styles : css/v2/pages/epreuves.css (commun aux épreuves du Guichet).
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/guichets.php';

$d = require __DIR__ . '/data/diagnostic.php';
$g = guichet('diagnostic');

// Réponses reçues : q0 … q6, chacune a, b, c ou d
$reponses = [];
foreach ($d['questions'] as $i => [, $choix]) {
    $r = $_GET["q$i"] ?? '';
    if (isset($choix[$r])) {
        $reponses[$i] = $r;
    }
}
$complet = count($reponses) === count($d['questions']);
$incomplet = !$complet && $reponses;

$profil = null;
if ($complet) {
    $comptes = array_count_values($reponses);
    arsort($comptes);
    $premiers = array_keys($comptes, max($comptes), true);
    $profil = $d['profils'][count($premiers) === 1 ? $premiers[0] : 'e'];
    $moyennete = 50; // toujours exactement la moyenne
}

page_debut([
    'titre' => 'Quel client êtes-vous ?',
    'description' => 'Le diagnostic du conseiller moyen, guichet n° 1 : sept questions pour savoir quel client moyen vous êtes. Résultat garanti moyen, verdict tamponné.',
    'page' => 'guichet',
    'css' => ['css/v2/pages/epreuves.css'],
    'scripts' => ['js/partage.js'],
]);
echo bandeau_guichets('diagnostic');
?>
<section class="heros epreuve" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Quel client êtes-vous ?</h1>
      <p class="heros__chapeau">Sept questions, établies par le conseiller moyen à partir de son expérience, qui est longue. Le résultat est garanti : vous êtes moyen. Reste à savoir lequel.</p>
      <p class="epreuve__note">Aucune réponse n'est enregistrée. Le conseiller retient tout de mémoire, puis oublie.</p>
      <div class="epreuve__plaque"><?= plaque_guichet($g['numero'], 'Diagnostic') ?></div>
    </div>
    <div class="epreuve__poste">
<?php if ($profil): ?>
      <div class="epreuve__verdict" id="resultat">
        <?= attestation([
            'type' => 'verdict', 'titre' => $profil[0], 'niveau' => 2, 'reference' => 'DIAG-' . date('Y') . '-' . sprintf('%04d', crc32(implode('', $reponses)) % 10000),
            'service' => 'Le diagnostic du conseiller, guichet n° 1',
            'corps_html' => '<p>' . e($profil[1]) . '</p><p class="verdict__mesure">Taux de moyenneté : ' . $moyennete . ' % (exactement la moyenne).</p>',
            'signataire' => 'Le conseiller moyen', 'fonction' => 'fondateur', 'paraphe' => 'C. M.',
            'tampon' => ['texte' => 'Diagnostiqué', 'mouvement' => 'au-chargement', 'angle' => -7],
            'apres_html' => '<div class="boutons verdict__actions">'
                . bouton('Obtenir mon certificat de client moyen', 'attestation.php?document=' . $g['document'])
                . bouton('Partager mon résultat', null, ['type' => 'button', 'variante' => 'secondaire', 'attributs' => ['hidden' => true,
                    'data-partager' => 'texte', 'data-nom' => 'diagnostic', 'data-titre' => $profil[0], 'data-texte' => $profil[1],
                    'data-detail' => 'Taux de moyenneté : ' . $moyennete . ' %.', 'data-etiquette' => 'Diagnostic officiel du conseiller moyen', 'data-url' => 'diagnostic.php']])
                . lien_fleche('Refaire le diagnostic', 'diagnostic.php')
                . '</div>',
        ]) ?>
      </div>
<?php else: ?>
      <form class="feuille" method="get" action="diagnostic.php#resultat" aria-labelledby="questions-titre">
        <div class="feuille__entete">
          <h2 id="questions-titre">Questionnaire de diagnostic</h2>
          <p class="mono">Formulaire CP+ 7-Q · Une réponse par question, toutes obligatoires.</p>
        </div>
<?php if ($incomplet): ?>
        <?= bloc_alerte('Le conseiller a besoin de toutes vos réponses pour vous trouver moyen. ' . (count($d['questions']) - count($reponses)) . ' question(s) restent sans réponse.', 'erreur', ['titre' => 'Diagnostic incomplet']) ?>
<?php endif; ?>
<?php foreach ($d['questions'] as $i => [$question, $choix]): ?>
        <fieldset class="question">
          <legend><span class="mono"><?= $i + 1 ?>.</span><?= e($question) ?></legend>
<?php foreach ($choix as $cle => $libelle): ?>
          <label class="question__choix"><input type="radio" name="q<?= $i ?>" value="<?= e($cle) ?>" required<?= ($reponses[$i] ?? '') === $cle ? ' checked' : '' ?>><span><?= e($libelle) ?></span></label>
<?php endforeach; ?>
        </fieldset>
<?php endforeach; ?>
        <div class="boutons"><?= bouton('Obtenir mon diagnostic', null, ['type' => 'submit']) ?></div>
      </form>
<?php endif; ?>
    </div>
  </div>
</section>
<?php page_fin(); ?>
