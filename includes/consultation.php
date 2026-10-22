<?php
/**
 * Consultation approfondie (consultation.php) : tirage des questions et validation des réponses.
 * Banque de questions : data/questions.php.
 */

const NB_QUESTIONS = 4;

/** Tire de nouvelles questions et les garde en session pour valider l'envoi. */
function tirer_questions(array $banque): array
{
    $cles = (array) array_rand($banque, NB_QUESTIONS);
    shuffle($cles);
    $_SESSION['questionnaire'] = $cles;
    return $cles;
}

/**
 * Nettoie et valide les réponses aux questions tirées, au problème (facultatif) et à l'accord.
 * Retourne [$valeurs, $erreurs] ; $erreurs est vide si tout est correct.
 */
function valider_consultation(array $banque, array $tirees, array $source): array
{
    $valeurs = [];
    $erreurs = [];
    foreach ($tirees as $cle) {
        $q = $banque[$cle];
        $valeur = trim((string) ($source[$cle] ?? ''));
        $valeurs[$cle] = $valeur;

        if ($q['type'] === 'texte' && ($valeur === '' || mb_strlen($valeur) > $q['max'])) {
            $erreurs[$cle] = "Répondez à cette question ({$q['max']} caractères maximum).";
        } elseif ($q['type'] === 'nombre' && filter_var($valeur, FILTER_VALIDATE_INT, ['options' => ['min_range' => $q['min'], 'max_range' => $q['max']]]) === false) {
            $erreurs[$cle] = "Indiquez un nombre entier entre {$q['min']} et {$q['max']}.";
        } elseif ($q['type'] === 'choix' && !in_array($valeur, $q['options'], true)) {
            $erreurs[$cle] = 'Choisissez une réponse.';
        }
    }

    $valeurs['probleme'] = trim((string) ($source['probleme'] ?? ''));
    if (mb_strlen($valeurs['probleme']) > 300) {
        $erreurs['probleme'] = 'Votre problème est trop long (300 caractères maximum). Ayez des problèmes plus courts.';
    }
    if (empty($source['accord'])) {
        $erreurs['accord'] = 'Vous devez accepter que ce conseil ne serve à rien.';
    }
    return [$valeurs, $erreurs];
}

/** Réponses présentées à l'IA : libellé de la question => réponse (et le problème, s'il est donné). */
function reponses_consultation(array $banque, array $tirees, array $valeurs): array
{
    $reponses = [];
    foreach ($tirees as $cle) {
        $reponses[$banque[$cle]['libelle']] = $valeurs[$cle];
    }
    if ($valeurs['probleme'] !== '') {
        $reponses['Son problème'] = $valeurs['probleme'];
    }
    return $reponses;
}
