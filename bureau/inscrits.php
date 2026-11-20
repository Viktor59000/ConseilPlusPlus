<?php
// bureau/inscrits.php — Affichage des inscrits : tableau filtrable (statut, jour, recherche), fiche d'un inscrit (?id=)
// et décision de l'agent (confirmer, annuler, remettre à traiter), renvoyée au site par l'API avec une courte réponse.
require_once __DIR__ . '/includes/amorce.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    // Retour à la liste filtrée ou à la fiche (adresse reconstruite ici, jamais reprise telle quelle du formulaire)
    $retour = isset($_POST['fiche']) ? "inscrits.php?id=$id" : 'inscrits.php?' . http_build_query(array_filter(criteres_inscris((array) ($_POST['filtre'] ?? []))));
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
<?php if ($fiche): ?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Inscrit n° <?= numero_dossier((int) $fiche['remote_id']) ?></p>
    <h1><?= htmlspecialchars($fiche['prenom'] . ' ' . $fiche['nom']) ?></h1>
    <p class="aide">Rendez-vous le <?= htmlspecialchars(jour_long($fiche['jour'])) ?> à <?= horaire_court($fiche['horaire']) ?>.
      <span class="pastille pastille-<?= $fiche['statut'] ?>"><?= STATUTS_INSCRIS[$fiche['statut']] ?></span></p>
  </div>
  <p><a class="lien-fort" href="planning.php?semaine=<?= htmlspecialchars($fiche['jour']) ?>">Voir dans le planning</a> ·
    <a class="lien-fort" href="inscrits.php">Tous les inscrits</a></p>
</div>

<div class="colonnes">
  <section class="carte" aria-labelledby="titre-fiche">
    <h2 id="titre-fiche">Fiche</h2>
    <dl class="fiche">
      <dt>remote_id</dt><dd class="mono"><?= (int) $fiche['remote_id'] ?> (id sur le site)</dd>
      <dt>Email</dt><dd><a href="mailto:<?= htmlspecialchars($fiche['email']) ?>"><?= htmlspecialchars($fiche['email']) ?></a></dd>
      <dt>Téléphone</dt><dd class="mono"><?= htmlspecialchars($fiche['telephone']) ?></dd>
      <dt>Déposée le</dt><dd class="mono"><?= date_courte($fiche['create_at']) ?></dd>
<?php if ($distante['code'] === 200): ?>
      <dt>Statut sur le site</dt><dd class="mono"><?= htmlspecialchars((string) $distante['data']['statut']) ?></dd>
<?php endif; ?>
    </dl>
    <h3>Problème déposé au guichet</h3>
<?php if ($distante['code'] === 200): ?>
    <p class="message"><?= $distante['data']['message'] !== null ? nl2br(htmlspecialchars($distante['data']['message'])) : 'Aucun. Le client vient pour le principe.' ?></p>
<?php if ($distante['data']['reponse'] !== null): ?>
    <h3>Réponse lue par le client</h3>
    <p class="message"><?= nl2br(htmlspecialchars($distante['data']['reponse'])) ?></p>
<?php endif; ?>
<?php elseif ($distante['code'] === 404): ?>
    <p class="aide">L'inscription n'existe plus sur le site (compte supprimé).</p>
<?php else: ?>
    <p class="aide"><?= htmlspecialchars(message_erreur_api($distante)) ?> : détail indisponible.</p>
<?php endif; ?>
  </section>

  <section class="carte" aria-labelledby="titre-decision">
    <h2 id="titre-decision">Décision du cabinet</h2>
    <form class="formulaire" method="post" action="inscrits.php">
      <?= csrf_champ() ?>
      <input type="hidden" name="id" value="<?= (int) $fiche['id'] ?>">
      <input type="hidden" name="fiche" value="1">
      <label for="statut">Statut</label>
      <select id="statut" name="statut">
