<?php
/**
 * Le mot (presque) du jour — Le Petit Moyen.
 * Un mot par jour, identique pour tout le monde : il est enregistré en base (table mots_du_jour)
 * la première fois qu'il est demandé, puis ne change plus.
 * - Par défaut, il vient de la banque écrite à la main (data/mots.php).
 * - L'IA peut préparer le mot du lendemain à l'avance (preparer_mot), sans jamais ralentir une page.
 */
require_once __DIR__ . '/ia.php';

const CONSIGNE_MOT = <<<TXT
Tu rédiges une entrée du « Petit Moyen », le faux dictionnaire officiel du cabinet ConseilPlus+, le conseiller moyen.
Une entrée = un jeu de mots (ou un mot qui n'en est pas un) avec une définition approximative, sur un ton administratif et pince-sans-rire.

Le mot peut être :
- un mot-valise inventé à partir du quotidien français (bureau, transports, cuisine, numérique, administration…),
- un vrai mot défini de travers, avec une mauvaise foi totale,
- ou un faux jeu de mots assumé (la définition n'a aucun rapport).

Règles :
- Français correct, humour bienveillant : rien de vexant, de sexuel, de politique, de violent ni de médical.
- Définition : une phrase, 25 mots maximum. Exemple d'usage : une phrase courte entre guillemets français.
- Synonyme : un à quatre mots, discutable. Certitude : un nombre entier entre 1 et 100.
- Pas d'émojis, pas de markdown, pas d'explication.

Exemples de ton :
MOT: Procrastinette | NATURE: n. f. | DEFINITION: Petite procrastination de poche, à emporter partout. | EXEMPLE: « Je me suis accordé une procrastinette entre deux réunions. » | SYNONYME: sieste debout | CERTITUDE: 64
MOT: Dimanche | NATURE: n. m. | DEFINITION: Jour officiellement consacré à la préparation psychologique du lundi. | EXEMPLE: « Ce dimanche, j'ai beaucoup appréhendé. » | SYNONYME: lundi en avance | CERTITUDE: 92

Sortie : exactement une ligne au format
MOT: … | NATURE: … | DEFINITION: … | EXEMPLE: « … » | SYNONYME: … | CERTITUDE: …
TXT;

function mots_banque(): array
{
    static $banque = null;
    return $banque ??= require __DIR__ . '/../data/mots.php';
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
function enregistrer_mot(PDO $bdd, string $jour, array $entree, string $source): bool
{
    $stmt = $bdd->prepare('INSERT IGNORE INTO mots_du_jour (jour, mot, nature, definition, exemple, synonyme, certitude, source)
                           VALUES (:jour, :mot, :nature, :definition, :exemple, :synonyme, :certitude, :source)');
    $stmt->execute([
        'jour' => $jour, 'mot' => $entree['mot'], 'nature' => $entree['nature'], 'definition' => $entree['definition'],
        'exemple' => $entree['exemple'], 'synonyme' => $entree['synonyme'], 'certitude' => $entree['certitude'], 'source' => $source,
    ]);
    return $stmt->rowCount() > 0;
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

    $lire = $bdd->prepare('SELECT jour, mot, nature, definition, exemple, synonyme, certitude, source FROM mots_du_jour WHERE jour = :jour');
    $lire->execute(['jour' => $jour]);
    if ($entree = $lire->fetch(PDO::FETCH_ASSOC)) {
        return $entree;
    }

    // Banque : point de départ fixé par la date, puis premier mot non publié récemment
    $banque = mots_banque();
    $recents = array_flip(mots_recents($bdd));
    $depart = crc32($jour) % count($banque);
    $choix = $banque[$depart];
    for ($i = 0; $i < count($banque); $i++) {
        $candidat = $banque[($depart + $i) % count($banque)];
        if (!isset($recents[$candidat['mot']])) {
            $choix = $candidat;
            break;
        }
    }
    enregistrer_mot($bdd, $jour, $choix, 'banque');

    $lire->execute(['jour' => $jour]);
    return $lire->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Demande à l'IA l'entrée d'un jour (en pratique : demain) et l'enregistre si elle est valide.
 * Retourne true si une entrée a été créée, false si elle existait déjà ou si l'IA a échoué.
 */
function preparer_mot(PDO $bdd, string $jour): bool
{
    $existe = $bdd->prepare('SELECT 1 FROM mots_du_jour WHERE jour = :jour');
    $existe->execute(['jour' => $jour]);
    if ($existe->fetchColumn()) {
        return false;
    }

    $deja = array_unique([...mots_recents($bdd, 365), ...array_column(mots_banque(), 'mot')]);
    $demande = 'Rédige l\'entrée du jour. Mots déjà utilisés, à ne pas reprendre : ' . implode(', ', $deja) . '.';

    $entree = null;
    groq_completer(CONSIGNE_MOT, $demande, function (string $t) use (&$entree, $deja) {
        $champs = [];
        foreach (explode('|', $t) as $morceau) {
            if (preg_match('/^\s*(MOT|NATURE|DEFINITION|EXEMPLE|SYNONYME|CERTITUDE)\s*:\s*(.+?)\s*$/u', $morceau, $m)) {
                $champs[strtolower($m[1])] = $m[2];
            }
        }
        if (count($champs) !== 6 || !ctype_digit($champs['certitude'])) {
            return false;
        }
        $champs['certitude'] = max(1, min(100, (int) $champs['certitude']));
        $champs['exemple'] = trim($champs['exemple'], ' «»"');
        $limites = ['mot' => 80, 'nature' => 80, 'definition' => 300, 'exemple' => 300, 'synonyme' => 120];
        foreach ($limites as $cle => $max) {
            if ($champs[$cle] === '' || mb_strlen($champs[$cle]) > $max) {
                return false;
            }
        }
        if (in_array(mb_strtolower($champs['mot']), array_map('mb_strtolower', $deja), true)) {
            return false;
        }
        $entree = $champs;
        return true;
    });

    return $entree !== null && enregistrer_mot($bdd, $jour, $entree, 'ia');
}
