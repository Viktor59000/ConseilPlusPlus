<?php
/**
 * Le mot (presque) du jour — Le Petit Moyen.
 * Un mot par jour, identique pour tout le monde : il est enregistré en base (table mots_du_jour)
 * la première fois qu'il est demandé, puis ne change plus.
 *
 * Deux formats :
 * - « definition » : fausse entrée de dictionnaire (banque data/mots.php) ;
 * - « sketch »     : mini-sketch du conseiller et du stagiaire, hommage non officiel aux « Mots »
 *                    d'Éric Judor et Ramzy Bédia (banque data/sketchs.php, ou IA).
 * Sans entrée préparée, la banque choisit (un jour sur trois environ, un sketch).
 * L'IA prépare le sketch du lendemain à l'avance (preparer_mot), sans jamais ralentir une page.
 */
require_once __DIR__ . '/ia.php';

const FORMULE_FINALE = "Le cabinet ne voit pas d'autre explication.";

const OUVERTURES = [
    "Mesdames, messieurs, bonjour. Aujourd'hui, ensemble, nous allons apprendre un mot. Un mot que nous ne connaissons pas. Ni vous. Ni nous. Le mot du jour est…",
    "Bonjour, bonjour. Aujourd'hui, le cabinet va vous apprendre un mot. Un vrai. Un mot que personne ne connaît, pas même le cabinet. Le mot du jour est…",
    "Chers clients, chères clientes. Un mot. Aujourd'hui. Ensemble. Un mot que nous allons découvrir en même temps que vous, ce qui est rare. Le mot du jour est…",
    "Bonjour à tous, et bonjour à chacun. Aujourd'hui, nous allons apprendre un mot difficile. Très difficile. Même pour nous. Surtout pour nous. Le mot du jour est…",
];

const CONSIGNE_SKETCH = <<<TXT
# RÔLE
Tu écris le sketch du jour du « Petit Moyen », le dictionnaire officieux du cabinet ConseilPlus+,
dans le style des « Mots » d'Éric et Ramzy (M6, 1997) : hommage non officiel.
Deux personnages naïfs prétendent apprendre au public un mot que personne ne connaît, puis en donnent
une fausse définition par une scène du quotidien qui se termine sur un calembour tordu.

# PERSONNAGES
- LE CONSEILLER : maître de cérémonie solennel et ampoulé, se croit pédagogue.
- LE STAGIAIRE : enthousiaste et à côté de la plaque, propose de fausses pistes, ajoute souvent une remarque inutile à la fin.

# MÉTHODE (dans cet ordre)
0. Découpage : avant tout, trouve comment le mot SONNE comme une phrase ou une expression banale, en le cassant
   en morceaux (« non / chat / lance » pour nonchalance, « bal / d'ac / un » pour baldaquin,
   « la vis / ici / si tu » pour vicissitude, « cafard / nan / hum » pour capharnaüm). Écris-le dans la clé "decoupage".
   La chute du sketch sera construite avec ce découpage.

