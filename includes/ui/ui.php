<?php
/**
 * Composants d'interface du nouveau site « Le Guichet » (refonte 2026-11) : point d'entrée unique.
 *   require_once __DIR__ . '/includes/ui/ui.php';   (page_debut() le charge déjà : includes/gabarit.php)
 *
 * Chaque composant est une fonction PHP pure qui RENVOIE du HTML (rien n'est affiché directement) :
 *   <?= bouton('Déposer ma demande', 'guichet.php') ?>
 * Tout texte passé en paramètre est échappé, SAUF les paramètres nommés « …_html » (contenu déjà construit, par
 * exemple le résultat d'un autre composant) : à la page de les échapper avec e().
 * Styles : css/v2/composants.css. Mouvement (tampon qui s'abat, phrase qui s'encre) : js/v2/guichet.js.
 * Le catalogue complet, avec les signatures, est dans design-system/conseilplus/REFONTE-2026-11.md (section « Système »)
 * et se voit dans tests/galerie.php.
 *
 *   actions.php     bouton(), lien_fleche()
 *   papiers.php     tampon(), statut(), ticket(), dossier(), attestation()
 *   blocs.php       carte(), bloc_alerte(), etapes_statut()
 *   champs.php      champ()
 *   scene.php       scene()
 *   graphiques.php  graphique_barres(), graphique_anneau()
 *   regime.php      regime_formulaire(), regime_volet()
 *   pictos.php      picto() : pictogrammes maison au trait (guichet, chemise, tampon, plume, telephone, casier, sonnette)
 *   ui.php          machine() : libellé d'interface en mono capitales entre crochets (« [ MON DOSSIER ] »)
 */
require_once __DIR__ . '/../formulaire.php'; // e()

/**
 * Préfixe des adresses du site depuis la page courante ('' à la racine, '../' depuis tests/).
 * page_debut() le règle ; les composants qui pointent vers un fichier (scène, logo) le lisent.
 */
function ui_racine(?string $nouvelle = null): string
{
    static $racine = '';
    if ($nouvelle !== null) {
        $racine = $nouvelle;
    }
    return $racine;
}

/** Identifiant HTML unique dans la page, à partir d'un préfixe (« carte-1 », « carte-2 »…). */
function ui_id(string $prefixe): string
{
    static $compteurs = [];
    $compteurs[$prefixe] = ($compteurs[$prefixe] ?? 0) + 1;
    return $prefixe . '-' . $compteurs[$prefixe];
}

/**
 * Attributs HTML échappés à partir d'un tableau : ['data-x' => 'a', 'hidden' => true, 'title' => null]
 * → ' data-x="a" hidden'. true = attribut booléen, null / false = absent.
 */
function ui_attributs(array $attributs): string
{
    $html = '';
    foreach ($attributs as $nom => $valeur) {
        if ($valeur === null || $valeur === false) {
            continue;
        }
        $html .= $valeur === true ? ' ' . e((string) $nom) : ' ' . e((string) $nom) . '="' . e((string) $valeur) . '"';
    }
    return $html;
}

/** Classes CSS : liste filtrée (les valeurs vides sont retirées) → « bouton bouton--principal ». */
function ui_classes(string ...$classes): string
{
    return e(implode(' ', array_filter($classes)));
}

/** Flèche dessinée des boutons et liens (une seule flèche sur tout le site). */
function ui_fleche(): string
{
    return '<svg class="bouton__fleche" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M2.5 8h10.5M9 4l4 4-4 4"/></svg>';
}

/**
 * Libellé « machine » : mono, capitales, entre crochets dessinés par CSS (« [ N° 047 ] »), comme les numéros et
 * références imprimés par le guichet. Les crochets ne sont pas lus par les lecteurs d'écran.
 */
function machine(string $texte, array $o = []): string
{
    return '<span class="' . ui_classes('machine', $o['classe'] ?? '') . '">' . e($texte) . '</span>';
}

/**
 * Logo « La note » (includes/logo.php) en SVG intégré, couleurs du thème (encre + boucle rouge).
 * $id : identifiant unique du masque (un par logo dans la page). $decoratif : masqué aux lecteurs d'écran
 * (quand un texte voisin nomme déjà le cabinet).
 */
function ui_logo(string $id, bool $decoratif = false): string
{
    $logo_id = $id;
    ob_start();
    include __DIR__ . '/../logo.php';
    $svg = trim((string) ob_get_clean());
    return $decoratif ? str_replace('<svg class="logo-svg" viewBox="0 0 984 256" role="img"', '<svg class="logo-svg" viewBox="0 0 984 256" aria-hidden="true"', $svg) : $svg;
}

require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/papiers.php';
require_once __DIR__ . '/blocs.php';
require_once __DIR__ . '/champs.php';
require_once __DIR__ . '/scene.php';
require_once __DIR__ . '/graphiques.php';
require_once __DIR__ . '/regime.php';
require_once __DIR__ . '/pictos.php';
