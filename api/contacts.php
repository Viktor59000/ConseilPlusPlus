<?php
/**
 * api/contacts.php — API REST des demandes de contact.
 *
 * POST                      Le site vitrine envoie une demande (JSON ou formulaire)       → 201 | 422
 * GET    ?statut=nouveau    Le logiciel de gestion liste les demandes (clé requise)        → 200
 * GET    ?id=42             Détail d'une demande (clé requise)                             → 200 | 404
 * PATCH  ?id=42             Change le statut, par défaut « importe » (clé requise)         → 200 | 404 | 422
 * DELETE ?id=42             Supprime une demande traitée (clé requise)                     → 204 | 404
 *
 * Clé : en-tête X-API-Key (voir config.php).
 * POST est limité à 5 demandes par minute et par IP (429 au-delà).
 */
require("../includes/api.php");
require("../includes/contacts.php");

switch ($_SERVER['REQUEST_METHOD']) {

    case 'POST':
        limiter(5); // 5 demandes par minute et par visiteur (anti-spam)
        [$valeurs, $erreurs] = valider_contact(lire_corps());
        if ($erreurs) {
            repondre(422, ['erreur' => 'Champs invalides.', 'champs' => $erreurs]);
        }
        $stmt = $bdd->prepare('INSERT INTO contacts (nom, email, message, type_demande) VALUES (:nom, :email, :message, :type_demande)');
        $stmt->execute($valeurs);
        $id = (int) $bdd->lastInsertId();
        header("Location: contacts.php?id=$id");
        repondre(201, ['id' => $id, 'statut' => 'nouveau']);

    case 'GET':
        exiger_cle();
        if (isset($_GET['id'])) {
            $stmt = $bdd->prepare('SELECT * FROM contacts WHERE id = :id');
            $stmt->execute(['id' => lire_id()]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);
            $contact ? repondre(200, $contact) : repondre(404, ['erreur' => 'Demande introuvable.']);
        }
        $statut = $_GET['statut'] ?? null;
        if ($statut !== null && !in_array($statut, STATUTS, true)) {
            repondre(400, ['erreur' => 'Statut invalide (nouveau ou importe).']);
        }
        if ($statut === null) {
            $stmt = $bdd->query('SELECT * FROM contacts ORDER BY date_creation');
        } else {
            $stmt = $bdd->prepare('SELECT * FROM contacts WHERE statut = :statut ORDER BY date_creation');
            $stmt->execute(['statut' => $statut]);
        }
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        repondre(200, ['total' => count($contacts), 'contacts' => $contacts]);

    case 'PATCH':
        exiger_cle();
        $id = lire_id();
        $statut = lire_corps()['statut'] ?? 'importe';
        if (!in_array($statut, STATUTS, true)) {
            repondre(422, ['erreur' => 'Statut invalide (nouveau ou importe).']);
        }
        $stmt = $bdd->prepare('UPDATE contacts SET statut = :statut WHERE id = :id');
        $stmt->execute(['statut' => $statut, 'id' => $id]);
        $existe = $bdd->prepare('SELECT 1 FROM contacts WHERE id = :id');
        $existe->execute(['id' => $id]);
        $existe->fetchColumn() ? repondre(200, ['id' => $id, 'statut' => $statut]) : repondre(404, ['erreur' => 'Demande introuvable.']);

    case 'DELETE':
        exiger_cle();
        $stmt = $bdd->prepare('DELETE FROM contacts WHERE id = :id');
        $stmt->execute(['id' => lire_id()]);
        $stmt->rowCount() ? repondre(204) : repondre(404, ['erreur' => 'Demande introuvable.']);

    default:
        header('Allow: GET, POST, PATCH, DELETE');
        repondre(405, ['erreur' => 'Méthode non autorisée.']);
}
