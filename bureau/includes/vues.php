<?php
/**
 * Le Bureau — petites aides d'affichage partagées par les pages.
 */

/**
 * Barres horizontales : une par ligne, libellé $lignes[i][$cle], valeur $lignes[i][$valeur].
 * Largeur relative à $base (par défaut la plus grande valeur, qui fait alors 100 %).
 */
function barres(array $lignes, string $cle, string $valeur = 'appels', ?int $base = null): string
{
    $base = $base ?: (max(array_column($lignes, $valeur) ?: [1]) ?: 1);
    $html = '<ul class="barres">';
    foreach ($lignes as $l) {
        $html .= '<li><span class="barres-libelle">' . htmlspecialchars((string) $l[$cle]) . ' <span class="mono">(' . (int) $l[$valeur] . ')</span></span>'
            . '<span class="barres-fond"><span class="barres-plein" style="width: ' . round(100 * $l[$valeur] / $base) . '%"></span></span></li>';
    }
    return $html . '</ul>';
}
