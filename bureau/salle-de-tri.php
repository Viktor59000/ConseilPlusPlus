<?php
/**
 * bureau/salle-de-tri.php — La Salle de tri : les réclamations (dossiers) et les rendez-vous (inscriptions) rangés en
 * casiers, une colonne par statut. On fait avancer un élément en le glissant dans un autre casier (bureau/js/salle-de-tri.js),
 * ou, au clavier et sans JavaScript, en choisissant sa destination puis « Tamponner ». Dans tous les cas, c'est le POST
 * existant qui agit (dossier.php : examiner / classer / rouvrir ; inscrits.php : statut), avec son jeton CSRF ;
 * « retour=salle » ramène ici. Le détail s'ouvre dans un tiroir (dialog) ou, sans JavaScript, sur la fiche.
 */
require_once __DIR__ . '/includes/amorce.php';

$genre = ($_GET['casier'] ?? '') === 'inscriptions' ? 'inscriptions' : 'dossiers';
$casiers = $genre === 'dossiers' ? casiers_dossiers($bdd) : casiers_inscris($bdd);
$statuts = $genre === 'dossiers' ? STATUTS_BUREAU : STATUTS_INSCRIS;
$passages = $genre === 'dossiers' ? PASSAGES_DOSSIERS : PASSAGES_INSCRIS;
$nb_dossiers = (int) $bdd->query("SELECT COUNT(*) FROM dossiers WHERE statut <> 'classe'")->fetchColumn();
$nb_inscris = (int) $bdd->query("SELECT COUNT(*) FROM inscris WHERE statut = 'a_traiter'")->fetchColumn();
$aides = $genre === 'dossiers'
    ? ['a_traiter' => 'Arrivés par le courrier', 'en_cours' => 'Classer demande une réponse', 'classe' => 'Réponse rendue']
    : ['a_traiter' => 'Arrivés par la synchro', 'confirme' => 'À venir', 'annule' => 'À venir'];
// Casier vide : ce qui le remplit (le premier casier se remplit par le relevé ou la synchro, les autres d'un coup de tampon)
$vides = $genre === 'dossiers'
    ? ['a_traiter' => 'Aucune réclamation en attente. Relevez le courrier pour faire entrer les nouvelles.',
       'en_cours' => "Aucun dossier à l'examen. Tamponnez une fiche « À traiter » pour la prendre en charge.",
       'classe' => "Rien de classé pour l'instant. Un dossier se classe une fois sa réponse rédigée."]
    : ['a_traiter' => 'Aucun rendez-vous à traiter. Lancez une synchro pour faire entrer les nouveaux.',
       'confirme' => 'Aucun rendez-vous confirmé à venir.',
       'annule' => 'Aucune annulation à venir.'];

$titre = 'Salle de tri';
$page = 'salle-de-tri';
$scripts = ['salle-de-tri.js'];
require __DIR__ . '/includes/haut.php';
?>
<header class="tete">
  <div class="tete__texte">
    <h1>La Salle de tri</h1>
    <p class="tete__sous">Glissez une fiche dans un autre casier, ou choisissez sa destination et tamponnez. Le site en est informé par l'API.</p>
  </div>
  <div class="tete__actions">
    <nav class="segments" aria-label="Casiers">
      <a href="salle-de-tri.php"<?= $genre === 'dossiers' ? ' aria-current="page"' : '' ?>>Réclamations <b><?= $nb_dossiers ?><span class="visuellement-cache"> ouvertes</span></b></a>
      <a href="salle-de-tri.php?casier=inscriptions"<?= $genre === 'inscriptions' ? ' aria-current="page"' : '' ?>>Rendez-vous <b><?= $nb_inscris ?><span class="visuellement-cache"> à traiter</span></b></a>
    </nav>
<?php if ($genre === 'dossiers'): ?>
    <form method="post" action="releve.php">
      <?= csrf_champ() ?>
      <?= bouton('Relever le courrier', null, ['fleche' => false, 'icone_html' => picto_bureau('courrier')]) ?>
    </form>
<?php else: ?>
    <form method="post" action="synchro.php">
      <?= csrf_champ() ?>
      <?= bouton('Lancer la synchro', null, ['fleche' => false, 'icone_html' => picto_bureau('synchro')]) ?>
    </form>
<?php endif; ?>
  </div>
</header>

