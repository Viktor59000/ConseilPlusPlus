<?php
// bureau/tableau.php — Page d'arrivée du Bureau (le « tableau » de la séance) : tuiles (à synchroniser, rendez-vous du
// jour, à traiter, en retard), débit du guichet sur 14 jours, répartition des statuts, prochains rendez-vous, mes
// dossiers et dossiers en retard ; au second plan : palmarès du Petit Moyen, types de demandes, purge, sauvegardes.
require_once __DIR__ . '/includes/amorce.php';

$compte = $bdd->query("SELECT statut, COUNT(*) AS n FROM dossiers GROUP BY statut")->fetchAll(PDO::FETCH_KEY_PAIR);
$compte_rdv = $bdd->query("SELECT statut, COUNT(*) AS n FROM inscris WHERE jour >= CURDATE() GROUP BY statut")->fetchAll(PDO::FETCH_KEY_PAIR);
$par_type = $bdd->query("SELECT type_demande, COUNT(*) AS n FROM dossiers GROUP BY type_demande ORDER BY n DESC")->fetchAll();
$mes_dossiers = filtrer_dossiers($bdd, criteres_dossiers(['vue' => 'moi', 'tri' => 'echeance']), $agent['id'], 50);
$mes_dossiers = array_slice(array_filter($mes_dossiers, fn ($d) => $d['statut'] !== 'classe'), 0, 6);
$en_retard = filtrer_dossiers($bdd, criteres_dossiers(['vue' => 'retard', 'tri' => 'echeance']), $agent['id'], 6);
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
$inscris_a_traiter = (int) ($compte_rdv['a_traiter'] ?? 0);
$rdv_du_jour = filtrer_inscris($bdd, criteres_inscris(['jour' => date('Y-m-d')]), 50);
$rdv_du_jour = array_values(array_filter($rdv_du_jour, fn ($i) => $i['statut'] !== 'annule'));
$prochains = $bdd->query("SELECT * FROM inscris WHERE statut <> 'annule' AND (jour > CURDATE() OR (jour = CURDATE() AND horaire >= CURTIME()))
                          ORDER BY jour, horaire, id LIMIT 7")->fetchAll();
$debit = debit_guichet($bdd, 14);
$dossiers_a_traiter = (int) ($compte['a_traiter'] ?? 0);

$titre = 'Tableau';
$page = 'tableau';
require __DIR__ . '/includes/haut.php';
?>
<header class="tete">
  <div class="tete__texte">
    <p class="tete__ref"><span><?= e(ucfirst(jour_long(date('Y-m-d')))) ?></span><span>Dernier relevé : <?= $dernier ? date_courte($dernier['date_releve']) : 'jamais' ?></span></p>
    <h1>Bonjour, <?= e($agent['nom']) ?>.</h1>
    <p class="tete__sous"><?= $dernier ? e($dernier['detail']) : 'Le courrier s\'accumule, sereinement.' ?></p>
  </div>
  <div class="tete__actions">
    <form method="post" action="releve.php">
      <?= csrf_champ() ?>
      <?= bouton('Relever le courrier', null, ['variante' => 'secondaire', 'icone_html' => picto_bureau('courrier')]) ?>
    </form>
    <form method="post" action="synchro.php">
      <?= csrf_champ() ?>
      <?= bouton('Lancer la synchro', null, ['fleche' => false, 'icone_html' => picto_bureau('synchro')]) ?>
    </form>
  </div>
</header>

<div class="zone">
  <section class="tuiles" aria-label="Chiffres du jour">
    <a class="tuile tuile--accent" href="synchro.php">
      <span class="tuile__libelle"><?= picto_bureau('synchro') ?>À synchroniser</span>
      <span class="tuile__valeur"><?= $a_synchroniser ?? '—' ?></span>
      <span class="tuile__detail"><?= $a_synchroniser === null ? e(message_erreur_api($attente)) : 'inscriptions en attente sur le site' ?></span>
    </a>
    <a class="tuile" href="planning.php">
      <span class="tuile__libelle"><?= picto_bureau('planning') ?>Rendez-vous du jour</span>
      <span class="tuile__valeur"><?= count($rdv_du_jour) ?></span>
      <span class="tuile__detail"><?= $rdv_du_jour ? 'premier à ' . horaire_court($rdv_du_jour[0]['horaire']) : 'le guichet reste ouvert, par principe' ?></span>
    </a>
    <a class="tuile" href="salle-de-tri.php">
      <span class="tuile__libelle"><?= picto('casier') ?>À traiter</span>
      <span class="tuile__valeur"><?= $dossiers_a_traiter + $inscris_a_traiter ?></span>
      <span class="tuile__detail"><?= nombre_de($dossiers_a_traiter, 'dossier') ?> · <?= nombre_de($inscris_a_traiter, 'rendez-vous', 'rendez-vous') ?></span>
    </a>
    <a class="tuile<?= $nb_retard ? ' tuile--alerte' : '' ?>" href="dossiers.php?vue=retard&amp;tri=echeance">
      <span class="tuile__libelle"><?= picto_bureau('horloge') ?>En retard</span>
      <span class="tuile__valeur"><?= $nb_retard ?></span>
      <span class="tuile__detail"><?= $nb_retard ? 'échéance interne dépassée' : 'le Bureau est, pour une fois, dans les temps' ?></span>
    </a>
  </section>

  <div class="grille-b">
    <section class="panneau s-8" aria-labelledby="titre-debit">
      <div class="panneau__tete">
        <h2 id="titre-debit"><?= picto('guichet') ?>Débit du guichet</h2>
        <span class="note-b">Demandes et inscriptions déposées sur le site, par jour</span>
      </div>
      <div class="panneau__corps">
        <?= graphique_barres($debit, ['titre' => '14 derniers jours', 'sous_titre' => 'du ' . date('d/m', strtotime('-13 day')) . ' au ' . date('d/m') . ', aujourd\'hui en laiton',
            'unite' => 'dépôts', 'entete' => 'Jour du mois', 'accent' => 13, 'hauteur' => 168]) ?>
      </div>
    </section>

    <section class="panneau s-4" aria-labelledby="titre-prochains">
      <div class="panneau__tete">
        <h2 id="titre-prochains"><?= picto_bureau('planning') ?>Prochains rendez-vous</h2>
        <a class="lien-b" href="planning.php">Planning</a>
      </div>
<?php if (!$prochains): ?>
      <p class="vide">Aucun rendez-vous à venir. Lancez une synchro : ils attendent peut-être sur le site.</p>
<?php else: ?>
      <ul class="lignes">
<?php foreach ($prochains as $i): ?>
        <li><span class="mono"><?= $i['jour'] === date('Y-m-d') ? 'Auj.' : date('d/m', strtotime($i['jour'])) ?> <?= horaire_court($i['horaire']) ?></span>
          <a class="reste" href="inscrits.php?id=<?= (int) $i['id'] ?>"><?= e($i['prenom'] . ' ' . $i['nom']) ?></a>
          <span class="pousse"><?= pastille($i['statut'], STATUTS_INSCRIS[$i['statut']]) ?></span></li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
    </section>

    <section class="panneau s-4 m-6" aria-labelledby="titre-statuts-dossiers">
      <div class="panneau__tete">
        <h2 id="titre-statuts-dossiers"><?= picto('chemise') ?>Dossiers par statut</h2>
        <a class="lien-b" href="salle-de-tri.php">Salle de tri</a>
      </div>
      <div class="panneau__corps">
        <?= graphique_anneau([
            ['libelle' => STATUTS_BUREAU['a_traiter'], 'valeur' => $compte['a_traiter'] ?? 0, 'serie' => 4],
            ['libelle' => STATUTS_BUREAU['en_cours'], 'valeur' => $compte['en_cours'] ?? 0, 'serie' => 1],
            ['libelle' => STATUTS_BUREAU['classe'], 'valeur' => $compte['classe'] ?? 0, 'serie' => 3],
        ], ['titre' => 'Réclamations', 'total' => 'dossiers', 'entete' => 'Statut']) ?>
        <p class="note-b">Taux de classement : <?= $total ? round(100 * ($compte['classe'] ?? 0) / $total) : 0 ?> %. Objectif : moyen.</p>
      </div>
    </section>

    <section class="panneau s-4 m-6" aria-labelledby="titre-statuts-rdv">
      <div class="panneau__tete">
        <h2 id="titre-statuts-rdv"><?= picto_bureau('inscrits') ?>Rendez-vous à venir</h2>
        <a class="lien-b" href="inscrits.php">Inscrits</a>
      </div>
      <div class="panneau__corps">
        <?= graphique_anneau([
            ['libelle' => STATUTS_INSCRIS['a_traiter'], 'valeur' => $compte_rdv['a_traiter'] ?? 0, 'serie' => 4],
            ['libelle' => STATUTS_INSCRIS['confirme'], 'valeur' => $compte_rdv['confirme'] ?? 0, 'serie' => 3],
            ['libelle' => STATUTS_INSCRIS['annule'], 'valeur' => $compte_rdv['annule'] ?? 0, 'serie' => 2],
        ], ['titre' => 'Inscriptions', 'total' => 'rendez-vous', 'entete' => 'Statut']) ?>
        <p class="note-b">Aujourd'hui compris ; les jours passés sont au registre des inscrits.</p>
      </div>
    </section>

    <section class="panneau s-4" aria-labelledby="titre-mes-dossiers">
      <div class="panneau__tete">
        <h2 id="titre-mes-dossiers"><?= picto('tampon') ?>Mes dossiers</h2>
        <a class="lien-b" href="dossiers.php?vue=moi&amp;tri=echeance">Tous</a>
      </div>
<?php if (!$mes_dossiers): ?>
      <p class="vide">Aucun dossier ouvert ne vous est affecté. Une situation enviable, et provisoire.</p>
<?php else: ?>
      <ul class="lignes">
<?php foreach ($mes_dossiers as $d): ?>
        <li><a class="mono" href="dossier.php?id=<?= $d['id'] ?>"><?= numero_dossier($d['id_site']) ?></a>
          <span class="reste"><?= e($d['nom']) ?></span>
          <span class="pousse mono<?= en_retard($d) ? ' note-b--alerte' : '' ?>"><?= date_echeance($d['echeance']) ?></span></li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
<?php if ($en_retard): ?>
      <div class="panneau__tete panneau__tete--suite"><h3>En retard</h3><a class="lien-b" href="dossiers.php?vue=retard&amp;tri=echeance">Tous</a></div>
      <ul class="lignes">
<?php foreach ($en_retard as $d): ?>
        <li><a class="mono" href="dossier.php?id=<?= $d['id'] ?>"><?= numero_dossier($d['id_site']) ?></a>
          <span class="reste"><?= e($d['nom']) ?></span>
          <span class="pousse"><?= pastille('retard', date_echeance($d['echeance'])) ?></span></li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
    </section>
  </div>

  <section class="second-plan" aria-labelledby="titre-reste">
    <h2 id="titre-reste" class="second-plan__titre">Le reste du Bureau</h2>
    <div class="grille-b">
      <section class="panneau s-4" aria-labelledby="titre-types">
        <div class="panneau__tete"><h3 id="titre-types">Demandes par type</h3><span class="note-b">Priorité : moyenne</span></div>
        <div class="panneau__corps">
<?php if (!$par_type): ?>
          <p class="note-b">Pas encore de dossier. La répartition est donc parfaitement équilibrée.</p>
<?php else: ?>
          <?= barres($par_type, 'type_demande', 'n', $total) ?>
<?php endif; ?>
        </div>
      </section>

      <section class="panneau s-4" aria-labelledby="titre-palmares">
        <div class="panneau__tete"><h3 id="titre-palmares">Palmarès du Petit Moyen</h3><a class="lien-b" href="petit-moyen.php">Mot de demain</a></div>
<?php if ($palmares === null): ?>
        <p class="vide">Palmarès indisponible : le site ne répond pas.</p>
<?php elseif (!$palmares): ?>
        <p class="vide">Aucun vote pour l'instant. Le public réfléchit.</p>
<?php else: ?>
        <ol class="lignes">
<?php foreach ($palmares as $rang => $p): ?>
          <li><span class="mono"><?= $rang + 1 ?></span><strong class="reste"><?= e($p['mot']) ?></strong>
            <span class="pousse mono">Moyen <?= (int) $p['moyen'] ?> · Pas moyen <?= (int) $p['pas_moyen'] ?></span></li>
<?php endforeach; ?>
        </ol>
<?php endif; ?>
      </section>

      <section class="panneau s-4" aria-labelledby="titre-sauvegarde">
        <div class="panneau__tete"><h3 id="titre-sauvegarde">Sauvegardes</h3><span class="note-b">Fichiers .sql complets</span></div>
        <div class="panneau__corps">
          <p class="note-b">À garder en lieu sûr : ils contiennent les adresses email et les mots de passe chiffrés. Réimport : phpMyAdmin, base vide, Importer.</p>
          <div class="boutons">
            <form method="post" action="export.php"><?= csrf_champ() ?><button class="mini" name="base" value="site" type="submit">Base du site</button></form>
            <form method="post" action="export.php"><?= csrf_champ() ?><button class="mini" name="base" value="gestion" type="submit">Base du Bureau</button></form>
          </div>
        </div>
      </section>

      <section class="panneau s-12 panneau--plat" aria-labelledby="titre-purge">
        <div class="panneau__tete"><h3 id="titre-purge">Purge des anciens dossiers</h3>
          <span class="note-b"><?= nombre_de($purgeables, 'dossier classé', 'dossiers classés') ?> depuis plus de 6 mois, encore présent(s) sur le site</span></div>
        <div class="panneau__corps">
          <p class="note-b">La purge supprime ces demandes dans la base du site (<code>DELETE /api/contacts.php</code>, 200 au plus par passage) ; les dossiers restent au Bureau.</p>
<?php if ($purgeables): ?>
          <form method="post" action="purge.php" class="filtres-b">
            <?= csrf_champ() ?>
            <input type="hidden" name="lot" value="1">
            <label class="confirmer-b"><input type="checkbox" name="confirmer" value="1" required> Je confirme la suppression sur le site</label>
            <button class="mini mini--danger" type="submit">Purger <?= $purgeables ?> demande(s)</button>
          </form>
<?php endif; ?>
        </div>
      </section>
    </div>
  </section>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
