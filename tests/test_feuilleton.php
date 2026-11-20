<?php
/**
 * tests/test_feuilleton.php — Vérifie la saison de « La Plume et le Tampon » (data/feuilleton.php).
 *
 * Lancer en ligne de commande (aucun serveur ni base nécessaire) :
 *   php tests/test_feuilleton.php
 *
 * Contrôle : au moins 12 épisodes numérotés à la suite, trois scènes chacun, chaque réplique dite par un personnage
 * connu, le Sceau muet, une fin en suspens, une durée de lecture de 3 à 5 minutes, aucun nom de l'émission d'origine,
 * et les fonctions de lecture (épisode de la semaine cyclique, sommaire, mise à plat).
 */
require __DIR__ . '/../includes/feuilleton.php';

$echecs = 0;

/** Vérification avec un détail facultatif. */
function constater(string $test, bool $ok, string $detail = ''): void
{
    global $echecs;
    $echecs += $ok ? 0 : 1;
    if (!$ok) {
        printf("[ECHEC] %s%s\n", $test, $detail === '' ? '' : " — $detail");
    }
}

$banque = feuilleton();
$personnages = $banque['personnages'];
$episodes = $banque['episodes'];
$equipe = array_column((require __DIR__ . '/../data/textes.php')['equipe'], 'cle');

// Lecture à voix haute : environ 150 mots par minute ; 3 à 5 minutes (marge basse pour un épisode vif)
const MOTS_MIN = 300;
const MOTS_MAX = 750;
const REPLIQUE_MAX = 300; // caractères
// Rien ne doit venir de l'émission d'origine (noms de personnages, titres d'épisodes, invités)
const INTERDITS = ['Christophe', 'Pupute', 'Claudia', 'Pulpipi', 'Bonniche', 'Pouilleux', 'Cape et l', 'Denisot',
    'Byzantin', 'Ferrets', 'Mousquetaire', 'Marché Opus', 'The Valet', 'tire les rois', 'Inventeur', 'Robins'];

// Personnages
foreach ($personnages as $cle => $p) {
    foreach (['nom', 'court', 'role'] as $champ) {
        constater("personnage $cle : $champ", is_string($p[$champ] ?? null) && $p[$champ] !== '');
    }
    constater("personnage $cle : membre de l'équipe connu", $p['equipe'] === null || in_array($p['equipe'], $equipe, true),
        (string) $p['equipe']);
    constater("personnage $cle : voix", $p['voix'] === null
        || (isset($p['voix']['voix'], $p['voix']['pitch'], $p['voix']['rate']) && $p['voix']['pitch'] > 0 && $p['voix']['rate'] > 0));
}
foreach (['heraut', 'conseiller', 'mireille', 'messmerde', 'stagiaire', 'bertrand', 'tampon', 'cafe'] as $cle) {
    constater("personnage obligatoire : $cle", isset($personnages[$cle]));
}
constater('le Sceau est muet (voix null)', array_key_exists('voix', $personnages['tampon'] ?? []) && $personnages['tampon']['voix'] === null);

