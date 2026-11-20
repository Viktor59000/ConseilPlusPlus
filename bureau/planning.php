<?php
// bureau/planning.php — Planning des rendez-vous du guichet : vue semaine (jours en colonnes, créneaux en lignes,
// rendez-vous en fiches colorées par statut), semaine précédente / suivante, et la même semaine en liste
// (?vue=liste : lecture linéaire, lecteurs d'écran, téléphone).
require_once __DIR__ . '/includes/amorce.php';

$lundi = lundi_de($_GET['semaine'] ?? null);
$planning = planning_semaine($bdd, $lundi);
$precedente = date('Y-m-d', strtotime("$lundi -7 day"));
$suivante = date('Y-m-d', strtotime("$lundi +7 day"));
$vendredi = end($planning['jours']);
$aujourdhui = date('Y-m-d');
$actifs = count(array_filter($planning['liste'], fn ($i) => $i['statut'] !== 'annule'));
$liste = ($_GET['vue'] ?? '') === 'liste';
$semaine = $lundi === lundi_de(null) ? '' : $lundi; // paramètre gardé dans les liens (vide : cette semaine)
$lien = fn (array $p) => 'planning.php' . (($q = http_build_query(array_filter($p))) !== '' ? '?' . $q : '');
$par_statut = array_count_values(array_column($planning['liste'], 'statut'));

$titre = 'Planning';
$page = 'planning';
require __DIR__ . '/includes/haut.php';
?>
<header class="tete">
  <div class="tete__texte">
    <p class="tete__ref"><span>Planning</span><span>Semaine <?= (int) date('W', strtotime($lundi)) ?></span></p>
    <h1>Du <?= e(jour_long($lundi)) ?> au <?= e(jour_long($vendredi)) ?></h1>
    <p class="tete__sous"><?= nombre_de($actifs, 'rendez-vous', 'rendez-vous') ?> cette semaine
      (<?= (int) ($par_statut['a_traiter'] ?? 0) ?> à traiter, <?= (int) ($par_statut['confirme'] ?? 0) ?> confirmé(s), <?= (int) ($par_statut['annule'] ?? 0) ?> annulé(s)).
      Le guichet reçoit du lundi au vendredi, à la minute près.</p>
  </div>
  <div class="tete__actions">
    <nav class="semaines-b" aria-label="Changer de semaine">
      <a class="mini" href="<?= e($lien(['semaine' => $precedente, 'vue' => $liste ? 'liste' : ''])) ?>" aria-label="Semaine précédente">‹<span class="masque-etroit"> Précédente</span></a>
      <a class="mini" href="<?= e($lien(['vue' => $liste ? 'liste' : ''])) ?>"<?= $semaine === '' ? ' aria-current="page"' : '' ?>>Cette semaine</a>
      <a class="mini" href="<?= e($lien(['semaine' => $suivante, 'vue' => $liste ? 'liste' : ''])) ?>" aria-label="Semaine suivante"><span class="masque-etroit">Suivante </span>›</a>
    </nav>
    <nav class="segments" aria-label="Affichage">
      <a href="<?= e($lien(['semaine' => $semaine])) ?>"<?= !$liste ? ' aria-current="page"' : '' ?>><?= picto_bureau('tableau') ?>Semaine</a>
      <a href="<?= e($lien(['semaine' => $semaine, 'vue' => 'liste'])) ?>"<?= $liste ? ' aria-current="page"' : '' ?>><?= picto_bureau('journal') ?>Liste</a>
    </nav>
  </div>
</header>

<div class="zone">
<?php if (!$liste): ?>
  <section class="panneau" aria-labelledby="titre-grille">
    <div class="panneau__tete">
      <h2 id="titre-grille"><?= picto_bureau('planning') ?>Vue semaine</h2>
      <p class="legende-b" aria-hidden="true"><?= pastille('a_traiter', 'À traiter') ?> <?= pastille('confirme', 'Confirmé') ?> <span class="note-b">annulés : en liste</span></p>
    </div>
