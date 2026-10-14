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

# FORME D'UN CONSEIL (une seule phrase, 15 à 28 mots)
« [Ouverture administrative], [action 1] et [action 2]. »
- Ouverture : « Vu… », « Considérant… », « Au regard de… », « Compte tenu de… », « En application de… » + un élément du dossier détourné (« Vu vos 17 cuillères, »).
- Deux actions à l'impératif, avec vouvoiement, sans aucun lien logique entre elles, construites sur le gabarit imposé.
- Arrête la phrase après la deuxième action : le cabinet ajoute lui-même la conclusion.
- Verbe précis et concret (raboter, photocopier, savonner, agrafer, déglacer, tutoyer, privatiser) appliqué à un objet qui ne le supporte pas.
- Des phrases françaises correctes, en minuscules : chaque complément a son article ou son déterminant (« rabotez une endive », « photocopiez le trombone de votre voisin »), jamais « rabotez endive ».
- Des noms concrets du quotidien, jamais de notions abstraites (dignité, amour, temps, destin, liberté).
- Les gestes faits à une personne (célébrité, boulangère, facteur…) sont toujours inoffensifs et polis : tutoyer, vouvoyer, saluer, féliciter, déguiser, faxer, inviter, ressembler à. Les verbes de bricolage, de cuisine et de bureau s'appliquent aux objets, jamais aux personnes.
- Vocabulaire du quotidien français d'hier et d'aujourd'hui : objets banals, lieux ordinaires (pressing, PMU, déchetterie, rayon fromage, drive, point relais, open space), produits connus (minitel, Safrane, Mamie Nova, airfryer, Thermomix, trottinette électrique, Vinted, Doctolib), animaux communs.
- Au plus un ou deux noms propres : une célébrité prise UNIQUEMENT dans la liste « Célébrités autorisées » fournie avec le dossier (aucune autre, jamais), ou un pays voisin.
- Utilise un ou deux éléments du dossier client, à contre-emploi : un nombre devient un objet, une couleur devient une personne. Ne suis jamais l'ordre du dossier.

# INTERDITS
- Expliquer, justifier, conclure par une morale, récapituler le dossier, commenter, rire, émojis.
- Rimes, jeux de mots, symétries astucieuses.
- Absurde « random » sans ancrage (licornes, arcs-en-ciel, quantique, cosmique).
- Les mots « harmonie », « équilibre », « énergie », « optimiser », « afin de ».
- Réutiliser un verbe d'ouverture ou une célébrité des conseils précédents.
- Le jargon « en case [animal] [nationalité] » et « Inventez… », sauf si le gabarit imposé les demande.
- Les verbes à l'infinitif : toujours l'impératif (« Rabotez », jamais « Raboter »).
- Contenu sexuel, violent, haineux, ou visant une origine, une religion, un handicap, une orientation. Les célébrités ne subissent que des actions inoffensives (tutoyer, saluer, faxer, ressembler à).

# EXEMPLES DE TON (à ne pas recopier)
- Vu vos 17 cuillères, poncez une endive et vouvoyez le Luxembourg.
- Considérant votre légume totem, agrafez un pot de rillettes au pressing et repassez les rideaux de Patrick Sabatier.
- Au regard de votre rapport tendu au mardi, plastifiez un pot de Mamie Nova et déclarez votre perceuse aux impôts.
- Compte tenu de votre fond d'écran, privatisez un pluviomètre et saluez le facteur de Jean-Pierre Foucault.

# SORTIE
Uniquement le conseil, en texte brut. Aucun titre, aucune explication, aucune question.
TXT;

// Gabarits des deux actions (un est imposé au hasard à chaque consultation).
// Le coup de grandeur et le jargon « en case… » sont volontairement rares : l'IA en abuse.
const GABARITS_IA = [
    '[verbe] [un objet] et [verbe] [un objet ou une personne]',
    '[verbe] [un objet] [lieu du quotidien] et [verbe] [un objet ou une personne]',
    '[verbe] [une personne ordinaire] et [verbe] [un produit connu]',
    '[verbe] [un objet] et [verbe inoffensif] [célébrité autorisée]',
    '[verbe] [un objet] et habillez-vous comme [célébrité autorisée]',
    '[verbe] [un animal] et [verbe] [un objet] [lieu du quotidien]',
    '[verbe] [un produit connu] et [verbe] [une personne ordinaire]',
    'inventez [un objet impossible] et offrez ça à [pays voisin]',
    '[verbe] [un objet] et [verbe] [un objet] en case [animal] [nationalité]',
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
            . "\nGabarit imposé pour les deux actions : « " . GABARITS_IA[array_rand(GABARITS_IA)] . ' »'
            . "\nNiveau : " . random_int(2, 3) . " (2 = Kamoulox classique, 3 = surenchère)";
        if ($historique) {
            $demande .= "\n\nConseils déjà donnés (ne pas réutiliser leurs verbes ni leurs célébrités) :\n- " . implode("\n- ", $historique);
        }

        $texte = groq_completer(CONSIGNE_IA, $demande, fn(string $t) =>
            !preg_match('/^\p{L}+(er|ir)\b/u', $t) // pas d'infinitif en ouverture (« Raboter » au lieu de « Rabotez »)
            && !preg_match('/\bet\s+\p{Ll}+(er|ir)\s/u', $t) // ni dans la deuxième action (« et tutoyer » au lieu de « et tutoyez »)
            && !preg_match('/\p{L}+erz\b/u', $t) // coquilles du type « saluerz »
            && complements_avec_article($t)
            && noms_propres_autorises($t));
        if ($texte !== null) {
            $texte = rtrim(raccourcir($texte, 1), " .!…");
            $texte = mb_strtoupper(mb_substr($texte, 0, 1)) . mb_substr($texte, 1);
            $_SESSION['historique_ia'] = array_slice([...$historique, $texte], -HISTORIQUE_IA);
            // La conclusion vient de nos banques (l'IA recopiait toujours la même, avec des fautes)
            return ['conseil' => $texte . ', ' . composer_resultat(), 'source' => 'ia'];
        }
    }

    // Générateur local de secours
    $c = tirer_conseil($bdd);
    return ['conseil' => "{$c['situation']} {$c['action']} {$c['resultat']}", 'source' => 'secours'];
}

