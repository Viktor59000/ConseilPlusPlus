<?php
/**
 * Gabarit commun du nouveau site « Le Guichet » (refonte 2026-11). Remplace, page par page, includes/header.php
 * et includes/footer.php (gardés tant que des pages n'ont pas migré).
 *
 *   require_once __DIR__ . '/includes/init.php';
 *   require_once __DIR__ . '/includes/gabarit.php';
 *   page_debut(['titre' => 'Le Hall', 'description' => '…', 'page' => 'hall']);
 *   ?> … sections de la page (dans <main>) … <?php
 *   page_fin();
 *
 * page_debut(array $meta) — clés :
 *   titre          titre de l'onglet (« Le Hall » → « Le Hall — ConseilPlus+ ») ; titre_complet : tel quel
 *   description    meta description (une phrase, propre à la page)
 *   page           entrée du menu en cours : hall | guichet | petit-moyen | personnel | reclamations | dossier
 *   racine         préfixe des adresses depuis la page ('' à la racine, '../' pour tests/) ; aussi base_href
 *                  (<base href>, pour une page servie à une autre adresse : erreur 404)
 *   canonical      adresse canonique (défaut : adresse publique + nom du script ; false : aucune)
 *   image          image d'aperçu des liens partagés (défaut assets/og-image.png, 1200 × 630), image_alt
 *   robots         contenu de meta robots (ex. « noindex »)
 *   css            feuilles en plus, chemins depuis la racine du dépôt (intégrées dans la couche « page »)
 *   scripts        scripts en plus, chemins depuis la racine (« js/v2/hall.js »), chargés en defer et versionnés
 *   bureau         true : intègre aussi css/v2/bureau.css
 *   html_attributs attributs de <html> écrits par le serveur (galerie : data-theme, data-regime forcés)
 *   classe_corps   classes de <body>
 *
 * Fournit : CSP (envoyer_csp()), CSS intégré et minifié (css/v2/*.css), police préchargée, script du thème et du
 * régime (avant affichage), lien d'évitement, en-tête (logo, menu, Mon dossier, thème, régime), filtre SVG d'encre
 * des tampons (#encre-tampon), <main id="contenu">, puis page_fin() : pied de page et scripts.
 */
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/theme.php';
require_once __DIR__ . '/textes.php';
require_once __DIR__ . '/url.php';
require_once __DIR__ . '/ui/ui.php';

/** Entrées du menu principal : clé de page => [adresse, libellé, pictogramme (includes/ui/pictos.php)]. */
const MENU_SITE = [
    'hall' => ['index.php', 'Le Hall', 'sonnette'],
    'guichet' => ['guichet.php', 'Le Guichet', 'guichet'],
    'petit-moyen' => ['petit-moyen.php', 'Le Petit Moyen', 'plume'],
    'personnel' => ['cabinet.php', 'Le Personnel', 'tampon'],
    'reclamations' => ['contact.php', 'Réclamations', 'telephone'],
];

/** Feuilles du nouveau site intégrées dans la page, minifiées, chemins des polices réécrits depuis la page. */
function css_v2(array $fichiers, string $racine): string
{
    $chemins = array_map(fn ($f) => __DIR__ . '/../' . $f, $fichiers);
    return str_replace('../../assets/', $racine . 'assets/', css_integre(...$chemins));
}

/** Filtre SVG de l'encre des tampons (bords rongés, petits manques), une fois par page. */
function filtre_encre(): string
{
    return '<svg class="visuellement-cache" aria-hidden="true" focusable="false" width="0" height="0"><defs>'
        . '<filter id="encre-tampon" x="-10%" y="-20%" width="120%" height="140%" color-interpolation-filters="sRGB">'
        . '<feTurbulence type="fractalNoise" baseFrequency=".75" numOctaves="2" seed="11" result="bruit"/>'
        . '<feDisplacementMap in="SourceGraphic" in2="bruit" scale="2" xChannelSelector="R" yChannelSelector="G" result="bord"/>'
        . '<feTurbulence type="fractalNoise" baseFrequency="1.9" numOctaves="1" seed="4" result="grain"/>'
        . '<feColorMatrix in="grain" type="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 -5 4.1" result="manques"/>'
        . '<feComposite in="bord" in2="manques" operator="in"/>'
        . '</filter></defs></svg>';
}

