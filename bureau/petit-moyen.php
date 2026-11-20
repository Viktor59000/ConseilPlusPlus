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
    // Le formulaire porte la date qu'il affichait : passé minuit, « demain » a changé et l'action viserait un autre mot
    $jour = (string) ($_POST['jour'] ?? '');
    if ($jour !== $demain) {
        $lu = DateTime::createFromFormat('!Y-m-d', $jour);
        $affiche = $lu && $lu->format('Y-m-d') === $jour ? 'du ' . $lu->format('d/m/Y') : 'affiché';
        rediriger('petit-moyen.php', "Action refusée : le mot $affiche n'est plus celui de demain (minuit est passé entre-temps). "
            . "Rien n'a été modifié. Voici le mot du " . date_echeance($demain) . ", à relire avant d'agir.", 'erreur');
    }
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
<header class="tete">
  <div class="tete__texte">
    <p class="tete__ref"><span>Publication le <?= date_echeance($demain) ?> à minuit</span></p>
    <h1>Le mot de demain</h1>
    <p class="tete__sous">Le Bureau le relit par l'API (<code>GET /api/mot-du-jour.php?date=<?= $demain ?></code>), le corrige (<code>PATCH</code>) ou le rejette (<code>DELETE</code>). Une fois publié, un mot ne se réécrit plus.</p>
  </div>
<?php if (!$mot && !$indisponible): ?>
  <form class="tete__actions" method="post">
    <?= csrf_champ() ?>
    <input type="hidden" name="jour" value="<?= $demain ?>">
    <?= bouton('Faire préparer par l\'IA', null, ['nom' => 'action', 'valeur' => 'preparer', 'fleche' => false, 'icone_html' => picto('plume')]) ?>
  </form>
<?php endif; ?>
</header>

<div class="zone">
<?php if ($indisponible): ?>
  <?= bloc_alerte('Mot de demain indisponible : ' . lcfirst(message_erreur_api($r)) . '.', 'erreur') ?>
<?php elseif (!$mot): ?>
  <section class="panneau" aria-labelledby="titre-aucun">
    <div class="panneau__tete"><h2 id="titre-aucun">Aucun mot préparé pour demain</h2></div>
    <p class="vide">Le jour venu, la banque du site choisira une définition ou un sketch déjà relus. Pour un sketch inédit, demandez sa préparation à l'IA (bouton ci-dessus, jusqu'à 90 secondes de réflexion), puis relisez-le ici.</p>
  </section>
<?php else:
    $format = $mot['format']; ?>
  <div class="grille-b">
    <section class="panneau s-7" aria-labelledby="titre-correction">
      <div class="panneau__tete"><h2 id="titre-correction">Corriger</h2><span class="note-b"><?= $format === 'sketch' ? 'Sketch' : 'Définition' ?></span></div>
      <div class="panneau__corps">
<?php if (isset($erreurs['corps'])): ?>
        <?= bloc_alerte($erreurs['corps'], 'erreur') ?>
<?php endif; ?>
        <form method="post" class="formulaire-b">
          <?= csrf_champ() ?>
          <input type="hidden" name="jour" value="<?= $demain ?>">
          <input type="hidden" name="format" value="<?= e($format) ?>">
<?php foreach ($champs_communs + $champs_format[$format] + ['etymologie' => 'Fausse étymologie (facultative)'] as $champ => $libelle):
          $id_champ = 'pm-' . $champ;
          $aide = isset($erreurs[$champ]) ? ' aria-describedby="' . $id_champ . '-erreur" aria-invalid="true"' : ''; ?>
          <div class="champ-b">
            <label for="<?= $id_champ ?>"><?= e($libelle) ?></label>
<?php if (in_array($champ, $longs, true)): ?>
            <textarea class="saisie" id="<?= $id_champ ?>" name="<?= $champ ?>" rows="3"<?= $aide ?>><?= e((string) ($valeurs[$champ] ?? '')) ?></textarea>
<?php else: ?>
            <input class="saisie" id="<?= $id_champ ?>" name="<?= $champ ?>" value="<?= e((string) ($valeurs[$champ] ?? '')) ?>"<?= $aide ?>>
<?php endif; ?>
<?php if (isset($erreurs[$champ])): ?>
            <p class="note-b note-b--alerte" id="<?= $id_champ ?>-erreur"><?= e($erreurs[$champ]) ?></p>
<?php endif; ?>
          </div>
<?php endforeach; ?>
          <div><?= bouton('Enregistrer les corrections', null, ['nom' => 'action', 'valeur' => 'corriger', 'fleche' => false]) ?></div>
        </form>
      </div>
    </section>

    <div class="s-5 pile-b">
      <section class="panneau" aria-labelledby="titre-apercu">
        <div class="panneau__tete"><h2 id="titre-apercu">Tel qu'il sera publié</h2>
          <span class="note-b"><?= $mot['source'] === 'ia' ? 'Préparé par l\'IA' : 'Banque du site' ?>, certitude <?= (int) $mot['certitude'] ?> %</span></div>
        <div class="panneau__corps">
          <div class="entree-b">
            <p class="entree-b__mot"><strong><?= e($mot['mot']) ?></strong>, <em><?= e($mot['nature']) ?></em></p>
<?php if ($format === 'sketch'): ?>
            <p class="note-b"><?= e($mot['sketch']['scene']) ?></p>
<?php foreach ($mot['sketch']['repliques'] as $rep): ?>
            <p><strong><?= $rep['qui'] === 'conseiller' ? 'Le conseiller' : 'Le stagiaire' ?> :</strong> <?= e($rep['texte']) ?></p>
<?php endforeach; ?>
<?php if (!empty($mot['sketch']['relance'])): ?>
            <p><strong>Le stagiaire :</strong> <?= e($mot['sketch']['relance']) ?></p>
<?php endif; ?>
<?php else: ?>
            <p><?= e($mot['definition']) ?></p>
            <p class="note-b">Ex. : <?= e($mot['exemple']) ?></p>
            <p class="note-b">Syn. : <?= e($mot['synonyme']) ?></p>
<?php endif; ?>
<?php if ($mot['etymologie']): ?>
            <p class="note-b">Étym. : <?= e($mot['etymologie']) ?></p>
<?php endif; ?>
          </div>
        </div>
      </section>

      <section class="panneau panneau--danger" aria-labelledby="titre-rejet">
        <div class="panneau__tete"><h2 id="titre-rejet">Rejeter ce mot</h2></div>
        <div class="panneau__corps">
          <p class="note-b">Supprime l'entrée préparée (<code>DELETE</code>). Le jour venu, la banque choisira ; une nouvelle préparation reste possible d'ici là.</p>
          <form method="post" class="formulaire-b">
            <?= csrf_champ() ?>
            <input type="hidden" name="jour" value="<?= $demain ?>">
            <label class="confirmer-b"><input type="checkbox" name="confirmer" value="1" required> Je confirme le rejet</label>
            <div><button class="mini mini--danger" name="action" value="rejeter" type="submit">Rejeter le mot de demain</button></div>
          </form>
        </div>
      </section>
    </div>
  </div>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
