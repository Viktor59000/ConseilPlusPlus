<?php
/**
 * Appel HTTP vers une API (JSON) avec cURL.
 * Retourne ['code' => statut HTTP (0 si échec réseau), 'data' => réponse décodée ou null].
 */
function appeler_api(string $methode, string $url, ?array $corps = null, array $entetes = [], int $timeout = 10): array
{
    $ch = curl_init($url);
    $entetes[] = 'Accept: application/json';
    if ($corps !== null) {
        $entetes[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($corps, JSON_INVALID_UTF8_SUBSTITUTE));
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $methode,
        CURLOPT_HTTPHEADER => $entetes,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        // Certificats racine (WAMP n'en fournit pas) : nécessaire pour les API en HTTPS
        CURLOPT_CAINFO => __DIR__ . '/../certs/cacert.pem',
    ]);
    $reponse = curl_exec($ch);
    $code = $reponse === false ? 0 : curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $code, 'data' => $reponse === false ? null : json_decode($reponse, true)];
}

/** URL de base du site (ex. http://localhost/Site), pour appeler sa propre API. */
function url_site(): string
{
    $protocole = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    return $protocole . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
}