function page_debut(array $meta): void
{
    $racine = (string) ($meta['racine'] ?? '');
    ui_racine($racine);
    $GLOBALS['gabarit_meta'] = $meta;
    $titre = $meta['titre_complet'] ?? (($meta['titre'] ?? 'ConseilPlus+') . ' — ConseilPlus+');
    $description = $meta['description'] ?? 'ConseilPlus+, le conseiller moyen : une administration qui traite vos problèmes, à peu près.';
    $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $canonical = $meta['canonical'] ?? (url_publique() . '/' . ($script === 'index.php' ? '' : $script));
    $image = $meta['image'] ?? url_publique() . '/assets/og-image.png';
    $page = $meta['page'] ?? '';
    $connecte = isset($_SESSION['user_id']);
    $feuilles = ['css/v2/jetons.css', 'css/v2/socle.css', 'css/v2/composants.css', 'css/v2/site.css'];
    if (!empty($meta['bureau'])) {
        $feuilles[] = 'css/v2/bureau.css';
    }
    $page_css = '';
    foreach ($meta['css'] ?? [] as $f) {
        $page_css .= '@layer page{' . css_v2([$f], $racine) . '}';
    }
    $dossier = $connecte ? 'compte.php' : 'login.php';

    envoyer_csp();
    echo '<!DOCTYPE html>', "\n", '<html lang="fr"', ui_attributs($meta['html_attributs'] ?? []), '>', "\n<head>\n";
    echo '<meta charset="UTF-8">', "\n";
    if (!empty($meta['base_href'])) {
        echo '<base href="', e($meta['base_href']), '">', "\n";
    }
    echo '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">', "\n";
    echo '<title>', e($titre), "</title>\n";
    echo '<meta name="description" content="', e($description), '">', "\n";
    if (!empty($meta['robots'])) {
        echo '<meta name="robots" content="', e($meta['robots']), '">', "\n";
    }
    if ($canonical !== false) {
        echo '<link rel="canonical" href="', e($canonical), '">', "\n";
    }
    // Aperçu des liens partagés
    echo '<meta property="og:type" content="website"><meta property="og:site_name" content="ConseilPlus+"><meta property="og:locale" content="fr_FR">', "\n";
    echo '<meta property="og:title" content="', e($titre), '"><meta property="og:description" content="', e($description), '">', "\n";
    if ($canonical !== false) {
        echo '<meta property="og:url" content="', e($canonical), '">', "\n";
    }
    echo '<meta property="og:image" content="', e($image), '"><meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">', "\n";
    echo '<meta property="og:image:alt" content="', e($meta['image_alt'] ?? 'ConseilPlus+ : Des conseils. C\'est déjà ça.'), '"><meta name="twitter:card" content="summary_large_image">', "\n";
    echo '<link rel="icon" href="', e($racine), 'assets/icons/favicon.ico" sizes="48x48"><link rel="icon" href="', e($racine), 'assets/icons/favicon.svg" type="image/svg+xml">', "\n";
    echo '<link rel="apple-touch-icon" href="', e($racine), 'assets/icons/apple-touch-icon.png"><link rel="manifest" href="', e($racine), 'manifest.webmanifest">', "\n";
    echo '<meta name="theme-color" content="#E8E8E3" data-clair="#E8E8E3" data-sombre="#141412"><meta name="apple-mobile-web-app-title" content="ConseilPlus+">', "\n";
    if ($connecte) {
        echo '<meta name="csrf" content="', csrf_jeton(), '">', "\n";
    }
    echo theme_script(), "\n";
    echo '<link rel="preload" href="', e($racine), 'assets/fonts/schibsted-grotesk.woff2" as="font" type="font/woff2" crossorigin>', "\n";
    echo '<link rel="preload" href="', e($racine), 'assets/fonts/jetbrains-mono-500.woff2" as="font" type="font/woff2" crossorigin>', "\n";
    // Feuilles intégrées : un aller-retour réseau en moins avant le premier affichage
    echo '<style>', css_v2($feuilles, $racine), $page_css, "</style>\n</head>\n";
    echo '<body', ui_attributs(['class' => $meta['classe_corps'] ?? null]), ">\n";
    echo '<a class="evitement" href="#contenu">Aller au contenu</a>', "\n", filtre_encre(), "\n";

    // En-tête : logo à gauche, menu au centre, Mon dossier / thème / régime à droite
    echo '<header class="entete" data-entete><div class="conteneur entete__in">';
    echo '<a class="entete__logo" href="', e($racine), 'index.php"', $page === 'hall' ? ' aria-current="page"' : '', '>', ui_logo('logo-entete'), '</a>';
    echo '<nav class="entete__nav" id="menu-principal" aria-label="Navigation principale"><ul>';
    foreach (MENU_SITE as $cle => [$adresse, $libelle, $picto]) {
        echo '<li><a href="', e($racine . $adresse), '"', $page === $cle ? ' aria-current="page"' : '', '>', picto($picto), '<span>', e($libelle), '</span></a></li>';
    }
    echo '</ul></nav><div class="entete__outils">';
    echo bouton('Mon dossier', $racine . $dossier, ['variante' => 'secondaire', 'taille' => 'compacte', 'fleche' => false,
        'icone_html' => picto('chemise'), 'classe' => 'entete__dossier', 'attributs' => ['aria-current' => $page === 'dossier' ? 'page' : null]]);
    echo theme_bouton(), regime_volet();
    echo '<button class="outil menu-bouton" type="button" aria-expanded="false" aria-controls="menu-principal">'
        . '<span class="visuellement-cache">Menu</span><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h10"/></svg></button>';
    echo "</div></div></header>\n<main id=\"contenu\">\n";
}

