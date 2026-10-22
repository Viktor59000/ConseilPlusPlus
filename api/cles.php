<?php
/**
 * api/cles.php — Clés d'API par application (réservé à la clé maîtresse, celle du Bureau).
 *
 * GET                 Liste des clés (jamais la clé elle-même : seulement son début)    → 200
 * POST                Crée une clé { nom, droits: ["lecture", "ecriture"] }              → 201 | 422
 *                     La clé n'est renvoyée qu'une seule fois : seule son empreinte SHA-256 est gardée.
 * DELETE ?id=3        Révoque une clé (elle cesse aussitôt de fonctionner) ; une seconde fois : l'efface → 204 | 404
 */
require_once __DIR__ . '/../includes/api.php';

const DROITS = ['lecture', 'ecriture'];

exiger_cle('admin');

router([
    'GET' => function () use ($bdd) {
        $cles = $bdd->query('SELECT id, nom, apercu, droits, date_creation, derniere_utilisation, revoquee FROM api_cles ORDER BY revoquee, date_creation DESC')
            ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cles as &$c) {
            $c['id'] = (int) $c['id'];
            $c['droits'] = explode(',', $c['droits']);
            $c['revoquee'] = (bool) $c['revoquee'];
        }
        repondre(200, ['total' => count($cles), 'cles' => $cles]);
    },

    'POST' => function () use ($bdd) {
        $corps = lire_corps();
        $nom = trim((string) ($corps['nom'] ?? ''));
        $droits = $corps['droits'] ?? ['lecture'];
        $erreurs = [];
        // Noms réservés : ceux que le journal donne à la clé maîtresse, au site lui-même et au public
        if (!preg_match('/^[\p{L}\p{N} ._-]{2,40}$/u', $nom) || in_array(mb_strtolower($nom), [APPLICATION_GESTION, 'site', 'public'], true)) {
            $erreurs['nom'] = "2 à 40 lettres, chiffres, espaces, points ou tirets (gestion, site et public sont réservés).";
        }
        if (!is_array($droits) || !$droits || array_diff($droits, DROITS)) {
            $erreurs['droits'] = 'Liste de droits : lecture et/ou ecriture.';
        }
        refuser_champs($erreurs);
        $droits = array_values(array_intersect(DROITS, $droits));
        $cle = 'cp_' . bin2hex(random_bytes(24));
        $stmt = $bdd->prepare('INSERT INTO api_cles (nom, empreinte, apercu, droits) VALUES (:nom, :empreinte, :apercu, :droits)');
        $stmt->execute(['nom' => $nom, 'empreinte' => hash('sha256', $cle), 'apercu' => substr($cle, 0, 9), 'droits' => implode(',', $droits)]);
        repondre(201, ['id' => (int) $bdd->lastInsertId(), 'nom' => $nom, 'droits' => $droits, 'cle' => $cle,
            'avertissement' => "Notez cette clé maintenant : elle ne sera plus jamais affichée."]);
    },

    'DELETE' => function () use ($bdd) {
        // Premier DELETE : révocation (la clé reste dans la liste, pour l'historique). Second DELETE : effacement.
        $id = lire_id();
        $stmt = $bdd->prepare('UPDATE api_cles SET revoquee = 1 WHERE id = :id AND revoquee = 0');
        $stmt->execute(['id' => $id]);
        if (!$stmt->rowCount()) {
            $stmt = $bdd->prepare('DELETE FROM api_cles WHERE id = :id');
            $stmt->execute(['id' => $id]);
        }
        $stmt->rowCount() ? repondre(204) : repondre(404, ['erreur' => 'Clé introuvable.']);
    },
]);
