<?php
/**
 * Blocs : carte, alerte, suivi du dossier (étapes de statut, façon colis).
 */

/**
 * Carte (à plat, filet fin ; une seule forme de carte sur le site). Avec un lien, toute la carte est cliquable
 * (le lien du titre s'étend sur la carte) et elle monte de 2 px au survol, son image zoome à 1,02.
 *   carte(['titre' => 'Le Petit Moyen', 'texte' => 'Un mot par jour, à peu près défini.', 'href' => 'petit-moyen.php'])
 * Options :
 *   titre (obligatoire), texte (échappé) ou texte_html, href
 *   etiquette   petite référence mono au-dessus du titre (« Service 4 »)
 *   scene       ['nom' => 'methode-1', 'alt' => '…'] : gravure en haut de la carte (voir scene())
 *   pied_html   contenu du bas de carte (statut, lien fléché…) ; avec href et sans pied, une flèche est ajoutée
 *   niveau      niveau du titre, 2 à 4 (défaut 3)
 *   classe, attributs
 */
function carte(array $o): string
{
    $niveau = max(2, min(4, (int) ($o['niveau'] ?? 3)));
    $lien = isset($o['href']);
    $titre = $lien ? '<a href="' . e($o['href']) . '">' . e($o['titre'] ?? '') . '</a>' : e($o['titre'] ?? '');
    $image = '';
    if (!empty($o['scene']['nom'])) {
        $image = '<div class="carte__image">' . scene($o['scene']['nom'], $o['scene']['alt'] ?? '', ['classe' => 'scene--libre', 'tailles' => '(min-width: 1024px) 30vw, (min-width: 640px) 45vw, 100vw']) . '</div>';
    }
    $texte = $o['texte_html'] ?? (isset($o['texte']) ? e($o['texte']) : '');
    $pied = $o['pied_html'] ?? ($lien ? ui_fleche() : '');
    return '<article class="' . ui_classes('carte', $lien ? 'carte--lien' : '', $o['classe'] ?? '') . '"' . ui_attributs($o['attributs'] ?? []) . '>'
        . $image
        . (isset($o['etiquette']) ? '<p class="carte__etiquette">' . e($o['etiquette']) . '</p>' : '')
        . '<h' . $niveau . ' class="carte__titre">' . $titre . '</h' . $niveau . '>'
        . ($texte !== '' ? '<p class="carte__texte">' . $texte . '</p>' : '')
        . ($pied !== '' ? '<div class="carte__pied">' . $pied . '</div>' : '')
        . '</article>';
}

/**
 * Alerte (avis du guichet) : bandeau à filet de couleur. Une erreur est lue tout de suite (role="alert"),
 * les autres poliment (role="status").
 *   bloc_alerte('Votre demande est bien partie.', 'succes', ['titre' => 'Demande reçue'])
 * $type : 'info' (défaut) | 'succes' | 'attention' | 'erreur'.
 * Options : titre, reference (petit n° mono avant le titre, défaut selon le type : « Avis », « Erreur »…),
 *           texte_html (remplace $texte, déjà échappé), id, classe.
 * (Ne pas confondre avec alerte() de includes/formulaire.php, utilisée par les anciennes pages.)
 */
function bloc_alerte(string $texte, string $type = 'info', array $o = []): string
{
    $type = in_array($type, ['info', 'succes', 'attention', 'erreur'], true) ? $type : 'info';
    $references = ['info' => 'Avis', 'succes' => 'Accusé', 'attention' => 'Note', 'erreur' => 'Erreur'];
    $titre = isset($o['titre'])
        ? '<p class="alerte__titre"><span class="mono">' . e($o['reference'] ?? $references[$type]) . '</span><span>' . e($o['titre']) . '</span></p>'
        : '';
    return '<div class="' . ui_classes('alerte', 'alerte--' . $type, $o['classe'] ?? '') . '" role="' . ($type === 'erreur' ? 'alert' : 'status') . '"'
        . ui_attributs(['id' => $o['id'] ?? null]) . '>' . $titre . '<p>' . ($o['texte_html'] ?? e($texte)) . '</p></div>';
}

/**
 * Suivi du dossier, façon colis : Déposé → En instruction → Répondu (statuts de l'API : nouveau, importe, repondu).
 *   etapes_statut('importe', ['dates' => ['nouveau' => '20/11 · 10:42', 'importe' => '20/11 · 14:05']])
 * Les étapes avant $statut sont « franchies », $statut est « en cours » (aria-current="step"), les suivantes « à venir ».
 * Un dossier répondu a toutes ses étapes franchies.
 * Options :
 *   dates        statut => date affichée (texte libre)
 *   descriptions statut => phrase (défaut : STATUTS_DOSSIER)
 *   libelle      nom de la liste pour les lecteurs d'écran (défaut « Suivi du dossier »)
 *   classe
 */
function etapes_statut(string $statut, array $o = []): string
{
    $cles = array_keys(STATUTS_DOSSIER);
    $rang = array_search($statut, $cles, true);
    $rang = $rang === false ? 0 : $rang;
    $coche = '<svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M3 8.5l3.2 3.2L13 5"/></svg>';
    $html = '<ol class="' . ui_classes('suivi', $o['classe'] ?? '') . '" style="--n: ' . count($cles) . '" aria-label="' . e($o['libelle'] ?? 'Suivi du dossier') . '">';
    foreach ($cles as $i => $cle) {
        $etat = ($i < $rang || ($statut === 'repondu' && $i === $rang)) ? 'fait' : ($i === $rang ? 'encours' : 'avenir');
        $dit = ['fait' => 'étape franchie', 'encours' => 'étape en cours', 'avenir' => 'à venir'][$etat];
        $html .= '<li class="suivi__etape suivi__etape--' . $etat . '"' . ($etat === 'encours' || ($statut === 'repondu' && $i === $rang) ? ' aria-current="step"' : '') . '>'
            . '<span class="suivi__repere">' . ($etat === 'fait' ? $coche : '') . '</span>'
            . '<span class="suivi__nom">' . e(STATUTS_DOSSIER[$cle]['libelle']) . ' <span class="visuellement-cache">(' . $dit . ')</span></span>'
            . (isset($o['dates'][$cle]) ? '<span class="suivi__date">' . e($o['dates'][$cle]) . '</span>' : '')
            . '<span class="suivi__desc">' . e($o['descriptions'][$cle] ?? STATUTS_DOSSIER[$cle]['phrase']) . '</span>'
            . '</li>';
    }
    return $html . '</ol>';
}
