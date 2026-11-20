<?php
// bureau/inscrits.php — Affichage des inscrits : tableau filtrable (statut, jour, recherche), fiche d'un inscrit (?id=)
// et décision de l'agent (confirmer, annuler, remettre à traiter), renvoyée au site par l'API avec une courte réponse.
require_once __DIR__ . '/includes/amorce.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    // Retour à la liste filtrée ou à la fiche (adresse reconstruite ici, jamais reprise telle quelle du formulaire)
    $retour = match (true) {
        isset($_POST['fiche']) => "inscrits.php?id=$id",
        ($_POST['retour'] ?? '') === 'salle' => 'salle-de-tri.php?casier=inscriptions', // déplacement depuis la Salle de tri
        default => 'inscrits.php?' . http_build_query(array_filter(criteres_inscris((array) ($_POST['filtre'] ?? [])))),
    };
    exiger_post($retour);
    $statut = (string) ($_POST['statut'] ?? '');
    $inscrit = lire_inscrit($bdd, $id);
    if (!$inscrit || !array_key_exists($statut, STATUTS_INSCRIS)) {
        rediriger($retour, 'Inscrit ou statut inconnu : rien n\'a changé.', 'erreur');
    }
    $reponse = trim((string) ($_POST['reponse'] ?? REPONSES_INSCRIS[$statut]));
    if (mb_strlen($reponse) > REPONSE_INSCRIS_MAX) {
        rediriger($retour, 'Réponse trop longue (' . REPONSE_INSCRIS_MAX . ' caractères au plus) : le cabinet résume.', 'erreur');
    }
    $resultat = changer_statut_inscrit($bdd, $inscrit, $statut, $reponse);
    $nom = $inscrit['prenom'] . ' ' . $inscrit['nom'];
    rediriger($retour, in_array($resultat, ['publie', 'absente'], true)
        ? "$nom : « " . STATUTS_INSCRIS[$statut] . ' ». ' . COMPTE_RENDU_INSCRIS[$resultat]
        : "$nom : statut inchangé. " . COMPTE_RENDU_INSCRIS[$resultat], in_array($resultat, ['publie', 'absente'], true) ? 'ok' : 'erreur');
}

// Fiche d'un inscrit : la base du Bureau, complétée par l'API (problème déposé, réponse lue par le client)
$fiche = isset($_GET['id']) ? lire_inscrit($bdd, (int) $_GET['id']) : null;
if (isset($_GET['id']) && !$fiche) {
    rediriger('inscrits.php', 'Inscrit introuvable dans la base du Bureau.', 'erreur');
}
if ($fiche) {
    $distante = api('GET', 'inscriptions.php?id=' . (int) $fiche['remote_id'], null, true, 5);
}
$criteres = criteres_inscris($_GET);
$inscrits = $fiche ? [] : filtrer_inscris($bdd, $criteres);

$titre = $fiche ? 'Inscrit n° ' . numero_dossier((int) $fiche['remote_id']) : 'Inscrits';
$page = 'inscrits';
require __DIR__ . '/includes/haut.php';
?>
<?php if ($fiche):
    $numero = numero_dossier((int) $fiche['remote_id']); ?>
<header class="tete">
  <div class="tete__texte">
    <p class="tete__ref"><a href="inscrits.php">Inscrits</a><span class="mono">N° <?= $numero ?></span></p>
    <h1><?= e($fiche['prenom'] . ' ' . $fiche['nom']) ?></h1>
    <p class="tete__sous"><?= pastille($fiche['statut'], STATUTS_INSCRIS[$fiche['statut']]) ?>
      Rendez-vous le <?= e(jour_long($fiche['jour'])) ?> à <?= horaire_court($fiche['horaire']) ?>.</p>
  </div>
  <div class="tete__actions">
    <?= bouton('Voir dans le planning', 'planning.php?semaine=' . $fiche['jour'], ['variante' => 'secondaire', 'fleche' => false, 'icone_html' => picto_bureau('planning')]) ?>
  </div>
</header>

<div class="zone">
  <div class="grille-b">
    <div class="s-8 pile-b">
      <section class="panneau" aria-labelledby="titre-probleme">
        <div class="panneau__tete"><h2 id="titre-probleme">Problème déposé au guichet</h2><span class="note-b">Lu par l'API, non copié</span></div>
        <div class="panneau__corps">
<?php if ($distante['code'] === 200): ?>
          <div class="feuille-b"><?= $distante['data']['message'] !== null ? e($distante['data']['message']) : 'Aucun. Le client vient pour le principe.' ?></div>
