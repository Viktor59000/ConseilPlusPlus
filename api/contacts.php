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
        // Filtres facultatifs
        $conditions = [];
        $valeurs = [];
        if ($statut !== null) {
            $conditions[] = 'statut = :statut';
            $valeurs['statut'] = $statut;
        }
        if (isset($_GET['type'])) {
            if (!in_array($_GET['type'], TYPES_DEMANDE, true)) {
                repondre(400, ['erreur' => 'Type de demande invalide.']);
            }
            $conditions[] = 'type_demande = :type';
            $valeurs['type'] = $_GET['type'];
        }
        if (isset($_GET['q']) && trim($_GET['q']) !== '') {
            $conditions[] = '(nom LIKE :q OR email LIKE :q2 OR message LIKE :q3)';
            $motif = '%' . addcslashes(mb_substr(trim($_GET['q']), 0, 100), '%_\\') . '%';
            $valeurs += ['q' => $motif, 'q2' => $motif, 'q3' => $motif];
        }
        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

        // Tri (liste blanche) : date (par défaut), -date (récentes d'abord), nom, -nom
        $tris = ['date' => 'date_creation, id', '-date' => 'date_creation DESC, id DESC', 'nom' => 'nom, id', '-nom' => 'nom DESC, id DESC'];
        $tri = $_GET['tri'] ?? 'date';
        if (!isset($tris[$tri])) {
            repondre(400, ['erreur' => 'Tri invalide (date, -date, nom ou -nom).']);
        }
        $ordre = ' ORDER BY ' . $tris[$tri];

        // Sans ?page= : toute la liste (compatible avec Le Bureau). Avec ?page= : pagination.
        if (!isset($_GET['page'])) {
            $stmt = $bdd->prepare("SELECT * FROM contacts$where$ordre");
            $stmt->execute($valeurs);
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            repondre(200, ['total' => count($contacts), 'contacts' => $contacts]);
        }
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $par_page = filter_input(INPUT_GET, 'par_page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
        if (!$page || (isset($_GET['par_page']) && !$par_page)) {
            repondre(400, ['erreur' => 'Pagination invalide (page ≥ 1, par_page de 1 à 100).']);
        }
        $par_page = $par_page ?: 20;
        $compte = $bdd->prepare("SELECT COUNT(*) FROM contacts$where");
        $compte->execute($valeurs);
        $total = (int) $compte->fetchColumn();
        $pages = max(1, (int) ceil($total / $par_page));
        $stmt = $bdd->prepare("SELECT * FROM contacts$where$ordre LIMIT $par_page OFFSET " . (($page - 1) * $par_page));
        $stmt->execute($valeurs);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // En-tête Link (RFC 8288) : pages suivante, précédente, première et dernière
        $lien = fn (int $p) => '<contacts.php?' . http_build_query(['page' => $p] + $_GET) . '>';
        $liens = [$lien(1) . '; rel="first"', $lien($pages) . '; rel="last"'];
        if ($page > 1) {
            $liens[] = $lien($page - 1) . '; rel="prev"';
        }
        if ($page < $pages) {
            $liens[] = $lien($page + 1) . '; rel="next"';
        }
        header('Link: ' . implode(', ', $liens));
        header("X-Total-Count: $total");
        repondre(200, ['total' => $total, 'page' => $page, 'par_page' => $par_page, 'pages' => $pages, 'contacts' => $contacts]);

    case 'PATCH':
        exiger_cle('ecriture');
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
        exiger_cle('ecriture');
        $stmt = $bdd->prepare('DELETE FROM contacts WHERE id = :id');
        $stmt->execute(['id' => lire_id()]);
        $stmt->rowCount() ? repondre(204) : repondre(404, ['erreur' => 'Demande introuvable.']);

    default:
        header('Allow: GET, POST, PATCH, DELETE');
        repondre(405, ['erreur' => 'Méthode non autorisée.']);
}
