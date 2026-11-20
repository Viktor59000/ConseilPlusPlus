<?php
/**
 * Actions : boutons et liens d'action. Une seule paire de boutons sur tout le site (principal plein / secondaire
 * au trait, même hauteur, même libellé) ; un seul bouton principal par vue (l'action du moment : déposer, se connecter).
 * Un bouton = un verbe et un objet, sans flèche. La flèche reste une option, pour le rare lien qui mène vraiment à une
 * suite (« Épisode suivant »). Survol : le bouton monte de 1 px ; clic : il s'enfonce (css/v2/composants.css).
 */

/**
 * Bouton (lien <a> si $href est donné, sinon <button>).
 *   bouton('Déposer ma demande', 'guichet.php')
 *   bouton('Envoyer', null, ['type' => 'submit', 'variante' => 'principal'])
 *   bouton('Annuler', 'compte.php', ['variante' => 'secondaire'])
 * Options :
 *   variante   'principal' (défaut, plein, encre) | 'secondaire' (contour)
 *   fleche     bool, flèche après le libellé (défaut : false ; seulement pour un lien qui mène à une suite)
 *   taille     'normale' (défaut) | 'compacte' (même hauteur de 48 px, marges plus étroites : barres d'outils)
 *   large      bool, toute la largeur
 *   type       'submit' | 'button' (défaut 'submit' pour un <button>)
 *   nom, valeur  name / value d'un <button>
 *   icone_html SVG placé avant le libellé (déjà construit, aria-hidden)
 *   classe     classes CSS en plus
 *   attributs  autres attributs (tableau, voir ui_attributs())
 */
function bouton(string $libelle, ?string $href = null, array $o = []): string
{
    $variante = ($o['variante'] ?? 'principal') === 'secondaire' ? 'secondaire' : 'principal';
    $fleche = !empty($o['fleche']);
    $classes = ui_classes('bouton', 'bouton--' . $variante, ($o['taille'] ?? '') === 'compacte' ? 'bouton--compact' : '',
        !empty($o['large']) ? 'bouton--large' : '', $o['classe'] ?? '');
    $interieur = ($o['icone_html'] ?? '') . '<span class="bouton__libelle">' . e($libelle) . '</span>' . ($fleche ? ui_fleche() : '');
    $attributs = $o['attributs'] ?? [];
    if ($href !== null) {
        return '<a class="' . $classes . '" href="' . e($href) . '"' . ui_attributs($attributs) . '>' . $interieur . '</a>';
    }
    $attributs = ['type' => $o['type'] ?? 'submit', 'name' => $o['nom'] ?? null, 'value' => $o['valeur'] ?? null] + $attributs;
    return '<button class="' . $classes . '"' . ui_attributs($attributs) . '>' . $interieur . '</button>';
}

/**
 * Lien d'action tertiaire (« Voir le dossier »), sans cadre, souligné d'un trait qui se trace au survol.
 * Le nom est historique : la flèche n'est plus mise que sur demande.
 * Options : fleche (bool, défaut false : un lien qui mène vraiment à une suite), classe, attributs.
 */
function lien_fleche(string $libelle, string $href, array $o = []): string
{
    return '<a class="' . ui_classes('lien-fleche', $o['classe'] ?? '') . '" href="' . e($href) . '"' . ui_attributs($o['attributs'] ?? []) . '>'
        . '<span>' . e($libelle) . '</span>' . (!empty($o['fleche']) ? ui_fleche() : '') . '</a>';
}