<?php if ($distante['data']['reponse'] !== null): ?>
          <h3>Réponse lue par le client</h3>
          <pre class="lettre"><?= e($distante['data']['reponse']) ?></pre>
<?php endif; ?>
<?php elseif ($distante['code'] === 404): ?>
          <p class="note-b">L'inscription n'existe plus sur le site (compte supprimé).</p>
<?php else: ?>
          <p class="note-b note-b--alerte"><?= e(message_erreur_api($distante)) ?> : détail indisponible.</p>
<?php endif; ?>
        </div>
      </section>

      <section class="panneau" aria-labelledby="titre-decision">
        <div class="panneau__tete"><h2 id="titre-decision">Décision du cabinet</h2><span class="note-b">Envoyée au site par <code>PATCH /api/inscriptions.php</code></span></div>
        <div class="panneau__corps">
          <form class="formulaire-b" method="post" action="inscrits.php">
            <?= csrf_champ() ?>
            <input type="hidden" name="id" value="<?= (int) $fiche['id'] ?>">
            <input type="hidden" name="fiche" value="1">
            <div class="champ-b"><label for="statut">Statut</label>
              <select class="saisie" id="statut" name="statut">
<?php foreach (STATUTS_INSCRIS as $cle => $libelle): ?>
                <option value="<?= $cle ?>"<?= $cle === ($fiche['statut'] === 'a_traiter' ? 'confirme' : $fiche['statut']) ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
              </select></div>
            <div class="champ-b"><label for="reponse">Réponse au client (facultative, <?= REPONSE_INSCRIS_MAX ?> caractères au plus)</label>
              <textarea class="saisie" id="reponse" name="reponse" rows="4" maxlength="<?= REPONSE_INSCRIS_MAX ?>"><?= e(REPONSES_INSCRIS[$fiche['statut'] === 'a_traiter' ? 'confirme' : $fiche['statut']]) ?></textarea></div>
            <p class="note-b">Le client lit la décision dans « Mon dossier ». Le statut ne change ici que si le site l'a reçue.</p>
            <div><?= bouton('Enregistrer et envoyer au site', null, ['fleche' => false, 'icone_html' => picto('tampon')]) ?></div>
          </form>
        </div>
      </section>
    </div>

    <aside class="s-4 pile-b inspecteur" aria-label="Fiche de l'inscrit">
      <section class="panneau" aria-labelledby="titre-fiche">
        <div class="panneau__tete"><h2 id="titre-fiche">Coordonnées et rendez-vous</h2></div>
        <div class="panneau__corps">
          <dl class="fiche-b">
            <dt>Statut</dt><dd><?= pastille($fiche['statut'], STATUTS_INSCRIS[$fiche['statut']]) ?></dd>
            <dt>Jour</dt><dd><?= e(ucfirst(jour_long($fiche['jour']))) ?></dd>
            <dt>Horaire</dt><dd class="mono"><?= horaire_court($fiche['horaire']) ?></dd>
            <dt>Email</dt><dd><a href="mailto:<?= e($fiche['email']) ?>"><?= e($fiche['email']) ?></a></dd>
            <dt>Téléphone</dt><dd class="mono"><?= e($fiche['telephone']) ?></dd>
            <dt>Déposée le</dt><dd class="mono"><?= date_courte($fiche['create_at']) ?></dd>
            <dt>N° sur le site</dt><dd><span class="mono"><?= (int) $fiche['remote_id'] ?></span> <span class="note-b">(remote_id)</span></dd>
<?php if ($distante['code'] === 200): ?>
            <dt>Sur le site</dt><dd><?= pastille((string) $distante['data']['statut'], ['nouveau' => 'Nouveau', 'importe' => 'Importé', 'confirme' => 'Confirmé', 'annule' => 'Annulé'][$distante['data']['statut']] ?? (string) $distante['data']['statut']) ?></dd>
<?php endif; ?>
          </dl>
        </div>
      </section>
    </aside>
  </div>
</div>
<?php else: ?>
<header class="tete">
  <div class="tete__texte">
    <h1>Affichage des inscrits</h1>
    <p class="tete__sous"><span data-filtre-compte><?= count($inscrits) ?></span> inscrit(s) affiché(s), par date de rendez-vous. Confirmez ou annulez : la décision part au site.</p>
  </div>
  <div class="tete__actions">
    <form method="post" action="export-inscrits.php">
      <?= csrf_champ() ?>
<?php foreach ($criteres as $cle => $valeur): ?>
      <input type="hidden" name="<?= $cle ?>" value="<?= e($valeur) ?>">
