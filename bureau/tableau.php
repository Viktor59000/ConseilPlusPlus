<?php
// bureau/tableau.php — Page d'arrivée du Bureau : inscriptions (à synchroniser, à traiter, rendez-vous du jour),
// puis chiffres des dossiers, relevé du courrier, mes dossiers, dossiers en retard, à traiter, purge des anciens dossiers.
require_once __DIR__ . '/includes/amorce.php';

$compte = $bdd->query("SELECT statut, COUNT(*) AS n FROM dossiers GROUP BY statut")->fetchAll(PDO::FETCH_KEY_PAIR);
$par_type = $bdd->query("SELECT type_demande, COUNT(*) AS n FROM dossiers GROUP BY type_demande ORDER BY n DESC")->fetchAll();
$a_traiter = $bdd->query("SELECT * FROM dossiers WHERE statut <> 'classe' ORDER BY date_demande LIMIT 8")->fetchAll();
$mes_dossiers = filtrer_dossiers($bdd, criteres_dossiers(['vue' => 'moi', 'tri' => 'echeance']), $agent['id'], 50);
$mes_dossiers = array_slice(array_filter($mes_dossiers, fn ($d) => $d['statut'] !== 'classe'), 0, 8);
$en_retard = filtrer_dossiers($bdd, criteres_dossiers(['vue' => 'retard', 'tri' => 'echeance']), $agent['id'], 8);
$nb_retard = (int) $bdd->query('SELECT COUNT(*) FROM dossiers d WHERE ' . SQL_EN_RETARD)->fetchColumn();
$purgeables = (int) $bdd->query('SELECT COUNT(*) FROM dossiers WHERE ' . SQL_PURGEABLES)->fetchColumn();
$dernier = $bdd->query("SELECT * FROM releves WHERE nature = 'releve' ORDER BY date_releve DESC LIMIT 1")->fetch();
// Palmarès des votes du public, lu par l'API publique du site (le Bureau ne touche pas à sa base)
$palmares = api('GET', 'votes.php?palmares=5', null, false);
$palmares = $palmares['code'] === 200 && is_array($palmares['data']) ? $palmares['data'] : null;
$total = array_sum($compte);
// Inscriptions aux rendez-vous : en attente sur le site (total lu par l'API), à traiter et rendez-vous du jour (base du Bureau)
$attente = api('GET', 'inscriptions.php?statut=nouveau&page=1&par_page=1', null, true, 5);
$a_synchroniser = $attente['code'] === 200 ? (int) ($attente['data']['total'] ?? 0) : null;
$inscris_a_traiter = (int) $bdd->query("SELECT COUNT(*) FROM inscris WHERE statut = 'a_traiter' AND jour >= CURDATE()")->fetchColumn();
$rdv_du_jour = filtrer_inscris($bdd, criteres_inscris(['jour' => date('Y-m-d')]), 50);
$rdv_du_jour = array_values(array_filter($rdv_du_jour, fn ($i) => $i['statut'] !== 'annule'));

$titre = 'Tableau de bord';
$page = 'tableau';
require __DIR__ . '/includes/haut.php';
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Tableau de bord</p>
    <h1>Bonjour, <?= htmlspecialchars($agent['nom']) ?>.</h1>
    <p class="aide">Dernier relevé : <?= $dernier ? date_courte($dernier['date_releve']) . ' — ' . htmlspecialchars($dernier['detail']) : 'jamais. Le courrier s\'accumule, sereinement.' ?></p>
  </div>
  <form method="post" action="releve.php">
    <?= csrf_champ() ?>
    <button class="btn btn-tampon" type="submit">Relever le courrier</button>
  </form>
</div>

<section class="carte" aria-labelledby="titre-inscriptions">
  <div class="entete-page">
    <div>
      <h2 id="titre-inscriptions">Inscriptions au guichet</h2>
      <p class="aide"><?= $a_synchroniser === null ? htmlspecialchars(message_erreur_api($attente)) . ' : inscriptions en attente inconnues.'
          : "$a_synchroniser inscription(s) à synchroniser sur le site." ?>
        <?= $inscris_a_traiter ?> rendez-vous à venir à traiter (<a href="inscrits.php?statut=a_traiter">voir</a>).</p>
    </div>
    <form method="post" action="synchro.php">
      <?= csrf_champ() ?>
      <button class="btn btn-tampon" type="submit">Lancer la synchro</button>
    </form>
  </div>
  <h3>Rendez-vous du jour</h3>
