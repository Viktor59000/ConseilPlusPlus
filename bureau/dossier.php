<?php
/**
 * bureau/dossier.php — Fiche d'un dossier : la demande, son statut, son affectation, la réponse officielle du cabinet,
 * les notes internes et les autres demandes de la même personne.
 * Actions (POST + CSRF) : examiner, rédiger une réponse (conseil tiré de l'API), enregistrer, classer, rouvrir,
 * affecter (responsable + échéance), noter. La purge du site passe par purge.php.
 * id_agent = dernier intervenant (mis à jour par les actions qui modifient le dossier, pas par « rédiger »).
 */
require_once __DIR__ . '/includes/amorce.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$stmt = $bdd->prepare('SELECT d.*, i.nom AS intervenant, r.nom AS responsable FROM dossiers d
                       LEFT JOIN agents i ON i.id = d.id_agent LEFT JOIN agents r ON r.id = d.id_responsable WHERE d.id = ?');
$stmt->execute([$id ?: 0]);
$dossier = $stmt->fetch();
if (!$dossier) {
    rediriger('dossiers.php', 'Dossier introuvable. Il a peut-être été égaré, ce qui arrive.', 'erreur');
}
$agents = $bdd->query('SELECT id, nom FROM agents ORDER BY nom')->fetchAll(PDO::FETCH_KEY_PAIR);
$retour = 'dossier.php?id=' . $dossier['id'];

$brouillon = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exiger_post($retour);
    $action = $_POST['action'] ?? '';

    if ($action === 'noter') {
        // Note interne : jamais envoyée au demandeur
        $note = trim((string) ($_POST['note'] ?? ''));
        if ($note === '') {
            rediriger($retour, 'Une note vide n\'apporte rien au dossier, même ici.', 'erreur');
        }
        $bdd->prepare('INSERT INTO notes (id_dossier, id_agent, texte) VALUES (?, ?, ?)')
            ->execute([$dossier['id'], $agent['id'], mb_substr($note, 0, 2000)]);
        rediriger($retour . '#notes', 'Note ajoutée au dossier. Elle restera entre nous.');
    }

    $maj = $bdd->prepare('UPDATE dossiers SET statut = :statut, reponse = :reponse, date_reponse = :date_reponse, envoi = :envoi,
                          id_agent = :agent, id_responsable = :responsable, echeance = :echeance WHERE id = :id');
    $valeurs = ['statut' => $dossier['statut'], 'reponse' => $dossier['reponse'], 'date_reponse' => $dossier['date_reponse'],
        'envoi' => $dossier['envoi'], 'agent' => $agent['id'], 'responsable' => $dossier['id_responsable'],
        'echeance' => $dossier['echeance'], 'id' => $dossier['id']];
    $texte = trim((string) ($_POST['reponse'] ?? ''));
    switch ($action) {
        case 'examiner':
            $valeurs['statut'] = 'en_cours';
            $valeurs['responsable'] ??= $agent['id'];
            annoncer('Dossier pris en charge. Il est désormais « en cours d\'examen », ce qui ne garantit rien.');
            break;
        case 'affecter':
            $choix = (int) ($_POST['responsable'] ?? 0);
            $echeance = (string) ($_POST['echeance'] ?? '');
            $date = DateTime::createFromFormat('!Y-m-d', $echeance);
            if ($echeance !== '' && (!$date || $date->format('Y-m-d') !== $echeance)) {
                rediriger($retour, 'Échéance illisible. Le calendrier du Bureau n\'accepte que les vraies dates.', 'erreur');
            }
            $valeurs['responsable'] = isset($agents[$choix]) ? $choix : null;
            $valeurs['echeance'] = $echeance !== '' ? $echeance : null;
            annoncer($valeurs['responsable'] ? 'Dossier affecté à ' . $agents[$choix] . '.' : 'Dossier rendu à la pile commune.');
            break;
        case 'rediger':
            $ton = array_key_exists($_POST['ton'] ?? '', TONS_REPONSE) ? $_POST['ton'] : 'standard';
            $brouillon = rediger_reponse($dossier, $ton);
            $valeurs['statut'] = $dossier['statut'] === 'a_traiter' ? 'en_cours' : $dossier['statut'];
            $valeurs['agent'] = $dossier['id_agent']; // un simple projet ne fait pas de l'agent un intervenant
            break;
        case 'enregistrer':
            $valeurs['reponse'] = mb_substr($texte, 0, 5000) ?: null;
            $valeurs['statut'] = $dossier['statut'] === 'a_traiter' ? 'en_cours' : $dossier['statut'];
            annoncer('Réponse enregistrée.');
            break;
        case 'classer':
            if ($texte === '') {
                annoncer('Impossible de classer un dossier sans réponse. Même le Bureau a ses principes.', 'erreur');
                $valeurs['agent'] = $dossier['id_agent'];
                break;
            }
            $valeurs['reponse'] = mb_substr($texte, 0, 5000);
            [$parti, $raison] = envoyer_reponse($dossier, $valeurs['reponse'], $agent['nom']);
            if (!$parti && envoi_actif()) {
                // L'envoi a échoué : la réponse est gardée, le dossier reste ouvert pour réessayer
                $valeurs['statut'] = 'en_cours';
                $valeurs['envoi'] = 'echec';
                annoncer("L'email n'est pas parti ($raison). La réponse est enregistrée : réessayez plus tard.", 'erreur');
                break;
            }
            $valeurs['statut'] = 'classe';
            $valeurs['date_reponse'] = date('Y-m-d H:i:s');
            $valeurs['envoi'] = $parti ? 'envoye' : 'archive';
            annoncer($parti
                ? "Réponse envoyée par email à {$dossier['email']}. Dossier classé."
                : "Réponse archivée et dossier classé (envoi par email non configuré : aucun mail n'est parti).");
            break;
        case 'rouvrir':
            $valeurs['statut'] = 'en_cours';
            annoncer('Dossier rouvert. Le classement était peut-être prématuré.');
            break;
        default:
            rediriger($retour);
    }
    $maj->execute($valeurs);
    if ($brouillon === null) {
        rediriger($retour);
    }
    $dossier = array_merge($dossier, ['statut' => $valeurs['statut']]);
}