<?php endforeach; ?>
      <?= bouton('Exporter la sélection', null, ['variante' => 'secondaire', 'fleche' => false, 'icone_html' => picto_bureau('export')]) ?>
    </form>
  </div>
</header>

<div class="zone">
  <form class="filtres-b barre-filtres" method="get" action="inscrits.php">
    <nav class="segments" aria-label="Statut">
      <a href="inscrits.php?<?= e(http_build_query(array_filter(['jour' => $criteres['jour'], 'q' => $criteres['q']]))) ?>"<?= $criteres['statut'] === '' ? ' aria-current="page"' : '' ?>>Tous</a>
<?php foreach (STATUTS_INSCRIS as $cle => $libelle): ?>
      <a href="inscrits.php?<?= e(http_build_query(array_filter(['statut' => $cle, 'jour' => $criteres['jour'], 'q' => $criteres['q']]))) ?>"<?= $criteres['statut'] === $cle ? ' aria-current="page"' : '' ?>><?= $libelle ?></a>
<?php endforeach; ?>
    </nav>
    <input type="hidden" name="statut" value="<?= e($criteres['statut']) ?>">
    <div class="champ-b"><label for="jour">Jour</label><input class="saisie" id="jour" name="jour" type="date" value="<?= e($criteres['jour']) ?>"></div>
    <div class="champ-b champ-b--large"><label for="q">Nom, prénom, email ou téléphone</label>
      <input class="saisie" id="q" name="q" type="search" value="<?= e($criteres['q']) ?>" data-filtre="#table-inscrits" autocomplete="off"></div>
    <button class="mini" type="submit">Filtrer</button>
<?php if (array_filter($criteres)): ?>
    <a class="lien-b lien-b--nu" href="inscrits.php">Effacer les filtres</a>
<?php endif; ?>
  </form>

  <section class="panneau" aria-labelledby="titre-table-inscrits">
    <h2 id="titre-table-inscrits" class="visuellement-cache">Liste des inscrits</h2>
<?php if (!$inscrits): ?>
    <p class="vide"><?= array_filter($criteres) ? 'Aucun inscrit ne correspond à ces filtres. <a href="inscrits.php">Effacer les filtres</a>' : 'Aucun inscrit pour l\'instant. Lancez une <a href="synchro.php">synchro</a> : ils attendent peut-être sur le site.' ?></p>
<?php else: ?>
    <div class="defile">
      <table class="table-b" id="table-inscrits">
        <thead><tr><th scope="col">N°</th><th scope="col">Inscrit</th><th scope="col">Téléphone</th><th scope="col">Rendez-vous</th><th scope="col">Déposée le</th><th scope="col">Statut</th><th scope="col"><span class="visuellement-cache">Décision</span></th></tr></thead>
        <tbody>
<?php foreach ($inscrits as $i):
          $nom = $i['prenom'] . ' ' . $i['nom']; ?>
          <tr<?= $i['jour'] < date('Y-m-d') ? ' class="ligne-passee"' : '' ?>>
            <td class="mono"><a href="inscrits.php?id=<?= (int) $i['id'] ?>"><?= numero_dossier((int) $i['remote_id']) ?></a></td>
            <td><span class="double"><a href="inscrits.php?id=<?= (int) $i['id'] ?>"><?= e($nom) ?></a><small><?= e($i['email']) ?></small></span></td>
            <td class="mono"><?= e($i['telephone']) ?></td>
            <td class="mono"><?= date_echeance($i['jour']) ?> à <?= horaire_court($i['horaire']) ?></td>
            <td class="mono muet"><?= date_courte($i['create_at']) ?></td>
            <td><?= pastille($i['statut'], STATUTS_INSCRIS[$i['statut']]) ?></td>
            <td>
              <form class="actions-ligne" method="post" action="inscrits.php">
                <?= csrf_champ() ?>
                <input type="hidden" name="id" value="<?= (int) $i['id'] ?>">
<?php foreach ($criteres as $cle => $valeur): ?>
                <input type="hidden" name="filtre[<?= $cle ?>]" value="<?= e($valeur) ?>">
<?php endforeach; ?>
<?php if ($i['statut'] !== 'confirme'): ?>
                <button class="mini mini--ok" name="statut" value="confirme" type="submit" aria-label="Confirmer le rendez-vous de <?= e($nom) ?>">Confirmer</button>
<?php endif; ?>
<?php if ($i['statut'] !== 'annule'): ?>
                <button class="mini mini--danger" name="statut" value="annule" type="submit" aria-label="Annuler le rendez-vous de <?= e($nom) ?>">Annuler</button>
<?php endif; ?>
              </form>
            </td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php endif; ?>
  </section>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/bas.php'; ?>
