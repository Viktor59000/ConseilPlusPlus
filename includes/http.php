<?php
/**
 * Appels HTTP sortants (cURL), partagés par le site et Le Bureau : API du site, Groq, Brevo, Web Push.
 * Certificats racine fournis par certs/cacert.pem (WAMP n'en a pas) : la vérification SSL reste toujours active.
 */
require_once __DIR__ . '/url.php';

/**
 * Requête brute. $corps : null (aucun corps) ou chaîne déjà encodée.
 * Retourne ['code' => statut HTTP (0 si échec réseau), 'brut' => corps de la réponse (null si échec)].
 */
function requete_http(string $methode, string $url, ?string $corps = null, array $entetes = [], int $timeout = 10): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $methode,
        CURLOPT_HTTPHEADER => $entetes,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CAINFO => __DIR__ . '/../certs/cacert.pem',
    ]);
    if ($corps !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $corps);
    }
    $reponse = curl_exec($ch);
    $code = $reponse === false ? 0 : curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'brut' => $reponse === false ? null : $reponse];
}

/**
 * Appel d'une API JSON : le corps (tableau) est encodé, la réponse décodée.
 * Retourne ['code' => statut HTTP (0 si échec réseau), 'data' => réponse décodée ou null].
 */
function appeler_api(string $methode, string $url, ?array $corps = null, array $entetes = [], int $timeout = 10): array
{
    $entetes[] = 'Accept: application/json';
    if ($corps !== null) {
        $entetes[] = 'Content-Type: application/json';
    }
    $r = requete_http($methode, $url, $corps === null ? null : json_encode($corps, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        $entetes, $timeout);
    return ['code' => $r['code'], 'data' => $r['brut'] === null ? null : json_decode($r['brut'], true)];
}
