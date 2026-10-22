<?php
/**
 * petit-moyen.php — Le Petit Moyen : archives du mot (presque) du jour.
 * Affiche le mot du jour puis les mots des jours précédents (table mots_du_jour), jamais ceux à venir.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/mot-du-jour.php';

$mot_jour = mot_du_jour($bdd);
$jours = $bdd->query('SELECT jour FROM mots_du_jour WHERE jour < CURDATE() ORDER BY jour DESC LIMIT 60')->fetchAll(PDO::FETCH_COLUMN);
$archives = array_map(fn($j) => lire_mot($bdd, $j), $jours);

// Feuilleter : une page du dictionnaire (?page=N), au hasard si aucune n'est demandée
$pages = pages_dictionnaire();
$total = count($pages);
$numero = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => $total]])
    ?: random_int(1, $total);
$feuillet = $pages[$numero - 1];
$autre = ($numero + random_int(1, $total - 1) - 1) % $total + 1; // une autre page, jamais la même
$lettres = lettres_dictionnaire();
$etymologies = count(array_filter($pages, fn($p) => !empty($p['etymologie'])));

// Le sigle du jour (abréviationnisme), tiré selon la date comme le mot
$sigle = sigle_du_jour();
$sigles = sigles_banque();

// Palmarès du public : les mots qui ont reçu le plus de « Moyen » (au moins un vote positif)
$palmares = $bdd->query("SELECT mot, SUM(vote = 'moyen') AS moyen, SUM(vote = 'pas_moyen') AS pas_moyen
                         FROM votes GROUP BY mot HAVING moyen > 0
                         ORDER BY moyen - pas_moyen DESC, moyen DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$titre = 'Le Petit Moyen';
$description = 'Le Petit Moyen, dictionnaire officieux de ConseilPlus+ : un mot (presque) du jour, défini avec une précision moyenne.';
$page = 'petit-moyen';
include __DIR__ . '/includes/header.php';
?>

<section class="conteneur entete-page">
  <h1>Le Petit Moyen</h1>
  <p class="chapo">Le dictionnaire du cabinet. Un mot par jour, parfois un jeu de mots, parfois pas, toujours défini avec une précision moyenne. Certains jours, le conseiller et son stagiaire tentent même une démonstration.</p>
  <p class="pm-chiffres mono"><?= $total ?> pages · <?= $etymologies ?> étymologies établies par le cabinet · <?= count($sigles['sigles']) ?> sigles redéfinis</p>
</section>

<section class="conteneur" aria-labelledby="titre-aujourdhui">
  <h2 id="titre-aujourdhui">Aujourd'hui</h2>
<?php $mot = $mot_jour; $titre_niveau = 'h3'; include __DIR__ . '/includes/carte-mot.php'; ?>
</section>

<section class="conteneur" aria-labelledby="titre-sigle">
  <h2 id="titre-sigle">Le sigle du jour</h2>
  <p class="chapo">Glossaire de l'abréviationnisme, mouvement interne du cabinet.</p>
  <article class="sigle-jour">
    <p class="sigle-lettres"><abbr title="<?= htmlspecialchars($sigle['developpement']) ?>"><?= htmlspecialchars($sigle['sigle']) ?></abbr></p>
    <div>
      <p class="sigle-developpement"><?= htmlspecialchars($sigle['developpement']) ?></p>
      <p class="sigle-definition"><?= htmlspecialchars($sigle['definition']) ?></p>
      <p class="sigle-exemple"><span class="mono">En réunion</span> « <?= htmlspecialchars($sigle['exemple']) ?> »</p>
    </div>
  </article>
  <details class="sommaire">
    <summary>Le glossaire complet : <?= count($sigles['sigles']) ?> sigles</summary>
    <p class="glossaire-intro"><?= htmlspecialchars($sigles['introduction']) ?></p>
    <dl class="glossaire">
<?php foreach ($sigles['sigles'] as $s): ?>
      <div><dt><?= htmlspecialchars($s['sigle']) ?> <span><?= htmlspecialchars($s['developpement']) ?></span></dt><dd><?= htmlspecialchars($s['definition']) ?> <em>« <?= htmlspecialchars($s['exemple']) ?> »</em></dd></div>
<?php endforeach; ?>
    </dl>
  </details>
</section>

<section class="conteneur feuilleter" id="feuilleter" aria-labelledby="titre-feuilleter">
  <h2 id="titre-feuilleter">Feuilleter le dictionnaire</h2>
  <p class="chapo">Vous ne pouvez pas attendre demain ? Le cabinet comprend. Ouvrez le Petit Moyen à n'importe quelle page.</p>
<?php $mot = $feuillet; $titre_niveau = 'h3'; include __DIR__ . '/includes/carte-mot.php'; ?>
  <div class="actions">
    <a class="btn btn-tampon" href="petit-moyen.php?page=<?= $autre ?>#feuilleter">Ouvrir à une autre page</a>
  </div>
  <nav class="lettrines" aria-label="Ouvrir le dictionnaire à la lettre">
<?php foreach ($lettres as $lettre => $premiere): ?>
    <a href="petit-moyen.php?page=<?= $premiere ?>#feuilleter"><?= $lettre ?></a>
<?php endforeach; ?>
  </nav>
  <details class="sommaire">
    <summary>Sommaire : les <?= $total ?> mots du dictionnaire</summary>
    <ol class="sommaire-liste">
<?php foreach ($pages as $p): ?>
      <li><a href="petit-moyen.php?page=<?= $p['page'] ?>#feuilleter"<?= $p['page'] === $numero ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($p['mot']) ?></a><?= $p['format'] === 'sketch' ? ' <span class="sommaire-sketch">(sketch)</span>' : '' ?></li>
<?php endforeach; ?>
    </ol>
  </details>
  <details class="sommaire">
    <summary>Abréviations et conventions du Petit Moyen</summary>
    <dl class="conventions">
      <div><dt>n. m., n. f., v. tr., adj.</dt><dd>Nature grammaticale, établie par le cabinet à l'issue d'un vote à main levée.</dd></div>
      <div><dt>Étymologie</dt><dd>Origine du mot, reconstituée à partir de registres que le cabinet est seul à avoir consultés. Les dates sont exactes au siècle près.</dd></div>
      <div><dt>Attesté</dt><dd>Vu au moins une fois par quelqu'un de confiance.</dd></div>
      <div><dt>Source fragile</dt><dd>Vu une fois, de loin, par temps de brouillard.</dd></div>
      <div><dt>Synonyme douteux</dt><dd>Mot que le cabinet a jugé interchangeable, sans l'avoir essayé.</dd></div>
      <div><dt>Découpage officiel</dt><dd>Démonstration sonore du stagiaire, tamponnée sans réserve.</dd></div>
      <div><dt>Certitude</dt><dd>Niveau de confiance du cabinet dans sa propre définition, en pourcentage. Au-dessous de 50 %, l'entrée est maintenue par principe.</dd></div>
    </dl>
  </details>
</section>

<?php if ($archives): ?>
<?php if ($palmares): ?>
<section class="conteneur" aria-labelledby="titre-palmares">
  <h2 id="titre-palmares">Les mots préférés du public</h2>
  <ol class="honneur">
<?php foreach ($palmares as $i => $p): ?>
    <li>
      <span class="honneur-rang<?= $i === 0 ? ' entoure' : '' ?>" aria-hidden="true"><?= $i + 1 ?><?= $i === 0 ? boucle_stylo() : '' ?></span>
      <strong><?= htmlspecialchars($p['mot']) ?></strong>
      <span>Moyen <?= (int) $p['moyen'] ?> · Pas moyen <?= (int) $p['pas_moyen'] ?></span>
      <span></span>
    </li>
<?php endforeach; ?>
  </ol>
</section>

<?php endif; ?>
<section class="conteneur" aria-labelledby="titre-archives">
  <h2 id="titre-archives">Les jours précédents</h2>
  <ul class="archives">
<?php foreach ($archives as $mot): ?>
    <li>
      <details class="archive">
        <summary>
          <span class="archive-date mono"><?= htmlspecialchars((new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Europe/Paris', null, 'd MMM y'))->format(new DateTime($mot['jour']))) ?></span>
          <span class="archive-mot"><?= htmlspecialchars($mot['mot']) ?></span>
          <span class="archive-extrait"><?= htmlspecialchars(chute_sketch($mot) ?? $mot['definition']) ?></span>
        </summary>
<?php $titre_niveau = 'h3'; include __DIR__ . '/includes/carte-mot.php'; ?>
      </details>
    </li>
<?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<script src="<?= fichier_versionne('js/sketch.js') ?>" defer></script>
<script src="<?= fichier_versionne('js/partage.js') ?>" defer></script>
<script src="<?= fichier_versionne('js/votes.js') ?>" defer></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
