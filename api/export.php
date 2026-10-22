<?php
/**
 * api/export.php — Sauvegarde complète de la base du site (structure + données), pour le logiciel de gestion.
 *
 * GET   (clé requise)   → 200 { base, fichier, sql }
 *
 * Clé : en-tête X-API-Key. Contient des données personnelles : ne jamais exposer sans clé.
 * Aucun secret ne sort, même avec la clé maîtresse : empreintes des mots de passe vidées (après une restauration,
 * chaque client passe par « Mot de passe oublié »), jetons de réinitialisation et clé privée des notifications omis.
 */
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/export-sql.php';

exiger_methode('GET');
exiger_cle('admin'); // sauvegarde complète : clé maîtresse uniquement

$base = $bdd->query('SELECT DATABASE()')->fetchColumn();
repondre(200, [
    'base' => $base,
    'fichier' => "conseilplus-$base-" . date('Y-m-d-His') . '.sql',
    'sql' => export_sql($bdd, 'site vitrine', function (string $table, array $ligne): ?array {
        if ($table === 'reinitialisations' || ($table === 'reglages' && $ligne['cle'] === 'vapid_prive')) {
            return null;
        }
        if ($table === 'utilisateurs') {
            $ligne['mdp'] = '';
        }
        return $ligne;
    }),
]);
