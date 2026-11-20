<?php
/**
 * Les papiers du Guichet : tampon, statut, ticket, dossier (chemise kraft), attestation / verdict.
 * Ce sont les seuls éléments du site qui portent une ombre (une feuille posée sur le comptoir).
 * Le circuit d'un dossier (API, champ « statut ») : nouveau → importe (affiché « En instruction ») → repondu.
 */

/** Statuts d'un dossier : libellé affiché et phrase du suivi. Seule source des libellés (site, Mon dossier, Bureau). */
const STATUTS_DOSSIER = [
    'nouveau' => ['libelle' => 'Déposé', 'phrase' => 'Votre demande est au guichet. Elle attend son tour, comme tout le monde.'],
    'importe' => ['libelle' => 'En instruction', 'phrase' => 'Le cabinet a pris votre dossier. Il le lit, puis le relit.'],
    'repondu' => ['libelle' => 'Répondu', 'phrase' => 'Le verdict est rendu et tamponné. Il est sans appel, mais pas sans intérêt.'],
];

/** Libellé affiché d'un statut (« importe » → « En instruction ») ; un statut inconnu est affiché tel quel. */
function statut_libelle(string $statut): string
{
    return STATUTS_DOSSIER[$statut]['libelle'] ?? $statut;
}

/**
 * Étiquette de statut (petit cadre mono avec un carré de couleur) : statut('importe') → « En instruction ».
 * Couleurs : nouveau laiton, importe encre, repondu vert, refuse rouge. Option : libelle (remplace le libellé).
 */
function statut(string $statut, array $o = []): string
{
    $cle = preg_replace('/[^a-z]/', '', strtolower($statut));
    return '<span class="statut statut--' . e($cle) . '">' . e($o['libelle'] ?? statut_libelle($statut)) . '</span>';
}

/**
 * Tampon encreur (texte en capitales mono, cadre double, encre qui a légèrement bavé).
 *   tampon('Reçu')
 *   tampon('Conforme', ['couleur' => 'vert', 'angle' => 4, 'mention' => '20 nov. 2026'])
 *   tampon('Reçu', ['mouvement' => 'en-attente', 'id' => 'tampon-recu'])  → Guichet.tamponner(element) le fait s'abattre
 * Options :
 *   couleur    'rouge' (défaut) | 'vert' | 'encre' | 'laiton'
 *   angle      inclinaison en degrés (défaut -6)
 *   taille     'petit' | 'normal' (défaut) | 'grand' | 'geant' (l'objet-héros d'une section)
 *   mention    petite ligne sous le texte (date, service)
 *   mouvement  'aucun' (défaut, déjà posé) | 'au-chargement' (s'abat une fois la page affichée)
 *              | 'en-attente' (invisible jusqu'à Guichet.tamponner() ; visible sans JS et en régime allégé)
 *   decoratif  bool : masqué aux lecteurs d'écran (quand le texte voisin dit déjà la même chose)
 *   id, classe
 * Le filtre d'encre #encre-tampon est fourni par page_debut() : un tampon hors du gabarit s'affiche sans lui.
 */
function tampon(string $texte, array $o = []): string
{
    $couleur = in_array($o['couleur'] ?? 'rouge', ['rouge', 'vert', 'encre', 'laiton'], true) ? ($o['couleur'] ?? 'rouge') : 'rouge';
    $taille = in_array($o['taille'] ?? '', ['petit', 'grand', 'geant'], true) ? 'tampon--' . $o['taille'] : '';
    $mouvement = in_array($o['mouvement'] ?? 'aucun', ['au-chargement', 'en-attente'], true) ? $o['mouvement'] : null;
    $attributs = [
        'id' => $o['id'] ?? null,
        'style' => '--angle: ' . round((float) ($o['angle'] ?? -6), 1) . 'deg',
        'data-tampon' => $mouvement,
        'aria-hidden' => !empty($o['decoratif']) ? 'true' : null,
    ];
    $mention = isset($o['mention']) ? '<span class="tampon__mention">' . e($o['mention']) . '</span>' : '';
    return '<span class="' . ui_classes('tampon', $couleur !== 'rouge' ? 'tampon--' . $couleur : '', $taille, $o['classe'] ?? '') . '"'
        . ui_attributs($attributs) . '><span class="tampon__texte">' . e($texte) . '</span>' . $mention . '</span>';
}

