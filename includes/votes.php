<?php
/**
 * Votes du public sur les entrées du Petit Moyen (« Moyen » ou « Pas moyen »), table votes.
 */
require_once __DIR__ . '/mot-du-jour.php';

const CHOIX_VOTE = ['moyen', 'pas_moyen'];

/** Compteurs des mots demandés (0 pour un mot sans vote). */
function compter_votes(PDO $bdd, array $mots): array
{
    $resultat = array_fill_keys($mots, ['moyen' => 0, 'pas_moyen' => 0]);
    if (!$mots) {
        return $resultat;
    }
    $marques = implode(',', array_fill(0, count($mots), '?'));
    $stmt = $bdd->prepare("SELECT mot, vote, COUNT(*) AS n FROM votes WHERE mot IN ($marques) GROUP BY mot, vote");
    $stmt->execute($mots);
    foreach ($stmt as $ligne) {
        if (isset($resultat[$ligne['mot']])) {
            $resultat[$ligne['mot']][$ligne['vote']] = (int) $ligne['n'];
        }
    }
    return $resultat;
}

/** Vrai si le mot existe au Petit Moyen (banque ou mot du jour publié) : pas de vote sur n'importe quoi. */
function mot_connu(PDO $bdd, string $mot): bool
{
    if (in_array($mot, array_column(pages_dictionnaire(), 'mot'), true)) {
        return true;
    }
    $stmt = $bdd->prepare('SELECT 1 FROM mots_du_jour WHERE mot = ?');
    $stmt->execute([$mot]);
    return (bool) $stmt->fetchColumn();
}