function page_fin(): void
{
    $meta = $GLOBALS['gabarit_meta'] ?? [];
    $racine = (string) ($meta['racine'] ?? '');
    $colonnes = [
        'Services' => [['guichet.php', 'Le Guichet'], ['compte.php', 'Mon dossier'], ['petit-moyen.php', 'Le Petit Moyen'], ['messmerde.php', 'Messmerde'], ['produit.php', 'Nos formules']],
        'Le cabinet' => [['cabinet.php', 'Le Personnel'], ['contact.php', 'Réclamations'], ['standard.php', 'Le standard'], ['application.php', 'L\'application'], ['plan-du-site.php', 'Plan du site']],
        'Légal' => [['mentions-legales.php', 'Mentions légales'], ['confidentialite.php', 'Confidentialité'], ['accessibilite.php', 'Accessibilité']],
        'Développeurs' => [['developpeurs.php', 'L\'API'], ['api/openapi.php', 'Description OpenAPI'], ['api/conseils.php', 'Un conseil en JSON']],
    ];
    echo "</main>\n<footer class=\"pied\"><div class=\"conteneur\"><div class=\"pied__grille\">";
    // Colonne de la marque : logo, devise, et le régime « Guichet adapté » (CERFA 0-A)
    echo '<div class="pied__marque">', ui_logo('logo-pied'), '<p class="pied__devise">Des conseils. C\'est déjà ça.</p>',
        '<div class="pied__regime">', regime_formulaire('pied'),
        '<p class="pied__sans-js" data-regime-sans-js>Le Guichet adapté suit les réglages de votre appareil (mouvement réduit, contraste renforcé).</p></div></div>';
    foreach ($colonnes as $titre => $liens) {
        echo '<nav class="pied__colonne" aria-label="', e($titre), '"><h2 class="pied__titre">', e($titre), '</h2><ul>';
        foreach ($liens as [$adresse, $libelle]) {
            echo '<li><a href="', e($racine . $adresse), '">', e($libelle), '</a></li>';
        }
        echo '</ul></nav>';
    }
    echo '</div><div class="pied__legal"><p>ConseilPlus+ est une administration fictive : aucun conseil délivré ici ne doit être suivi. '
        . 'Hommage non officiel à l\'humour absurde.</p><p>© ', date('Y'), ' ConseilPlus+, le conseiller moyen · Polices Schibsted Grotesk et JetBrains Mono (licence OFL)</p></div>';
    echo "</div></footer>\n";
    echo '<script type="application/json" id="textes-site">', json_encode(['onglet' => textes()['onglet_absent']], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG), "</script>\n";
    $scripts = array_merge(['js/theme.js', 'js/v2/guichet.js'], $racine === '' ? ['js/pwa.js'] : [], $meta['scripts'] ?? []);
    foreach ($scripts as $s) {
        echo '<script src="', e(fichier_versionne($racine . $s)), '" defer></script>', "\n";
    }
    echo "</body>\n</html>\n";
}