/**
 * Ticket de file d'attente (papier découpé, numéro géant en mono).
 *   ticket(47, ['guichet' => '3 bis', 'date' => '20/11/2026 · 10:42', 'attente' => 'Attente estimée : moyenne'])
 * $numero : entier (affiché sur 3 chiffres : 047) ou texte tel quel.
 * Options : guichet (défaut '1'), libelle (défaut 'Votre numéro'), date, attente, tampon_html (un tampon() posé dessus),
 *           classe.
 */
function ticket(int|string $numero, array $o = []): string
{
    $numero = is_int($numero) ? sprintf('%03d', $numero) : $numero;
    $guichet = (string) ($o['guichet'] ?? '1');
    $pied = '';
    foreach (['date', 'attente'] as $cle) {
        if (!empty($o[$cle])) {
            $pied .= '<span>' . e($o[$cle]) . '</span>';
        }
    }
    return '<div class="' . ui_classes('ticket', $o['classe'] ?? '') . '" role="group" aria-label="' . e('Ticket n° ' . $numero . ', guichet ' . $guichet) . '">'
        . '<div class="ticket__papier">'
        . '<p class="ticket__entete"><span>ConseilPlus+</span><span>Guichet ' . e($guichet) . '</span></p>'
        . '<p class="ticket__libelle">' . e($o['libelle'] ?? 'Votre numéro') . '</p>'
        . '<p class="ticket__numero">' . e($numero) . '</p>'
        . ($pied !== '' ? '<p class="ticket__pied">' . $pied . '</p>' : '')
        . '</div>' . ($o['tampon_html'] ?? '') . '</div>';
}

/**
 * Dossier : chemise cartonnée kraft avec onglet (n° de dossier), titre, statut, et une feuille de papier dedans.
 *   dossier('2026-0147', 'importe', '<p>' . e($message) . '</p>', ['titre' => 'Mon voisin parle à ses tomates'])
 * $contenu_html : contenu de la feuille, DÉJÀ ÉCHAPPÉ (paragraphes, attestation(), etapes_statut()…).
 * Options :
 *   titre      titre du dossier (défaut : « Dossier n° … »)
 *   niveau     niveau du titre, 2 à 4 (défaut 3)
 *   meta       tableau libellé => valeur, affiché sous la feuille en mono (« Déposé le » => « 20/11/2026 »)
 *   tampon_html  un tampon() posé sur la feuille
 *   id, classe
 */
function dossier(string $numero, string $statut, string $contenu_html, array $o = []): string
{
    $id = $o['id'] ?? ui_id('dossier');
    $niveau = max(2, min(4, (int) ($o['niveau'] ?? 3)));
    $meta = '';
    foreach ($o['meta'] ?? [] as $libelle => $valeur) {
        $meta .= '<span>' . e($libelle) . ' : ' . e((string) $valeur) . '</span>';
    }
    return '<article class="' . ui_classes('dossier', $o['classe'] ?? '') . '" id="' . e($id) . '" aria-labelledby="' . e($id) . '-titre">'
        . '<p class="dossier__onglet">Dossier n° ' . e($numero) . '</p>'
        . '<div class="dossier__chemise">'
        . '<div class="dossier__entete"><h' . $niveau . ' class="dossier__titre" id="' . e($id) . '-titre">' . e($o['titre'] ?? 'Dossier n° ' . $numero) . '</h' . $niveau . '>'
        . statut($statut) . '</div>'
        . '<div class="dossier__feuille">' . $contenu_html . ($o['tampon_html'] ?? '') . '</div>'
        . ($meta !== '' ? '<p class="dossier__meta">' . $meta . '</p>' : '')
        . '</div></article>';
}

