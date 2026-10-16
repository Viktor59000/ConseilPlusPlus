<?php
/**
 * diagnostic.php — « Quel client êtes-vous ? », le diagnostic du conseiller moyen.
 * Formulaire GET (aucune donnée enregistrée), résultat calculé ici : fonctionne sans JavaScript.
 * Contenus : data/diagnostic.php.
 */
session_start();
$d = require __DIR__ . '/data/diagnostic.php';

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

$titre = 'Quel client êtes-vous ?';
$description = 'Le diagnostic du conseiller moyen : sept questions pour savoir quel client moyen vous êtes. Résultat garanti moyen.';
$page = 'autre';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Le diagnostic du conseiller</p>
  <h1>Quel client êtes-vous ?</h1>
  <p class="chapo">Sept questions, établies par le conseiller moyen à partir de son expérience, qui est longue. Le résultat est garanti : vous êtes moyen. Reste à savoir lequel.</p>
</section>

<?php if ($profil): ?>
<section class="conteneur" aria-labelledby="titre-resultat" id="resultat">
  <div class="diagnostic-resultat">
    <p class="mono diagnostic-label">Résultat du diagnostic</p>
    <h2 id="titre-resultat"><?= htmlspecialchars($profil[0]) ?></h2>
    <p class="diagnostic-texte"><?= htmlspecialchars($profil[1]) ?></p>
    <p class="mono diagnostic-taux">Taux de moyenneté : <?= $moyennete ?> % (exactement la moyenne)</p>
    <p class="diagnostic-tampon" aria-hidden="true">Diagnostiqué</p>
    <div class="actions">
      <button type="button" class="btn btn-tampon" hidden data-partager="texte" data-nom="diagnostic"
        data-titre="<?= htmlspecialchars($profil[0]) ?>" data-texte="<?= htmlspecialchars($profil[1]) ?>"
        data-detail="Taux de moyenneté : <?= $moyennete ?> %." data-etiquette="Diagnostic officiel du conseiller moyen"
        data-url="diagnostic.php">Partager mon résultat</button>
      <a class="btn btn-clair" href="diagnostic.php">Refaire le diagnostic</a>
    </div>
  </div>
</section>
<?php else: ?>
<section class="conteneur" aria-labelledby="titre-questions">
  <h2 id="titre-questions" class="visuellement-cache">Les questions</h2>
<?php if ($incomplet): ?>
  <p class="alerte alerte-erreur" role="alert">Le conseiller a besoin de toutes vos réponses pour vous trouver moyen.</p>
<?php endif; ?>
  <form class="diagnostic" method="get" action="diagnostic.php#resultat">
<?php foreach ($d['questions'] as $i => [$question, $choix]): ?>
    <fieldset class="diagnostic-question">
      <legend><span class="mono"><?= $i + 1 ?>.</span> <?= htmlspecialchars($question) ?></legend>
<?php foreach ($choix as $cle => $libelle): ?>
      <label class="diagnostic-choix"><input type="radio" name="q<?= $i ?>" value="<?= $cle ?>" required<?= ($reponses[$i] ?? '') === $cle ? ' checked' : '' ?>> <?= htmlspecialchars($libelle) ?></label>
<?php endforeach; ?>
    </fieldset>
<?php endforeach; ?>
    <div class="actions">
      <button class="btn btn-tampon" type="submit">Obtenir mon diagnostic</button>
    </div>
  </form>
</section>
<?php endif; ?>

<script src="<?= fichier_versionne('js/partage.js') ?>" defer></script>
<?php include("includes/footer.php"); ?>
