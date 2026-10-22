<?php
/**
 * « Questions pour un stagiaire » (stagiaire.php) : banque de questions et répliques (data/stagiaire.php).
 * La partie se joue dans le navigateur (js/stagiaire.js) ; sans JavaScript, la question du jour reste lisible.
 */
require_once __DIR__ . '/textes.php';

function stagiaire_banque(): array
{
    static $banque = null;
    return $banque ??= require __DIR__ . '/../data/stagiaire.php';
}

/** La question du jour (tirage par date) : [question, bonne réponse, réponse du stagiaire, argument]. */
function stagiaire_question_du_jour(?string $jour = null): array
{
    return tirage_du_jour(stagiaire_banque()['questions'], 'stagiaire', $jour);
}
