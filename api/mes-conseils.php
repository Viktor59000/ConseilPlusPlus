<?php
/**
 * api/mes-conseils.php — API du carnet de conseils de l'utilisateur connecté.
 *
 * GET               Liste des conseils classés                                 → 200
 * POST              Classe un conseil {texte, dossier, source}                 → 201 | 409 | 422
 * DELETE ?id=42     Retire un conseil du carnet                                → 204 | 404
 *
 * Authentification par la session du site (cookie) : 401 si personne n'est connecté.
 * POST et DELETE exigent le jeton CSRF dans l'en-tête X-CSRF-Token.
 */
session_start();
require("../includes/api.php");
require("../includes/csrf.php");

const CARNET_MAX = 200;
const SOURCES = ['generateur', 'ia', 'secours'];

if (!isset($_SESSION['user_id'])) {
    repondre(401, ['erreur' => 'Connectez-vous pour accéder à votre carnet.']);
}
$utilisateur = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !csrf_valide()) {
    repondre(403, ['erreur' => 'Jeton CSRF manquant ou invalide (en-tête X-CSRF-Token).']);
}

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        $stmt = $bdd->prepare('SELECT id, texte, dossier, source, date_classement FROM carnet WHERE id_utilisateur = :u ORDER BY date_classement DESC, id DESC');
        $stmt->execute(['u' => $utilisateur]);
        $conseils = $stmt->fetchAll(PDO::FETCH_ASSOC);
        repondre(200, ['total' => count($conseils), 'conseils' => $conseils]);

    case 'POST':
        limiter(30);
        $corps = lire_corps();
        $texte = trim(preg_replace('/\s+/u', ' ', (string) ($corps['texte'] ?? '')));
        $dossier = trim((string) ($corps['dossier'] ?? ''));
        $source = (string) ($corps['source'] ?? 'generateur');

        $erreurs = [];
        if ($texte === '' || mb_strlen($texte) > 500) {
            $erreurs['texte'] = 'Le conseil doit faire entre 1 et 500 caractères.';
        }
        if (!preg_match('/^[0-9]{4}-[A-Z]{1,2}$/', $dossier)) {
            $erreurs['dossier'] = 'Numéro de dossier invalide (ex. 4821-K).';
        }
        if (!in_array($source, SOURCES, true)) {
            $erreurs['source'] = 'Source invalide (generateur, ia ou secours).';
        }
        if ($erreurs) {
            repondre(422, ['erreur' => 'Champs invalides.', 'champs' => $erreurs]);
        }

        $stmt = $bdd->prepare('SELECT COUNT(*) FROM carnet WHERE id_utilisateur = :u');
        $stmt->execute(['u' => $utilisateur]);
        if ($stmt->fetchColumn() >= CARNET_MAX) {
            repondre(409, ['erreur' => 'Carnet plein (' . CARNET_MAX . ' conseils). Faites un peu de tri.']);
        }

        // Déjà classé ? (même texte et même dossier) : on ne le duplique pas
        $stmt = $bdd->prepare('SELECT id FROM carnet WHERE id_utilisateur = :u AND dossier = :d AND texte = :t');
        $stmt->execute(['u' => $utilisateur, 'd' => $dossier, 't' => $texte]);
        if ($id = $stmt->fetchColumn()) {
            repondre(200, ['id' => (int) $id, 'deja_classe' => true]);
        }

        $stmt = $bdd->prepare('INSERT INTO carnet (id_utilisateur, texte, dossier, source) VALUES (:u, :t, :d, :s)');
        $stmt->execute(['u' => $utilisateur, 't' => $texte, 'd' => $dossier, 's' => $source]);
        repondre(201, ['id' => (int) $bdd->lastInsertId()]);

    case 'DELETE':
        $stmt = $bdd->prepare('DELETE FROM carnet WHERE id = :id AND id_utilisateur = :u');
        $stmt->execute(['id' => lire_id(), 'u' => $utilisateur]);
        $stmt->rowCount() ? repondre(204) : repondre(404, ['erreur' => 'Conseil introuvable dans votre carnet.']);

    default:
        header('Allow: GET, POST, DELETE');
        repondre(405, ['erreur' => 'Méthode non autorisée.']);
}
