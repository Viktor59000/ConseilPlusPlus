<?php
/**
 * Outils du compte client ajoutés autour du code du cours (auth/), sans le modifier :
 * mot de passe oublié (reinitialiser.php), suppression du compte (compte.php).
 */

const MDP_LONGUEUR_MIN = 8;

/**
 * Empreinte d'un mot de passe, calculée exactement comme dans auth/inscription.php et auth/connexion.php
 * (grain de sel statique + SHA-512, imposé par le cadre pédagogique). Les deux fichiers du cours restent la
 * référence : si leur méthode change un jour, changer celle-ci aussi, sinon les mots de passe réinitialisés
 * ne permettraient plus de se connecter.
 */
function empreinte_comme_le_cours(string $mdp): string
{
    return hash('sha512', $mdp . 'dTbZ!912cV@2Ad');
}

/**
 * Adresse du site pour les liens envoyés par courriel. En ligne : URL_PUBLIQUE (jamais l'en-tête Host, qu'un
 * attaquant pourrait falsifier pour détourner le lien). En local uniquement : l'adresse localhost courante.
 */
function url_du_site(): string
{
    $hote = $_SERVER['HTTP_HOST'] ?? '';
    if (preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $hote)) {
        return 'http://' . $hote . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }
    return url_publique();
}
