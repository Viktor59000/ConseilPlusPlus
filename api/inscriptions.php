<?php
/**
 * api/inscriptions.php — API REST des inscriptions à un rendez-vous au guichet.
 *
 * GET    ?creneaux=1        Créneaux encore libres (public)                                → 200
 * POST                      Le client connecté prend rendez-vous (session + CSRF)          → 201 | 401 | 403 | 409 | 422 | 429
 * DELETE ?id=42             Le client connecté annule son rendez-vous (session + CSRF)     → 200 | 401 | 403 | 404 | 409
 * GET    ?statut=nouveau    Le logiciel de gestion liste les inscriptions (clé « lecture ») → 200 | 400
 * GET    ?id=42             Détail d'une inscription (clé « lecture »)                     → 200 | 404
 * PATCH  ?id=42             Statut (importe par défaut, confirme, annule…) + réponse       → 200 | 404 | 409 | 422
 *                           facultative (clé « ecriture »)
 *
 * Authentification du dépôt : la session du site (cookie), comme api/mes-conseils.php. L'identifiant du client vient
 * de $_SESSION, jamais du corps de la requête : un tiers ne peut pas inscrire quelqu'un d'autre. La page du Guichet
 * appelle cette route côté serveur en présentant la session du visiteur (deposer_inscription()), ou en JavaScript.
 */
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/inscriptions.php';
require_once __DIR__ . '/../includes/csrf.php';

// Client connecté au site (POST, DELETE) : 401 sans session, 403 sans jeton CSRF. Aucune session n'est créée
// pour un appel sans cookie (Le Bureau, clés d'application).
$client = function (): int {
    if (isset($_COOKIE[session_name()])) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        repondre(401, ['erreur' => 'Connectez-vous pour prendre rendez-vous au guichet.']);
    }
    if (!csrf_valide()) {
        repondre(403, ['erreur' => 'Jeton CSRF manquant ou invalide (en-tête X-CSRF-Token).']);
    }
    $utilisateur = (int) $_SESSION['user_id'];
    session_write_close(); // la session n'est plus modifiée : on libère son verrou
    return $utilisateur;
};

