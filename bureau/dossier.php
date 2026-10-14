<?php
/**
 * bureau/dossier.php — Fiche d'un dossier : la demande, son statut, et la réponse officielle du cabinet.
 * Actions (POST + CSRF) : examiner, rédiger une réponse (conseil tiré de l'API), enregistrer, classer, rouvrir.
 */
require("includes/bdd.php");
require("includes/session.php");
require("includes/metier.php");
$agent = exiger_agent();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$stmt = $bdd->prepare('SELECT d.*, a.nom AS agent FROM dossiers d LEFT JOIN agents a ON a.id = d.id_agent WHERE d.id = ?');
$stmt->execute([$id ?: 0]);
$dossier = $stmt->fetch();
if (!$dossier) {
    http_response_code(404);
    annoncer('Dossier introuvable. Il a peut-être été égaré, ce qui arrive.', 'erreur');
    header('Location: dossiers.php');
    exit();
}

$brouillon = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valide()) {
        annoncer('Session expirée : action annulée.', 'erreur');
    } else {
        $maj = $bdd->prepare('UPDATE dossiers SET statut = :statut, reponse = :reponse, date_reponse = :date_reponse, envoi = :envoi,
                              id_agent = :agent WHERE id = :id');
        $valeurs = ['statut' => $dossier['statut'], 'reponse' => $dossier['reponse'], 'date_reponse' => $dossier['date_reponse'],
            'envoi' => $dossier['envoi'], 'agent' => $agent['id'], 'id' => $dossier['id']];
        $texte = trim((string) ($_POST['reponse'] ?? ''));
        switch ($_POST['action'] ?? '') {
            case 'examiner':
                $valeurs['statut'] = 'en_cours';
                annoncer('Dossier pris en charge. Il est désormais « en cours d\'examen », ce qui ne garantit rien.');
                break;
            case 'rediger':
                $brouillon = rediger_reponse($dossier, $agent['nom']);
                $valeurs['statut'] = $dossier['statut'] === 'a_traiter' ? 'en_cours' : $dossier['statut'];
                break;
            case 'enregistrer':
                $valeurs['reponse'] = mb_substr($texte, 0, 5000) ?: null;
                $valeurs['statut'] = $dossier['statut'] === 'a_traiter' ? 'en_cours' : $dossier['statut'];
                annoncer('Réponse enregistrée.');
                break;
            case 'classer':
                if ($texte === '') {
                    annoncer('Impossible de classer un dossier sans réponse. Même le Bureau a ses principes.', 'erreur');
                    break;
                }
                $valeurs['reponse'] = mb_substr($texte, 0, 5000);
                [$parti, $raison] = envoyer_reponse($dossier, $valeurs['reponse']);
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
        }
        $maj->execute($valeurs);
        if ($brouillon === null) {
            header('Location: dossier.php?id=' . $dossier['id']);
            exit();
        }
        $dossier = array_merge($dossier, ['statut' => $valeurs['statut']]);
    }
}

$reponse = $brouillon ?? $dossier['reponse'] ?? '';
$classe = $dossier['statut'] === 'classe';
$titre = 'Dossier n° ' . sprintf('%04d', $dossier['id_site']);
$page = 'dossiers';
include("includes/haut.php");
?>
<p><a class="lien-fort" href="dossiers.php">← Tous les dossiers</a></p>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Dossier n° <?= sprintf('%04d', $dossier['id_site']) ?></p>
    <h1><?= htmlspecialchars($dossier['type_demande']) ?></h1>
    <p><span class="pastille pastille-<?= $dossier['statut'] ?>"><?= STATUTS_DOSSIER[$dossier['statut']] ?></span>
      <?php if (!$classe): ?><span class="aide">Délai estimé : <?= htmlspecialchars(delai_estime((int) $dossier['id'])) ?>.</span><?php endif; ?></p>
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
      <dt>Agent</dt><dd><?= $dossier['agent'] ? htmlspecialchars($dossier['agent']) : 'Personne, pour l\'instant' ?></dd>
    </dl>
    <blockquote class="message"><?= nl2br(htmlspecialchars($dossier['message'])) ?></blockquote>
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
<?php else: ?>
    <form method="post" class="formulaire">
      <?= csrf_champ() ?>
      <label for="reponse">Texte de la réponse</label>
      <textarea id="reponse" name="reponse" rows="16" maxlength="5000" placeholder="Utilisez « Rédiger une réponse » : le cabinet propose un texte, que vous pouvez corriger."><?= htmlspecialchars($reponse) ?></textarea>
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
<?php include("includes/bas.php"); ?>