<?php if (!$rdv_du_jour): ?>
  <p class="aide">Aucun rendez-vous aujourd'hui. Le guichet reste ouvert, par principe.</p>
<?php else: ?>
  <ul class="historique">
<?php foreach ($rdv_du_jour as $i): ?>
    <li><span class="mono"><?= horaire_court($i['horaire']) ?></span>
      <a href="inscrits.php?id=<?= (int) $i['id'] ?>"><?= htmlspecialchars($i['prenom'] . ' ' . $i['nom']) ?></a>
      <span class="mono petit"><?= htmlspecialchars($i['telephone']) ?></span>
      <span class="pastille pastille-<?= $i['statut'] ?>"><?= STATUTS_INSCRIS[$i['statut']] ?></span></li>
<?php endforeach; ?>
  </ul>
<?php endif; ?>
  <p><a class="lien-fort" href="planning.php">Planning de la semaine</a></p>
</section>

<section class="chiffres" aria-label="Chiffres du Bureau">
  <div class="chiffre"><span class="chiffre-valeur"><?= (int) ($compte['a_traiter'] ?? 0) ?></span><span class="chiffre-libelle">à traiter</span></div>
  <div class="chiffre"><span class="chiffre-valeur"><?= (int) ($compte['en_cours'] ?? 0) ?></span><span class="chiffre-libelle">en cours d'examen</span></div>
  <div class="chiffre"><span class="chiffre-valeur"><?= (int) ($compte['classe'] ?? 0) ?></span><span class="chiffre-libelle">classés</span></div>
  <div class="chiffre"><span class="chiffre-valeur<?= $nb_retard ? ' alerte' : '' ?>"><?= $nb_retard ?></span><span class="chiffre-libelle">en retard (échéance dépassée)</span></div>
  <div class="chiffre"><span class="chiffre-valeur"><?= $total ? round(100 * ($compte['classe'] ?? 0) / $total) : 0 ?> %</span><span class="chiffre-libelle">taux de classement (objectif : moyen)</span></div>
</section>

<div class="colonnes colonnes-egales">
  <section class="carte" aria-labelledby="titre-mes-dossiers">
    <h2 id="titre-mes-dossiers">Mes dossiers</h2>
<?php if (!$mes_dossiers): ?>
    <p class="aide">Aucun dossier ouvert ne vous est affecté. Une situation enviable, et provisoire.</p>
<?php else: ?>
    <ul class="historique">
<?php foreach ($mes_dossiers as $d): ?>
      <li><a class="mono" href="dossier.php?id=<?= $d['id'] ?>"><?= numero_dossier($d['id_site']) ?></a> <?= htmlspecialchars($d['nom']) ?>
        <span class="mono petit<?= en_retard($d) ? ' alerte' : '' ?>">échéance <?= date_echeance($d['echeance']) ?></span></li>
<?php endforeach; ?>
    </ul>
    <p><a class="lien-fort" href="dossiers.php?vue=moi&amp;tri=echeance">Tous mes dossiers</a></p>
<?php endif; ?>
  </section>

  <section class="carte" aria-labelledby="titre-retard">
    <h2 id="titre-retard">En retard</h2>
<?php if (!$en_retard): ?>
    <p class="aide">Aucune échéance dépassée. Le Bureau est, pour une fois, dans les temps.</p>
<?php else: ?>
    <ul class="historique">
<?php foreach ($en_retard as $d): ?>
      <li><a class="mono" href="dossier.php?id=<?= $d['id'] ?>"><?= numero_dossier($d['id_site']) ?></a> <?= htmlspecialchars($d['nom']) ?>
        <span class="mono petit alerte">échéance <?= date_echeance($d['echeance']) ?></span>
        <span class="petit"><?= $d['responsable'] ? htmlspecialchars($d['responsable']) : 'sans responsable' ?></span></li>
<?php endforeach; ?>
    </ul>
    <p><a class="lien-fort" href="dossiers.php?vue=retard&amp;tri=echeance">Tous les dossiers en retard</a></p>
