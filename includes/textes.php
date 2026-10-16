<?php
/**
 * Petits textes tournants du site (banque : data/textes.php) : tampons, mentions, note de service,
 * témoignages, FAQ, équipe… Chaque appel tire au hasard, sauf la note de service (liée à la date).
 */

function textes(): array
{
    static $banque = null;
    return $banque ??= require __DIR__ . '/../data/textes.php';
}

/** Un élément au hasard d'une liste de la banque (« tampons », « mentions »…). */
function texte_hasard(string $liste): string
{
    $choix = textes()[$liste];
    return $choix[array_rand($choix)];
}

/** Note de service du jour : date particulière si c'en est une, sinon celle du jour de la semaine. */
function note_de_service(): string
{
    $t = textes();
    return $t['dates'][date('d-m')] ?? $t['jours'][(int) date('N')];
}

/** $n témoignages différents, au hasard : [[texte, nom, précision], …]. */
function temoignages(int $n): array
{
    $tous = textes()['temoignages'];
    shuffle($tous);
    return array_slice($tous, 0, $n);
}

/** Bloc HTML de témoignages (accueil, formules). */
function bloc_temoignages(int $n = 3): string
{
    $html = '';
    foreach (temoignages($n) as [$texte, $nom, $precision]) {
        $html .= '<figure class="temoignage"><blockquote><p>' . htmlspecialchars($texte) . '</p></blockquote>'
            . '<figcaption><strong>' . htmlspecialchars($nom) . '</strong>, ' . htmlspecialchars($precision) . '</figcaption></figure>';
    }
    return $html;
}
