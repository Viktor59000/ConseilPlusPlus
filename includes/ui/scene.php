<?php
/**
 * Scène : une gravure du cabinet (assets/scenes/) en <picture> réactive.
 * Fichiers attendus : assets/scenes/<nom>-<largeur>.webp (plusieurs largeurs, ex. 480, 800, 1200) et
 * assets/scenes/<nom>-800.jpg (repli). Les largeurs et le rapport sont lus sur le disque (pas de liste à tenir).
 * Style d'image du site : gravure à l'encre, palette papier journal, laiton et rouge (sources hors dépôt :
 * Documents/Cours/Semestre5/images-sources/). Le texte alternatif porte l'humour : il décrit, sérieusement.
 * Régime allégé : js/v2/guichet.js réduit les images pas encore chargées à leur plus petite largeur.
 */

/** Largeurs WebP disponibles d'une scène et taille du repli : ['largeurs' => [480, 800, …], 'l' => 800, 'h' => 1000]. */
function scene_fichiers(string $nom): array
{
    static $cache = [];
    if (isset($cache[$nom])) {
        return $cache[$nom];
    }
    $dossier = __DIR__ . '/../../assets/scenes/';
    $largeurs = [];
    foreach (glob($dossier . $nom . '-*.webp') ?: [] as $fichier) {
        if (preg_match('/-(\d+)\.webp$/', $fichier, $m)) {
            $largeurs[] = (int) $m[1];
        }
    }
    sort($largeurs);
    $taille = is_file($dossier . $nom . '-800.jpg') ? getimagesize($dossier . $nom . '-800.jpg') : false;
    return $cache[$nom] = ['largeurs' => $largeurs, 'l' => $taille[0] ?? 800, 'h' => $taille[1] ?? 800];
}

/**
 * Gravure en <picture> : source WebP (srcset), repli JPEG, width/height (pas de décalage au chargement), alt.
 *   scene('cabinet', 'Le bureau du cabinet : quatre-vingt-quatre tiroirs, une seule lampe allumée.')
 *   scene('methode-1', '', ['tailles' => '(min-width: 1024px) 40vw, 100vw'])   ('' = décorative)
 * Options :
 *   tailles     attribut sizes (défaut « (min-width: 1024px) 50vw, 100vw »)
 *   chargement  'lazy' (défaut) | 'eager' ; prioritaire : bool (eager + fetchpriority="high", pour l'image du héros)
 *   classe      classes en plus sur <picture> (« scene--libre » : sans cadre ni fond)
 */
function scene(string $nom, string $alt, array $o = []): string
{
    $f = scene_fichiers($nom);
    $chemin = ui_racine() . 'assets/scenes/' . rawurlencode($nom);
    $srcset = implode(', ', array_map(fn ($l) => $chemin . '-' . $l . '.webp ' . $l . 'w', $f['largeurs']));
    $tailles = $o['tailles'] ?? '(min-width: 1024px) 50vw, 100vw';
    $prioritaire = !empty($o['prioritaire']);
    $img = ['src' => $chemin . '-800.jpg', 'width' => $f['l'], 'height' => $f['h'], 'alt' => $alt,
        'loading' => $prioritaire ? 'eager' : ($o['chargement'] ?? 'lazy'), 'decoding' => 'async',
        'fetchpriority' => $prioritaire ? 'high' : null, 'data-scene' => true];
    return '<picture class="' . ui_classes('scene', $o['classe'] ?? '') . '">'
        . ($srcset !== '' ? '<source type="image/webp" srcset="' . e($srcset) . '" sizes="' . e($tailles) . '">' : '')
        . '<img' . ui_attributs($img) . '></picture>';
}
