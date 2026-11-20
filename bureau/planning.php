<?php
// bureau/planning.php — Planning des rendez-vous du guichet : vue semaine (jours en colonnes, créneaux en lignes),
// semaine précédente / suivante, puis la même semaine en liste (lecture linéaire, lecteurs d'écran).
require_once __DIR__ . '/includes/amorce.php';

$lundi = lundi_de($_GET['semaine'] ?? null);
$planning = planning_semaine($bdd, $lundi);
$precedente = date('Y-m-d', strtotime("$lundi -7 day"));
$suivante = date('Y-m-d', strtotime("$lundi +7 day"));
$vendredi = end($planning['jours']);
$aujourdhui = date('Y-m-d');
$actifs = count(array_filter($planning['liste'], fn ($i) => $i['statut'] !== 'annule'));

$titre = 'Planning';
$page = 'planning';
require __DIR__ . '/includes/haut.php';
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Planning</p>
    <h1>Semaine du <?= htmlspecialchars(jour_long($lundi)) ?> au <?= htmlspecialchars(jour_long($vendredi)) ?></h1>
    <p class="aide"><?= $actifs ?> rendez-vous cette semaine (annulés non comptés). Le guichet reçoit du lundi au vendredi, à la minute près.</p>
  </div>
  <nav class="semaines" aria-label="Changer de semaine">
    <a class="btn btn-clair" href="planning.php?semaine=<?= $precedente ?>">Semaine précédente</a>
    <a class="btn btn-clair" href="planning.php">Cette semaine</a>
    <a class="btn btn-clair" href="planning.php?semaine=<?= $suivante ?>">Semaine suivante</a>
  </nav>
</div>

<section class="carte" aria-labelledby="titre-grille">
  <h2 id="titre-grille">Vue semaine</h2>
<?php if (!$planning['horaires']): ?>
  <p class="aide">Horaires du guichet indisponibles (le site ne répond pas) et aucun rendez-vous cette semaine.</p>
<?php else: ?>
  <div class="defile">
    <table class="tableau planning">
      <caption class="visuellement-cache">Rendez-vous de la semaine : un jour par colonne, un créneau par ligne</caption>
      <thead>
        <tr>
          <th scope="col">Créneau</th>
<?php foreach ($planning['jours'] as $jour): ?>
          <th scope="col"<?= $jour === $aujourdhui ? ' class="aujourdhui"' : '' ?>><?= htmlspecialchars(jour_long($jour)) ?><?= $jour === $aujourdhui ? ' <span class="visuellement-cache">(aujourd\'hui)</span>' : '' ?></th>
<?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
<?php foreach ($planning['horaires'] as $horaire): ?>
        <tr>
          <th scope="row"><?= horaire_court($horaire) ?></th>
<?php foreach ($planning['jours'] as $jour): ?>
          <td>
<?php if (empty($planning['grille'][$jour][$horaire])): ?>
            <span class="libre petit">libre</span>
<?php else: ?>
            <ul>
<?php foreach ($planning['grille'][$jour][$horaire] as $i): ?>
              <li><a href="inscrits.php?id=<?= (int) $i['id'] ?>"><?= htmlspecialchars($i['prenom'] . ' ' . $i['nom']) ?></a>
                <span class="pastille pastille-<?= $i['statut'] ?>"><?= STATUTS_INSCRIS[$i['statut']] ?></span></li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
          </td>
<?php endforeach; ?>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</section>

<section class="carte" aria-labelledby="titre-liste">
  <h2 id="titre-liste">Vue liste</h2>
  <div class="jours-liste">
<?php foreach ($planning['jours'] as $jour): ?>
<?php $du_jour = array_filter($planning['liste'], fn ($i) => $i['jour'] === $jour); ?>
    <h3><?= htmlspecialchars(ucfirst(jour_long($jour))) ?><?= $jour === $aujourdhui ? ' (aujourd\'hui)' : '' ?></h3>
<?php if (!$du_jour): ?>
    <p class="aide">Aucun rendez-vous.</p>
<?php else: ?>
    <ul class="historique">
<?php foreach ($du_jour as $i): ?>
      <li><span class="mono"><?= horaire_court($i['horaire']) ?></span>
        <a href="inscrits.php?id=<?= (int) $i['id'] ?>"><?= htmlspecialchars($i['prenom'] . ' ' . $i['nom']) ?></a>
        <span class="mono petit"><?= htmlspecialchars($i['telephone']) ?></span>
        <span class="pastille pastille-<?= $i['statut'] ?>"><?= STATUTS_INSCRIS[$i['statut']] ?></span></li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/bas.php'; ?>
