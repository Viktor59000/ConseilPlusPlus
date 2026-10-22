<?php
/**
 * Le Bureau — client de l'API du site vitrine (seul lien entre les deux applications).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../includes/http.php'; // appeler_api(), partagé avec le site

/**
 * Appel JSON à l'API du site. $ressource : « contacts.php?statut=nouveau », « conseils.php »…
 * Retourne ['code' => statut HTTP (0 si le site est injoignable), 'data' => réponse décodée ou null].
 */
function api(string $methode, string $ressource, ?array $corps = null, bool $avec_cle = true): array
{
    return appeler_api($methode, rtrim(BUREAU_API_URL, '/') . '/' . $ressource, $corps,
        $avec_cle ? ['X-API-Key: ' . BUREAU_API_KEY] : [], 20);
}

/** Message lisible pour un appel à l'API qui a échoué (sans point final : l'appelant complète). */
function message_erreur_api(array $r): string
{
    return $r['code'] === 401 ? 'Clé d\'API refusée par le site' : 'Le site ne répond pas (code ' . $r['code'] . ')';
}
