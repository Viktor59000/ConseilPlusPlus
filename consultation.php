<?php
/**
 * consultation.php — Consultation approfondie : un questionnaire qui ne sert à rien,
 * dont les réponses sont envoyées à une IA (includes/ia.php) qui rend un conseil tordu.
 * Les questions changent à chaque visite : 4 sont tirées dans data/questions.php.
 * Réservé aux clients connectés.
 */
require_once __DIR__ . '/includes/init.php';
exiger_connexion();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/ia.php';
require_once __DIR__ . '/includes/consultation.php';
require_once __DIR__ . '/includes/formulaire.php';

$banque = require __DIR__ . '/data/questions.php';
$erreurs = [];
$resultat = null;
$valeurs = ['probleme' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Les questions affichées sont celles gardées en session (pas celles envoyées par le navigateur)
    $tirees = array_values(array_intersect($_SESSION['questionnaire'] ?? [], array_keys($banque)));

    if (!csrf_valide()) {
        $tirees = tirer_questions($banque);
        $erreurs['session'] = 'Le formulaire a expiré. Voici de nouvelles questions, tout aussi inutiles.';
    } elseif (count($tirees) !== NB_QUESTIONS) {
        $tirees = tirer_questions($banque);
        $erreurs['session'] = 'Le questionnaire a expiré. Voici de nouvelles questions, tout aussi inutiles.';
    } else {
        [$valeurs, $erreurs] = valider_consultation($banque, $tirees, $_POST);
    }

    if (!$erreurs) {
        $resultat = demander_conseil_ia(reponses_consultation($banque, $tirees, $valeurs), $bdd);
        $dossier = sprintf('%04d-IA', random_int(1, 9999));
        unset($_SESSION['questionnaire']);
    }
} else {
    $tirees = tirer_questions($banque);
}

$titre = 'Consultation approfondie';
$description = 'Répondez à un questionnaire sans rapport avec votre situation et recevez un verdict officiel de notre intelligence artificielle moyenne.';
$page = 'espace';
$onglet = 'consultation';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/espace-nav.php';
?>

<section class="conteneur entete-page">
  <p class="etiquette">Formulaire CP+ <?= random_int(1000, 9999) ?>-<?= chr(random_int(65, 90)) ?></p>
  <h1>Consultation approfondie</h1>
  <p class="chapo">Répondez à ce questionnaire sans aucun rapport avec votre situation. Les questions changent à chaque visite, pour garantir une inutilité toujours renouvelée.</p>
</section>

<?php if ($resultat): ?>
<section class="conteneur">
  <article class="fiche fiche-grande" id="verdict" aria-labelledby="verdict-titre">
    <header class="fiche-entete">
      <h2 id="verdict-titre">Verdict de l'expertise</h2>
      <span class="mono">Dossier n° <span data-dossier><?= $dossier ?></span></span>
    </header>
    <p class="fiche-conseil"><?= nl2br(htmlspecialchars($resultat['conseil'])) ?></p>
    <span class="tampon tampon-anime" aria-hidden="true">Validé</span>
    <p class="fiche-pied mono"><?= $resultat['source'] === 'ia'
        ? 'Rédigé par : l\'intelligence artificielle moyenne'
        : 'IA indisponible. Rédigé par : le stagiaire' ?></p>
  </article>
  <div class="actions">
    <a class="btn btn-tampon" href="consultation.php">Nouvelle consultation</a>
    <button class="btn btn-clair" type="button" data-classer="#verdict" data-source="<?= $resultat['source'] ?>" hidden>Classer au dossier</button>
  </div>
</section>
<script src="<?= fichier_versionne('js/carnet.js') ?>" defer></script>
<?php else: ?>
<section class="conteneur guichets">
  <form class="formulaire" action="consultation.php" method="POST" novalidate aria-labelledby="titre-questionnaire">
    <?= csrf_champ() ?>
    <h2 id="titre-questionnaire">Questionnaire</h2>
<?php if (isset($erreurs['session'])): ?>
    <?= alerte($erreurs['session']) ?>
<?php elseif ($erreurs): ?>
    <?= alerte('Le questionnaire contient ' . count($erreurs) . ' erreur(s). Même pour un formulaire inutile, nous avons des exigences.') ?>
<?php endif; ?>

<?php foreach ($tirees as $cle):
    $q = $banque[$cle];
    $id = 'q-' . $cle;
    $valeur = $valeurs[$cle] ?? '';
?>
    <div class="<?= classe_champ($erreurs, $cle) ?>">
      <label for="<?= $id ?>"><?= htmlspecialchars($q['libelle']) ?></label>
<?php if ($q['type'] === 'choix'): ?>
      <select id="<?= $id ?>" name="<?= $cle ?>" required<?= aria_erreur($erreurs, $cle) ?>>
        <option value="">Choisir…</option>
<?php foreach ($q['options'] as $option): ?>
        <option<?= $valeur === $option ? ' selected' : '' ?>><?= htmlspecialchars($option) ?></option>
<?php endforeach; ?>
      </select>
<?php elseif ($q['type'] === 'nombre'): ?>
      <input id="<?= $id ?>" type="number" name="<?= $cle ?>" min="<?= $q['min'] ?>" max="<?= $q['max'] ?>" inputmode="numeric" required value="<?= htmlspecialchars($valeur) ?>"<?= aria_erreur($erreurs, $cle) ?>>
<?php else: ?>
      <input id="<?= $id ?>" type="text" name="<?= $cle ?>" maxlength="<?= $q['max'] ?>" required value="<?= htmlspecialchars($valeur) ?>"<?= aria_erreur($erreurs, $cle) ?>>
<?php endif; ?>
      <?= erreur_champ($erreurs, $cle) ?>
    </div>
<?php endforeach; ?>

    <div class="<?= classe_champ($erreurs, 'probleme') ?>">
      <label for="q-probleme">Votre problème <span class="facultatif">(facultatif, nous ne le lirons pas)</span></label>
      <textarea id="q-probleme" name="probleme" rows="3" maxlength="300"<?= aria_erreur($erreurs, 'probleme') ?>><?= htmlspecialchars($valeurs['probleme']) ?></textarea>
      <?= erreur_champ($erreurs, 'probleme') ?>
    </div>

    <div class="<?= classe_champ($erreurs, 'accord', 'champ champ-case') ?>">
      <input id="q-accord" type="checkbox" name="accord" value="1" required<?= !empty($_POST['accord']) ? ' checked' : '' ?><?= aria_erreur($erreurs, 'accord') ?>>
      <label for="q-accord">J'accepte que ce conseil ne serve à rien.</label>
      <?= erreur_champ($erreurs, 'accord') ?>
    </div>

    <button class="btn btn-tampon" type="submit">Lancer l'expertise</button>
  </form>

  <aside class="formulaire formulaire-info" aria-label="Informations">
    <h2>Comment ça marche</h2>
    <ol class="liste-info">
      <li>Vos réponses sont transmises à notre intelligence artificielle.</li>
      <li>Elle les mélange sans les comprendre, comme un vrai consultant.</li>
      <li>Vous recevez un conseil officiel, tamponné et parfaitement inutile.</li>
    </ol>
    <p class="mono">Questions en circulation : <?= count($banque) ?>. Délai d'utilité : jamais.</p>
  </aside>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
