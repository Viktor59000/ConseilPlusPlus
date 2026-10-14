<?php
/**
 * Conseiller IA : envoie les réponses du questionnaire à l'API gratuite de Groq
 * (format compatible OpenAI) et renvoie un conseil volontairement tordu.
 * Sans clé, ou si l'API ne répond pas, le générateur local prend le relais.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/tirage.php';

const GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';

const CONSIGNE_IA = <<<TXT
Tu es « le conseiller moyen » du cabinet ConseilPlus+, un faux cabinet de conseil.
On te transmet les réponses d'un client à un questionnaire qui ne sert à rien.
Rédige UN conseil absurde et tordu, en français, avec vouvoiement : deux phrases courtes, 50 mots maximum.
Ton : administratif et très sûr de toi, comme un fonctionnaire qui récite un règlement.
Mélange plusieurs réponses du client qui n'ont aucun rapport entre elles, avec une conclusion inattendue.
Ne récapitule pas les réponses : donne directement le conseil. Varie ta première phrase, ne commence pas par « Conformément ».
Le conseil doit rester inoffensif : rien de dangereux, de médical, de financier, d'illégal ni de vexant.
Réponds uniquement par le conseil, en texte brut, sans titre, sans liste, sans markdown.
TXT;

/**
 * Retourne ['conseil' => texte, 'source' => 'ia' | 'secours'].
 */
function demander_conseil_ia(array $reponses, PDO $bdd): array
{
    if (GROQ_API_KEY !== '') {
        $description = '';
        foreach ($reponses as $question => $reponse) {
            $description .= "- $question : $reponse\n";
        }

        // 2 tentatives : le modèle renvoie parfois une réponse vide après avoir trop « réfléchi »
        for ($essai = 1; $essai <= 2; $essai++) {
            $resultat = appeler_api('POST', GROQ_URL, [
                'model' => GROQ_MODEL,
                'messages' => [
                    ['role' => 'system', 'content' => CONSIGNE_IA],
                    ['role' => 'user', 'content' => "Réponses du client :\n$description"],
                ],
                'temperature' => 1.0,
                'max_tokens' => 800,
                // gpt-oss « réfléchit » avant de répondre : réflexion courte et non renvoyée
                'reasoning_effort' => 'low',
                'include_reasoning' => false,
            ], ['Authorization: Bearer ' . GROQ_API_KEY], 15);

            $texte = trim($resultat['data']['choices'][0]['message']['content'] ?? '');
            if ($resultat['code'] === 200 && $texte !== '') {
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
