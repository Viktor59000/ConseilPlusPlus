<?php
/**
 * Le Guichet (refonte 2026-11) : les guichets numérotés (les épreuves), le bandeau qui les relie, la plaque de
 * guichet, les étapes du suivi d'un rendez-vous et le ticket de la file d'attente.
 * Pages : guichet.php (prise de rendez-vous + liste des guichets), diagnostic.php, consultation.php, stagiaire.php,
 * duel.php, attestation.php (chacune garde son adresse et sa logique), compte.php (« Mon dossier »).
 * Règles métier des rendez-vous : includes/inscriptions.php (seulement appelé d'ici).
 */
require_once __DIR__ . '/ui/ui.php';
require_once __DIR__ . '/inscriptions.php';

/**
 * Les guichets du cabinet : clé => numéro, adresse, nom, détail (une phrase), connexion exigée, document délivré
 * à la fin (clé de data/attestations.php, lien attestation.php?document=…). Seule liste : guichet.php, le bandeau.
 */
const GUICHETS = [
    'diagnostic' => ['numero' => '1', 'adresse' => 'diagnostic.php', 'nom' => 'Le diagnostic',
        'detail' => 'Sept questions établies par le conseiller moyen. Le résultat est garanti : vous êtes moyen. Reste à savoir lequel.',
        'connexion' => false, 'document' => 'client-moyen'],
    'consultation' => ['numero' => '2', 'adresse' => 'consultation.php', 'nom' => 'La consultation',
        'detail' => 'Un questionnaire sans rapport avec votre situation, lu par l\'intelligence artificielle moyenne, qui rend un verdict.',
        'connexion' => true, 'document' => 'conseil-non-suivi'],
    'stagiaire' => ['numero' => '3', 'adresse' => 'stagiaire.php', 'nom' => 'Questions pour un stagiaire',
        'detail' => 'Dix questions simples. Le stagiaire buzze le premier et répond faux, avec aplomb. Vous êtes le jury.',
        'connexion' => false, 'document' => 'patience'],
    'duel' => ['numero' => '4', 'adresse' => 'duel.php', 'nom' => 'Le Duel',
        'detail' => 'Cent points contre le cabinet, à coups de phrases absurdes, notées selon un barème que personne n\'a lu.',
        'connexion' => true, 'document' => 'avis-favorable'],
    'documents' => ['numero' => '5', 'adresse' => 'attestation.php', 'nom' => 'Service des documents officiels',
        'detail' => 'Le formulaire du jour, remis à Bertrand, puis l\'attestation de votre choix, tamponnée par le doyen.',
        'connexion' => false, 'document' => null],
];

/**
 * Étapes du suivi d'un rendez-vous (« Mon dossier »), au format de etapes_statut() : statut de l'API => libellé, phrase.
 * Un rendez-vous annulé suit son propre parcours, plus court (ETAPES_RENDEZ_VOUS_ANNULE).
 */
const ETAPES_RENDEZ_VOUS = [
    'nouveau' => ['libelle' => 'Déposé', 'phrase' => 'Votre demande de rendez-vous est au guichet. Elle attend la prochaine levée.'],
    'importe' => ['libelle' => 'En instruction', 'phrase' => 'Le Bureau a relevé votre demande. Bertrand vérifie que le créneau existe toujours.'],
    'confirme' => ['libelle' => 'Confirmé', 'phrase' => 'Le cabinet vous attend au guichet, à la minute près. Il ne sera pas en avance.'],
];
const ETAPES_RENDEZ_VOUS_ANNULE = [
    'nouveau' => ['libelle' => 'Déposé', 'phrase' => 'La demande a bien été reçue au guichet.'],
    'annule' => ['libelle' => 'Annulé', 'phrase' => 'Le rendez-vous est annulé. Le créneau est rendu au guichet, qui le reprête volontiers.'],
];

/** Un guichet par sa clé (voir GUICHETS). */
function guichet(string $cle): array
{
    return GUICHETS[$cle];
}

/**
 * Bandeau des guichets, en haut de chaque épreuve : les cinq guichets en plaques numérotées, celui de la page en cours
 * marqué (aria-current). Le Guichet lui-même ouvre la rangée. $actif : clé de GUICHETS.
 */
