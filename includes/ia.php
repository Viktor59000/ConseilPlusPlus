<?php
/**
 * Conseiller IA : envoie les réponses du questionnaire à l'API gratuite de Groq
 * (format compatible OpenAI) et renvoie un conseil volontairement tordu.
 * Sans clé, ou si l'API ne répond pas, le générateur local prend le relais.
 *
 * Pour empêcher l'IA d'être trop logique, on brouille le dossier avant de l'envoyer :
 * réponses mélangées et rattachées aux mauvaises questions, ingrédient imposé sans rapport,
 * style imposé au hasard.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/tirage.php';

const GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';

const CONSIGNE_IA = <<<TXT
Tu es « le conseiller moyen » du cabinet ConseilPlus+, un faux cabinet de conseil.
On te transmet le dossier d'un client. Le dossier a été mal classé : les réponses ne correspondent pas aux questions, et c'est très bien comme ça.

Rédige UN conseil complètement absurde, façon Kamoulox, en français, avec vouvoiement : deux phrases courtes, 45 mots maximum.
Règles :
- Enchaîne des actions qui n'ont strictement aucun rapport entre elles (« faites ceci, puis faites cela »).
- Utilise les éléments du dossier à contre-emploi : un nombre devient un objet, une couleur devient une personne, un meuble devient un sentiment.
- Les liens de cause à effet sont faux, assumés et inventés pour l'occasion (jamais la même excuse). Ne justifie jamais rien, n'explique jamais, ne cherche aucune harmonie, aucun équilibre, aucune énergie.
- Termine par une conséquence qui n'a rien à voir avec le reste.
- Ton : un fonctionnaire très sérieux qui récite un règlement, sans jamais rire.
- Interdits : récapituler le dossier, commencer par « Conformément », les mots « harmonie », « équilibre », « énergie », « optimiser ».
- Inoffensif : rien de dangereux, de médical, de financier, d'illégal ni de vexant.
Réponds uniquement par le conseil, en texte brut, sans titre, sans liste, sans markdown.

Exemples du ton attendu (ne pas les recopier) :
« Classez vos dix-sept verts par ordre de taille, puis offrez votre tartine à la préfecture. Votre perceuse sera prévenue par courrier. »
« Le jaune moutarde ayant refusé de signer, remplacez votre soupe par une chaise pliante jusqu'à jeudi. Les pigeons apprécieront le geste. »
TXT;

// Style imposé, tiré au hasard à chaque consultation
const STYLES_IA = [
    'comme une notice de montage de meuble',
    'comme un horoscope du jour',
    "comme le message d'un répondeur téléphonique",
    "comme un article de loi inventé, avec son numéro",
    'comme une annonce en gare',
    'comme une recette de cuisine',
    'comme un bulletin météo',
    "comme une consigne de sécurité d'avion",
    'comme une prophétie de voyante de foire',
    'comme le compte rendu d\'une réunion de copropriété',
    'comme un SMS de rappel de rendez-vous',
    'comme une petite annonce',
];

/**
 * Brouille le dossier : les réponses sont redistribuées au hasard sur les questions.
 */
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
        $ingredient = tirer_conseil($bdd)['action'];
        $style = STYLES_IA[array_rand(STYLES_IA)];
        $demande = "Dossier du client (mal classé) :\n" . brouiller_dossier($reponses)
            . "\nIngrédient imposé, à glisser tel quel ou presque : « $ingredient ».\n"
            . "Style imposé : rédige le conseil $style.";

        // 3 tentatives : le modèle renvoie parfois une réponse vide ou coupée après avoir trop « réfléchi »
        for ($essai = 1; $essai <= 3; $essai++) {
            $resultat = appeler_api('POST', GROQ_URL, [
                'model' => GROQ_MODEL,
                'messages' => [
                    ['role' => 'system', 'content' => CONSIGNE_IA],
                    ['role' => 'user', 'content' => $demande],
                ],
                'temperature' => 1.2,
                'max_tokens' => 800,
                // gpt-oss « réfléchit » avant de répondre : réflexion courte et non renvoyée
                'reasoning_effort' => 'low',
                'include_reasoning' => false,
            ], ['Authorization: Bearer ' . GROQ_API_KEY], 15);

            $choix = $resultat['data']['choices'][0] ?? [];
            $texte = trim($choix['message']['content'] ?? '');
            // Réponse acceptée seulement si le modèle a terminé normalement (pas de phrase coupée)
            if ($resultat['code'] === 200 && $texte !== '' && ($choix['finish_reason'] ?? '') === 'stop') {
                return ['conseil' => raccourcir($texte), 'source' => 'ia'];
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
