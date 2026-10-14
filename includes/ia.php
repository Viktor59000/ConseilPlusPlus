<?php
/**
 * Conseiller IA : envoie le dossier du questionnaire à l'API gratuite de Groq
 * (format compatible OpenAI) et renvoie un conseil Kamoulox.
 * Sans clé, ou si l'API ne répond pas, le générateur local prend le relais.
 *
 * Prompt adapté du document « Générateur de phrases Kamoulox — Act-like » :
 * gabarit imposé au hasard, niveau 2 ou 3, historique des derniers conseils pour éviter
 * les répétitions, dossier brouillé pour que l'IA ne suive pas le formulaire.
 * Hommage non officiel au Kamoulox de Kad Merad et Olivier Baroux.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/tirage.php';
require_once __DIR__ . '/kamoulox.php';

const GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';
const HISTORIQUE_IA = 10;
// Modèle de repli si le quota gratuit du modèle principal est dépassé (HTTP 429)
const MODELE_REPLI = 'openai/gpt-oss-20b';

const CONSIGNE_IA = <<<TXT
# RÔLE
Tu es le conseiller moyen du cabinet ConseilPlus+, ancien Grand Maître du Kamoulox reconverti dans le conseil.
Chaque conseil que tu rends est un « coup » de Kamoulox : une recommandation absurde, énoncée avec le sérieux d'un expert qui vient de résoudre le dossier du siècle.

# FORME D'UN CONSEIL
- Deux actions à l'impératif, avec vouvoiement, SANS AUCUN lien logique entre elles : « [Verbe] [complément] et [verbe] [complément]. »
- 10 à 25 mots au total. Une seule phrase, deux au maximum.
- Verbe précis et concret (raboter, photocopier, savonner, agrafer, déglacer, tutoyer, privatiser) appliqué à un complément qui ne le supporte pas.
- Vocabulaire du quotidien français d'hier et d'aujourd'hui : objets banals, lieux ordinaires (pressing, PMU, déchetterie, rayon fromage, drive, point relais, open space), produits connus (minitel, Safrane, Mamie Nova, airfryer, Thermomix, trottinette électrique, Vinted, Doctolib), animaux communs.
- Au plus un ou deux noms propres : une célébrité prise UNIQUEMENT dans la liste « Célébrités autorisées » fournie avec le dossier (aucune autre, jamais), ou un pays voisin.
- Utilise un ou deux éléments du dossier client, à contre-emploi : un nombre devient un objet, une couleur devient une personne. Ne suis jamais l'ordre du dossier.

# INTERDITS
- Expliquer, justifier, conclure par une morale, récapituler le dossier, commenter, rire, émojis.
- Rimes, jeux de mots, symétries astucieuses.
- Absurde « random » sans ancrage (licornes, arcs-en-ciel, quantique, cosmique).
- Les mots « harmonie », « équilibre », « énergie », « optimiser », « afin de ».
- Réutiliser un verbe d'ouverture ou une célébrité des conseils précédents.
- Commencer par « Inventez », sauf si le gabarit imposé le demande.
- Les verbes à l'infinitif : toujours l'impératif (« Rabotez », jamais « Raboter »).
- Contenu sexuel, violent, haineux, ou visant une origine, une religion, un handicap, une orientation. Les célébrités ne subissent que des actions inoffensives (tutoyer, saluer, faxer, ressembler à).

# EXEMPLES DE TON (à ne pas recopier)
- Poncez une endive et vouvoyez le Luxembourg.
- Agrafez un ragondin et repassez les rideaux de Patrick Sabatier.
- Farcissez un rond-point et réclamez votre minitel en case loutre finlandaise.
- Privatisez un pluviomètre et tutoyez le facteur de Jean-Pierre Foucault.
- Le coefficient de friture recommande de vernir la moustache d'un douanier andorran.

# SORTIE
Uniquement le conseil, en texte brut. Aucun titre, aucune explication, aucune question.
TXT;

// Gabarits du document, adaptés au conseil (un est imposé au hasard à chaque consultation)
// Le coup de grandeur (« Inventez… et déclarez la guerre à… ») est volontairement rare : l'IA en abuse.
const GABARITS_IA = [
    '« [Verbe] [objet] et [verbe] [objet ou personne]. »',
    '« [Verbe] [objet] [lieu du quotidien] et [verbe] [objet ou personne]. »',
    '« [Verbe] [personne ordinaire] et [verbe] [produit connu]. »',
    '« [Verbe] [objet] et [verbe inoffensif] [célébrité un peu datée]. »',
    '« [Verbe] [objet] et habillez-vous comme [célébrité]. »',
    '« [Concept savant inventé] recommande de [verbe] [détail ridicule]. »',
    '« Inventez [objet impossible] et déclarez la guerre à [pays voisin]. »',
    '« [Verbe] [objet] et [verbe] [objet] en case [animal] [nationalité]. »',
];

/** Brouille le dossier : les réponses sont redistribuées au hasard sur les questions. */
function brouiller_dossier(array $reponses): string
{
    $questions = array_keys($reponses);
    $valeurs = array_values($reponses);
    shuffle($questions);
    shuffle($valeurs);

    $dossier = '';
    foreach ($questions as $i => $question) {
        $dossier .= "- $question : {$valeurs[$i]}\n";
    }
    return $dossier;
}