<?php endif; ?>
  </section>
</div>

<div class="colonnes">
  <section class="carte">
    <h2>Dossiers en attente</h2>
<?php if (!$a_traiter): ?>
    <p class="aide">Aucun dossier en attente. Profitez-en pour ranger votre bureau, ou pas.</p>
<?php else: ?>
    <table class="tableau">
      <thead><tr><th>N°</th><th>Demandeur</th><th>Type</th><th>Reçu le</th><th>Statut</th></tr></thead>
      <tbody>
<?php foreach ($a_traiter as $d): ?>
        <tr>
          <td class="mono"><a href="dossier.php?id=<?= $d['id'] ?>"><?= numero_dossier($d['id_site']) ?></a></td>
          <td><?= htmlspecialchars($d['nom']) ?></td>
          <td><?= htmlspecialchars($d['type_demande']) ?></td>
          <td class="mono"><?= date_courte($d['date_demande']) ?></td>
          <td><span class="pastille pastille-<?= $d['statut'] ?>"><?= STATUTS_DOSSIER[$d['statut']] ?></span></td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
    <p><a class="lien-fort" href="dossiers.php">Tous les dossiers</a></p>
<?php endif; ?>
  </section>

  <section class="carte">
    <h2>Répartition par type</h2>
<?php if (!$par_type): ?>
    <p class="aide">Pas encore de dossier. La répartition est donc parfaitement équilibrée.</p>
<?php else: ?>
    <?= barres($par_type, 'type_demande', 'n', $total) ?>
<?php endif; ?>
    <p class="aide mono">Priorité de tous les dossiers : moyenne.</p>

    <h2>Palmarès du Petit Moyen</h2>
<?php if ($palmares === null): ?>
    <p class="aide">Palmarès indisponible : le site ne répond pas.</p>
<?php elseif (!$palmares): ?>
    <p class="aide">Aucun vote pour l'instant. Le public réfléchit.</p>
<?php else: ?>
    <ol class="palmares">
<?php foreach ($palmares as $p): ?>
      <li><strong><?= htmlspecialchars($p['mot']) ?></strong> <span class="mono petit">Moyen <?= (int) $p['moyen'] ?> · Pas moyen <?= (int) $p['pas_moyen'] ?></span></li>
<?php endforeach; ?>
    </ol>
<?php endif; ?>
  </section>
</div>

<section class="carte sauvegarde" aria-labelledby="titre-purge">
  <div>
    <h2 id="titre-purge">Purge des anciens dossiers</h2>
    <p class="aide"><?= $purgeables ?> dossier(s) classé(s) depuis plus de 6 mois encore présent(s) sur le site. La purge supprime
      ces demandes dans la base du site (<code>DELETE /api/contacts.php</code>) ; les dossiers restent au Bureau.</p>
  </div>
<?php if ($purgeables): ?>
  <form method="post" action="purge.php" class="purge">
    <?= csrf_champ() ?>
    <input type="hidden" name="lot" value="1">
    <label class="confirmer"><input type="checkbox" name="confirmer" value="1" required> Je confirme la suppression sur le site</label>
    <div><button class="btn" type="submit">Purger <?= $purgeables ?> demande(s)</button></div>
  </form>
<?php endif; ?>
</section>

<section class="carte sauvegarde" aria-labelledby="titre-sauvegarde">
  <div>
    <h2 id="titre-sauvegarde">Sauvegardes</h2>
    <p class="aide">Un fichier .sql complet (tables et données), à garder en lieu sûr : il contient les adresses email
      et les mots de passe chiffrés. Pour le réimporter : phpMyAdmin → base vide → Importer.</p>
  </div>
  <div class="actions">
    <form method="post" action="export.php">
      <?= csrf_champ() ?>
      <button class="btn btn-tampon" name="base" value="site" type="submit">Télécharger la base du site</button>
    </form>
    <form method="post" action="export.php">
      <?= csrf_champ() ?>
      <button class="btn" name="base" value="gestion" type="submit">Télécharger la base du Bureau</button>
    </form>
  </div>
</section>
<?php require __DIR__ . '/includes/bas.php'; ?>
