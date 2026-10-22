<?php
/**
 * bureau/petit-moyen.php — Modération du mot de demain (Le Petit Moyen), uniquement par l'API du site :
 * relire (GET ?date=demain), corriger (PATCH), rejeter (DELETE : la banque reprendra le relais) ou faire préparer
 * par l'IA (POST). Les jours déjà publiés ne se modifient pas.
 */
require_once __DIR__ . '/includes/amorce.php';

$demain = date('Y-m-d', strtotime('+1 day'));
$ressource = 'mot-du-jour.php?date=' . $demain;

// Champs corrigeables (mêmes noms que l'API), selon le format du mot
$champs_communs = ['mot' => 'Mot', 'nature' => 'Nature'];
$champs_format = [
    'definition' => ['definition' => 'Définition', 'exemple' => 'Exemple', 'synonyme' => 'Synonyme'],
    'sketch' => ['epellation' => 'Épellation (le conseiller)', 'scene' => 'Scène (une phrase)', 'conseiller' => 'Conclusion du conseiller',
        'stagiaire' => 'Chute du stagiaire (« C\'est vrai. … »)', 'relance' => 'Relance du stagiaire (facultative)', 'decoupage' => 'Découpage (morceaux séparés par « · »)'],
];
$longs = ['definition', 'exemple', 'scene', 'conseiller', 'stagiaire', 'relance', 'etymologie', 'epellation'];

$erreurs = [];
$saisie = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exiger_post('petit-moyen.php');
    $action = $_POST['action'] ?? '';
    if ($action === 'preparer') {
        // L'IA peut réfléchir un moment : attente plus longue que d'habitude
        $r = api('POST', 'mot-du-jour.php', null, true, 90);
        match ($r['code']) {
            201 => rediriger('petit-moyen.php', 'Le stagiaire a préparé le mot de demain. Relisez-le avant minuit.'),
            200 => rediriger('petit-moyen.php', 'Rien de neuf : un mot était déjà prêt, ou l\'IA n\'a rien proposé de valable (la banque prendra le relais).'),
            429 => rediriger('petit-moyen.php', 'Trop de demandes de préparation : réessayez dans une minute.', 'erreur'),
            default => rediriger('petit-moyen.php', message_erreur_api($r) . '.', 'erreur'),
        };
    }
    if ($action === 'rejeter') {
        if (($_POST['confirmer'] ?? '') !== '1') {
            rediriger('petit-moyen.php', 'Rejet annulé : cochez la case de confirmation.', 'erreur');
        }
        $r = api('DELETE', $ressource);
        match ($r['code']) {
            204 => rediriger('petit-moyen.php', 'Mot de demain rejeté. Le jour venu, la banque choisira, à moins que vous n\'en fassiez préparer un autre.'),
            404 => rediriger('petit-moyen.php', 'Aucun mot préparé pour demain : rien à rejeter.', 'erreur'),
            default => rediriger('petit-moyen.php', message_erreur_api($r) . '.', 'erreur'),
        };
    }
    if ($action === 'corriger') {
        $format = array_key_exists($_POST['format'] ?? '', $champs_format) ? $_POST['format'] : 'definition';
        $saisie = [];
        foreach (array_keys($champs_communs + $champs_format[$format] + ['etymologie' => '']) as $champ) {
            $saisie[$champ] = trim((string) ($_POST[$champ] ?? ''));
        }
        $r = api('PATCH', $ressource, $saisie);
        if ($r['code'] === 200) {
            rediriger('petit-moyen.php', 'Corrections enregistrées : c\'est cette version qui sera publiée demain.');
        }
        if ($r['code'] !== 422) {
            rediriger('petit-moyen.php', $r['code'] === 404 ? 'Le mot de demain n\'existe plus (rejeté entre-temps ?).' : message_erreur_api($r) . '.', 'erreur');
        }
        $erreurs = $r['data']['champs'] ?? ['corps' => 'Correction refusée.'];
        annoncer('Correction refusée : ' . count($erreurs) . ' champ(s) à reprendre.', 'erreur');
    } elseif ($saisie === null) {
        rediriger('petit-moyen.php');
    }
}

$r = api('GET', $ressource);
$mot = $r['code'] === 200 ? $r['data'] : null;
$indisponible = !in_array($r['code'], [200, 404], true);

// Valeurs du formulaire : la saisie refusée, sinon le mot tel qu'il sera publié
$valeurs = $saisie ?? [];
if ($mot && $saisie === null) {
    $valeurs = ['mot' => $mot['mot'], 'nature' => $mot['nature'], 'etymologie' => $mot['etymologie'] ?? ''];
    if ($mot['format'] === 'sketch') {
        $repliques = $mot['sketch']['repliques'];
        $valeurs += ['epellation' => $mot['sketch']['epellation'], 'scene' => $mot['sketch']['scene'],
            'conseiller' => $repliques[0]['texte'], 'stagiaire' => end($repliques)['texte'],
            'relance' => $mot['sketch']['relance'] ?? '', 'decoupage' => $mot['sketch']['decoupage'] ?? ''];
    } else {
        $valeurs += ['definition' => $mot['definition'], 'exemple' => $mot['exemple'], 'synonyme' => $mot['synonyme']];
    }
}

