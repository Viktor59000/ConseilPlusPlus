<?php
/**
 * Outils du compte client ajoutés autour du code du cours (auth/), sans le modifier :
 * mot de passe oublié (reinitialiser.php), suppression du compte (compte.php).
 * Les liens des courriels utilisent url_des_liens() (includes/url.php).
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
