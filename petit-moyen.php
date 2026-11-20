<?php
/**
 * petit-moyen.php — Le Petit Moyen, dictionnaire officieux du cabinet : le mot (presque) du jour et son sketch,
 * le sigle du jour, le dictionnaire à feuilleter, l'épisode du feuilleton (page à part : feuilleton.php),
 * les mots préférés du public et les archives (table mots_du_jour, jamais les jours à venir).
 * Le widget et l'API du mot du jour (api/mot-du-jour.php, api/widget.php) lisent les mêmes fonctions : rien ne change pour eux.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/mot-du-jour.php';
require_once __DIR__ . '/includes/feuilleton.php';

$mot_jour = mot_du_jour($bdd);
$jours = $bdd->query('SELECT jour FROM mots_du_jour WHERE jour < CURDATE() ORDER BY jour DESC LIMIT 60')->fetchAll(PDO::FETCH_COLUMN);
$archives = array_filter(array_map(fn($j) => lire_mot($bdd, $j), $jours));

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

// Le feuilleton : l'épisode de la semaine
$episode = episode_de_la_semaine();

// Palmarès du public : les mots qui ont reçu le plus de « Moyen » (au moins un vote positif)
$palmares = $bdd->query("SELECT mot, SUM(vote = 'moyen') AS moyen, SUM(vote = 'pas_moyen') AS pas_moyen
                         FROM votes GROUP BY mot HAVING moyen > 0
                         ORDER BY moyen - pas_moyen DESC, moyen DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$format_court = new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Europe/Paris', null, 'd MMM y');
$vedette = $mot_jour ? (chute_sketch($mot_jour) ?? $mot_jour['definition']) : null;

page_debut([
    'titre' => 'Le Petit Moyen',
    'description' => 'Le Petit Moyen, dictionnaire officieux de ConseilPlus+ : un mot (presque) du jour, défini avec une précision moyenne, un sigle redéfini et un feuilleton.',
    'page' => 'petit-moyen',
    'css' => ['css/v2/pages/petit-moyen.css'],
    'scripts' => ['js/sketch.js', 'js/partage.js', 'js/votes.js'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <p class="reference"><span>Dictionnaire officieux</span><span>Édition permanente</span></p>
      <h1 id="heros-titre">Le Petit Moyen</h1>
      <p class="heros__chapeau">Le dictionnaire du cabinet. Un mot par jour, parfois un jeu de mots, parfois pas, toujours défini avec une précision moyenne. Certains jours, le conseiller et son stagiaire tentent même une démonstration.</p>
      <div class="boutons">
        <?= bouton('Lire le mot du jour', '#aujourdhui') ?>
        <?= bouton('Le feuilleton', 'feuilleton.php', ['variante' => 'secondaire']) ?>
      </div>
      <dl class="heros__faits">
        <div><dt>Pages</dt><dd><?= $total ?></dd></div>
        <div><dt>Étymologies établies</dt><dd><?= $etymologies ?></dd></div>
        <div><dt>Sigles redéfinis</dt><dd><?= count($sigles['sigles']) ?></dd></div>
      </dl>
    </div>
<?php if ($mot_jour): ?>
    <div class="heros__visuel">
      <a class="pm-vedette" href="#aujourdhui" data-papier>
        <span class="pm-vedette__date mono"><?= e(ucfirst((new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris'))->format(new DateTime($mot_jour['jour'])))) ?></span>
        <span class="pm-vedette__mot"><?= e($mot_jour['mot']) ?></span>
        <span class="pm-vedette__nature"><?= e($mot_jour['nature']) ?></span>
        <span class="pm-vedette__texte"><?= e($vedette) ?></span>
        <span class="pm-vedette__suite"><span>Lire l'entrée complète</span><?= ui_fleche() ?></span>
        <?= tampon('Mot du jour', ['angle' => 7, 'mouvement' => 'au-chargement', 'decoratif' => true]) ?>
      </a>
    </div>
<?php endif; ?>
  </div>
</section>

<?php if ($mot_jour): ?>
<section class="ecran ecran--papier" id="aujourdhui" aria-labelledby="aujourdhui-titre">
  <div class="conteneur grille">
    <div class="c-5">
      <div class="ecran__tete">
        <p class="reference"><span>Entrée du jour</span></p>
        <h2 id="aujourdhui-titre">Aujourd'hui</h2>
        <p class="chapeau muet"><?= $mot_jour['format'] === 'sketch'
            ? 'Le stagiaire lit le mot de travers, le conseiller le reprend, la scène tranche. Jouez-la, avec ou sans les voix.'
            : 'Une définition établie par le cabinet, un exemple d\'usage et un synonyme qu\'il n\'a pas essayé.' ?></p>
      </div>
    </div>
    <div class="d-7 pm-entree">
<?php $mot = $mot_jour; $titre_niveau = 'h3'; include __DIR__ . '/includes/carte-mot.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="ecran" id="sigle" aria-labelledby="sigle-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <p class="reference"><span>Abréviationnisme</span><span>Mouvement interne du cabinet</span></p>
      <h2 id="sigle-titre">Le sigle du jour</h2>
    </div>
    <article class="sigle-jour">
      <p class="sigle-jour__lettres"><abbr title="<?= e($sigle['developpement']) ?>"><?= e($sigle['sigle']) ?></abbr></p>
      <div class="sigle-jour__corps">
        <p class="sigle-jour__developpement"><?= e($sigle['developpement']) ?></p>
        <p class="sigle-jour__definition"><?= e($sigle['definition']) ?></p>
        <p class="sigle-jour__exemple"><?= machine('En réunion') ?> « <?= e($sigle['exemple']) ?> »</p>
      </div>
    </article>
    <details class="volet">
      <summary>Le glossaire complet : <?= count($sigles['sigles']) ?> sigles</summary>
      <p class="volet__intro"><?= e($sigles['introduction']) ?></p>
      <dl class="glossaire">
<?php foreach ($sigles['sigles'] as $s): ?>
        <div><dt><?= e($s['sigle']) ?> <span><?= e($s['developpement']) ?></span></dt><dd><?= e($s['definition']) ?> <em>« <?= e($s['exemple']) ?> »</em></dd></div>
<?php endforeach; ?>
      </dl>
    </details>
  </div>
</section>

<section class="ecran ecran--fond-2" id="feuilleter" aria-labelledby="feuilleter-titre">
  <div class="conteneur grille">
    <div class="c-5 pm-feuilleter">
      <div class="ecran__tete">
        <p class="reference"><span>Page <?= $numero ?> sur <?= $total ?></span></p>
        <h2 id="feuilleter-titre">Feuilleter le dictionnaire</h2>
        <p class="chapeau muet">Vous ne pouvez pas attendre demain ? Le cabinet comprend. Ouvrez le Petit Moyen à n'importe quelle page.</p>
      </div>
      <div class="boutons"><?= bouton('Ouvrir à une autre page', 'petit-moyen.php?page=' . $autre . '#feuilleter', ['variante' => 'secondaire']) ?></div>
      <nav class="lettrines" aria-label="Ouvrir le dictionnaire à la lettre">
<?php foreach ($lettres as $lettre => $premiere): ?>
        <a href="petit-moyen.php?page=<?= $premiere ?>#feuilleter"><?= e((string) $lettre) ?></a>
<?php endforeach; ?>
      </nav>
    </div>
    <div class="d-7 pm-entree">
<?php $mot = $feuillet; $titre_niveau = 'h3'; include __DIR__ . '/includes/carte-mot.php'; ?>
      <details class="volet">
        <summary>Sommaire : les <?= $total ?> mots du dictionnaire</summary>
        <ol class="sommaire-liste">
<?php foreach ($pages as $p): ?>
          <li><a href="petit-moyen.php?page=<?= $p['page'] ?>#feuilleter"<?= $p['page'] === $numero ? ' aria-current="page"' : '' ?>><?= e($p['mot']) ?></a><?= $p['format'] === 'sketch' ? ' <span class="muet">(sketch)</span>' : '' ?></li>
<?php endforeach; ?>
        </ol>
      </details>
      <details class="volet">
        <summary>Abréviations et conventions du Petit Moyen</summary>
        <dl class="glossaire">
          <div><dt>n. m., n. f., v. tr., adj.</dt><dd>Nature grammaticale, établie par le cabinet à l'issue d'un vote à main levée.</dd></div>
          <div><dt>Étymologie</dt><dd>Origine du mot, reconstituée à partir de registres que le cabinet est seul à avoir consultés. Les dates sont exactes au siècle près.</dd></div>
          <div><dt>Attesté</dt><dd>Vu au moins une fois par quelqu'un de confiance.</dd></div>
          <div><dt>Source fragile</dt><dd>Vu une fois, de loin, par temps de brouillard.</dd></div>
          <div><dt>Synonyme douteux</dt><dd>Mot que le cabinet a jugé interchangeable, sans l'avoir essayé.</dd></div>
          <div><dt>Découpage officiel</dt><dd>Démonstration sonore du stagiaire, tamponnée sans réserve.</dd></div>
          <div><dt>Certitude</dt><dd>Niveau de confiance du cabinet dans sa propre définition, en pourcentage. Au-dessous de 50 %, l'entrée est maintenue par principe.</dd></div>
        </dl>
      </details>
    </div>
  </div>
</section>

<section class="ecran ecran--kraft" id="feuilleton" aria-labelledby="feuilleton-titre">
  <div class="conteneur grille">
    <div class="c-6">
      <div class="ecran__tete">
        <p class="reference"><span>Feuilleton</span><span>Saison <?= (int) feuilleton()['saison'] ?></span><span>Épisode <?= (int) $episode['numero'] ?></span></p>
        <h2 id="feuilleton-titre"><?= e(feuilleton()['titre']) ?></h2>
        <p class="chapeau">Chaque semaine, l'équipe du cabinet joue un épisode de cape et d'épée en costume de carton. Cette semaine : <strong>« <?= e($episode['titre']) ?> »</strong>.</p>
      </div>
      <div class="boutons"><?= bouton('Voir l\'épisode', 'feuilleton.php') ?></div>
    </div>
    <div class="d-8 pm-episode">
      <p class="machine">Le Héraut</p>
      <blockquote class="pm-episode__heraut"><p><?= e($episode['heraut']) ?></p></blockquote>
    </div>
  </div>
</section>

<?php if ($archives): ?>
<section class="ecran" id="archives" aria-labelledby="archives-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <p class="reference"><span>Archives</span><span><?= count($archives) ?> jours</span></p>
      <h2 id="archives-titre">Les jours précédents</h2>
      <p class="chapeau muet">Chaque mot du jour, rangé à sa date. Le cabinet ne publie jamais ceux de demain, par prudence.</p>
    </div>
<?php if ($palmares): ?>
    <div class="palmares">
      <h3 class="palmares__titre">Les mots préférés du public</h3>
      <ol class="palmares__liste">
<?php foreach ($palmares as $i => $p): ?>
        <li><span class="palmares__rang mono" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span><strong><?= e($p['mot']) ?></strong><span class="mono muet">Moyen <?= (int) $p['moyen'] ?> · Pas moyen <?= (int) $p['pas_moyen'] ?></span></li>
<?php endforeach; ?>
      </ol>
    </div>
<?php endif; ?>
    <ul class="archives" role="list">
<?php foreach ($archives as $mot): ?>
      <li>
        <details class="archive">
          <summary>
            <span class="archive__date mono"><?= e($format_court->format(new DateTime($mot['jour']))) ?></span>
            <span class="archive__mot"><?= e($mot['mot']) ?></span>
            <span class="archive__extrait"><?= e(chute_sketch($mot) ?? $mot['definition']) ?></span>
          </summary>
<?php $titre_niveau = 'h3'; include __DIR__ . '/includes/carte-mot.php'; ?>
        </details>
      </li>
<?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>
<?php page_fin(); ?>
