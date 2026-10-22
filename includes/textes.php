<?php
/**
 * Petits textes tournants du site (banque : data/textes.php) : tampons, mentions, témoignages, FAQ, équipe, annonces du hall…
 * Chaque appel tire au hasard, sauf les textes du jour (liés à la date, les mêmes pour tout le monde) :
 * l'article du règlement intérieur (data/reglement.php) et le formulaire du jour de Bertrand (data/formulaires.php).
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

/** Élément du jour d'une liste : tirage déterministe par date (AAAA-MM-JJ), $sel distinguant les listes. */
function tirage_du_jour(array $liste, string $sel, ?string $jour = null): mixed
{
    return $liste[crc32($sel . '-' . ($jour ?? date('Y-m-d'))) % count($liste)];
}

/** Article du règlement intérieur du jour (accueil) : ['numero' => '14', 'texte' => …]. */
function article_du_jour(?string $jour = null): array
{
    [$numero, $texte] = tirage_du_jour(require __DIR__ . '/../data/reglement.php', 'reglement', $jour);
    return ['numero' => $numero, 'texte' => $texte];
}

/** Formulaire du jour de Bertrand (attestation.php) : ['code', 'titre', 'cases' => […]]. */
function formulaire_du_jour(?string $jour = null): array
{
    return tirage_du_jour((require __DIR__ . '/../data/formulaires.php')['formulaires'], 'formulaire', $jour);
}

/** Délai de traitement « estimé » d'un dossier : toujours le même pour un numéro donné (site et Bureau). */
function delai_estime(int $id): string
{
    $delais = textes()['delais'];
    return $delais[$id % count($delais)];
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
