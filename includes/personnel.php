<?php
/**
 * Le Personnel (cabinet.php) : l'équipe de data/textes.php (« equipe ») complétée par data/personnel.php
 * (n° de poste, étape du dossier, texte alternatif du portrait), et le circuit d'un dossier.
 */
require_once __DIR__ . '/textes.php';

/** Données de la page : ['membres' => [membre + poste, etape, alt], 'circuit' => [cle => étape]]. */
function personnel(): array
{
    static $donnees = null;
    if ($donnees === null) {
        $ajouts = require __DIR__ . '/../data/personnel.php';
        $membres = [];
        foreach (textes()['equipe'] as $m) {
            $membres[] = $m + ($ajouts['membres'][$m['cle']] ?? ['poste' => '00', 'etape' => null, 'alt' => '']);
        }
        usort($membres, fn ($a, $b) => strcmp($a['poste'], $b['poste']));
        $donnees = ['membres' => $membres, 'circuit' => $ajouts['circuit']];
    }
    return $donnees;
}

/** Les membres qui tiennent une étape du circuit (« instruction » → Mireille, le stagiaire). */
function membres_de_l_etape(string $etape): array
{
    return array_values(array_filter(personnel()['membres'], fn ($m) => $m['etape'] === $etape));
}

/**
 * Portrait d'un membre (assets/equipe/<cle>.webp, 640 × 640) ; sans fichier, un monogramme au tampon
 * (le portrait de Bertrand est attendu : assets/equipe/accueil.webp le remplacera sans rien changer d'autre).
 */
function portrait_membre(array $m, string $racine = '', string $tailles = '(min-width: 1024px) 22vw, (min-width: 640px) 45vw, 100vw'): string
{
    $fichier = 'assets/equipe/' . $m['cle'] . '.webp';
    if (is_file(__DIR__ . '/../' . $fichier)) {
        $petit = 'assets/equipe/' . $m['cle'] . '-360.webp';
        $srcset = is_file(__DIR__ . '/../' . $petit) ? $racine . $petit . ' 360w, ' . $racine . $fichier . ' 640w' : null;
        return '<img class="portrait__image"' . ui_attributs(['src' => $racine . $fichier, 'srcset' => $srcset, 'sizes' => $srcset ? $tailles : null,
            'alt' => $m['alt'] ?? '', 'width' => 640, 'height' => 640, 'loading' => 'lazy', 'decoding' => 'async']) . '>';
    }
    $nom = preg_replace('/^(Le |La )/u', '', $m['nom']);
    return '<span class="portrait__monogramme" role="img" aria-label="' . e($m['alt'] ?? '') . '">'
        . '<span aria-hidden="true">' . e(mb_strtoupper(mb_substr($nom, 0, 1))) . '</span>'
        . tampon('Portrait à venir', ['taille' => 'petit', 'angle' => -8, 'couleur' => 'encre', 'decoratif' => true]) . '</span>';
}