<?php foreach (STATUTS_INSCRIS as $cle => $libelle): ?>
        <option value="<?= $cle ?>"<?= $cle === ($fiche['statut'] === 'a_traiter' ? 'confirme' : $fiche['statut']) ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
      </select>
      <label for="reponse">Réponse au client (facultative, <?= REPONSE_INSCRIS_MAX ?> caractères au plus)</label>
      <textarea id="reponse" name="reponse" rows="4" maxlength="<?= REPONSE_INSCRIS_MAX ?>"><?= htmlspecialchars(REPONSES_INSCRIS[$fiche['statut'] === 'a_traiter' ? 'confirme' : $fiche['statut']]) ?></textarea>
      <p class="aide petit">Statut et réponse partent au site (<code>PATCH /api/inscriptions.php</code>) ; le client les lit dans « Mon dossier ».</p>
      <button class="btn btn-tampon" type="submit">Enregistrer et envoyer au site</button>
    </form>
  </section>
</div>
<?php else: ?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Inscrits</p>
    <h1>Affichage des inscrits</h1>
    <p class="aide"><?= count($inscrits) ?> inscrit(s) affiché(s), par date de rendez-vous.</p>
  </div>
  <form method="post" action="export-inscrits.php">
    <?= csrf_champ() ?>
<?php foreach ($criteres as $cle => $valeur): ?>
    <input type="hidden" name="<?= $cle ?>" value="<?= htmlspecialchars($valeur) ?>">
<?php endforeach; ?>
    <button class="btn" type="submit">Exporter la sélection en CSV</button>
  </form>
</div>

<form class="filtres carte" method="get" action="inscrits.php">
  <div>
    <label for="statut">Statut</label>
    <select id="statut" name="statut">
      <option value="">Tous</option>
<?php foreach (STATUTS_INSCRIS as $cle => $libelle): ?>
      <option value="<?= $cle ?>"<?= $criteres['statut'] === $cle ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="jour">Jour</label>
    <input id="jour" name="jour" type="date" value="<?= htmlspecialchars($criteres['jour']) ?>">
  </div>
  <div class="filtres-recherche">
    <label for="q">Nom, prénom, email ou téléphone</label>
    <input id="q" name="q" type="search" value="<?= htmlspecialchars($criteres['q']) ?>">
  </div>
  <button class="btn" type="submit">Filtrer</button>
</form>

<section class="carte">
<?php if (!$inscrits): ?>
  <p class="aide">Aucun inscrit ne correspond. Lancez une <a href="synchro.php">synchro</a> : ils attendent peut-être sur le site.</p>
<?php else: ?>
  <table class="tableau">
    <thead><tr><th>N°</th><th>Inscrit</th><th>Téléphone</th><th>Rendez-vous</th><th>Déposée le</th><th>Statut</th><th>Décision</th></tr></thead>
    <tbody>
<?php foreach ($inscrits as $i): ?>
      <tr>
        <td class="mono"><a href="inscrits.php?id=<?= (int) $i['id'] ?>"><?= numero_dossier((int) $i['remote_id']) ?></a></td>
        <td><?= htmlspecialchars($i['prenom'] . ' ' . $i['nom']) ?><br><span class="mono petit"><?= htmlspecialchars($i['email']) ?></span></td>
        <td class="mono"><?= htmlspecialchars($i['telephone']) ?></td>
        <td class="mono"><?= date_echeance($i['jour']) ?> à <?= horaire_court($i['horaire']) ?></td>
        <td class="mono"><?= date_courte($i['create_at']) ?></td>
        <td><span class="pastille pastille-<?= $i['statut'] ?>"><?= STATUTS_INSCRIS[$i['statut']] ?></span></td>
        <td>
          <form class="reponse-rapide" method="post" action="inscrits.php">
            <?= csrf_champ() ?>
            <input type="hidden" name="id" value="<?= (int) $i['id'] ?>">
<?php foreach ($criteres as $cle => $valeur): ?>
            <input type="hidden" name="filtre[<?= $cle ?>]" value="<?= htmlspecialchars($valeur) ?>">
<?php endforeach; ?>
<?php if ($i['statut'] !== 'confirme'): ?>
            <button class="btn" name="statut" value="confirme" type="submit" aria-label="Confirmer le rendez-vous de <?= htmlspecialchars($i['prenom'] . ' ' . $i['nom']) ?>">Confirmer</button>
<?php endif; ?>
<?php if ($i['statut'] !== 'annule'): ?>
            <button class="btn" name="statut" value="annule" type="submit" aria-label="Annuler le rendez-vous de <?= htmlspecialchars($i['prenom'] . ' ' . $i['nom']) ?>">Annuler</button>
<?php endif; ?>
          </form>
        </td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/bas.php'; ?>
