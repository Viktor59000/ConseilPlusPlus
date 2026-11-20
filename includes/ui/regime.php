<?php
/**
 * « Le Guichet adapté » : le choix du régime de traitement, présenté comme le formulaire CERFA n° 0-A.
 * Trois régimes (attribut data-regime sur <html>, jetons dans css/v2/jetons.css) :
 *   ordinaire    le guichet tel qu'il est ;
 *   allege       mouvement réduit (un tampon se pose en fondu), images légères ;
 *   prioritaire  contraste renforcé, texte agrandi.
 * Sans choix, le régime suit l'appareil (prefers-contrast, prefers-reduced-motion) : script de includes/theme.php.
 * Le choix est gardé dans le navigateur (localStorage) par js/v2/guichet.js, qui coche aussi la bonne case.
 * Sans JavaScript, le formulaire reste caché (il ne pourrait rien faire) : les réglages du système s'appliquent.
 */

/** Les trois régimes : valeur => [nom, détail]. */
const REGIMES = [
    'ordinaire' => ['Ordinaire', 'Le guichet tel qu\'il est, tampons compris.'],
    'allege' => ['Allégé', 'Mouvement réduit, images légères. Le tampon se pose sans bruit.'],
    'prioritaire' => ['Prioritaire', 'Contraste renforcé, texte agrandi. On vous passe devant.'],
];

/**
 * Formulaire CERFA 0-A (trois boutons radio). $id : préfixe unique dans la page (« pied », « entete »).
 * Le formulaire ne s'envoie jamais : js/v2/guichet.js applique le choix dès qu'une case change.
 */
function regime_formulaire(string $id): string
{
    $html = '<form class="cerfa" data-regime-formulaire hidden aria-labelledby="regime-' . e($id) . '-titre">'
        . '<fieldset><legend id="regime-' . e($id) . '-titre"><span class="cerfa__numero">Cerfa n° 0-A</span> Le Guichet adapté</legend>'
        . '<p class="cerfa__notice">Cochez le régime de traitement souhaité. Effet immédiat, sans pièce justificative.</p>'
        . '<div class="cerfa__cases">';
    foreach (REGIMES as $valeur => [$nom, $detail]) {
        $champ = 'regime-' . $id . '-' . $valeur;
        $html .= '<label class="cerfa__case" for="' . e($champ) . '">'
            . '<input type="radio" id="' . e($champ) . '" name="regime-' . e($id) . '" value="' . e($valeur) . '" aria-describedby="' . e($champ) . '-detail">'
            . '<span class="cerfa__nom">' . e($nom) . '</span>'
            . '<span class="cerfa__detail" id="' . e($champ) . '-detail">' . e($detail) . '</span></label>';
    }
    return $html . '</div></fieldset></form>';
}

/** Réglage du régime dans l'en-tête : un bouton-volet (details) qui ouvre le même formulaire. Caché sans JavaScript. */
function regime_volet(): string
{
    return '<details class="regime-volet" data-regime-volet hidden>'
        . '<summary class="outil" title="Le Guichet adapté"><span class="visuellement-cache">Le Guichet adapté : régime de traitement</span>'
        . '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">'
        . '<path d="M4 7h9M17 7h3M4 17h3M11 17h9"/><circle cx="15" cy="7" r="2.2"/><circle cx="9" cy="17" r="2.2"/></svg></summary>'
        . '<div class="regime-volet__panneau">' . regime_formulaire('entete') . '</div></details>';
}
