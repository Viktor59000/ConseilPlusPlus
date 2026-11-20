<?php
// bureau/synchro.php — Synchro des inscriptions : lit par l'API les inscriptions « nouveau » du site, les range dans
// la table inscris (sans doublon, par remote_id), les marque « importe » sur le site et reporte les annulations.
// Après le POST, le compte rendu chiffré (importées, déjà présentes, annulations, erreurs) est montré une fois.
require_once __DIR__ . '/includes/amorce.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exiger_post('synchro.php');
    $r = synchroniser_inscriptions($bdd, $agent['id']);
    $_SESSION['synchro_compte_rendu'] = $r; // affiché une seule fois sur la page suivante
    if ($r['erreurs'] > 0 && $r['importees'] === 0 && $r['deja'] === 0) {
        rediriger('synchro.php', 'Synchro incomplète : ' . $r['detail'], 'erreur');
    }
    rediriger('synchro.php', "Synchro terminée : {$r['importees']} inscription(s) importée(s), {$r['deja']} déjà présente(s), "
        . "{$r['annulees']} annulation(s) reportée(s), {$r['erreurs']} erreur(s)."
        . ($r['erreurs'] ? ' Les inscriptions non marquées le seront à la prochaine synchro.' : ''), $r['erreurs'] ? 'erreur' : 'ok');
}