/**
 * Attestation ou verdict : document officiel sur papier, cadre double (encre et laiton), champs remplis à la machine,
 * paraphe et tampon. C'est le document que le visiteur emporte.
 *   attestation([
 *     'titre' => 'Attestation de dépôt', 'reference' => 'ATT-2026-0147',
 *     'corps' => 'Le cabinet certifie avoir reçu une demande. Il ne certifie rien d\'autre.',
 *     'champs' => ['Déposant' => 'M. Dupont', 'Objet' => 'Tomates bavardes'],
 *     'signataire' => 'Bertrand', 'fonction' => 'Agent d\'accueil',
 *     'tampon' => ['texte' => 'Reçu', 'mention' => '20 nov. 2026', 'mouvement' => 'au-chargement'],
 *   ])
 * Options :
 *   type        'attestation' (défaut) | 'verdict' (titre au stylo rouge)
 *   titre       obligatoire en pratique (défaut « Attestation »)
 *   niveau      niveau du titre, 1 à 3 (défaut 2)
 *   reference   n° imprimé en haut à droite ; service (défaut « Service des attestations »)
 *   corps       texte (échappé) ; ou corps_html (déjà construit)
 *   champs      tableau libellé => valeur (échappés), présentés comme un formulaire rempli
 *   signataire, fonction, paraphe (défaut : le signataire)
 *   tampon      options de tampon() avec la clé 'texte' (null : pas de tampon)
 *   id, classe
 */
function attestation(array $o): string
{
    $id = $o['id'] ?? ui_id('attestation');
    $niveau = max(1, min(3, (int) ($o['niveau'] ?? 2)));
    $type = ($o['type'] ?? '') === 'verdict' ? 'verdict' : 'attestation';
    $corps = isset($o['corps_html']) ? $o['corps_html'] : (isset($o['corps']) ? '<p>' . e($o['corps']) . '</p>' : '');
    $champs = '';
    foreach ($o['champs'] ?? [] as $libelle => $valeur) {
        $champs .= '<dt>' . e($libelle) . '</dt><dd>' . e((string) $valeur) . '</dd>';
    }
    $tampon = '';
    if (!empty($o['tampon']['texte'])) {
        $t = $o['tampon'];
        $tampon = tampon($t['texte'], $t + ['taille' => 'grand', 'angle' => -8]);
    }
    $signature = '';
    if (!empty($o['signataire'])) {
        $signature = '<div class="attestation__signature"><span class="attestation__paraphe" aria-hidden="true">' . e($o['paraphe'] ?? $o['signataire']) . '</span>'
            . '<span>' . e($o['signataire']) . (isset($o['fonction']) ? ', ' . e($o['fonction']) : '') . '</span></div>';
    }
    return '<article class="' . ui_classes('attestation', 'attestation--' . $type, $o['classe'] ?? '') . '" id="' . e($id) . '" aria-labelledby="' . e($id) . '-titre">'
        . '<header class="attestation__entete"><div class="attestation__emetteur">' . ui_logo($id . '-logo', true)
        . '<span>Cabinet ConseilPlus+<br>' . e($o['service'] ?? 'Service des attestations') . '</span></div>'
        . (isset($o['reference']) ? '<p class="attestation__ref">Réf. ' . e($o['reference']) . '</p>' : '') . '</header>'
        . '<h' . $niveau . ' class="attestation__titre" id="' . e($id) . '-titre">' . e($o['titre'] ?? 'Attestation') . '</h' . $niveau . '>'
        . ($corps !== '' ? '<div class="attestation__corps">' . $corps . '</div>' : '')
        . ($champs !== '' ? '<dl class="attestation__champs">' . $champs . '</dl>' : '')
        . '<footer class="attestation__pied">' . $signature . $tampon . '</footer>'
        . '</article>';
}
