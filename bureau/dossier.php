<?php
/**
 * bureau/dossier.php — Fiche d'un dossier : la demande, son statut, son affectation, la réponse officielle du cabinet,
 * les notes internes et les autres demandes de la même personne.
 * Actions (POST + CSRF) : examiner, rédiger une réponse (conseil tiré de l'API), enregistrer, classer, rouvrir,
 * affecter (responsable + échéance), noter, redéposer sur le site. La purge du site passe par purge.php.
 * Classer dépose aussi la réponse sur le site (PATCH statut « repondu ») : le client connecté la lit dans son compte.
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
// Déplacement depuis la Salle de tri (glisser ou « Tamponner ») : on y revient, avec l'annonce
if (($_POST['retour'] ?? '') === 'salle') {
    $retour = 'salle-de-tri.php';
}

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

    if ($action === 'publier') {
        // Nouvel essai de dépôt sur le site, sans renvoyer d'email
        if ($dossier['statut'] !== 'classe' || $dossier['date_purge']) {
            rediriger($retour, 'Seule la réponse d\'un dossier classé, et encore présent sur le site, peut y être déposée.', 'erreur');
        }
        $publication = publier_reponse($dossier, (string) $dossier['reponse']);
        rediriger($retour, PUBLICATION_REPONSE[$publication], $publication === 'erreur' ? 'erreur' : 'ok');
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
            // Boucle retour : la réponse est aussi déposée sur le site (sauf demande déjà purgée)
            $publication = $dossier['date_purge'] ? 'absente' : publier_reponse($dossier, $valeurs['reponse']);
            annoncer(($parti
                ? "Réponse envoyée par email à {$dossier['email']}. Dossier classé."
                : "Réponse archivée et dossier classé (envoi par email non configuré : aucun mail n'est parti).")
                . ' ' . PUBLICATION_REPONSE[$publication], $publication === 'erreur' ? 'erreur' : 'ok');
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
$numero = numero_dossier((int) $dossier['id_site']);
?>
<header class="tete">
  <div class="tete__texte">
    <p class="tete__ref"><a href="dossiers.php">Registre des dossiers</a><span class="mono">N° <?= $numero ?></span></p>
    <h1><?= e($dossier['type_demande']) ?> de <?= e($dossier['nom']) ?></h1>
    <p class="tete__sous"><?= pastille($dossier['statut'], STATUTS_BUREAU[$dossier['statut']]) ?>
      <?php if (en_retard($dossier)): ?><?= pastille('retard', 'En retard') ?><?php endif; ?>
      Reçue sur le site le <?= date_courte($dossier['date_demande']) ?>.</p>
  </div>
<?php if ($dossier['statut'] === 'a_traiter'): ?>
  <form class="tete__actions" method="post">
    <?= csrf_champ() ?>
    <?= bouton('Prendre en charge', null, ['nom' => 'action', 'valeur' => 'examiner', 'fleche' => false, 'icone_html' => picto('tampon')]) ?>
  </form>
<?php endif; ?>
</header>

<div class="zone">
  <div class="grille-b">
    <div class="s-8 pile-b">
      <section class="panneau" aria-labelledby="titre-demande">
        <div class="panneau__tete"><h2 id="titre-demande">La demande</h2>
          <a class="lien-b lien-b--nu" href="mailto:<?= e($dossier['email']) ?>"><?= e($dossier['email']) ?></a></div>
        <div class="panneau__corps"><div class="feuille-b"><?= e($dossier['message']) ?></div></div>
      </section>

      <section class="panneau" aria-labelledby="titre-reponse" id="reponse-officielle">
        <div class="panneau__tete"><h2 id="titre-reponse">Réponse officielle</h2>
<?php if ($classe): ?>
          <span class="note-b"><?= $dossier['envoi'] === 'envoye' ? 'Envoyée par email' : 'Archivée (aucun email envoyé)' ?> le <?= date_courte($dossier['date_reponse']) ?></span>
<?php else: ?>
          <span class="note-b"><?= envoi_actif() ? 'Envoi par email configuré' : 'Envoi par email non configuré : archivage seul' ?></span>
<?php endif; ?>
        </div>
        <div class="panneau__corps">
<?php if ($classe): ?>
          <div class="lettre-tamponnee" data-papier>
            <pre class="lettre"><?= e($dossier['reponse']) ?></pre>
            <?= tampon('Classé', ['couleur' => 'vert', 'angle' => -8, 'mention' => date('d/m/Y', strtotime((string) $dossier['date_reponse'])), 'decoratif' => true]) ?>
          </div>
          <form method="post" class="boutons">
            <?= csrf_champ() ?>
            <button class="mini" name="action" value="rouvrir" type="submit">Rouvrir le dossier</button>
<?php if (!$dossier['date_purge']): ?>
            <button class="mini" name="action" value="publier" type="submit">Redéposer sur le site</button>
<?php endif; ?>
          </form>
<?php if (!$dossier['date_purge']): ?>
          <p class="note-b">Au classement, la réponse est déposée dans l'espace client du site (<code>PATCH /api/contacts.php</code>, statut « repondu ») ; « Redéposer » refait seulement ce dépôt, sans email.</p>
<?php endif; ?>
<?php else: ?>
          <form method="post" class="formulaire-b">
            <?= csrf_champ() ?>
            <div class="champ-b">
              <label for="reponse">Texte de la réponse</label>
              <textarea class="saisie" id="reponse" name="reponse" rows="14" maxlength="5000" placeholder="« Rédiger une réponse » : le cabinet propose un texte, que vous corrigez ensuite."><?= e($reponse) ?></textarea>
            </div>
            <div class="filtres-b">
              <div class="champ-b">
                <label for="ton">Ton de la réponse proposée</label>
                <select class="saisie" id="ton" name="ton">
<?php foreach (TONS_REPONSE as $cle => $libelle): ?>
                  <option value="<?= $cle ?>"<?= ($_POST['ton'] ?? 'standard') === $cle ? ' selected' : '' ?>><?= $libelle ?></option>
<?php endforeach; ?>
                </select>
              </div>
              <button class="mini" name="action" value="rediger" type="submit" formnovalidate><?= $reponse ? 'Proposer une autre réponse' : 'Rédiger une réponse' ?></button>
              <button class="mini" name="action" value="enregistrer" type="submit">Enregistrer le brouillon</button>
              <span class="pousse-b"><?= bouton(envoi_actif() ? 'Envoyer par email et classer' : 'Archiver et classer', null, ['nom' => 'action', 'valeur' => 'classer', 'fleche' => false, 'icone_html' => picto('tampon')]) ?></span>
            </div>
<?php if ($brouillon !== null): ?>
            <p class="note-b">Projet de réponse généré (conseil tiré de l'API du site). Il n'est pas encore enregistré.</p>
<?php endif; ?>
<?php if (envoi_actif() && $dossier['envoi'] === 'echec'): ?>
            <p class="note-b note-b--alerte">Le dernier envoi a échoué. Vérifiez l'adresse du demandeur, puis réessayez.</p>
<?php endif; ?>
          </form>
<?php endif; ?>
        </div>
      </section>

      <section class="panneau" id="notes" aria-labelledby="titre-notes">
        <div class="panneau__tete"><h2 id="titre-notes">Notes internes</h2><span class="note-b">Visibles des seuls agents</span></div>
<?php if (!$notes): ?>
        <p class="vide">Aucune note. Une note reste entre agents : elle ne part jamais au demandeur.</p>
<?php else: ?>
        <ol class="notes-b">
<?php foreach ($notes as $n): ?>
          <li><p class="notes-b__qui"><span class="avatar" aria-hidden="true"><?= e(initiales((string) ($n['agent'] ?? '?'))) ?></span>
            <strong><?= $n['agent'] ? e($n['agent']) : 'Agent parti' ?></strong> <span class="mono"><?= date_courte($n['date']) ?></span></p>
            <p><?= nl2br(e($n['texte'])) ?></p></li>
<?php endforeach; ?>
        </ol>
<?php endif; ?>
        <div class="panneau__corps">
          <form method="post" class="formulaire-b">
            <?= csrf_champ() ?>
            <div class="champ-b"><label for="note">Nouvelle note</label>
              <textarea class="saisie" id="note" name="note" rows="3" maxlength="2000" required></textarea></div>
            <div><button class="mini" name="action" value="noter" type="submit">Ajouter la note</button></div>
          </form>
        </div>
      </section>
    </div>

    <aside class="s-4 pile-b inspecteur" aria-label="Propriétés du dossier">
      <section class="panneau" aria-labelledby="titre-proprietes">
        <div class="panneau__tete"><h2 id="titre-proprietes">Propriétés</h2></div>
        <div class="panneau__corps">
          <dl class="fiche-b">
            <dt>Statut</dt><dd><?= pastille($dossier['statut'], STATUTS_BUREAU[$dossier['statut']]) ?></dd>
            <dt>Demandeur</dt><dd><?= e($dossier['nom']) ?></dd>
            <dt>Reçue</dt><dd class="mono"><?= date_courte($dossier['date_demande']) ?></dd>
            <dt>Importée</dt><dd class="mono"><?= date_courte($dossier['date_import']) ?></dd>
            <dt>Responsable</dt><dd><?= $dossier['responsable'] ? e($dossier['responsable']) : 'Personne, pour l\'instant' ?></dd>
            <dt>Échéance</dt><dd class="mono<?= en_retard($dossier) ? ' note-b--alerte' : '' ?>"><?= date_echeance($dossier['echeance']) ?></dd>
            <dt>Intervenant</dt><dd><?= $dossier['intervenant'] ? e($dossier['intervenant']) : '—' ?></dd>
<?php if (!$classe): ?>
            <dt>Délai annoncé</dt><dd><?= e(delai_estime((int) $dossier['id'])) ?></dd>
<?php endif; ?>
<?php if ($dossier['date_purge']): ?>
            <dt>Retirée du site</dt><dd class="mono"><?= date_courte($dossier['date_purge']) ?></dd>
<?php endif; ?>
          </dl>
        </div>
      </section>

      <section class="panneau" aria-labelledby="titre-affectation">
        <div class="panneau__tete"><h2 id="titre-affectation">Affectation</h2></div>
        <div class="panneau__corps">
          <form method="post" class="formulaire-b">
            <?= csrf_champ() ?>
            <div class="champ-b"><label for="responsable">Responsable</label>
              <select class="saisie" id="responsable" name="responsable">
                <option value="">Personne</option>
<?php foreach ($agents as $id_a => $nom_a): ?>
                <option value="<?= $id_a ?>"<?= (int) $dossier['id_responsable'] === $id_a ? ' selected' : '' ?>><?= e($nom_a) ?></option>
<?php endforeach; ?>
              </select></div>
            <div class="champ-b"><label for="echeance">Échéance interne (le client ne la voit pas)</label>
              <input class="saisie" id="echeance" name="echeance" type="date" value="<?= e((string) $dossier['echeance']) ?>"></div>
            <div><button class="mini" name="action" value="affecter" type="submit">Affecter</button></div>
          </form>
        </div>
      </section>

      <section class="panneau" aria-labelledby="titre-autres">
        <div class="panneau__tete"><h2 id="titre-autres">Autres demandes de cette adresse</h2></div>
<?php if (!$autres): ?>
        <p class="vide">Première demande à cette adresse. Le cabinet fait bonne impression, pour l'instant.</p>
<?php else: ?>
        <ul class="lignes">
<?php foreach ($autres as $o): ?>
          <li><a class="mono" href="dossier.php?id=<?= $o['id'] ?>"><?= numero_dossier((int) $o['id_site']) ?></a>
            <span class="reste"><?= e($o['type_demande']) ?></span>
            <span class="pousse"><?= pastille($o['statut'], STATUTS_BUREAU[$o['statut']]) ?></span></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
      </section>

<?php if ($classe && !$dossier['date_purge']): ?>
      <section class="panneau panneau--danger" aria-labelledby="titre-purge">
        <div class="panneau__tete"><h2 id="titre-purge">Purger du site</h2></div>
        <div class="panneau__corps">
          <p class="note-b">Supprime définitivement la demande dans la base du site (<code>DELETE /api/contacts.php</code>). Le dossier reste au Bureau.</p>
          <form method="post" action="purge.php" class="formulaire-b">
            <?= csrf_champ() ?>
            <input type="hidden" name="id" value="<?= $dossier['id'] ?>">
            <label class="confirmer-b"><input type="checkbox" name="confirmer" value="1" required> Je confirme la suppression sur le site</label>
            <div><button class="mini mini--danger" type="submit">Purger du site</button></div>
          </form>
        </div>
      </section>
<?php endif; ?>
    </aside>
  </div>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