$titre = 'Le Petit Moyen';
$page = 'petit-moyen';
require __DIR__ . '/includes/haut.php';
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Modération du dictionnaire</p>
    <h1>Le mot de demain</h1>
    <p class="aide">Publication le <?= date_echeance($demain) ?> à minuit. Le Bureau le relit par l'API (<code>GET /api/mot-du-jour.php?date=<?= $demain ?></code>), le corrige (<code>PATCH</code>) ou le rejette (<code>DELETE</code>). Une fois publié, un mot ne se réécrit plus.</p>
  </div>
<?php if (!$mot && !$indisponible): ?>
  <form method="post">
    <?= csrf_champ() ?>
    <button class="btn btn-clair" name="action" value="preparer" type="submit">Faire préparer par l'IA</button>
  </form>
<?php endif; ?>
</div>

<?php if ($indisponible): ?>
<p class="annonce annonce-erreur">Mot de demain indisponible : <?= htmlspecialchars(lcfirst(message_erreur_api($r))) ?>.</p>
<?php elseif (!$mot): ?>
<section class="carte">
  <h2>Aucun mot préparé pour demain</h2>
  <p class="aide">Le jour venu, la banque du site choisira une définition ou un sketch déjà relus. Pour un sketch inédit, demandez sa préparation à l'IA (bouton ci-dessus), puis relisez-le ici.</p>
</section>
<?php else:
    $format = $mot['format']; ?>
<div class="colonnes">
  <section class="carte" aria-labelledby="titre-correction">
    <h2 id="titre-correction">Corriger</h2>
<?php if (isset($erreurs['corps'])): ?>
    <p class="alerte"><?= htmlspecialchars($erreurs['corps']) ?></p>
<?php endif; ?>
    <form method="post" class="formulaire">
      <?= csrf_champ() ?>
      <input type="hidden" name="format" value="<?= htmlspecialchars($format) ?>">
<?php foreach ($champs_communs + $champs_format[$format] + ['etymologie' => 'Fausse étymologie (facultative)'] as $champ => $libelle):
      $id_champ = 'pm-' . $champ;
      $aide = isset($erreurs[$champ]) ? ' aria-describedby="' . $id_champ . '-erreur" aria-invalid="true"' : ''; ?>
      <label for="<?= $id_champ ?>"><?= htmlspecialchars($libelle) ?></label>
<?php if (in_array($champ, $longs, true)): ?>
      <textarea id="<?= $id_champ ?>" name="<?= $champ ?>" rows="3"<?= $aide ?>><?= htmlspecialchars((string) ($valeurs[$champ] ?? '')) ?></textarea>
<?php else: ?>
      <input id="<?= $id_champ ?>" name="<?= $champ ?>" value="<?= htmlspecialchars((string) ($valeurs[$champ] ?? '')) ?>"<?= $aide ?>>
<?php endif; ?>
<?php if (isset($erreurs[$champ])): ?>
      <p class="alerte" id="<?= $id_champ ?>-erreur"><?= htmlspecialchars($erreurs[$champ]) ?></p>
<?php endif; ?>
<?php endforeach; ?>
      <div><button class="btn btn-tampon" name="action" value="corriger" type="submit">Enregistrer les corrections</button></div>
    </form>
  </section>

  <section class="carte" aria-labelledby="titre-apercu">
    <h2 id="titre-apercu">Tel qu'il sera publié</h2>
    <dl class="fiche">
      <dt>Mot</dt><dd><strong><?= htmlspecialchars($mot['mot']) ?></strong>, <em><?= htmlspecialchars($mot['nature']) ?></em></dd>
      <dt>Format</dt><dd><?= $format === 'sketch' ? 'Sketch du conseiller et du stagiaire' : 'Définition' ?></dd>
      <dt>Origine</dt><dd><?= $mot['source'] === 'ia' ? 'Préparé par l\'IA' : 'Banque du site' ?> · certitude <?= (int) $mot['certitude'] ?> %</dd>
    </dl>
<?php if ($format === 'sketch'): ?>
    <p class="aide"><?= htmlspecialchars($mot['sketch']['scene']) ?></p>
<?php foreach ($mot['sketch']['repliques'] as $rep): ?>
    <p><strong><?= $rep['qui'] === 'conseiller' ? 'Le conseiller' : 'Le stagiaire' ?> :</strong> <?= htmlspecialchars($rep['texte']) ?></p>
<?php endforeach; ?>
<?php if (!empty($mot['sketch']['relance'])): ?>
    <p><strong>Le stagiaire :</strong> <?= htmlspecialchars($mot['sketch']['relance']) ?></p>
<?php endif; ?>
<?php else: ?>
    <blockquote class="message"><?= htmlspecialchars($mot['definition']) ?></blockquote>
    <p class="aide">Ex. : <?= htmlspecialchars($mot['exemple']) ?> · Syn. : <?= htmlspecialchars($mot['synonyme']) ?></p>
<?php endif; ?>
<?php if ($mot['etymologie']): ?>
    <p class="aide">Étym. : <?= htmlspecialchars($mot['etymologie']) ?></p>
<?php endif; ?>

    <form method="post" class="purge">
      <?= csrf_champ() ?>
      <h3>Rejeter ce mot</h3>
      <p class="aide">Supprime l'entrée préparée (<code>DELETE</code>). Le jour venu, la banque choisira ; une nouvelle préparation reste possible d'ici là.</p>
      <label class="confirmer"><input type="checkbox" name="confirmer" value="1" required> Je confirme le rejet</label>
      <div><button class="btn" name="action" value="rejeter" type="submit">Rejeter le mot de demain</button></div>
    </form>
  </section>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/bas.php'; ?>
