<?php
/**
 * feuilleton.php — « La Plume et le Tampon », le feuilleton du Petit Moyen : l'épisode de la semaine (ou ?episode=N),
 * « Précédemment au cabinet », le script joué réplique par réplique par le lecteur de scènes (js/sketch.js, voix
 * des personnages dans data-reglages), la distribution et la liste des épisodes.
 * Banque : data/feuilleton.php ; lecture : includes/feuilleton.php. Sans JavaScript, le script complet est affiché.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/feuilleton.php';

$banque = feuilleton();
$semaine = numero_de_la_semaine();
$demande = filter_input(INPUT_GET, 'episode', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => count($banque['episodes'])]]);
$episode = episode($demande ?: $semaine);
$noms = noms_feuilleton();
$minutes = max(1, (int) round(mots_episode($episode) / 150)); // lecture à voix haute : environ 150 mots par minute

// Voix de synthèse des personnages qui parlent (le Sceau est muet : sa réplique s'affiche sans être lue)
$reglages = array_filter(array_map(fn ($p) => $p['voix'], $banque['personnages']));
// Distribution de l'épisode : les personnages qui y ont une réplique, dans l'ordre d'entrée
$presents = ['heraut' => true];
foreach ($episode['scenes'] as $scene) {
    foreach ($scene['repliques'] as $r) {
        if ($r['qui'] !== 'scene') {
            $presents[$r['qui']] = true;
        }
    }
}
$derniere_scene = count($episode['scenes']) - 1;

page_debut([
    'titre' => $banque['titre'] . ', épisode ' . $episode['numero'],
    'description' => 'La Plume et le Tampon, le feuilleton de cape et d\'épée du cabinet ConseilPlus+ : épisode ' . $episode['numero'] . ', « ' . $episode['titre'] . ' ».',
    'page' => 'petit-moyen',
    'canonical' => url_publique() . '/feuilleton.php' . ($demande ? '?episode=' . $episode['numero'] : ''),
    'css' => ['css/v2/pages/petit-moyen.css', 'css/v2/pages/feuilleton.css'],
    'scripts' => ['js/sketch.js'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <p class="reference"><span><a href="petit-moyen.php">Le Petit Moyen</a></span><span>Feuilleton</span><span>Saison <?= (int) $banque['saison'] ?></span></p>
      <h1 id="heros-titre"><?= e($banque['titre']) ?></h1>
      <p class="heros__chapeau">Chaque semaine, l'équipe du cabinet enfile des costumes en carton et joue un épisode de cape et d'épée au Château des Moyennes. Trois scènes, un Héraut, une fin en suspens.</p>
      <div class="boutons">
        <?= bouton('Voir l\'épisode', '#episode') ?>
        <?= bouton('Tous les épisodes', '#episodes', ['variante' => 'secondaire']) ?>
      </div>
      <dl class="heros__faits">
        <div><dt>Épisode</dt><dd><?= (int) $episode['numero'] ?> sur <?= count($banque['episodes']) ?></dd></div>
        <div><dt>Durée</dt><dd>≈ <?= $minutes ?> min</dd></div>
        <div><dt>Diffusion</dt><dd>un par semaine</dd></div>
      </dl>
    </div>
    <div class="heros__visuel">
      <div class="affiche" data-papier>
        <p class="affiche__haut"><span>Le cabinet présente</span><span><?= $episode['numero'] === $semaine ? 'Cette semaine' : 'En reprise' ?></span></p>
        <p class="affiche__numero" aria-hidden="true"><?= sprintf('%02d', $episode['numero']) ?></p>
        <p class="affiche__titre">« <?= e($episode['titre']) ?> »</p>
        <p class="affiche__distribution">Avec <?= e(implode(', ', array_map(fn ($c) => $noms[$c], array_keys(array_diff_key($presents, ['heraut' => 1, 'cour' => 1]))))) ?>.</p>
        <?= tampon('Épisode ' . $episode['numero'], ['taille' => 'grand', 'angle' => -8, 'mouvement' => 'au-chargement', 'decoratif' => true]) ?>
      </div>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="episode" aria-labelledby="episode-titre">
  <div class="conteneur grille">
    <aside class="c-5 episode-cote">
      <div class="ecran__tete">
        <p class="reference"><span>Épisode <?= (int) $episode['numero'] ?></span><span>≈ <?= $minutes ?> min</span></p>
        <h2 id="episode-titre"><?= e($episode['titre']) ?></h2>
      </div>
<?php if (!empty($episode['precedemment'])): ?>
      <div class="precedemment">
        <h3 class="precedemment__titre">Précédemment au cabinet</h3>
        <p><?= e($episode['precedemment']) ?></p>
      </div>
<?php else: ?>
      <p class="precedemment precedemment--premier">Premier épisode : il ne s'est encore rien passé, ce qui n'a jamais empêché le cabinet de s'en souvenir.</p>
<?php endif; ?>
      <div class="distribution">
        <h3 class="precedemment__titre">Distribution</h3>
        <dl>
<?php foreach (array_keys($presents) as $cle): $p = $banque['personnages'][$cle]; ?>
          <div><dt><?= e($p['nom']) ?></dt><dd><?= e($p['role']) ?></dd></div>
<?php endforeach; ?>
        </dl>
      </div>
    </aside>

    <div class="d-7 sketch episode" data-sketch data-reglages="<?= e(json_encode($reglages)) ?>">
      <div class="sketch-commandes" hidden>
        <button type="button" class="bouton bouton--principal" data-jouer="l'épisode">▶ Jouer l'épisode</button>
        <label class="sketch-voix"><input type="checkbox" data-voix> Avec les voix</label>
      </div>
      <div class="sketch-court">
        <p class="replique replique-heraut replique-annonce" data-qui="heraut"><span class="replique-qui mono"><?= e($noms['heraut']) ?></span> <span class="replique-texte"><?= e($episode['heraut']) ?></span></p>
<?php foreach ($episode['scenes'] as $i => $scene): $fin = count($scene['repliques']) - 1; ?>
        <h3 class="episode__scene" data-qui="titre"><span class="machine">Scène <?= $i + 1 ?></span> <?= e($scene['titre']) ?></h3>
        <ol class="sketch-script">
          <li class="sketch-scene" data-qui="scene"><?= e($scene['didascalie']) ?></li>
<?php foreach ($scene['repliques'] as $j => $r):
    $chute = $i === $derniere_scene && $j === $fin;
    if ($r['qui'] === 'scene'): ?>
          <li class="sketch-scene" data-qui="scene"><?= e($r['texte']) ?></li>
<?php else: ?>
          <li class="replique replique-<?= e($r['qui']) ?><?= $chute ? ' replique-chute' : '' ?>" data-qui="<?= e($r['qui']) ?>">
            <span class="replique-qui mono"><?= e($noms[$r['qui']] ?? $r['qui']) ?></span>
            <span><?= !empty($r['jeu']) ? '<span class="replique-jeu">(' . e($r['jeu']) . ')</span> ' : '' ?><span class="replique-texte"><?= $chute ? '<mark>' . e($r['texte']) . '</mark>' : e($r['texte']) ?></span></span>
          </li>
<?php endif; endforeach; ?>
        </ol>
<?php endforeach; ?>
        <p class="replique replique-heraut replique-suspense" data-qui="heraut"><span class="replique-qui mono"><?= e($noms['heraut']) ?></span> <span class="replique-texte"><?= e($episode['suspense']) ?></span></p>
      </div>
    </div>
  </div>
</section>

<section class="ecran" id="episodes" aria-labelledby="episodes-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <p class="reference"><span>Saison <?= (int) $banque['saison'] ?></span><span><?= count($banque['episodes']) ?> épisodes</span></p>
      <h2 id="episodes-titre">Tous les épisodes</h2>
      <p class="chapeau muet">Un épisode par semaine, le même pour tout le monde. Quand la saison est finie, elle recommence : le cabinet appelle cela une rediffusion, et le public une habitude.</p>
    </div>
    <ol class="liste-episodes" role="list">
<?php foreach (liste_episodes() as $e): ?>
      <li>
        <a class="liste-episodes__lien" href="feuilleton.php?episode=<?= $e['numero'] ?>#episode"<?= $e['numero'] === $episode['numero'] ? ' aria-current="page"' : '' ?>>
          <span class="liste-episodes__numero mono"><?= sprintf('%02d', $e['numero']) ?></span>
          <span class="liste-episodes__titre"><?= e($e['titre']) ?></span>
<?php if ($e['numero'] === $semaine): ?>
          <span class="statut statut--repondu">Cette semaine</span>
<?php endif; ?>
        </a>
      </li>
<?php endforeach; ?>
    </ol>
  </div>
</section>
<?php page_fin(); ?>
