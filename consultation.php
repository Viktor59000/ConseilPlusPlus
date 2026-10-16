<?php
/**
 * consultation.php — Consultation approfondie : un questionnaire qui ne sert à rien,
 * dont les réponses sont envoyées à une IA (includes/ia.php) qui rend un conseil tordu.
 * Les questions changent à chaque visite : 4 sont tirées dans data/questions.php.
 * Réservé aux clients connectés.
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

require("db.php");
require("includes/ia.php");
require("includes/csrf.php");

const NB_QUESTIONS = 4;
$banque = require("data/questions.php");

/** Tire de nouvelles questions et les garde en session pour valider l'envoi. */
function tirer_questions(array $banque): array
{
    $cles = (array) array_rand($banque, NB_QUESTIONS);
    shuffle($cles);
    $_SESSION['questionnaire'] = $cles;
    return $cles;
}

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
        foreach ($tirees as $cle) {
            $q = $banque[$cle];
            $valeur = trim((string) ($_POST[$cle] ?? ''));
            $valeurs[$cle] = $valeur;

            if ($q['type'] === 'texte' && ($valeur === '' || mb_strlen($valeur) > $q['max'])) {
                $erreurs[$cle] = "Répondez à cette question ({$q['max']} caractères maximum).";
            } elseif ($q['type'] === 'nombre' && filter_var($valeur, FILTER_VALIDATE_INT, ['options' => ['min_range' => $q['min'], 'max_range' => $q['max']]]) === false) {
                $erreurs[$cle] = "Indiquez un nombre entier entre {$q['min']} et {$q['max']}.";
            } elseif ($q['type'] === 'choix' && !in_array($valeur, $q['options'], true)) {
                $erreurs[$cle] = 'Choisissez une réponse.';
            }
        }

        $valeurs['probleme'] = trim((string) ($_POST['probleme'] ?? ''));
        if (mb_strlen($valeurs['probleme']) > 300) {
            $erreurs['probleme'] = 'Votre problème est trop long (300 caractères maximum). Ayez des problèmes plus courts.';
        }
        if (empty($_POST['accord'])) {
            $erreurs['accord'] = 'Vous devez accepter que ce conseil ne serve à rien.';
        }
    }

    if (!$erreurs) {
        $reponses = [];
        foreach ($tirees as $cle) {
            $reponses[$banque[$cle]['libelle']] = $valeurs[$cle];
        }
        if ($valeurs['probleme'] !== '') {
            $reponses['Son problème'] = $valeurs['probleme'];
        }
        $resultat = demander_conseil_ia($reponses, $bdd);
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
include("includes/header.php");
include("includes/espace-nav.php");

/** Affiche le message d'erreur d'un champ, s'il y en a un. */
function erreur_champ(array $erreurs, string $champ): void
{
    if (isset($erreurs[$champ])) {
        echo '<p class="champ-msg" id="err-' . $champ . '">' . htmlspecialchars($erreurs[$champ]) . "</p>\n";
    }
}
/** Attributs ARIA d'un champ en erreur. */
function aria_erreur(array $erreurs, string $champ): string
{
    return isset($erreurs[$champ]) ? ' aria-invalid="true" aria-describedby="err-' . $champ . '"' : '';
}
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
    <p class="alerte alerte-erreur" role="alert"><?= htmlspecialchars($erreurs['session']) ?></p>
<?php elseif ($erreurs): ?>
    <p class="alerte alerte-erreur" role="alert">Le questionnaire contient <?= count($erreurs) ?> erreur(s). Même pour un formulaire inutile, nous avons des exigences.</p>
<?php endif; ?>

<?php foreach ($tirees as $cle):
    $q = $banque[$cle];
    $id = 'q-' . $cle;
    $valeur = $valeurs[$cle] ?? '';
?>
    <div class="champ<?= isset($erreurs[$cle]) ? ' champ-erreur' : '' ?>">
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
      <?php erreur_champ($erreurs, $cle); ?>
    </div>
<?php endforeach; ?>

    <div class="champ<?= isset($erreurs['probleme']) ? ' champ-erreur' : '' ?>">
      <label for="q-probleme">Votre problème <span class="facultatif">(facultatif, nous ne le lirons pas)</span></label>
      <textarea id="q-probleme" name="probleme" rows="3" maxlength="300"<?= aria_erreur($erreurs, 'probleme') ?>><?= htmlspecialchars($valeurs['probleme']) ?></textarea>
      <?php erreur_champ($erreurs, 'probleme'); ?>
    </div>

    <div class="champ champ-case<?= isset($erreurs['accord']) ? ' champ-erreur' : '' ?>">
      <input id="q-accord" type="checkbox" name="accord" value="1" required<?= !empty($_POST['accord']) ? ' checked' : '' ?><?= aria_erreur($erreurs, 'accord') ?>>
      <label for="q-accord">J'accepte que ce conseil ne serve à rien.</label>
      <?php erreur_champ($erreurs, 'accord'); ?>
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

<?php include("includes/footer.php"); ?>
