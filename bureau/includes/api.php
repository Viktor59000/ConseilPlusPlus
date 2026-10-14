<?php
/**
 * Le Bureau — client de l'API du site vitrine (seul lien entre les deux applications).
 */
require_once __DIR__ . '/../config.php';

/**
 * Appel JSON à l'API du site. $ressource : « contacts.php?statut=nouveau », « conseils.php »…
 * Retourne ['code' => statut HTTP (0 si le site est injoignable), 'data' => réponse décodée ou null].
 */
function api(string $methode, string $ressource, ?array $corps = null, bool $avec_cle = true): array
{
    $entetes = ['Accept: application/json'];
    if ($avec_cle) {
        $entetes[] = 'X-API-Key: ' . BUREAU_API_KEY;
    }
    $ch = curl_init(rtrim(BUREAU_API_URL, '/') . '/' . $ressource);
    if ($corps !== null) {
        $entetes[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($corps));
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $methode,
        CURLOPT_HTTPHEADER => $entetes,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CAINFO => __DIR__ . '/../../certs/cacert.pem', // si l'API est appelée en HTTPS
    ]);
    $reponse = curl_exec($ch);
    $code = $reponse === false ? 0 : curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => $reponse === false ? null : json_decode($reponse, true)];
}