$compte_rendu = $_SESSION['synchro_compte_rendu'] ?? null;
unset($_SESSION['synchro_compte_rendu']);
// En attente sur le site : une page d'un seul élément suffit pour lire le total
$attente = api('GET', 'inscriptions.php?statut=nouveau&page=1&par_page=1', null, true, 5);
$a_synchroniser = $attente['code'] === 200 ? (int) ($attente['data']['total'] ?? 0) : null;
$synchros = $bdd->query("SELECT r.*, a.nom AS agent FROM releves r LEFT JOIN agents a ON a.id = r.id_agent
                         WHERE r.nature = 'synchro' ORDER BY r.date_releve DESC LIMIT 15")->fetchAll();
$total_inscris = (int) $bdd->query('SELECT COUNT(*) FROM inscris')->fetchColumn();
$derniere = $synchros[0] ?? null;

$titre = 'Synchro';
$page = 'synchro';
require __DIR__ . '/includes/haut.php';
?>
<header class="tete">
  <div class="tete__texte">
    <h1>Synchroniser les inscriptions</h1>
    <p class="tete__sous">Les rendez-vous pris au guichet du site arrivent ici par l'API, sans doublon. Le site apprend ensuite qu'ils sont en instruction.</p>
  </div>
</header>

<div class="zone">
  <div class="grille-b">
    <section class="panneau synchro-b s-7" aria-labelledby="titre-lancer">
      <div class="panneau__tete"><h2 id="titre-lancer">Guichet du site</h2>
        <span class="note-b">Dernière synchro : <?= $derniere ? date_courte($derniere['date_releve']) . ($derniere['agent'] ? ', par ' . e($derniere['agent']) : '') : 'jamais' ?></span></div>
      <div class="synchro-b__corps">
        <div class="synchro-b__attente">
          <p class="synchro-b__libelle"><?php if ($a_synchroniser === null): ?><?= e(message_erreur_api($attente)) ?> : nombre d'inscriptions en attente inconnu.<?php elseif ($a_synchroniser === 0): ?>Aucune inscription n'attend sur le site.<?php else: ?><span class="synchro-b__nombre"><?= $a_synchroniser ?></span> <?= $a_synchroniser > 1 ? 'inscriptions attendent' : 'inscription attend' ?> sur le site.<?php endif; ?></p>
          <span class="note-b"><?= nombre_de($total_inscris, 'inscrit') ?> dans la base du Bureau.</span>
        </div>
        <form method="post" action="synchro.php" class="synchro-b__action">
          <?= csrf_champ() ?>
          <?= bouton('Lancer la synchro', null, ['fleche' => false, 'icone_html' => picto_bureau('synchro')]) ?>
          <p class="note-b">Une seule pression suffit ; deux ne font pas de doublon.</p>
        </form>
      </div>
<?php if ($compte_rendu): ?>
      <div class="compte-rendu" data-papier aria-labelledby="titre-compte-rendu" role="group">
        <h3 id="titre-compte-rendu" class="compte-rendu__titre">Compte rendu de la dernière synchro</h3>
        <dl class="compte-rendu__chiffres">
          <div><dt>Importées</dt><dd><?= (int) $compte_rendu['importees'] ?></dd></div>
          <div><dt>Déjà présentes</dt><dd><?= (int) $compte_rendu['deja'] ?></dd></div>
          <div><dt>Annulations reportées</dt><dd><?= (int) $compte_rendu['annulees'] ?></dd></div>
          <div class="<?= $compte_rendu['erreurs'] ? 'compte-rendu--erreur' : '' ?>"><dt>Erreurs</dt><dd><?= (int) $compte_rendu['erreurs'] ?></dd></div>
        </dl>
        <p class="note-b"><?= e($compte_rendu['detail']) ?></p>
        <?= tampon($compte_rendu['erreurs'] ? 'Incomplet' : 'Synchronisé', ['couleur' => $compte_rendu['erreurs'] ? 'rouge' : 'vert', 'taille' => 'petit',
            'angle' => -6, 'mention' => date('d/m/Y H:i'), 'mouvement' => 'au-chargement', 'decoratif' => true]) ?>
      </div>
<?php endif; ?>
    </section>

    <section class="panneau s-5" aria-labelledby="titre-fonctionnement">
      <div class="panneau__tete"><h2 id="titre-fonctionnement">Ce que fait la synchro, dans l'ordre</h2></div>
      <ol class="etapes-b">
        <li><strong>Elle lit les inscriptions nouvelles du site</strong><code>GET /api/inscriptions.php?statut=nouveau</code></li>
        <li><strong>Elle les range dans la table inscris</strong><span class="note-b">sans doublon : <code>remote_id</code> est unique</span></li>
        <li><strong>Elle les marque comme importées sur le site</strong><code>PATCH /api/inscriptions.php?id=…</code></li>
        <li><strong>Elle reporte les annulations des clients</strong><code>GET …?statut=annule&amp;du=<?= date('Y-m-d') ?></code></li>
      </ol>
      <p class="vide">Une inscription dont le marquage a échoué reste « nouveau » sur le site : la synchro suivante la retrouve et la marque, sans la recréer. Le cabinet appelle cela de la redondance maîtrisée.</p>
    </section>
  </div>

  <section class="panneau" aria-labelledby="titre-historique">
    <div class="panneau__tete"><h2 id="titre-historique">Dernières synchros</h2><a class="lien-b" href="journal.php">Ouvrir le journal</a></div>
<?php if (!$synchros): ?>
    <p class="vide">Aucune synchro pour l'instant. Les inscriptions attendent, avec la patience des gens qui ont pris rendez-vous.</p>
<?php else: ?>
    <div class="defile">
      <table class="table-b">
        <thead><tr><th scope="col">Date</th><th scope="col">Agent</th><th scope="col" class="num">Importées</th><th scope="col" class="num">Erreurs</th><th scope="col">Détail</th></tr></thead>
        <tbody>
<?php foreach ($synchros as $s): ?>
          <tr>
            <td class="mono"><?= date_courte($s['date_releve']) ?></td>
            <td><?= $s['agent'] ? e($s['agent']) : '—' ?></td>
            <td class="mono num"><?= (int) $s['importes'] ?></td>
            <td class="mono num<?= $s['erreurs'] ? ' alerte-cellule' : '' ?>"><?= (int) $s['erreurs'] ?></td>
            <td class="muet"><?= e($s['detail']) ?></td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
