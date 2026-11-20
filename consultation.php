<?php
/**
 * consultation.php — Guichet n° 2 : la consultation approfondie. Un questionnaire qui ne sert à rien, dont les
 * réponses sont envoyées à une IA (includes/ia.php) qui rend un conseil tordu. Les questions changent à chaque visite :
 * 4 sont tirées dans data/questions.php (gardées en session). Réservé aux clients connectés.
 * Se conclut par le verdict tamponné (à garder au carnet : js/carnet.js) et le lien vers l'attestation de conseil non suivi.
 * Styles : css/v2/pages/epreuves.css.
 */
require_once __DIR__ . '/includes/init.php';
exiger_connexion();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/ia.php';
require_once __DIR__ . '/includes/consultation.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/guichets.php';

$banque = require __DIR__ . '/data/questions.php';
$g = guichet('consultation');
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

page_debut([
    'titre' => 'Consultation approfondie',
    'description' => 'Guichet n° 2 : répondez à un questionnaire sans rapport avec votre situation et recevez le verdict tamponné de notre intelligence artificielle moyenne.',
    'page' => 'guichet',
    'robots' => 'noindex',
    'css' => ['css/v2/pages/epreuves.css'],
    'scripts' => ['js/carnet.js'],
]);
echo bandeau_guichets('consultation');
?>
<section class="heros epreuve" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Consultation approfondie</h1>
      <p class="heros__chapeau">Répondez à ce questionnaire sans aucun rapport avec votre situation. Les questions changent à chaque visite, pour garantir une inutilité toujours renouvelée.</p>
      <ol class="liste-reglement">
        <li>Vos réponses sont transmises à l'intelligence artificielle du cabinet.</li>
        <li>Elle les mélange sans les comprendre, comme un vrai consultant.</li>
        <li>Vous recevez un verdict officiel, tamponné et parfaitement inutile.</li>
      </ol>
      <p class="epreuve__note mono">Questions en circulation : <?= count($banque) ?>. Délai d'utilité : jamais.</p>
      <div class="epreuve__plaque"><?= plaque_guichet($g['numero'], 'Consultation') ?></div>
    </div>
    <div class="epreuve__poste">
<?php if ($resultat): ?>
      <div class="epreuve__verdict" id="verdict-zone">
        <?= attestation([
            'id' => 'verdict', 'type' => 'verdict', 'titre' => 'Verdict de l\'expertise', 'niveau' => 2,
            'reference_html' => 'dossier n° <span data-dossier>' . e($dossier) . '</span>',
            'service' => 'La consultation, guichet n° 2',
            'corps_html' => '<p class="fiche-conseil">' . nl2br(e($resultat['conseil'])) . '</p>',
            'signataire' => $resultat['source'] === 'ia' ? 'L\'intelligence artificielle moyenne' : 'Le stagiaire',
            'fonction' => $resultat['source'] === 'ia' ? 'experte, à peu près' : 'l\'IA étant indisponible', 'paraphe' => $resultat['source'] === 'ia' ? 'I. A. M.' : 'Le stag.',
            'tampon' => ['texte' => 'Validé', 'mouvement' => 'au-chargement', 'angle' => -8],
            'apres_html' => '<div class="boutons verdict__actions">'
                . bouton('Obtenir l\'attestation de conseil non suivi', 'attestation.php?document=' . $g['document'])
                . bouton('Garder au carnet', null, ['type' => 'button', 'variante' => 'secondaire', 'attributs' => ['hidden' => true, 'data-classer' => '#verdict', 'data-source' => $resultat['source']]])
                . lien_fleche('Nouvelle consultation', 'consultation.php')
                . '</div>',
        ]) ?>
      </div>
<?php else: ?>
      <form class="feuille" action="consultation.php" method="POST" novalidate aria-labelledby="questionnaire-titre">
        <?= csrf_champ() ?>
        <div class="feuille__entete">
          <h2 id="questionnaire-titre">Questionnaire</h2>
          <p class="mono">Toutes les questions sont obligatoires. Le problème, non : il ne sera pas lu.</p>
        </div>
<?php if (isset($erreurs['session'])): ?>
        <?= bloc_alerte($erreurs['session'], 'erreur', ['titre' => 'Questionnaire renouvelé']) ?>
<?php elseif ($erreurs): ?>
        <?= bloc_alerte('Le questionnaire contient ' . count($erreurs) . ' erreur(s). Même pour un formulaire inutile, le cabinet a des exigences.', 'erreur', ['titre' => 'Questionnaire incomplet']) ?>
<?php endif; ?>
<?php foreach ($tirees as $cle):
    $q = $banque[$cle];
    $o = ['requis' => true, 'valeur' => $valeurs[$cle] ?? '', 'id' => 'q-' . $cle];
    if ($q['type'] === 'choix') {
        $o += ['type' => 'select', 'options' => ['' => 'Choisir…'] + array_combine($q['options'], $q['options'])];
    } elseif ($q['type'] === 'nombre') {
        $o += ['type' => 'number', 'inputmode' => 'numeric', 'officiel' => true, 'attributs' => ['min' => $q['min'], 'max' => $q['max']]];
    } else {
        $o += ['maxlength' => $q['max']];
    }
?>
        <?= champ($cle, $q['libelle'], $erreurs, $o) ?>
<?php endforeach; ?>
        <?= champ('probleme', 'Votre problème', $erreurs, ['type' => 'textarea', 'rows' => 3, 'maxlength' => 300, 'valeur' => $valeurs['probleme'], 'id' => 'q-probleme', 'aide' => 'Facultatif. Nous ne le lirons pas.']) ?>
        <?= champ('accord', 'J\'accepte que ce conseil ne serve à rien.', $erreurs, ['type' => 'case', 'requis' => true, 'valeur' => !empty($_POST['accord']), 'id' => 'q-accord']) ?>
        <div class="boutons"><?= bouton('Lancer l\'expertise', null, ['type' => 'submit']) ?></div>
      </form>
<?php endif; ?>
    </div>
  </div>
</section>
<?php page_fin(); ?>