# DÉROULÉ (l'ouverture et la formule finale sont ajoutées par le site, ne les écris pas)
1. Fausses pistes du stagiaire : 1 à 3 mots ou noms propres connus qui commencent comme le mot cible.
2. Épellation du mot par le conseiller, avec une erreur ou une précision inutile (lettre muette qu'on « ne compte pas », accent qu'on « n'entend pas »).
3. Scène : une didascalie courte, un moment du quotidien (famille, voisinage, travail, fête, commerce, petit accident sans gravité).
   Le conseiller et le stagiaire y jouent des rôles.
4. Répliques : 1 à 4. La dernière est la chute : une phrase banale où une expression courante, un découpage syllabique,
   un prénom, une onomatopée ou une prononciation ratée SONNE approximativement comme le mot cible.
   Le personnage la dit naturellement, sans la souligner.
5. Relance (facultative) : une remarque inutile du stagiaire après la formule finale.

# RÈGLES
- La chute ne contient JAMAIS le mot cible lui-même, ni un mot de sa famille : elle contient seulement le découpage sonore.
  « Ton chat est nonchalant » est un échec (c'est la vraie définition) ; « Non, chat… Lance ! » est réussi.
- Invente tes propres calembours : ne reprends JAMAIS une chute des sketchs originaux d'Éric et Ramzy.
- Dans la scène, les personnages ignorent qu'on cherche un mot : ils ne parlent jamais de mot, de dictionnaire,
  d'épellation ni de sens. Ils vivent leur moment du quotidien, et la chute arrive « par hasard ».
- Le calembour doit être approximatif, voire laborieux. Un jeu de mots trop propre est un échec.
- Scène réaliste et banale ; l'absurde vient uniquement du rapprochement sonore. Pas de fantastique.
- Personne ne donne la vraie définition. Personne n'explique la blague.
- Phrases courtes, langage oral, français correct.
- Rien de sexuel, haineux, violent ou humiliant. Pas d'alcool fort ni de drogue.

# EXEMPLE (format attendu)
{"mot":"Baldaquin","nature":"n. m.","decoupage":"bal / d'ac / un","pistes":["Balcon","Baladeur"],"epellation":"B, A, L, D, A, Q, U, I, N. Le U accompagne le Q, il ne travaille pas.","scene":"Fête du village. Le conseiller vend les tickets de la soirée dansante. Le stagiaire arrive, seul.","repliques":[{"qui":"conseiller","texte":"C'est deux euros l'entrée pour le bal. Je vous en mets combien ?"},{"qui":"stagiaire","texte":"Le bal ? D'ac… Un."}],"relance":"Je danse seul, mais je danse bien.","certitude":44}

# SORTIE
Uniquement un objet JSON sur une ligne, avec exactement ces clés : mot, nature, decoupage, pistes, epellation, scene, repliques, relance, certitude
(qui ∈ conseiller | stagiaire ; certitude entre 1 et 100 ; relance peut être une chaîne vide).
TXT;

function mots_banque(): array
{
    static $banque = null;
    return $banque ??= require __DIR__ . '/../data/mots.php';
}

function sketchs_banque(): array
{
    static $banque = null;
    return $banque ??= require __DIR__ . '/../data/sketchs.php';
}

/**
 * Toutes les pages du dictionnaire (définitions et sketchs de la banque), classées par ordre alphabétique
 * et numérotées à partir de 1. Même forme que les entrées de lire_mot(), sans date (jour = null).
 */
function pages_dictionnaire(): array
{
    static $pages = null;
    if ($pages !== null) {
        return $pages;
    }
    $pages = array_map(fn($m) => $m + ['format' => 'definition', 'sketch' => null], mots_banque());
    foreach (sketchs_banque()['sketchs'] as $s) {
        $pages[] = [
            'mot' => $s['mot'], 'nature' => $s['nature'], 'format' => 'sketch', 'certitude' => $s['certitude'],
            'sketch' => array_intersect_key($s, array_flip(['pistes', 'epellation', 'scene', 'repliques', 'relance'])),
        ];
    }
    $collator = new Collator('fr_FR');
    usort($pages, fn($a, $b) => $collator->compare($a['mot'], $b['mot']));
    return $pages = array_map(fn($p, $i) => $p + ['jour' => null, 'page' => $i + 1], $pages, array_keys($pages));
}

/** Mots déjà publiés récemment (pour éviter les répétitions). */
function mots_recents(PDO $bdd, int $jours = 60): array
{
    $stmt = $bdd->prepare('SELECT mot FROM mots_du_jour WHERE jour > CURDATE() - INTERVAL :j DAY');
    $stmt->bindValue('j', $jours, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/** Enregistre une entrée pour un jour donné (sans écraser une entrée existante). */
function enregistrer_mot(PDO $bdd, string $jour, array $e, string $source): bool
{
    $sketch = $e['format'] === 'sketch'
        ? json_encode(array_intersect_key($e, array_flip(['pistes', 'epellation', 'scene', 'repliques', 'relance'])), JSON_UNESCAPED_UNICODE)
        : null;
    $stmt = $bdd->prepare('INSERT IGNORE INTO mots_du_jour (jour, mot, nature, format, definition, exemple, synonyme, sketch, certitude, source)
                           VALUES (:jour, :mot, :nature, :format, :definition, :exemple, :synonyme, :sketch, :certitude, :source)');
    $stmt->execute([
        'jour' => $jour, 'mot' => $e['mot'], 'nature' => $e['nature'], 'format' => $e['format'],
        'definition' => $e['definition'] ?? null, 'exemple' => $e['exemple'] ?? null, 'synonyme' => $e['synonyme'] ?? null,
        'sketch' => $sketch, 'certitude' => $e['certitude'], 'source' => $source,
    ]);
    return $stmt->rowCount() > 0;
}

/** Lit une entrée et décode son sketch éventuel. */
function lire_mot(PDO $bdd, string $jour): ?array
{
    $stmt = $bdd->prepare('SELECT * FROM mots_du_jour WHERE jour = :jour');
    $stmt->execute(['jour' => $jour]);
    $e = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$e) {
        return null;
    }
    $e['sketch'] = $e['sketch'] ? json_decode($e['sketch'], true) : null;
    return $e;
}

/** Premier élément de $liste non publié récemment, en partant d'un point fixé par la date. */
function choisir_non_recent(array $liste, string $jour, array $recents): array
{
    $depart = crc32($jour) % count($liste);
    for ($i = 0; $i < count($liste); $i++) {
        $candidat = $liste[($depart + $i) % count($liste)];
        if (!isset($recents[$candidat['mot']])) {
            return $candidat;
        }
    }
    return $liste[$depart];
}

/**
 * Le mot d'un jour (aujourd'hui par défaut). S'il n'existe pas encore, il est tiré de la banque
 * (de façon déterministe selon la date, en évitant les mots publiés récemment) puis enregistré.
 * Retourne null pour une date future.
 */
function mot_du_jour(PDO $bdd, ?string $jour = null): ?array
{
    $jour ??= date('Y-m-d');
    if ($jour > date('Y-m-d')) {
        return null;
    }
    if ($entree = lire_mot($bdd, $jour)) {
        return $entree;
    }

    $recents = array_flip(mots_recents($bdd));
    if (crc32("sketch-$jour") % 3 === 0) {
        $choix = choisir_non_recent(sketchs_banque()['sketchs'], $jour, $recents) + ['format' => 'sketch'];
    } else {
        $choix = choisir_non_recent(mots_banque(), $jour, $recents) + ['format' => 'definition'];
    }
    enregistrer_mot($bdd, $jour, $choix, 'banque');
    return lire_mot($bdd, $jour);
}

/** Vérifie et nettoie un sketch renvoyé par l'IA. Retourne null s'il est invalide. */
function valider_sketch(string $json, array $deja): ?array
{
    $s = json_decode(trim($json, " \n\r\t`"), true);
    if (!is_array($s)) {
        return null;
    }
    $chaine = fn($v, int $max) => is_string($v) && trim($v) !== '' && mb_strlen($v) <= $max;
    if (!$chaine($s['mot'] ?? null, 60) || !$chaine($s['nature'] ?? null, 40) || !$chaine($s['epellation'] ?? null, 200)
        || !$chaine($s['scene'] ?? null, 300) || !is_array($s['pistes'] ?? null) || !is_array($s['repliques'] ?? null)) {
        return null;
    }
    if (in_array(mb_strtolower($s['mot']), array_map('mb_strtolower', $deja), true)) {
        return null;
    }
    $pistes = array_values(array_filter($s['pistes'], fn($p) => $chaine($p, 40)));
    $repliques = array_values(array_filter($s['repliques'], fn($r) => is_array($r)
        && in_array($r['qui'] ?? '', ['conseiller', 'stagiaire'], true) && $chaine($r['texte'] ?? null, 200)));
    if (count($pistes) < 1 || count($pistes) > 3 || count($repliques) < 1 || count($repliques) > 4 || !$chaine($s['decoupage'] ?? null, 120)) {
        return null;
    }
    // La chute ne doit pas contenir le mot lui-même (ni sa racine) : sinon c'est la vraie définition, pas un calembour
    $sans_accents = fn(string $t) => mb_strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII', $t));
    $racine = mb_substr($sans_accents($s['mot']), 0, 6);
    $chute = $sans_accents(end($repliques)['texte']);
    if (str_contains(str_replace([' ', '-', '…', '.'], '', $chute), $racine)) {
        return null;
    }
    $relance = is_string($s['relance'] ?? null) ? mb_substr(trim($s['relance']), 0, 150) : '';
    return [
        'format' => 'sketch',
        'mot' => mb_strtoupper(mb_substr(trim($s['mot']), 0, 1)) . mb_substr(trim($s['mot']), 1), 'nature' => trim($s['nature']),
        'pistes' => $pistes, 'epellation' => trim($s['epellation']), 'scene' => trim($s['scene']),
        'repliques' => array_map(fn($r) => ['qui' => $r['qui'], 'texte' => trim($r['texte'])], $repliques),
        'relance' => $relance,
        'certitude' => max(1, min(100, (int) ($s['certitude'] ?? 50))),
    ];
}

/**
 * Demande à l'IA le sketch d'un jour (en pratique : demain) et l'enregistre s'il est valide.
 * Retourne true si une entrée a été créée, false si elle existait déjà ou si l'IA a échoué.
 */
function preparer_mot(PDO $bdd, string $jour): bool
{
    if (lire_mot($bdd, $jour)) {
        return false;
    }

    $deja = array_unique([...mots_recents($bdd, 365), ...array_column(mots_banque(), 'mot'), ...array_column(sketchs_banque()['sketchs'], 'mot')]);
    $candidats = array_diff(sketchs_banque()['mots_savants'], array_map('mb_strtolower', $deja));
    $proposes = (array) array_rand(array_flip($candidats), min(6, count($candidats)));
    $demande = 'Choisis UN de ces mots (celui qui offre le meilleur calembour) : ' . implode(', ', $proposes)
        . ". Écris son sketch.\nMots interdits (déjà publiés) : " . implode(', ', $deja) . '.';

    $entree = null;
    // Effort de réflexion « medium » : un seul appel par jour, en arrière-plan, et le calembour demande de chercher
    groq_completer(CONSIGNE_SKETCH, $demande, function (string $t) use (&$entree, $deja) {
        $entree = valider_sketch($t, $deja);
        return $entree !== null;
    }, 'medium', 0.9);

    return $entree !== null && enregistrer_mot($bdd, $jour, $entree, 'ia');
}
