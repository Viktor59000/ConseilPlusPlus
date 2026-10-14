<?php
/**
 * Générateur Kamoulox (sans IA) : compose une action absurde à partir de gabarits
 * et des banques lexicales de data/kamoulox.php.
 * Hommage non officiel au Kamoulox de Kad Merad et Olivier Baroux.
 *
 * Gabarits (format conseil, impératif) :
 *   A — Classique       : [verbe] [objet]                       « Poncez une endive »
 *   B — People          : [verbe social] [célébrité]            « Tutoyez Patrick Sabatier »
 *   C — Comparaison     : habillez-vous / coiffez-vous comme…   « Coiffez-vous comme Tex »
 *   D — Pseudo-savant   : [concept] recommande de [infinitif]   « Le coefficient de friture recommande de vernir un douanier »
 *   E — Coup de grandeur: inventez [objet impossible] et déclarez la guerre à [pays]
 *   F — Lieu            : [verbe] [objet] [lieu]                « Agrafez un ragondin au pressing »
 * Un jargon de jeu fantôme (« en case poulpe moldave ») est parfois ajouté.
 */

function kamoulox_banques(): array
{
    static $banques = null;
    return $banques ??= require __DIR__ . '/../data/kamoulox.php';
}

function au_hasard(array $liste)
{
    return $liste[array_rand($liste)];
}

/** Remplace les {variables} du jargon par des mots tirés au hasard. */
function kamoulox_remplir(string $modele): string
{
    $b = kamoulox_banques();
    return strtr($modele, [
        '{animal}' => au_hasard($b['animaux_nus']),
        '{nationalite}' => au_hasard($b['nationalites']),
        '{objet}' => au_hasard($b['objets']),
        '{aliment}' => au_hasard($b['aliments']),
    ]);
}

/**
 * Compose une action Kamoulox à l'impératif (sans majuscule ni point final).
 * $exclure : gabarits à éviter (pour ne pas enchaîner deux fois le même).
 * $jargon : autorise l'ajout d'un jargon de jeu fantôme.
 * Retourne [action, gabarit, jargon ajouté ?].
 */
function composer_action(array $exclure = [], bool $jargon = true): array
{
    $b = kamoulox_banques();
    // A et F reviennent plus souvent : ce sont les plus « purs »
    $gabarits = array_values(array_diff(['A', 'A', 'A', 'B', 'C', 'D', 'E', 'F', 'F'], $exclure));
    $gabarit = au_hasard($gabarits);
    $complement = fn() => random_int(0, 2) ? au_hasard($b['objets']) : au_hasard(random_int(0, 1) ? $b['animaux'] : $b['personnes']);

    $action = match ($gabarit) {
        'A' => au_hasard($b['verbes'])[0] . ' ' . $complement(),
        'B' => au_hasard($b['verbes_sociaux'])[0] . ' ' . au_hasard($b['celebrites']),
        'C' => au_hasard(['habillez-vous comme', 'coiffez-vous comme', 'parlez comme', 'marchez comme']) . ' ' . au_hasard($b['celebrites']),
        'D' => mb_strtolower(au_hasard($b['concepts'])) . ' recommande de ' . au_hasard($b['verbes'])[1] . ' ' . $complement(),
        'E' => 'inventez ' . au_hasard($b['objets_nus']) . ' ' . au_hasard($b['qualificatifs']) . ' et déclarez la guerre ' . au_hasard($b['pays']),
        'F' => au_hasard($b['verbes'])[0] . ' ' . $complement() . ' ' . au_hasard($b['lieux']),
    };

    // Jargon de jeu fantôme, une fois sur quatre (jamais sur le coup de grandeur, déjà assez long)
    $avec_jargon = $jargon && $gabarit !== 'E' && random_int(1, 4) === 1;
    if ($avec_jargon) {
        $action .= ' ' . kamoulox_remplir(au_hasard($b['jargon']));
    }

    return [$action, $gabarit, $avec_jargon];
}