<?php if (!$planning['horaires']): ?>
    <p class="vide">Horaires du guichet indisponibles (le site ne répond pas) et aucun rendez-vous cette semaine.</p>
<?php else: ?>
    <div class="defile">
      <table class="planning-b">
        <caption class="visuellement-cache">Rendez-vous de la semaine : un jour par colonne, un créneau par ligne (annulés non affichés)</caption>
        <thead>
          <tr>
            <th scope="col" class="planning-b__coin"><span class="visuellement-cache">Créneau</span></th>
<?php foreach ($planning['jours'] as $jour): ?>
            <th scope="col" class="<?= $jour === $aujourdhui ? 'planning-b__jour--auj' : ($jour < $aujourdhui ? 'planning-b__jour--passe' : '') ?>">
              <span class="planning-b__nom"><?= e(mb_substr(jour_long($jour), 0, 3)) ?>.</span>
              <span class="planning-b__date"><?= date('j', strtotime($jour)) ?></span>
              <span class="visuellement-cache"><?= e(jour_long($jour)) ?><?= $jour === $aujourdhui ? ' (aujourd\'hui)' : '' ?></span>
            </th>
<?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
<?php foreach ($planning['horaires'] as $horaire): ?>
          <tr>
            <th scope="row" class="planning-b__heure"><?= horaire_court($horaire) ?></th>
<?php foreach ($planning['jours'] as $jour): ?>
            <td class="<?= $jour === $aujourdhui ? 'planning-b__auj' : ($jour < $aujourdhui ? 'planning-b__passe' : '') ?>">
<?php if (empty($planning['grille'][$jour][$horaire])): ?>
              <span class="planning-b__libre">libre</span>
<?php else: ?>
<?php foreach ($planning['grille'][$jour][$horaire] as $i): ?>
              <a class="rdv-b rdv-b--<?= $i['statut'] ?>" href="inscrits.php?id=<?= (int) $i['id'] ?>">
                <span class="rdv-b__nom"><?= e($i['prenom'] . ' ' . $i['nom']) ?></span>
                <span class="rdv-b__meta"><?= e($i['telephone']) ?></span>
                <span class="rdv-b__statut"><?= STATUTS_INSCRIS[$i['statut']] ?></span>
              </a>
<?php endforeach; ?>
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
<?php else: ?>
  <section class="liste-semaine" aria-label="Rendez-vous de la semaine, jour par jour">
<?php foreach ($planning['jours'] as $jour):
      $du_jour = array_filter($planning['liste'], fn ($i) => $i['jour'] === $jour); ?>
    <section class="panneau<?= $jour === $aujourdhui ? ' panneau--auj' : '' ?>" aria-labelledby="jour-<?= $jour ?>">
      <div class="panneau__tete">
        <h2 id="jour-<?= $jour ?>"><?= e(ucfirst(jour_long($jour))) ?><?= $jour === $aujourdhui ? ' <span class="pastille pastille--neutre">aujourd\'hui</span>' : '' ?></h2>
        <span class="note-b"><?= nombre_de(count($du_jour), 'rendez-vous', 'rendez-vous') ?></span>
      </div>
<?php if (!$du_jour): ?>
      <p class="vide">Aucun rendez-vous. Le guichet attend, sans impatience.</p>
<?php else: ?>
      <ul class="lignes">
<?php foreach ($du_jour as $i): ?>
        <li><span class="mono"><?= horaire_court($i['horaire']) ?></span>
          <a class="reste" href="inscrits.php?id=<?= (int) $i['id'] ?>"><?= e($i['prenom'] . ' ' . $i['nom']) ?></a>
          <span class="mono masque-etroit"><?= e($i['telephone']) ?></span>
          <span class="pousse"><?= pastille($i['statut'], STATUTS_INSCRIS[$i['statut']]) ?></span></li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
    </section>
<?php endforeach; ?>
  </section>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
