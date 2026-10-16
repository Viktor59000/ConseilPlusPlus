<?php
/**
 * cabinet.php — Le cabinet : l'équipe, les valeurs, quelques chiffres et des témoignages.
 * Contenus : data/textes.php (« equipe », « valeurs »). Portraits facultatifs : assets/equipe/<cle>.webp ;
 * sans image, un monogramme entouré au stylo les remplace.
 */
session_start();

$titre = 'Le cabinet';
$description = 'Le cabinet ConseilPlus+ : un conseiller moyen, un stagiaire depuis 2009, une machine à café et des valeurs moyennes.';
$page = 'cabinet';
include("includes/header.php");
$t = textes();
?>

<section class="conteneur entete-page">
  <p class="etiquette">Qui sommes-nous</p>
  <h1>Le cabinet</h1>
  <p class="chapo">Fondé un mardi, faute de mieux. Depuis, une petite équipe délivre des conseils moyens avec un sérieux <span class="surligne">exemplaire</span>.</p>
</section>

<section class="conteneur" aria-labelledby="titre-equipe">
  <h2 id="titre-equipe">L'équipe</h2>
  <ul class="equipe">
<?php foreach ($t['equipe'] as $m):
    $portrait = 'assets/equipe/' . $m['cle'] . '.webp'; ?>
    <li class="membre">
<?php if (is_file(__DIR__ . '/' . $portrait)): ?>
      <img class="membre-photo" src="<?= $portrait ?>" alt="" width="640" height="640" loading="lazy" decoding="async">
<?php else: ?>
      <span class="membre-portrait membre-monogramme entoure" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr(preg_replace('/^(Le |La )/', '', $m['nom']), 0, 1))) ?><?= boucle_stylo() ?></span>
<?php endif; ?>
      <h3><?= htmlspecialchars($m['nom']) ?></h3>
      <p class="membre-role mono"><?= htmlspecialchars($m['role']) ?></p>
      <p><?= htmlspecialchars($m['bio']) ?></p>
      <dl class="membre-fiche">
<?php foreach ($m['fiche'] as $intitule => $valeur): ?>
        <div><dt><?= htmlspecialchars($intitule) ?></dt><dd><?= htmlspecialchars($valeur) ?></dd></div>
<?php endforeach; ?>
      </dl>
    </li>
<?php endforeach; ?>
  </ul>
</section>

<section class="bande" aria-labelledby="titre-valeurs">
  <div class="conteneur">
    <p class="etiquette">Charte interne</p>
    <h2 id="titre-valeurs">Nos valeurs, affichées dans la salle de pause</h2>
    <ol class="etapes">
<?php foreach ($t['valeurs'] as $i => [$valeur, $explication]): ?>
      <li class="valeur">
        <span class="etape-num entoure" aria-hidden="true"><?= $i + 1 ?><?= boucle_stylo() ?></span>
        <h3><?= htmlspecialchars($valeur) ?></h3>
        <p><?= htmlspecialchars($explication) ?></p>
      </li>
<?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="conteneur" aria-labelledby="titre-chiffres">
  <h2 id="titre-chiffres">Le cabinet en quelques chiffres</h2>
  <dl class="chiffres-cabinet">
    <div><dt>Conseils suivis à notre connaissance</dt><dd>0</dd></div>
    <div><dt>Stagiaire, depuis 2009</dt><dd>1</dd></div>
    <div><dt>Délai moyen de traitement</dt><dd>entre 3 jours et jamais</dd></div>
    <div><dt>Dossiers sans numéro</dt><dd>aucun</dd></div>
  </dl>
</section>

<section class="conteneur temoignages" aria-labelledby="titre-temoignages">
  <h2 id="titre-temoignages">Ils en parlent mieux que nous</h2>
  <div class="temoignages-grille">
    <?= bloc_temoignages(3) ?>
  </div>
</section>

<section class="conteneur appel">
  <div>
    <h2>Une question pour l'équipe ?</h2>
    <p class="chapo">Le conseiller vous écoute. Il ne vous entend pas, mais il vous écoute.</p>
  </div>
  <a class="btn btn-tampon avance" href="contact.php">Nous contacter</a>
</section>

<?php include("includes/footer.php"); ?>
