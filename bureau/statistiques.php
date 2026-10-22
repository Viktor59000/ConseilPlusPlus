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
<div class="entete-page">
  <div>
    <p class="etiquette mono">Tableau de bord technique</p>
    <h1>Statistiques de l'API</h1>
    <p class="aide">Chaque échange avec l'API du site est journalisé (<code>api_logs</code>). Ces chiffres sont lus par l'API elle-même : <code>GET /api/statistiques.php?jours=<?= $jours ?></code>.</p>
  </div>
  <nav class="periode" aria-label="Période">
<?php foreach ([7, 14, 30] as $j): ?>
    <a href="?jours=<?= $j ?>"<?= $j === $jours ? ' aria-current="page"' : '' ?>><?= $j ?> jours</a>
<?php endforeach; ?>
  </nav>
</div>

<?php if (!$stats): ?>
<p class="annonce annonce-erreur">Statistiques indisponibles : <?= htmlspecialchars(lcfirst(message_erreur_api($r))) ?>.</p>
<?php else:
    $max = max(array_column($stats['par_jour'], 'appels') ?: [1]) ?: 1; ?>
<section class="chiffres" aria-label="Résumé">
  <div class="chiffre"><span class="chiffre-valeur"><?= $stats['total'] ?></span><span class="chiffre-libelle">appels sur <?= $jours ?> jours</span></div>
  <div class="chiffre"><span class="chiffre-valeur"><?= round($stats['total'] / $jours, 1) ?></span><span class="chiffre-libelle">appels par jour en moyenne</span></div>
  <div class="chiffre"><span class="chiffre-valeur"><?= $stats['erreurs'] ?></span><span class="chiffre-libelle">réponses en erreur (codes 4xx et 5xx)</span></div>
  <div class="chiffre"><span class="chiffre-valeur"><?= $stats['total'] ? round(100 * ($stats['total'] - $stats['erreurs']) / $stats['total']) : 100 ?> %</span><span class="chiffre-libelle">de réponses réussies</span></div>
</section>

<section class="carte">
  <h2>Appels par jour</h2>
  <ol class="histogramme" data-jours="<?= $jours ?>" aria-label="Appels par jour">
<?php foreach ($stats['par_jour'] as $j):
      $date = new DateTime($j['jour']); ?>
    <li style="--h: <?= round(100 * $j['appels'] / $max) ?>%">
      <span class="histogramme-valeur mono"><?= $j['appels'] ?: '' ?></span>
      <span class="histogramme-barre" aria-hidden="true"></span>
      <span class="histogramme-jour mono"><?= $date->format('d/m') ?><span class="visuellement-cache"> : <?= $j['appels'] ?> appel<?= $j['appels'] > 1 ? 's' : '' ?></span></span>
    </li>
<?php endforeach; ?>
  </ol>
</section>

<div class="colonnes">
  <section class="carte">
    <h2>Ressources les plus appelées</h2>
    <?= $stats['par_ressource'] ? barres($stats['par_ressource'], 'ressource') : '<p class="aide">Aucun appel sur la période.</p>' ?>
  </section>
  <section class="carte">
    <h2>Codes de réponse</h2>
    <?= $stats['par_code'] ? barres($stats['par_code'], 'code') : '<p class="aide">Aucun appel sur la période.</p>' ?>
    <h2>Applications appelantes</h2>
    <?= $stats['par_application'] ? barres($stats['par_application'], 'application') : '<p class="aide">Aucun appel sur la période.</p>' ?>
  </section>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/bas.php'; ?>
