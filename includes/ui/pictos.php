<?php
/**
 * Pictogrammes maison au trait fin (24 × 24, trait 1,5, couleur du texte) pour la navigation du site et la barre
 * latérale du Bureau. Dessinés pour le cabinet, aucun emprunt à une bibliothèque d'icônes.
 *   picto('guichet')                      décoratif (aria-hidden), à côté d'un libellé visible
 *   picto('tampon', ['titre' => 'Tamponner'])   seul : porte son nom (role="img")
 * Noms : guichet, chemise, tampon, plume, telephone, casier, sonnette.
 */

/** Tracés des pictogrammes (attribut d de <path>, plusieurs sous-tracés possibles). */
const PICTOS = [
    // Le guichet : une vitre à hygiaphone au-dessus d'un comptoir
    'guichet' => 'M4 20V5.5A1.5 1.5 0 0 1 5.5 4h13A1.5 1.5 0 0 1 20 5.5V20M2.5 20h19M4 14h16M9 14v-3a3 3 0 0 1 6 0v3M10.5 8.2h3',
    // La chemise : un dossier cartonné à onglet, une feuille qui dépasse
    'chemise' => 'M3 7V19a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1h-8.5L9.8 5.6A1 1 0 0 0 9 5.2H4a1 1 0 0 0-1 1V7M6.5 8V3.8h9.5V8',
    // Le tampon : poignée, manche, semelle, et l'empreinte
    'tampon' => 'M9.5 9.5a2.5 2.5 0 1 1 5 0c0 1.1-.6 1.6-.9 2.5h-3.2c-.3-.9-.9-1.4-.9-2.5ZM10.4 12v2M13.6 12v2M5.5 14h13v2.5h-13ZM4 20h16',
    // La plume : un bec fendu et sa ligne d'écriture
    'plume' => 'M19.5 4.5c-6 .5-10 4.5-11.5 10.5l-1.5 4.5 4.5-1.5c6-1.5 10-5.5 10.5-11.5ZM8 15l-2 2M11.5 12.5l2-2M3.5 20.5h8',
    // Le téléphone du standard : combiné posé sur sa base à cadran
    'telephone' => 'M4 8.5c0-2 3.5-3.5 8-3.5s8 1.5 8 3.5v1.2a.8.8 0 0 1-.8.8h-2.4a.8.8 0 0 1-.8-.8V8.4M4 8.5v1.2a.8.8 0 0 0 .8.8h2.4a.8.8 0 0 0 .8-.8V8.4M7.5 12.5h9l2.5 7h-14ZM12 17.2a1.6 1.6 0 1 0 0-3.2 1.6 1.6 0 0 0 0 3.2Z',
    // Le casier : trois rangées de tiroirs à poignée
    'casier' => 'M4 3.5h16v17H4ZM4 9.2h16M4 14.8h16M10.5 6.4h3M10.5 12h3M10.5 17.6h3',
    // La sonnette du hall : dôme, bouton, socle
    'sonnette' => 'M12 6.5V5M10.5 5h3M5 16.5a7 7 0 0 1 14 0ZM3.5 16.5h17v2h-17Z',
];

function picto(string $nom, array $o = []): string
{
    $d = PICTOS[$nom] ?? PICTOS['chemise'];
    $titre = $o['titre'] ?? null;
    $acces = $titre !== null ? ' role="img" aria-label="' . e($titre) . '"' : ' aria-hidden="true"';
    return '<svg class="' . ui_classes('picto', $o['classe'] ?? '') . '" viewBox="0 0 24 24"' . $acces . ' focusable="false">'
        . '<path d="' . $d . '"/></svg>';
}
