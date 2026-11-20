<?php
/**
 * Graphiques maison, dessinés en SVG côté serveur (aucune bibliothèque, aucun script), partagés par le site et Le Bureau.
 * Chaque graphique : <figure> avec son titre visible, un SVG role="img" avec <title> (le résumé en une phrase),
 * et un tableau de repli visuellement caché (les valeurs exactes pour les lecteurs d'écran).
 * Les textes (libellés, valeurs) restent en HTML à côté du SVG : ils gardent leur taille sur téléphone.
 * Couleurs : séries .serie-1 à .serie-5 (encre, rouge, vert, laiton, kraft), contrastes ≥ 3:1 dans les deux thèmes.
 *
 * Données : liste de lignes ['libelle' => 'lun.', 'valeur' => 12, 'serie' => 2 (facultatif)]
 *           ou tableau simple libellé => valeur.
 */

/** Normalise les données d'un graphique en liste de ['libelle', 'valeur', 'serie']. */
function graphique_lignes(array $donnees): array
{
    $lignes = [];
    $i = 0;
    foreach ($donnees as $cle => $ligne) {
        $i++;
        $lignes[] = is_array($ligne)
            ? ['libelle' => (string) ($ligne['libelle'] ?? $cle), 'valeur' => (float) ($ligne['valeur'] ?? 0), 'serie' => (int) ($ligne['serie'] ?? 0)]
            : ['libelle' => (string) $cle, 'valeur' => (float) $ligne, 'serie' => 0];
    }
    return $lignes;
}

/** Nombre affiché à la française (12 ; 3,5 ; 1 204). */
function graphique_nombre(float $n): string
{
    return number_format($n, floor($n) == $n ? 0 : 1, ',', "\u{202F}");
}

/** Tableau de repli (visuellement caché) commun aux graphiques. */
function graphique_tableau(string $titre, array $lignes, string $entete, string $unite): string
{
    $html = '<table class="visuellement-cache"><caption>' . e($titre) . '</caption><thead><tr><th scope="col">' . e($entete) . '</th>'
        . '<th scope="col">' . e($unite !== '' ? ucfirst($unite) : 'Valeur') . '</th></tr></thead><tbody>';
    foreach ($lignes as $l) {
        $html .= '<tr><th scope="row">' . e($l['libelle']) . '</th><td>' . e(graphique_nombre($l['valeur'])) . '</td></tr>';
    }
    return $html . '</tbody></table>';
}

/**
 * Barres verticales (débit du guichet, demandes par jour…).
 *   graphique_barres(['lun.' => 4, 'mar.' => 12, 'mer.' => 7], ['titre' => 'Débit du guichet', 'unite' => 'demandes'])
 * Options :
 *   titre (obligatoire), sous_titre (gris, à la suite du titre)
 *   unite        unité des valeurs (« demandes »), pour le résumé et le tableau
 *   entete       en-tête de la colonne des libellés du tableau (défaut « Période »)
 *   max          valeur du haut de l'échelle (défaut : la plus grande valeur)
 *   accent       index (0…) de la barre mise en avant (série 2, rouge) ; sinon chaque ligne garde sa série (défaut 1)
 *   hauteur      hauteur du tracé en px (défaut 200)
 *   resume       phrase du <title> (défaut : « Maximum : 12 demandes (mar.). »)
 *   id, classe
 */
function graphique_barres(array $donnees, array $o): string
{
    $lignes = graphique_lignes($donnees);
    $n = max(1, count($lignes));
    $titre = (string) ($o['titre'] ?? 'Graphique');
    $unite = (string) ($o['unite'] ?? '');
    $valeurs = array_column($lignes, 'valeur') ?: [0];
    $max = (float) ($o['max'] ?? max($valeurs)) ?: 1.0;
    $id = $o['id'] ?? ui_id('graphique');
    $plus = $lignes ? $lignes[array_search(max($valeurs), $valeurs)] : null;
    $resume = $o['resume'] ?? ($plus ? 'Maximum : ' . graphique_nombre($plus['valeur']) . ($unite !== '' ? ' ' . $unite : '') . ' (' . $plus['libelle'] . ').' : 'Aucune donnée.');

    // Tracé dans un repère 100 × 100 étiré à la taille du cadre (les traits gardent 1 px : vector-effect)
    $largeur_barre = min(18, 62 / $n);
    $svg = '<svg class="graphique__barres" viewBox="0 0 100 100" preserveAspectRatio="none" role="img" aria-labelledby="' . e($id) . '-svg">'
        . '<title id="' . e($id) . '-svg">' . e($titre . ' : ' . $resume) . '</title><g class="graphique__grille">';
    foreach ([25, 50, 75] as $y) {
        $svg .= '<line x1="0" x2="100" y1="' . $y . '" y2="' . $y . '"/>';
    }
    $svg .= '</g>';
    $axe = '';
    foreach ($lignes as $i => $l) {
        $h = max(0, min(100, 100 * $l['valeur'] / $max));
        $x = (100 / $n) * ($i + .5) - $largeur_barre / 2;
        $serie = isset($o['accent']) ? ($i === (int) $o['accent'] ? 2 : 1) : ($l['serie'] ?: 1);
        $svg .= '<rect class="serie-' . $serie . '" x="' . round($x, 3) . '" y="' . round(100 - $h, 3) . '" width="' . round($largeur_barre, 3) . '" height="' . round($h, 3) . '"/>';
        $axe .= '<span><b>' . e(graphique_nombre($l['valeur'])) . '</b>' . e($l['libelle']) . '</span>';
    }
    $svg .= '<line class="graphique__base" x1="0" x2="100" y1="100" y2="100"/></svg>';

    return '<figure class="' . ui_classes('graphique', $o['classe'] ?? '') . '" id="' . e($id) . '" style="--hauteur: ' . (int) ($o['hauteur'] ?? 200) . 'px">'
        . '<figcaption class="graphique__titre">' . e($titre) . (isset($o['sous_titre']) ? ' <span class="muet">' . e($o['sous_titre']) . '</span>' : '') . '</figcaption>'
        . '<div class="graphique__zone">' . $svg . '<div class="graphique__axe" style="--n: ' . $n . '" aria-hidden="true">' . $axe . '</div></div>'
        . graphique_tableau($titre, $lignes, (string) ($o['entete'] ?? 'Période'), $unite)
        . '</figure>';
}