function bandeau_guichets(string $actif): string
{
    $racine = ui_racine();
    $html = '<nav class="bandeau-guichets" aria-label="Les guichets"><div class="conteneur"><ul>'
        . '<li><a class="bandeau-guichets__retour" href="' . e($racine) . 'guichet.php">' . picto('guichet') . '<span>Le Guichet</span></a></li>';
    foreach (GUICHETS as $cle => $g) {
        $html .= '<li><a href="' . e($racine . $g['adresse']) . '"' . ($cle === $actif ? ' aria-current="page"' : '') . '>'
            . '<span class="bandeau-guichets__numero" aria-hidden="true">' . e($g['numero']) . '</span>'
            . '<span><span class="visuellement-cache">Guichet n° ' . e($g['numero']) . ' : </span>' . e($g['nom']) . '</span></a></li>';
    }
    return $html . '</ul></div></nav>';
}

/**
 * Plaque de guichet en laiton (« GUICHET » et le numéro en très grand) : l'objet-héros d'une épreuve.
 * Décorative : la référence du héros dit déjà « Guichet n° … ».
 */
function plaque_guichet(string $numero, string $mention = ''): string
{
    return '<div class="plaque" aria-hidden="true"><span class="plaque__mot">Guichet</span>'
        . '<span class="plaque__numero">' . e($numero) . '</span>'
        . ($mention !== '' ? '<span class="plaque__mention">' . e($mention) . '</span>' : '') . '</div>';
}

/**
 * La file d'attente du guichet, lue dans les rendez-vous : le prochain numéro (celui que recevra le prochain dépôt)
 * et le nombre de demandes déposées pas encore relevées par Le Bureau. ['numero' => 148, 'attente' => 3]
 */
function file_du_guichet(PDO $bdd): array
{
    $ligne = $bdd->query("SELECT COALESCE(MAX(id), 0) + 1 AS numero, COALESCE(SUM(statut = 'nouveau'), 0) AS attente FROM inscriptions")
        ->fetch(PDO::FETCH_ASSOC);
    return ['numero' => (int) $ligne['numero'], 'attente' => (int) $ligne['attente']];
}

/** Numéro de dossier affiché d'un rendez-vous : « RDV-2026-0147 » (année du dépôt, identifiant sur 4 chiffres). */
function numero_rendez_vous(array $inscription): string
{
    return 'RDV-' . substr((string) $inscription['date_creation'], 0, 4) . '-' . sprintf('%04d', (int) $inscription['id']);
}

/** Libellé court d'un jour proposé au guichet : « lundi 23 novembre » (liste des jours du formulaire). */
function libelle_jour_court(string $jour): string
{
    return (new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Europe/Paris', null, 'EEEE d MMMM'))
        ->format(new DateTime($jour));
}

/**
 * Le conseil du jour (Hall, Mon dossier) : une fiche officielle (attestation()) qui porte les crochets attendus par
 * js/conseil.js (un autre conseil sans recharger), js/partage.js et js/carnet.js : .fiche-conseil, [data-conseil],
 * [data-dossier], data-tampons, .tampon. $conseil : tirer_conseil() (includes/tirage.php).
 * Options : id (défaut « conseil-du-jour »), titre, niveau, service, actions_html (boutons sous la fiche).
 */
function fiche_conseil(array $conseil, array $o = []): string
{
    require_once __DIR__ . '/textes.php';
    $corps = '<p class="fiche-conseil" aria-live="polite">'
        . '<span data-conseil="situation">' . e($conseil['situation']) . '</span> '
        . '<mark data-conseil="action">' . e($conseil['action']) . '</mark> '
        . '<span data-conseil="resultat">' . e($conseil['resultat']) . '</span></p>';
    return attestation([
        'id' => $o['id'] ?? 'conseil-du-jour',
        'classe' => 'fiche-du-jour',
        'titre' => $o['titre'] ?? 'Recommandation officielle',
        'niveau' => $o['niveau'] ?? 3,
        'service' => $o['service'] ?? 'Le conseiller moyen',
        'reference_html' => 'dossier n° <span data-conseil="dossier" data-dossier>' . e($conseil['dossier']) . '</span>',
        'corps_html' => $corps,
        'signataire' => 'Le conseiller moyen', 'fonction' => 'fondateur', 'paraphe' => 'C. M.',
        'tampon' => ['texte' => texte_hasard('tampons'), 'angle' => -9, 'taille' => 'normal', 'decoratif' => true],
        'attributs' => ['data-tampons' => json_encode(textes()['tampons'], JSON_UNESCAPED_UNICODE)],
        'apres_html' => isset($o['actions_html']) ? '<div class="boutons fiche-du-jour__actions">' . $o['actions_html'] . '</div>' : '',
    ]);
}

/** « 14 h 12 » à partir de « 14:12 ». */
function libelle_horaire(string $horaire): string
{
    return str_replace(':', ' h ', substr($horaire, 0, 5));
}