router([
    'GET' => function () use ($bdd) {
        if (isset($_GET['creneaux'])) {
            repondre(200, ['horaires' => HORAIRES_GUICHET, 'jours' => creneaux_libres($bdd)]);
        }
        exiger_cle();
        if (isset($_GET['id'])) {
            $inscription = lire_inscription($bdd, lire_id());
            $inscription ? repondre(200, $inscription) : repondre(404, ['erreur' => 'Inscription introuvable.']);
        }

        // Filtres facultatifs : statut, jour précis, ou à partir d'un jour (du)
        $conditions = [];
        $valeurs = [];
        if (isset($_GET['statut'])) {
            if (!in_array($_GET['statut'], STATUTS_INSCRIPTION, true)) {
                repondre(400, ['erreur' => 'Statut invalide (' . implode(', ', STATUTS_INSCRIPTION) . ').']);
            }
            $conditions[] = 'statut = :statut';
            $valeurs['statut'] = $_GET['statut'];
        }
        foreach (['jour' => 'jour = :jour', 'du' => 'jour >= :du'] as $parametre => $condition) {
            if (!isset($_GET[$parametre])) {
                continue;
            }
            $date = DateTime::createFromFormat('!Y-m-d', (string) $_GET[$parametre]);
            if (!$date || $date->format('Y-m-d') !== $_GET[$parametre]) {
                repondre(400, ['erreur' => "Paramètre $parametre invalide (format AAAA-MM-JJ)."]);
            }
            $conditions[] = $condition;
            $valeurs[$parametre] = $_GET[$parametre];
        }
        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $ordre = ' ORDER BY date_creation, id';

        // Sans ?page= : la liste d'un bloc (plafonnée à 500). Avec ?page= : pagination, comme api/contacts.php.
        if (!isset($_GET['page'])) {
            $stmt = $bdd->prepare('SELECT ' . COLONNES_INSCRIPTION . " FROM inscriptions$where$ordre LIMIT 500");
            $stmt->execute($valeurs);
            $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            repondre(200, ['total' => count($inscriptions), 'inscriptions' => $inscriptions]);
        }
        $page = lire_entier('page');
        $par_page = lire_entier('par_page', 1, 100);
        if (!$page || (isset($_GET['par_page']) && !$par_page)) {
            repondre(400, ['erreur' => 'Pagination invalide (page ≥ 1, par_page de 1 à 100).']);
        }
        $par_page ??= 20;
        $compte = $bdd->prepare("SELECT COUNT(*) FROM inscriptions$where");
        $compte->execute($valeurs);
        $total = (int) $compte->fetchColumn();
        $pages = max(1, (int) ceil($total / $par_page));
        $stmt = $bdd->prepare('SELECT ' . COLONNES_INSCRIPTION . " FROM inscriptions$where$ordre LIMIT $par_page OFFSET " . (($page - 1) * $par_page));
        $stmt->execute($valeurs);

        $lien = fn (int $p) => '<inscriptions.php?' . http_build_query(['page' => $p] + $_GET) . '>';
        $liens = [$lien(1) . '; rel="first"', $lien($pages) . '; rel="last"'];
        if ($page > 1) {
            $liens[] = $lien($page - 1) . '; rel="prev"';
        }
        if ($page < $pages) {
            $liens[] = $lien($page + 1) . '; rel="next"';
        }
        header('Link: ' . implode(', ', $liens));
        header("X-Total-Count: $total");
        repondre(200, ['total' => $total, 'page' => $page, 'par_page' => $par_page, 'pages' => $pages,
            'inscriptions' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    },

    'POST' => function () use ($bdd, $client) {
        $utilisateur = $client();
        limiter(5); // 5 dépôts par minute et par visiteur
        $compte = coordonnees_client($bdd, $utilisateur) ?? repondre(401, ['erreur' => 'Compte introuvable : reconnectez-vous.']);
        [$valeurs, $erreurs] = valider_inscription(lire_corps(), $compte);
        refuser_champs($erreurs);
        $resultat = creer_inscription($bdd, $utilisateur, $valeurs);
        if (($resultat['erreur'] ?? '') === 'complet') {
            repondre(409, ['erreur' => 'Vous avez déjà ' . INSCRIPTIONS_ACTIVES_MAX . ' rendez-vous à venir. Le cabinet ne reçoit pas plus souvent.']);
        }
        if (($resultat['erreur'] ?? '') === 'pris') {
            repondre(409, ['erreur' => 'Ce créneau vient d\'être pris. Choisissez-en un autre, tout aussi précis.']);
        }
        header("Location: inscriptions.php?id={$resultat['id']}");
        repondre(201, ['id' => $resultat['id'], 'statut' => 'nouveau', 'jour' => $valeurs['jour'], 'horaire' => $valeurs['horaire']]);
    },

    'DELETE' => function () use ($bdd, $client) {
        $utilisateur = $client();
        $id = lire_id();
        match (annuler_inscription_client($bdd, $id, $utilisateur)) {
            'annulee' => repondre(200, ['id' => $id, 'statut' => 'annule']),
            'impossible' => repondre(409, ['erreur' => 'Ce rendez-vous est déjà annulé ou passé.']),
            default => repondre(404, ['erreur' => 'Rendez-vous introuvable parmi les vôtres.']),
        };
    },

    'PATCH' => function () use ($bdd) {
        exiger_cle('ecriture');
        $id = lire_id();
        [$statut, $reponse, $erreurs] = valider_statut_inscription(lire_corps());
        refuser_champs($erreurs);
        match (changer_statut_inscription($bdd, $id, $statut, $reponse)) {
            'ok' => repondre(200, ['id' => $id, 'statut' => $statut, 'reponse' => lire_inscription($bdd, $id)['reponse']]),
            'pris' => repondre(409, ['erreur' => 'Le créneau de ce rendez-vous a été repris entre-temps : impossible de le réactiver.']),
            default => repondre(404, ['erreur' => 'Inscription introuvable.']),
        };
    },
]);