/**
 * Retourne ['conseil' => texte, 'source' => 'ia' | 'secours'].
 */
function demander_conseil_ia(array $reponses, PDO $bdd): array
{
    if (GROQ_API_KEY !== '') {
        $historique = $_SESSION['historique_ia'] ?? [];
        $celebrites = (array) array_rand(array_flip(kamoulox_banques()['celebrites']), 6);
        $demande = "Dossier client (mal classé) :\n" . brouiller_dossier($reponses)
            . "\nCélébrités autorisées (si tu en cites une) : " . implode(', ', $celebrites)
            . "\nGabarit imposé : " . GABARITS_IA[array_rand(GABARITS_IA)]
            . "\nNiveau : " . random_int(2, 3) . " (2 = Kamoulox classique, 3 = surenchère)";
        if ($historique) {
            $demande .= "\n\nConseils déjà donnés (ne pas réutiliser leurs verbes ni leurs célébrités) :\n- " . implode("\n- ", $historique);
        }

        // Mots interdits que le modèle laisse parfois passer : la réponse est alors redemandée
        $interdits = '/(quantique|cosmique|licorne|arc-en-ciel|harmonie|équilibre|énergie|optimis)/iu';

        // 3 tentatives : le modèle renvoie parfois une réponse vide ou coupée après avoir trop « réfléchi »
        $modele = GROQ_MODEL;
        for ($essai = 1; $essai <= 3; $essai++) {
            $resultat = appeler_api('POST', GROQ_URL, [
                'model' => $modele,
                'messages' => [
                    ['role' => 'system', 'content' => CONSIGNE_IA],
                    ['role' => 'user', 'content' => $demande],
                ],
                'temperature' => 1.0,
                'max_tokens' => 800,
                // gpt-oss « réfléchit » avant de répondre : réflexion courte et non renvoyée
                'reasoning_effort' => 'low',
                'include_reasoning' => false,
            ], ['Authorization: Bearer ' . GROQ_API_KEY], 15);

            $choix = $resultat['data']['choices'][0] ?? [];
            $texte = trim($choix['message']['content'] ?? '', " \n\r\t\"«»");
            // Réponse acceptée seulement si le modèle a terminé normalement (pas de phrase coupée)
            $valide = $texte !== '' && ($choix['finish_reason'] ?? '') === 'stop'
                && !preg_match($interdits, $texte)
                && !preg_match('/^\p{L}+(er|ir)\b/u', $texte) // pas d'infinitif en ouverture (« Raboter » au lieu de « Rabotez »)
                && noms_propres_autorises($texte);
            if ($resultat['code'] === 200 && $valide) {
                $texte = raccourcir($texte);
                $_SESSION['historique_ia'] = array_slice([...$historique, $texte], -HISTORIQUE_IA);
                return ['conseil' => $texte, 'source' => 'ia'];
            }
            if ($resultat['code'] === 429 && $modele !== MODELE_REPLI) {
                $modele = MODELE_REPLI; // quota dépassé : on bascule sur le modèle de repli
                continue;
            }
            if ($resultat['code'] !== 200) {
                break; // autre erreur de l'API (clé, réseau) : inutile de réessayer
            }
        }
        error_log('ConseilPlus+ : échec de l\'API Groq (HTTP ' . $resultat['code'] . ')');
    }

    // Générateur local de secours
    $c = tirer_conseil($bdd);
    return ['conseil' => "{$c['situation']} {$c['action']} {$c['resultat']}", 'source' => 'secours'];
}

/**
 * Vérifie que chaque nom propre de type « Prénom Nom » du texte figure dans nos banques
 * (célébrités choisies pour leur côté inoffensif, produits, pays…). Sinon, la réponse est rejetée.
 */
function noms_propres_autorises(string $texte): bool
{
    static $autorises = null;
    if ($autorises === null) {
        $tout = '';
        $banques = kamoulox_banques();
        array_walk_recursive($banques, function ($mot) use (&$tout) { $tout .= " | $mot"; });
        preg_match_all('/\p{Lu}[\p{Ll}\'-]+(?:[ -]\p{Lu}[\p{Ll}\'-]+)+/u', $tout, $m);
        $autorises = array_flip($m[0]);
    }
    preg_match_all('/\p{Lu}[\p{Ll}\'-]+(?:[ -]\p{Lu}[\p{Ll}\'-]+)+/u', $texte, $m);
    foreach ($m[0] as $nom) {
        // En début de phrase, le verbe a lui aussi une majuscule (« Tutoyez Michel Drucker ») : on l'ignore
        $sans_premier_mot = preg_replace('/^\S+[ -]/u', '', $nom);
        if (!isset($autorises[$nom]) && !isset($autorises[$sans_premier_mot])) {
            return false;
        }
    }
    return true;
}

/** Garde au plus les deux premières phrases (le modèle a tendance à être bavard). */
function raccourcir(string $texte, int $phrases = 2): string
{
    $morceaux = preg_split('/(?<=[.!?…])\s+/u', $texte, -1, PREG_SPLIT_NO_EMPTY);
    return implode(' ', array_slice($morceaux, 0, $phrases));
}
