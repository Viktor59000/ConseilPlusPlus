<?php
/**
 * « La Plume et le Tampon », le feuilleton du cabinet (banque : data/feuilleton.php, format décrit en tête du fichier).
 * Un épisode par semaine ISO, le même pour tout le monde, sans base de données : la saison tourne en boucle.
 */

/** La banque complète du feuilleton (chargée une seule fois). */
function feuilleton(): array
{
    static $banque = null;
    return $banque ??= require __DIR__ . '/../data/feuilleton.php';
}

/** Un épisode par son numéro (1, 2, 3…), ou null s'il n'existe pas. */
function episode(int $n): ?array
{
    foreach (feuilleton()['episodes'] as $episode) {
        if ($episode['numero'] === $n) {
            return $episode;
        }
    }
    return null;
}

/** Sommaire de la saison : [['numero' => 1, 'titre' => …], …]. */
function liste_episodes(): array
{
    return array_map(
        fn(array $e): array => ['numero' => $e['numero'], 'titre' => $e['titre']],
        feuilleton()['episodes']
    );
}

/** Numéro de l'épisode d'une semaine ISO (semaine 1 → épisode 1, puis en boucle sur la saison). */
function numero_de_la_semaine(?string $jour = null): int
{
    $semaine = (int) date('W', $jour === null ? time() : strtotime($jour));
    return ($semaine - 1) % count(feuilleton()['episodes']) + 1;
}

/** L'épisode de la semaine (date du jour, ou $jour au format AAAA-MM-JJ). */
function episode_de_la_semaine(?string $jour = null): array
{
    return episode(numero_de_la_semaine($jour));
}

/** Un personnage par sa clé (« conseiller », « mireille »…), ou null. */
function personnage(string $cle): ?array
{
    return feuilleton()['personnages'][$cle] ?? null;
}

/** Libellés courts des personnages (clé → « Le Conseiller »…), comme $noms pour includes/script-sketch.php. */
function noms_feuilleton(): array
{
    return array_map(fn(array $p): string => $p['court'], feuilleton()['personnages']);
}

/**
 * L'épisode à plat, prêt pour le lecteur de scènes : [[qui, texte, rôle, jeu], …].
 * Même convention que includes/script-sketch.php (qui = « scene » pour une didascalie ; rôle « chute » pour la
 * dernière réplique de la dernière scène), plus le Héraut en ouverture (rôle « annonce ») et en clôture (« suspense »).
 * « Précédemment » n'y figure pas : la page l'affiche à part, avant de lancer la lecture.
 */
function lignes_episode(array $episode): array
{
    $lignes = [['heraut', $episode['heraut'], 'annonce', '']];
    $derniere = count($episode['scenes']) - 1;
    foreach ($episode['scenes'] as $i => $scene) {
        $lignes[] = ['scene', $scene['didascalie'], '', ''];
        $fin = count($scene['repliques']) - 1;
        foreach ($scene['repliques'] as $j => $r) {
            $lignes[] = [$r['qui'], $r['texte'], $i === $derniere && $j === $fin ? 'chute' : '', $r['jeu'] ?? ''];
        }
    }
    $lignes[] = ['heraut', $episode['suspense'], 'suspense', ''];
    return $lignes;
}

/** Nombre de mots d'un épisode (précédemment, Héraut, didascalies et répliques) : sert à estimer sa durée. */
function mots_episode(array $episode): int
{
    $texte = ($episode['precedemment'] ?? '') . ' ' . implode(' ', array_column(lignes_episode($episode), 1));
    return count(preg_split('/\s+/u', trim($texte)));
}
