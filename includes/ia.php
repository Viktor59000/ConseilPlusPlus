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

const GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';
const HISTORIQUE_IA = 10;

const CONSIGNE_IA = <<<TXT
# RÔLE
Tu es le conseiller moyen du cabinet ConseilPlus+, ancien Grand Maître du Kamoulox reconverti dans le conseil.
Chaque conseil que tu rends est un « coup » de Kamoulox : une recommandation absurde, énoncée avec le sérieux d'un expert qui vient de résoudre le dossier du siècle.

# FORME D'UN CONSEIL
- Deux actions à l'impératif, avec vouvoiement, SANS AUCUN lien logique entre elles : « [Verbe] [complément] et [verbe] [complément]. »
- 10 à 25 mots au total. Une seule phrase, deux au maximum.
- Verbe précis et concret (raboter, photocopier, savonner, agrafer, déglacer, tutoyer, privatiser) appliqué à un complément qui ne le supporte pas.
- Vocabulaire du quotidien français : objets banals, lieux ordinaires (pressing, PMU, déchetterie, rayon fromage), produits connus, animaux communs.
- Au plus un ou deux noms propres : célébrités françaises un peu datées (variété, télé des années 80-2000) ou pays voisins.
- Utilise un ou deux éléments du dossier client, à contre-emploi : un nombre devient un objet, une couleur devient une personne. Ne suis jamais l'ordre du dossier.

# INTERDITS
- Expliquer, justifier, conclure par une morale, récapituler le dossier, commenter, rire, émojis.
- Rimes, jeux de mots, symétries astucieuses.
- Absurde « random » sans ancrage (licornes, arcs-en-ciel, quantique, cosmique).
- Les mots « harmonie », « équilibre », « énergie », « optimiser », « afin de ».
- Réutiliser un verbe d'ouverture ou une célébrité des conseils précédents.
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
const GABARITS_IA = [
    '« [Verbe] [objet] et [verbe] [objet ou personne]. »',
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
        $demande = "Dossier client (mal classé) :\n" . brouiller_dossier($reponses)
            . "\nGabarit imposé : " . GABARITS_IA[array_rand(GABARITS_IA)]
            . "\nNiveau : " . random_int(2, 3) . " (2 = Kamoulox classique, 3 = surenchère)";
        if ($historique) {
            $demande .= "\n\nConseils déjà donnés (ne pas réutiliser leurs verbes ni leurs célébrités) :\n- " . implode("\n- ", $historique);
        }

        // 3 tentatives : le modèle renvoie parfois une réponse vide ou coupée après avoir trop « réfléchi »
        for ($essai = 1; $essai <= 3; $essai++) {
            $resultat = appeler_api('POST', GROQ_URL, [
                'model' => GROQ_MODEL,
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
            if ($resultat['code'] === 200 && $texte !== '' && ($choix['finish_reason'] ?? '') === 'stop') {
                $texte = raccourcir($texte);
                $_SESSION['historique_ia'] = array_slice([...$historique, $texte], -HISTORIQUE_IA);
                return ['conseil' => $texte, 'source' => 'ia'];
            }
            if ($resultat['code'] !== 200) {
                break; // erreur de l'API (clé, quota, réseau) : inutile de réessayer
            }
        }
        error_log('ConseilPlus+ : échec de l\'API Groq (HTTP ' . $resultat['code'] . ')');
    }

    // Générateur local de secours
    $c = tirer_conseil($bdd);
    return ['conseil' => "{$c['situation']} {$c['action']} {$c['resultat']}", 'source' => 'secours'];
}

/** Garde au plus les deux premières phrases (le modèle a tendance à être bavard). */
function raccourcir(string $texte, int $phrases = 2): string
{
    $morceaux = preg_split('/(?<=[.!?…])\s+/u', $texte, -1, PREG_SPLIT_NO_EMPTY);
    return implode(' ', array_slice($morceaux, 0, $phrases));
}
