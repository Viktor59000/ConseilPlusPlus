<?php
/**
 * api/openapi.php — Description OpenAPI 3.1 de l'API, générée à partir de data/api.php
 * (la même source que la documentation developpeurs.php : les deux ne peuvent pas diverger).
 * GET (public) → 200, à importer dans Postman, Insomnia ou Swagger UI.
 */
require("../includes/api.php");
require_once __DIR__ . '/../includes/url.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    repondre(405, ['erreur' => 'Méthode non autorisée.']);
}

$groupes = require __DIR__ . '/../data/api.php';
$chemins = [];
$tags = [];
foreach ($groupes as $groupe => $endpoints) {
    $tags[] = ['name' => $groupe];
    foreach ($endpoints as $e) {
        $chemin = strtok($e['chemin'], '?');
        foreach (array_map('trim', explode('·', $e['methode'])) as $methode) {
            $methode = strtolower($methode);
            $operation = [
                'tags' => [$groupe],
                'summary' => $e['role'],
                'operationId' => $methode . str_replace(['/', '.php', '-'], ['_', '', '_'], $chemin),
                'responses' => [],
            ];
            $parametres = [];
            $champs = [];
            foreach ($e['parametres'] as $nom => $detail) {
                foreach (array_map('trim', explode(',', $nom)) as $n) {
                    // Paramètres d'URL pour les lectures et l'identifiant ; corps JSON pour les écritures
                    if ($n === 'id' || in_array($methode, ['get', 'delete'], true)) {
                        $parametres[] = ['name' => $n, 'in' => 'query', 'required' => false, 'description' => $detail, 'schema' => ['type' => $n === 'id' ? 'integer' : 'string']];
                    } else {
                        $champs[$n] = ['description' => $detail];
                    }
                }
            }
            if (preg_match('/[?&]id=/', $e['chemin']) && !in_array('id', array_column($parametres, 'name'), true)) {
                $parametres[] = ['name' => 'id', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'integer', 'minimum' => 1]];
            }
            if ($parametres) {
                $operation['parameters'] = $parametres;
            }
            if ($champs) {
                $operation['requestBody'] = ['content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => $champs]]]];
            }
            foreach ($e['reponses'] as $code => $detail) {
                $operation['responses'][(string) $code] = ['description' => $detail];
            }
            if (in_array($e['acces'], ['cle', 'admin'], true)) {
                $operation['security'] = [['cleApi' => []]];
            } elseif ($e['acces'] === 'session') {
                $operation['security'] = [['session' => []]];
            }
            $chemins[$chemin][$methode] = $operation;
        }
    }
}

$base = url_publique() . '/api';
repondre(200, [
    'openapi' => '3.1.0',
    'info' => [
        'title' => 'API de ConseilPlus+',
        'version' => '1.0.0',
        'description' => "Conseils absurdes, mot du jour, horoscope, votes, demandes de contact. Documentation lisible : " . url_publique() . '/developpeurs.php',
    ],
    'servers' => [['url' => "$base/v1", 'description' => 'Version 1 (actuelle)'], ['url' => $base, 'description' => 'Sans version (alias de la v1)']],
    'tags' => $tags,
    'paths' => $chemins,
    'components' => ['securitySchemes' => [
        'cleApi' => ['type' => 'apiKey', 'in' => 'header', 'name' => 'X-API-Key',
            'description' => "Clé d'application (droits lecture / ecriture) ou clé maîtresse du Bureau (tous les droits)."],
        'session' => ['type' => 'apiKey', 'in' => 'cookie', 'name' => 'PHPSESSID',
            'description' => 'Client connecté au site ; écritures avec le jeton CSRF (en-tête X-CSRF-Token).'],
    ]],
]);