/**
 * Envoie une demande à Groq et renvoie le texte de la réponse, ou null en cas d'échec.
 * - 3 tentatives : le modèle renvoie parfois une réponse vide ou coupée après avoir trop « réfléchi » ;
 * - réponse rejetée si elle contient un mot interdit ou si $valider la refuse ;
 * - bascule sur le modèle de repli si le quota gratuit est dépassé (HTTP 429).
 */
function groq_completer(string $systeme, string $demande, ?callable $valider = null, string $effort = 'low', float $temperature = 1.0): ?string
{
    if (GROQ_API_KEY === '') {
        return null;
    }
    // Mots que le modèle laisse parfois passer malgré la consigne
    // ainsi que les verbes violents (« tapez la serveuse » est déjà arrivé)
    $interdits = '/(quantique|cosmique|licorne|arc-en-ciel|harmonie|équilibre|énergie|optimis'
        . '|\b(tap|frapp|gifl|cogn|batt|poignard|étrangl|tu|bless|fouett|mord|bouscul|écras|assomm|fess|pinc)(ez|e|er)\b)/iu';

    $modele = GROQ_MODEL;
    for ($essai = 1; $essai <= 3; $essai++) {
        $resultat = appeler_api('POST', GROQ_URL, [
            'model' => $modele,
            'messages' => [
                ['role' => 'system', 'content' => $systeme],
                ['role' => 'user', 'content' => $demande],
            ],
            'temperature' => $temperature,
            'max_tokens' => $effort === 'low' ? 800 : 3000, // plus de réflexion = plus de jetons
            // gpt-oss « réfléchit » avant de répondre : réflexion courte et non renvoyée
            'reasoning_effort' => $effort,
            'include_reasoning' => false,
        ], ['Authorization: Bearer ' . GROQ_API_KEY], $effort === 'low' ? 15 : 40);

        $choix = $resultat['data']['choices'][0] ?? [];
        $texte = trim($choix['message']['content'] ?? '', " \n\r\t\"«»");
        // Réponse acceptée seulement si le modèle a terminé normalement (pas de phrase coupée)
        $valide = $texte !== '' && ($choix['finish_reason'] ?? '') === 'stop'
            && !preg_match($interdits, $texte)
            && ($valider === null || $valider($texte));
        if ($resultat['code'] === 200 && $valide) {
            return $texte;
        }
        if ($resultat['code'] === 429 && $modele !== MODELE_REPLI) {
            $modele = MODELE_REPLI; // quota dépassé : on bascule sur le modèle de repli
            continue;
        }
        if ($resultat['code'] !== 200) {
            break; // autre erreur de l'API (clé, réseau) : inutile de réessayer
        }
    }
    error_log("ConseilPlus+ : échec de l'API Groq (HTTP {$resultat['code']})");
    return null;
}

/**
 * Vérifie qu'après chaque verbe à l'impératif (« …ez ») vient un article, un déterminant, une préposition
 * ou un nom propre : rejette « Rabotez dignité et photocopiez trombone ».
 */
function complements_avec_article(string $texte): bool
{
    $determinants = ['un', 'une', 'des', 'du', 'de', 'le', 'la', 'les', 'votre', 'vos', 'mon', 'ma', 'mes', 'son', 'sa', 'ses',
        'ce', 'cet', 'cette', 'ces', 'au', 'aux', 'à', 'en', 'chaque', 'tout', 'toute', 'tous', 'toutes', 'deux', 'trois', 'quatre',
        'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'vous'];
    preg_match_all('/\b\p{L}+ez\s+(\S+)/u', $texte, $m);
    foreach ($m[1] as $mot) {
        $mot = trim($mot, ',;:.!?«»"');
        $ok = in_array(mb_strtolower($mot), $determinants, true)
            || preg_match("/^(l|d|qu)['’]/iu", $mot)  // l'horodateur, d'Andorre
            || preg_match('/^(\d|\p{Lu})/u', $mot);    // 17, Patrick Sabatier
        if (!$ok) {
            return false;
        }
    }
    return true;
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
