<?php
/**
 * Adresses du site, toutes au même endroit.
 * - url_publique() : adresse officielle (liens partagés, courriels, documentation), jamais localhost.
 * - url_des_liens() : adresse des liens envoyés par courriel (localhost en local, sinon l'adresse publique).
 * - url_interne() : adresse pour que le site appelle sa propre API depuis le serveur.
 */

/** Dossier du site sous le domaine (« /Site » sous WAMP, « » en ligne), sans barre finale. */
function dossier_du_site(): string
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
}

function url_publique(): string
{
    return rtrim(getenv('URL_PUBLIQUE') ?: 'https://conseilplus.onrender.com', '/');
}

/**
 * En ligne : URL_PUBLIQUE (jamais l'en-tête Host, qu'un attaquant pourrait falsifier pour détourner un lien
 * de réinitialisation). En local uniquement : l'adresse localhost courante.
 */
function url_des_liens(): string
{
    $hote = $_SERVER['HTTP_HOST'] ?? '';
    return preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $hote) ? 'http://' . $hote . dossier_du_site() : url_publique();
}

/**
 * En ligne (conteneur Render), URL_INTERNE vaut http://127.0.0.1 : l'appel reste dans le conteneur
 * et l'API le reconnaît comme venant du site (adresse locale). En local : l'adresse courante.
 */
function url_interne(): string
{
    if ($interne = getenv('URL_INTERNE')) {
        return rtrim($interne, '/') . dossier_du_site();
    }
    $protocole = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    return $protocole . '://' . $_SERVER['HTTP_HOST'] . dossier_du_site();
}