/**
 * Anneau (répartition : dossiers par statut…), total au centre et légende chiffrée.
 *   graphique_anneau([
 *     ['libelle' => 'Déposés', 'valeur' => 6, 'serie' => 4],
 *     ['libelle' => 'En instruction', 'valeur' => 3, 'serie' => 1],
 *     ['libelle' => 'Répondus', 'valeur' => 11, 'serie' => 3],
 *   ], ['titre' => 'Dossiers par statut', 'total' => 'dossiers'])
 * Options : titre (obligatoire), sous_titre, total (libellé sous le total, défaut « au total »), unite, entete
 *           (défaut « Catégorie »), resume, id, classe.
 */
function graphique_anneau(array $donnees, array $o): string
{
    $lignes = graphique_lignes($donnees);
    $titre = (string) ($o['titre'] ?? 'Graphique');
    $somme = array_sum(array_column($lignes, 'valeur'));
    $id = $o['id'] ?? ui_id('graphique');
    $parts = [];
    foreach ($lignes as $l) {
        $parts[] = $l['libelle'] . ' ' . graphique_nombre($l['valeur']);
    }
    $resume = $o['resume'] ?? ('Total ' . graphique_nombre($somme) . ' : ' . implode(', ', $parts) . '.');

    // Cercles superposés : chaque part est un arc (stroke-dasharray sur une longueur de tracé de 100)
    $svg = '<svg viewBox="0 0 120 120" role="img" aria-labelledby="' . e($id) . '-svg"><title id="' . e($id) . '-svg">' . e($titre . ' : ' . $resume) . '</title>'
        . '<circle class="graphique__piste" cx="60" cy="60" r="50" pathLength="100"/>';
    $depart = 0.0;
    $legende = '';
    foreach ($lignes as $i => $l) {
        $serie = $l['serie'] ?: ($i % 5) + 1;
        $part = $somme > 0 ? 100 * $l['valeur'] / $somme : 0;
        if ($part > 0) {
            // Un petit jour de 0,8 entre deux parts (sauf s'il n'y en a qu'une)
            $trait = count(array_filter($lignes, fn ($x) => $x['valeur'] > 0)) > 1 ? max(0, $part - .8) : $part;
            $svg .= '<circle class="serie-' . $serie . '" cx="60" cy="60" r="50" pathLength="100" stroke-dasharray="' . round($trait, 3) . ' 100"'
                . ' stroke-dashoffset="' . round(-$depart, 3) . '" transform="rotate(-90 60 60)"/>';
        }
        $depart += $part;
        $legende .= '<li class="serie-' . $serie . '"><span>' . e($l['libelle']) . '</span><b>' . e(graphique_nombre($l['valeur'])) . '</b></li>';
    }
    $svg .= '<text class="graphique__total" x="60" y="62" text-anchor="middle">' . e(graphique_nombre($somme)) . '</text>'
        . '<text class="graphique__total-libelle" x="60" y="80" text-anchor="middle">' . e($o['total'] ?? 'au total') . '</text></svg>';

    return '<figure class="' . ui_classes('graphique', $o['classe'] ?? '') . '" id="' . e($id) . '">'
        . '<figcaption class="graphique__titre">' . e($titre) . (isset($o['sous_titre']) ? ' <span class="muet">' . e($o['sous_titre']) . '</span>' : '') . '</figcaption>'
        . '<div class="graphique__anneau">' . $svg . '<ul class="graphique__legende" aria-hidden="true">' . $legende . '</ul></div>'
        . graphique_tableau($titre, $lignes, (string) ($o['entete'] ?? 'Catégorie'), (string) ($o['unite'] ?? ''))
        . '</figure>';
}