$notes = $bdd->prepare('SELECT n.texte, n.date, a.nom AS agent FROM notes n LEFT JOIN agents a ON a.id = n.id_agent
                        WHERE n.id_dossier = ? ORDER BY n.date, n.id');
$notes->execute([$dossier['id']]);
$notes = $notes->fetchAll();
$autres = $bdd->prepare('SELECT id, id_site, type_demande, date_demande, statut FROM dossiers WHERE email = ? AND id <> ?
                         ORDER BY date_demande DESC LIMIT 20');
$autres->execute([$dossier['email'], $dossier['id']]);
$autres = $autres->fetchAll();

$reponse = $brouillon ?? $dossier['reponse'] ?? '';
$classe = $dossier['statut'] === 'classe';
$titre = 'Dossier n° ' . numero_dossier($dossier['id_site']);
$page = 'dossiers';
require __DIR__ . '/includes/haut.php';
?>
<p><a class="lien-fort" href="dossiers.php">← Tous les dossiers</a></p>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Dossier n° <?= numero_dossier($dossier['id_site']) ?></p>
    <h1><?= htmlspecialchars($dossier['type_demande']) ?></h1>
    <p><span class="pastille pastille-<?= $dossier['statut'] ?>"><?= STATUTS_DOSSIER[$dossier['statut']] ?></span>
      <?php if (en_retard($dossier)): ?><span class="pastille pastille-retard">En retard</span><?php endif; ?>
      <?php if (!$classe): ?><span class="aide">Délai estimé (annoncé au client) : <?= htmlspecialchars(delai_estime((int) $dossier['id'])) ?>.</span><?php endif; ?></p>
  </div>
<?php if ($dossier['statut'] === 'a_traiter'): ?>
  <form method="post">
    <?= csrf_champ() ?>
    <button class="btn btn-clair" name="action" value="examiner" type="submit">Prendre en charge</button>
  </form>
<?php endif; ?>
</div>

<div class="colonnes">
  <section class="carte">
    <h2>La demande</h2>
    <dl class="fiche">
      <dt>Demandeur</dt><dd><?= htmlspecialchars($dossier['nom']) ?></dd>
      <dt>Email</dt><dd><a href="mailto:<?= htmlspecialchars($dossier['email']) ?>"><?= htmlspecialchars($dossier['email']) ?></a></dd>
      <dt>Reçue sur le site</dt><dd class="mono"><?= date_courte($dossier['date_demande']) ?></dd>
      <dt>Importée au Bureau</dt><dd class="mono"><?= date_courte($dossier['date_import']) ?></dd>
      <dt>Responsable</dt><dd><?= $dossier['responsable'] ? htmlspecialchars($dossier['responsable']) : 'Personne, pour l\'instant' ?></dd>
      <dt>Échéance</dt><dd class="mono<?= en_retard($dossier) ? ' alerte' : '' ?>"><?= date_echeance($dossier['echeance']) ?></dd>
      <dt>Dernier intervenant</dt><dd><?= $dossier['intervenant'] ? htmlspecialchars($dossier['intervenant']) : '—' ?></dd>
<?php if ($dossier['date_purge']): ?>
      <dt>Retirée du site</dt><dd class="mono"><?= date_courte($dossier['date_purge']) ?></dd>
<?php endif; ?>
    </dl>
    <blockquote class="message"><?= nl2br(htmlspecialchars($dossier['message'])) ?></blockquote>

    <h2 class="sous-titre">Affectation</h2>
    <form method="post" class="filtres">
      <?= csrf_champ() ?>
      <div>
        <label for="responsable">Responsable</label>
        <select id="responsable" name="responsable">
          <option value="">Personne</option>
<?php foreach ($agents as $id_a => $nom_a): ?>
          <option value="<?= $id_a ?>"<?= (int) $dossier['id_responsable'] === $id_a ? ' selected' : '' ?>><?= htmlspecialchars($nom_a) ?></option>
<?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="echeance">Échéance (interne)</label>
        <input id="echeance" name="echeance" type="date" value="<?= htmlspecialchars((string) $dossier['echeance']) ?>">
      </div>
      <button class="btn btn-clair" name="action" value="affecter" type="submit">Affecter</button>
    </form>
  </section>

  <section class="carte">
    <h2>Réponse officielle</h2>
<?php if ($classe): ?>
    <p class="aide mono"><?= $dossier['envoi'] === 'envoye' ? 'Envoyée par email' : "Archivée (pas d'email envoyé)" ?> le <?= date_courte($dossier['date_reponse']) ?>.</p>
    <pre class="lettre"><?= htmlspecialchars($dossier['reponse']) ?></pre>
    <form method="post">
      <?= csrf_champ() ?>
      <button class="btn btn-clair" name="action" value="rouvrir" type="submit">Rouvrir le dossier</button>
    </form>
<?php if (!$dossier['date_purge']): ?>
    <form method="post" action="purge.php" class="purge">
      <?= csrf_champ() ?>
      <input type="hidden" name="id" value="<?= $dossier['id'] ?>">
      <h3>Purger du site</h3>
      <p class="aide">Supprime définitivement la demande dans la base du site (<code>DELETE /api/contacts.php</code>). Le dossier reste au Bureau.</p>
      <label class="confirmer"><input type="checkbox" name="confirmer" value="1" required> Je confirme la suppression sur le site</label>
      <div><button class="btn" type="submit">Purger du site</button></div>
    </form>
<?php endif; ?>
<?php else: ?>
    <form method="post" class="formulaire">
      <?= csrf_champ() ?>
      <label for="reponse">Texte de la réponse</label>
      <textarea id="reponse" name="reponse" rows="16" maxlength="5000" placeholder="Utilisez « Rédiger une réponse » : le cabinet propose un texte, que vous pouvez corriger."><?= htmlspecialchars($reponse) ?></textarea>
      <label for="ton">Ton de la réponse proposée</label>
      <select id="ton" name="ton">
<?php foreach (TONS_REPONSE as $cle => $libelle): ?>
        <option value="<?= $cle ?>"<?= ($_POST['ton'] ?? 'standard') === $cle ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
      </select>
      <div class="actions">
        <button class="btn btn-clair" name="action" value="rediger" type="submit" formnovalidate><?= $reponse ? 'Proposer une autre réponse' : 'Rédiger une réponse' ?></button>
        <button class="btn btn-clair" name="action" value="enregistrer" type="submit">Enregistrer le brouillon</button>
        <button class="btn btn-tampon" name="action" value="classer" type="submit"><?= envoi_actif() ? 'Envoyer par email et classer' : 'Archiver et classer' ?></button>
      </div>
<?php if (!envoi_actif()): ?>
      <p class="aide">L'envoi par email n'est pas configuré : la réponse sera seulement archivée (voir docs/BUREAU.md).</p>
<?php elseif ($dossier['envoi'] === 'echec'): ?>
      <p class="aide alerte">Le dernier envoi a échoué. Vérifiez l'adresse du demandeur, puis réessayez.</p>
<?php endif; ?>
<?php if ($brouillon !== null): ?>
      <p class="aide">Projet de réponse généré (conseil tiré de l'API du site). Il n'est pas encore enregistré.</p>
<?php endif; ?>
    </form>
<?php endif; ?>
  </section>
</div>

<div class="colonnes colonnes-suite">
  <section class="carte" id="notes" aria-labelledby="titre-notes">
    <h2 id="titre-notes">Notes internes</h2>
    <p class="aide">Visibles des seuls agents : jamais envoyées au demandeur.</p>
<?php if (!$notes): ?>
    <p class="aide">Aucune note. Le dossier parle de lui-même, ou pas.</p>
<?php else: ?>
    <ol class="notes">
<?php foreach ($notes as $n): ?>
      <li><p class="mono petit"><?= date_courte($n['date']) ?> — <?= $n['agent'] ? htmlspecialchars($n['agent']) : 'Agent parti' ?></p>
        <p><?= nl2br(htmlspecialchars($n['texte'])) ?></p></li>
<?php endforeach; ?>
    </ol>
<?php endif; ?>
    <form method="post" class="formulaire">
      <?= csrf_champ() ?>
      <label for="note">Nouvelle note</label>
      <textarea id="note" name="note" rows="3" maxlength="2000" required></textarea>
      <div><button class="btn btn-clair" name="action" value="noter" type="submit">Ajouter la note</button></div>
    </form>
  </section>

  <section class="carte" aria-labelledby="titre-autres">
    <h2 id="titre-autres">Autres demandes de cette personne</h2>
<?php if (!$autres): ?>
    <p class="aide">Première demande à cette adresse. Le cabinet fait bonne impression, pour l'instant.</p>
<?php else: ?>
    <ul class="historique">
<?php foreach ($autres as $o): ?>
      <li><a class="mono" href="dossier.php?id=<?= $o['id'] ?>"><?= numero_dossier($o['id_site']) ?></a>
        <?= htmlspecialchars($o['type_demande']) ?> <span class="mono petit"><?= date_courte($o['date_demande']) ?></span>
        <span class="pastille pastille-<?= $o['statut'] ?>"><?= STATUTS_DOSSIER[$o['statut']] ?></span></li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