<div class="zone">
  <section aria-labelledby="titre-casiers">
    <h2 id="titre-casiers" class="visuellement-cache"><?= $genre === 'dossiers' ? 'Casiers des réclamations' : 'Casiers des rendez-vous' ?></h2>
    <p class="visuellement-cache" id="consigne-tri">Chaque fiche se déplace avec la liste « Déplacer vers » puis le bouton « Tamponner ». À la souris, on peut aussi la glisser dans un autre casier.</p>
    <div class="casiers" data-casiers data-genre="<?= $genre ?>">
<?php foreach ($statuts as $statut => $libelle):
      $casier = $casiers[$statut]; ?>
      <section class="casier<?= $casier['liste'] ? '' : ' casier--vide' ?>" data-casier="<?= $statut ?>" aria-labelledby="casier-<?= $statut ?>">
        <header class="casier__tete">
          <h3 id="casier-<?= $statut ?>" class="pastille pastille--<?= $statut ?>"><?= e($libelle) ?></h3>
          <span class="casier__compte"><?= $casier['total'] ?><span class="visuellement-cache"> élément(s)</span></span>
          <span class="casier__aide"><?= e($aides[$statut]) ?></span>
        </header>
        <ol class="casier__liste" aria-labelledby="casier-<?= $statut ?>">
<?php foreach ($casier['liste'] as $f):
          if ($genre === 'dossiers') {
              $id_html = 'dossier-' . $f['id'];
              $numero = numero_dossier((int) $f['id_site']);
              $nom = $f['nom'];
              $titre_fiche = $f['type_demande'];
              $fiche = 'dossier.php?id=' . $f['id'];
              $action = 'dossier.php?id=' . $f['id'];
              $vers = $passages[$statut];
              // Classer demande une réponse enregistrée : sans elle, le passage vers « Classé » mène à la fiche
              if ($statut === 'en_cours' && trim((string) $f['reponse']) === '') {
                  $vers = [];
              }
              $champ = 'action';
          } else {
              $id_html = 'inscrit-' . $f['id'];
              $numero = numero_dossier((int) $f['remote_id']);
              $nom = $f['prenom'] . ' ' . $f['nom'];
              $titre_fiche = ucfirst(jour_long($f['jour'])) . ' à ' . horaire_court($f['horaire']);
              $fiche = 'inscrits.php?id=' . $f['id'];
              $action = 'inscrits.php';
              $vers = $passages[$statut];
              $champ = 'statut';
          }
          $vers_js = [];
          foreach ($vers as $arrivee => [$valeur, $texte_tampon, $couleur]) {
              $vers_js[$arrivee] = ['valeur' => $valeur, 'tampon' => $texte_tampon, 'couleur' => $couleur];
          } ?>
          <li class="fiche-tri" id="<?= $id_html ?>" data-fiche data-papier data-vers="<?= e(json_encode($vers_js, JSON_UNESCAPED_UNICODE)) ?>">
            <p class="fiche-tri__haut"><span class="mono">N° <?= $numero ?></span>
<?php if ($genre === 'dossiers'): ?>
              <span><?= e($titre_fiche) ?></span>
              <span class="pousse mono"><?= date('d/m', strtotime($f['date_demande'])) ?></span>
<?php else: ?>
              <span class="pousse mono"><?= e($f['telephone']) ?></span>
<?php endif; ?>
            </p>
            <h4 class="fiche-tri__titre"><a href="<?= $fiche ?>" data-tiroir="<?= $id_html ?>-detail"><?= e($nom) ?></a></h4>
            <p class="fiche-tri__extrait"><?= $genre === 'dossiers' ? e(mb_substr($f['message'], 0, 140)) : e($titre_fiche) ?></p>
            <p class="fiche-tri__bas">
<?php if ($genre === 'dossiers'): ?>
<?php if (en_retard($f)): ?>
              <?= pastille('retard', 'En retard') ?>
<?php elseif ($f['echeance']): ?>
              <span>Échéance <span class="mono"><?= date_echeance($f['echeance']) ?></span></span>
<?php endif; ?>
              <span><?= $f['responsable'] ? e($f['responsable']) : 'Sans responsable' ?></span>
<?php else: ?>
              <span><?= e($f['email']) ?></span>
<?php endif; ?>
            </p>
