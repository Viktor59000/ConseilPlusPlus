<?php
// bureau/statistiques.php — Usage de l'API du site, lu par l'API elle-même (GET /api/statistiques.php, clé requise).
require_once __DIR__ . '/includes/amorce.php';

$jours = (int) ($_GET['jours'] ?? 14);
$jours = in_array($jours, [7, 14, 30], true) ? $jours : 14;
$r = api('GET', 'statistiques.php?jours=' . $jours);
$stats = $r['code'] === 200 ? $r['data'] : null;

$titre = 'Statistiques de l\'API';
$page = 'statistiques';
require __DIR__ . '/includes/haut.php';
?>
<header class="tete">
  <div class="tete__texte">
    <p class="tete__ref"><span>Tableau technique</span><span><code>api_logs</code></span></p>
    <h1>Statistiques de l'API</h1>
    <p class="tete__sous">Chaque échange avec l'API du site est journalisé. Ces chiffres sont lus par l'API elle-même : <code>GET /api/statistiques.php?jours=<?= $jours ?></code>.</p>
  </div>
  <nav class="segments tete__actions" aria-label="Période">
<?php foreach ([7, 14, 30] as $j): ?>
    <a href="?jours=<?= $j ?>"<?= $j === $jours ? ' aria-current="page"' : '' ?>><?= $j ?> jours</a>
<?php endforeach; ?>
  </nav>
</header>

<div class="zone">
<?php if (!$stats): ?>
  <?= bloc_alerte('Statistiques indisponibles : ' . lcfirst(message_erreur_api($r)) . '.', 'erreur') ?>
<?php else:
    $lignes = array_map(fn ($j) => ['libelle' => (new DateTime($j['jour']))->format($jours > 14 ? 'd' : 'd/m'), 'valeur' => (int) $j['appels']], $stats['par_jour']); ?>
  <section class="tuiles" aria-label="Résumé">
    <div class="tuile tuile--accent"><span class="tuile__libelle"><?= picto_bureau('statistiques') ?>Appels</span><span class="tuile__valeur"><?= (int) $stats['total'] ?></span><span class="tuile__detail">sur <?= $jours ?> jours</span></div>
    <div class="tuile"><span class="tuile__libelle">Par jour</span><span class="tuile__valeur"><?= e(graphique_nombre(round($stats['total'] / $jours, 1))) ?></span><span class="tuile__detail">en moyenne</span></div>
    <div class="tuile<?= $stats['erreurs'] ? ' tuile--alerte' : '' ?>"><span class="tuile__libelle">Erreurs</span><span class="tuile__valeur"><?= (int) $stats['erreurs'] ?></span><span class="tuile__detail">réponses 4xx et 5xx</span></div>
    <div class="tuile"><span class="tuile__libelle">Réussite</span><span class="tuile__valeur"><?= $stats['total'] ? round(100 * ($stats['total'] - $stats['erreurs']) / $stats['total']) : 100 ?> %</span><span class="tuile__detail">des réponses</span></div>
  </section>

  <section class="panneau" aria-labelledby="titre-par-jour">
    <div class="panneau__tete"><h2 id="titre-par-jour"><?= picto_bureau('statistiques') ?>Appels par jour</h2><span class="note-b">Aujourd'hui en laiton</span></div>
    <div class="panneau__corps">
      <?= graphique_barres($lignes, ['titre' => $jours . ' derniers jours', 'unite' => 'appels', 'entete' => 'Jour', 'accent' => count($lignes) - 1, 'hauteur' => 180]) ?>
    </div>
  </section>

  <div class="grille-b">
    <section class="panneau s-6" aria-labelledby="titre-ressources">
      <div class="panneau__tete"><h2 id="titre-ressources">Ressources les plus appelées</h2></div>
      <div class="panneau__corps"><?= $stats['par_ressource'] ? barres($stats['par_ressource'], 'ressource') : '<p class="note-b">Aucun appel sur la période.</p>' ?></div>
    </section>
    <section class="panneau s-6" aria-labelledby="titre-codes">
      <div class="panneau__tete"><h2 id="titre-codes">Codes de réponse</h2></div>
      <div class="panneau__corps"><?= $stats['par_code'] ? barres($stats['par_code'], 'code') : '<p class="note-b">Aucun appel sur la période.</p>' ?></div>
      <div class="panneau__tete panneau__tete--suite"><h2>Applications appelantes</h2></div>
      <div class="panneau__corps"><?= $stats['par_application'] ? barres($stats['par_application'], 'application') : '<p class="note-b">Aucun appel sur la période.</p>' ?></div>
    </section>
  </div>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