// Épisodes
constater('au moins 12 épisodes', count($episodes) >= 12, (string) count($episodes));
constater('titres tous différents', count(array_unique(array_column($episodes, 'titre'))) === count($episodes));
$durees = [];
foreach ($episodes as $i => $ep) {
    $n = $i + 1;
    $nom = "épisode $n";
    constater("$nom : numéro", ($ep['numero'] ?? null) === $n, (string) ($ep['numero'] ?? '?'));
    constater("$nom : titre", is_string($ep['titre'] ?? null) && $ep['titre'] !== '');
    constater("$nom : précédemment", $n === 1 ? $ep['precedemment'] === null : is_string($ep['precedemment']) && $ep['precedemment'] !== '');
    constater("$nom : introduction du Héraut", is_string($ep['heraut'] ?? null) && mb_strlen($ep['heraut']) >= 80);
    constater("$nom : fin en suspens", str_ends_with($ep['suspense'] ?? '', 'La suite, la semaine prochaine.'));
    constater("$nom : trois scènes", count($ep['scenes'] ?? []) === 3);

    $textes = [$ep['titre'], $ep['precedemment'] ?? '', $ep['heraut'], $ep['suspense']];
    foreach ($ep['scenes'] ?? [] as $s => $scene) {
        $ou = "$nom, scène " . ($s + 1);
        constater("$ou : titre et didascalie", ($scene['titre'] ?? '') !== '' && ($scene['didascalie'] ?? '') !== '');
        $repliques = $scene['repliques'] ?? [];
        $dites = array_filter($repliques, fn($r) => $r['qui'] !== 'scene');
        constater("$ou : 5 à 12 répliques", count($dites) >= 5 && count($dites) <= 12, (string) count($dites));
        if ($s === 2) {
            constater("$ou : finit sur une réplique (la chute)", $repliques !== [] && end($repliques)['qui'] !== 'scene');
        }
        $textes[] = $scene['titre'];
        $textes[] = $scene['didascalie'];
        foreach ($repliques as $r) {
            $qui = $r['qui'] ?? '';
            $texte = $r['texte'] ?? '';
            constater("$ou : personnage connu", $qui === 'scene' || isset($personnages[$qui]), $qui);
            constater("$ou : réplique non vide et raisonnable", $texte !== '' && mb_strlen($texte) <= REPLIQUE_MAX,
                mb_substr($texte, 0, 40));
            constater("$ou : clés de réplique", array_diff(array_keys($r), ['qui', 'texte', 'jeu']) === []);
            if ($qui === 'tampon') {
                constater("$ou : le Sceau reste muet", in_array($texte, ['Clac.', '…'], true), $texte);
            }
            $textes[] = $texte;
            $textes[] = $r['jeu'] ?? '';
        }
    }
    $mots = mots_episode($ep);
    $durees[] = $mots;
    constater("$nom : durée de lecture (3 à 5 min)", $mots >= MOTS_MIN && $mots <= MOTS_MAX, "$mots mots");
    $tout = implode(' ', $textes);
    foreach (INTERDITS as $mot) {
        constater("$nom : rien de l'émission d'origine", stripos($tout, $mot) === false, $mot);
    }
}

// Fonctions de lecture
constater('episode(1)', (episode(1)['titre'] ?? '') === $episodes[0]['titre']);
constater('episode(99) inexistant', episode(99) === null);
constater('episode(0) inexistant', episode(0) === null);
constater('liste_episodes()', count(liste_episodes()) === count($episodes) && array_keys(liste_episodes()[0]) === ['numero', 'titre']);
constater('semaine 1 → épisode 1', numero_de_la_semaine('2026-01-01') === 1); // jeudi : semaine ISO 1 de 2026
constater('semaine 13 → retour au début', numero_de_la_semaine('2026-03-26') === (13 - 1) % count($episodes) + 1);
constater('semaine 53 (2026-12-31) existe', episode_de_la_semaine('2026-12-31') !== null);
$vus = [];
for ($s = 0; $s < 52; $s++) {
    $vus[episode_de_la_semaine(date('Y-m-d', strtotime("2026-01-01 +$s week")))['numero']] = true;
}
constater('chaque épisode passe dans l\'année', count($vus) === count($episodes));
constater('épisode de la semaine courante', isset(episode_de_la_semaine()['titre']));
$lignes = lignes_episode(episode(1));
constater('lignes_episode : ouvre sur le Héraut', $lignes[0][0] === 'heraut' && $lignes[0][2] === 'annonce');
constater('lignes_episode : finit sur le suspense', end($lignes)[2] === 'suspense');
constater('lignes_episode : une seule chute', count(array_filter($lignes, fn($l) => $l[2] === 'chute')) === 1);
constater('noms_feuilleton()', noms_feuilleton()['mireille'] === 'Mireille');

printf("%d épisodes, %d à %d mots (≈ %.1f à %.1f min à 150 mots/min).\n", count($episodes), min($durees), max($durees),
    min($durees) / 150, max($durees) / 150);
echo $echecs === 0 ? "Tous les tests sont passés.\n" : "$echecs test(s) en échec.\n";
exit($echecs === 0 ? 0 : 1);