<?php if ($vers): ?>
            <form class="fiche-tri__deplacer" method="post" action="<?= $action ?>" data-deplacer>
              <?= csrf_champ() ?>
              <input type="hidden" name="retour" value="salle">
<?php if ($genre === 'inscriptions'): ?>
              <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
<?php elseif ($statut === 'en_cours'): ?>
              <input type="hidden" name="reponse" value="<?= e((string) $f['reponse']) ?>">
<?php endif; ?>
              <label for="vers-<?= $id_html ?>">Vers<span class="visuellement-cache"> (déplacer n° <?= $numero ?>, <?= e($nom) ?>)</span></label>
              <select class="saisie" id="vers-<?= $id_html ?>" name="<?= $champ ?>" aria-describedby="consigne-tri">
<?php foreach ($vers as $arrivee => [$valeur]): ?>
                <option value="<?= $valeur ?>"><?= e($statuts[$arrivee]) ?></option>
<?php endforeach; ?>
              </select>
              <button class="mini" type="submit">Tamponner</button>
            </form>
<?php elseif ($genre === 'dossiers' && $statut === 'en_cours'): ?>
            <p class="fiche-tri__deplacer"><a class="lien-b" href="<?= $fiche ?>#reponse">Rédiger la réponse pour classer</a></p>
<?php endif; ?>
            <template id="<?= $id_html ?>-detail">
              <p class="tete__ref"><span class="mono">N° <?= $numero ?></span><?= pastille($statut, $statuts[$statut]) ?></p>
              <h2><?= e($nom) ?></h2>
              <dl class="fiche-b">
<?php if ($genre === 'dossiers'): ?>
                <dt>Type</dt><dd><?= e($f['type_demande']) ?></dd>
                <dt>Email</dt><dd><?= e($f['email']) ?></dd>
                <dt>Reçue le</dt><dd class="mono"><?= date_courte($f['date_demande']) ?></dd>
                <dt>Responsable</dt><dd><?= $f['responsable'] ? e($f['responsable']) : 'Personne, pour l\'instant' ?></dd>
                <dt>Échéance</dt><dd class="mono"><?= date_echeance($f['echeance']) ?></dd>
<?php else: ?>
                <dt>Rendez-vous</dt><dd><?= e($titre_fiche) ?></dd>
                <dt>Email</dt><dd><?= e($f['email']) ?></dd>
                <dt>Téléphone</dt><dd class="mono"><?= e($f['telephone']) ?></dd>
                <dt>Déposée le</dt><dd class="mono"><?= date_courte($f['create_at']) ?></dd>
<?php endif; ?>
              </dl>
<?php if ($genre === 'dossiers'): ?>
              <div class="feuille-b"><?= e($f['message']) ?></div>
<?php if ($f['reponse']): ?>
              <h3>Réponse enregistrée</h3>
              <pre class="lettre"><?= e($f['reponse']) ?></pre>
<?php endif; ?>
<?php endif; ?>
              <p><?= bouton($genre === 'dossiers' ? 'Ouvrir la fiche complète' : 'Ouvrir la fiche de l\'inscrit', $fiche, ['variante' => 'secondaire', 'fleche' => false]) ?></p>
            </template>
          </li>
<?php endforeach; ?>
        </ol>
<?php if (!$casier['liste']): ?>
        <p class="casier__plus"><?= e($vides[$statut]) ?></p>
<?php elseif ($casier['total'] > count($casier['liste'])): ?>
        <p class="casier__plus"><a class="lien-b" href="<?= $genre === 'dossiers' ? 'dossiers.php?statut=' . $statut : 'inscrits.php?statut=' . $statut ?>">Voir les <?= $casier['total'] - count($casier['liste']) ?> autres au registre</a></p>
<?php endif; ?>
      </section>
<?php endforeach; ?>
    </div>
  </section>
</div>

<dialog class="tiroir" id="tiroir" aria-labelledby="tiroir-titre">
  <div class="tiroir__tete">
    <p class="tiroir__titre" id="tiroir-titre"><?= $genre === 'dossiers' ? 'Aperçu du dossier' : 'Aperçu du rendez-vous' ?></p>
    <form method="dialog"><button class="mini" type="submit">Fermer</button></form>
  </div>
  <div class="tiroir__corps" data-tiroir-corps></div>
</dialog>
<div class="visuellement-cache" aria-live="polite" data-annonce-tri></div>
<?php require __DIR__ . '/includes/bas.php'; ?>
