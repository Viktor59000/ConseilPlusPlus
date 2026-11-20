<?php
/**
 * Règles métier des inscriptions à un rendez-vous au guichet (partagées par l'API api/inscriptions.php,
 * la future page du Guichet et « Mon dossier »).
 *
 * Statuts côté site :
 *   nouveau  : déposée au guichet par le client connecté ;
 *   importe  : relevée par Le Bureau (Synchro) — affichée « en instruction » ;
 *   confirme : rendez-vous confirmé par le cabinet (réponse facultative) ;
 *   annule   : annulé par le client (« Mon dossier ») ou par le cabinet (réponse facultative). Le créneau se libère.
 */
require_once __DIR__ . '/http.php'; // appeler_api(), url_interne() : dépôt par l'API depuis la page du Guichet

const STATUTS_INSCRIPTION = ['nouveau', 'importe', 'confirme', 'annule'];
const LIBELLES_INSCRIPTION = [
    'nouveau' => 'Déposée au guichet',
    'importe' => 'En instruction',
    'confirme' => 'Rendez-vous confirmé',
    'annule' => 'Rendez-vous annulé',
];
// Horaires du guichet : le cabinet reçoit à la minute près, jamais à l'heure ronde (réputation de sérieux)
const HORAIRES_GUICHET = ['09:07', '10:41', '14:12', '14:27', '14:47', '16:58'];
const JOURS_PROPOSES = 10;           // jours ouvrés proposés, à partir de demain
const INSCRIPTIONS_ACTIVES_MAX = 3;  // rendez-vous à venir non annulés par client
const MESSAGE_INSCRIPTION_MAX = 1000;
const REPONSE_INSCRIPTION_MAX = 500;
// Colonnes renvoyées par l'API et lues par « Mon dossier » (horaire au format HH:MM)
const COLONNES_INSCRIPTION = "id, id_utilisateur, nom, prenom, email, telephone, jour, TIME_FORMAT(horaire, '%H:%i') AS horaire,
                              statut, message, reponse, date_creation";

/** Les prochains jours ouvrés (lundi à vendredi), à partir du lendemain de $depuis (aujourd'hui par défaut). */
function jours_ouvres_a_venir(int $nombre = JOURS_PROPOSES, ?string $depuis = null): array
{
    $jour = new DateTimeImmutable($depuis ?? 'today');
    $jours = [];
    while (count($jours) < $nombre) {
        $jour = $jour->modify('+1 day');
        if ((int) $jour->format('N') <= 5) {
            $jours[] = $jour->format('Y-m-d');
        }
    }
    return $jours;
}

/** Créneaux encore libres : ['AAAA-MM-JJ' => ['09:07', …], …] (un jour complet reste présent, avec une liste vide). */
function creneaux_libres(PDO $bdd): array
{
    $jours = jours_ouvres_a_venir();
    $stmt = $bdd->prepare("SELECT jour, TIME_FORMAT(horaire, '%H:%i') AS horaire FROM inscriptions
                           WHERE occupe = 1 AND jour BETWEEN ? AND ?");
    $stmt->execute([reset($jours), end($jours)]);
    $pris = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $pris[$c['jour']][] = $c['horaire'];
    }
    $libres = [];
    foreach ($jours as $jour) {
        $libres[$jour] = array_values(array_diff(HORAIRES_GUICHET, $pris[$jour] ?? []));
    }
    return $libres;
}

/** Téléphone français au format souple (06 12 34 56 78, 06.12.34.56.78, +33 6 12 34 56 78…) → « 06 12 34 56 78 », sinon null. */
function normaliser_telephone(string $telephone): ?string
{
    $chiffres = preg_replace('/[\s.\-()]/', '', $telephone);
    $chiffres = preg_replace('/^(\+33|0033)/', '0', $chiffres);
    if (!preg_match('/^0[1-9][0-9]{8}$/', $chiffres)) {
        return null;
    }
    return implode(' ', str_split($chiffres, 2));
}

/**
 * Nettoie et valide une inscription. $defauts : coordonnées du compte (nom, prenom, email), utilisées quand
 * le champ est absent. Retourne [$valeurs, $erreurs] ; $erreurs est vide si tout est correct.
 */
function valider_inscription(array $source, array $defauts = []): array
{
    $valeurs = [];
    foreach (['nom', 'prenom', 'email', 'telephone', 'jour', 'horaire', 'message'] as $champ) {
        $valeur = $source[$champ] ?? $defauts[$champ] ?? '';
        $valeurs[$champ] = is_scalar($valeur) ? trim((string) $valeur) : '';
    }
    $valeurs['horaire'] = substr($valeurs['horaire'], 0, 5); // « 14:12:00 » accepté

    $erreurs = [];
    if ($valeurs['nom'] === '' || mb_strlen($valeurs['nom']) > 100) {
        $erreurs['nom'] = 'Indiquez votre nom (100 caractères maximum).';
    }
    if ($valeurs['prenom'] === '' || mb_strlen($valeurs['prenom']) > 100) {
        $erreurs['prenom'] = 'Indiquez votre prénom (100 caractères maximum).';
    }
    if (!filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($valeurs['email']) > 150) {
        $erreurs['email'] = 'Indiquez une adresse email valide.';
    }
    $telephone = normaliser_telephone($valeurs['telephone']);
    if ($telephone === null) {
        $erreurs['telephone'] = 'Indiquez un numéro de téléphone français (10 chiffres, ex. 06 12 34 56 78).';
    } else {
        $valeurs['telephone'] = $telephone;
    }
    $date = DateTime::createFromFormat('!Y-m-d', $valeurs['jour']);
    if (!$date || $date->format('Y-m-d') !== $valeurs['jour']) {
        $erreurs['jour'] = 'Choisissez un jour (format AAAA-MM-JJ).';
    } elseif (!in_array($valeurs['jour'], jours_ouvres_a_venir(), true)) {
        $erreurs['jour'] = 'Le guichet reçoit du lundi au vendredi, à partir de demain et pour les ' . JOURS_PROPOSES . ' jours ouvrés à venir.';
    }
    if (!in_array($valeurs['horaire'], HORAIRES_GUICHET, true)) {
        $erreurs['horaire'] = 'Choisissez un des horaires du guichet (' . implode(', ', HORAIRES_GUICHET) . ').';
    }
    if (mb_strlen($valeurs['message']) > MESSAGE_INSCRIPTION_MAX) {
        $erreurs['message'] = 'Votre problème doit tenir en ' . MESSAGE_INSCRIPTION_MAX . ' caractères. Le cabinet résume, lui aussi.';
    }
    if ($valeurs['message'] === '') {
        $valeurs['message'] = null;
    }
    return [$valeurs, $erreurs];
}

/** Coordonnées du compte (nom, prenom, email), pour pré-remplir le formulaire du Guichet ; null si le compte n'existe plus. */
function coordonnees_client(PDO $bdd, int $utilisateur): ?array
{
    $stmt = $bdd->prepare('SELECT nom, prenom, mail AS email FROM utilisateurs WHERE id = ?');
    $stmt->execute([$utilisateur]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Enregistre une inscription validée (valider_inscription()) pour le client connecté.
 * Retourne ['id' => n] ou ['erreur' => 'complet' | 'pris'] (trop de rendez-vous à venir, ou créneau déjà retenu).
 */
function creer_inscription(PDO $bdd, int $utilisateur, array $valeurs): array
{
    $stmt = $bdd->prepare('SELECT COUNT(*) FROM inscriptions WHERE id_utilisateur = ? AND occupe = 1 AND jour >= CURDATE()');
    $stmt->execute([$utilisateur]);
    if ($stmt->fetchColumn() >= INSCRIPTIONS_ACTIVES_MAX) {
        return ['erreur' => 'complet'];
    }
    try {
        $bdd->prepare('INSERT INTO inscriptions (id_utilisateur, nom, prenom, email, telephone, jour, horaire, message)
                       VALUES (:u, :nom, :prenom, :email, :telephone, :jour, :horaire, :message)')
            ->execute(['u' => $utilisateur] + $valeurs);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? 0) === 1062) { // index unique (jour, horaire, occupe) : créneau déjà retenu
            return ['erreur' => 'pris'];
        }
        throw $e;
    }
    return ['id' => (int) $bdd->lastInsertId()];
}

/** Une inscription (colonnes publiques), ou null. */
function lire_inscription(PDO $bdd, int $id): ?array
{
    $stmt = $bdd->prepare('SELECT ' . COLONNES_INSCRIPTION . ' FROM inscriptions WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/** Inscriptions d'un client (« Mon dossier »), les rendez-vous les plus proches d'abord puis les passés. */
function inscriptions_du_client(PDO $bdd, int $utilisateur): array
{
    $stmt = $bdd->prepare('SELECT ' . COLONNES_INSCRIPTION . ' FROM inscriptions WHERE id_utilisateur = ?
                           ORDER BY jour < CURDATE(), IF(jour < CURDATE(), DATEDIFF(CURDATE(), jour), DATEDIFF(jour, CURDATE())), horaire, id');
    $stmt->execute([$utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Le client annule son rendez-vous (seulement le sien, à venir et pas déjà annulé). Le créneau se libère ; Le Bureau
 * l'apprend à la synchro suivante. Retourne 'annulee', 'introuvable' (pas à lui) ou 'impossible' (passé ou déjà annulé).
 */
function annuler_inscription_client(PDO $bdd, int $id, int $utilisateur): string
{
    $stmt = $bdd->prepare("UPDATE inscriptions SET statut = 'annule', occupe = NULL
                           WHERE id = ? AND id_utilisateur = ? AND statut <> 'annule' AND jour >= CURDATE()");
    $stmt->execute([$id, $utilisateur]);
    if ($stmt->rowCount() > 0) {
        return 'annulee';
    }
    $stmt = $bdd->prepare('SELECT 1 FROM inscriptions WHERE id = ? AND id_utilisateur = ?');
    $stmt->execute([$id, $utilisateur]);
    return $stmt->fetchColumn() ? 'impossible' : 'introuvable';
}

/**
 * Valide un changement de statut (PATCH de l'API, par Le Bureau). La réponse est facultative : absente, l'ancienne
 * est gardée ; chaîne vide, elle est effacée. Retourne [$statut, $reponse (null = inchangée), $erreurs].
 */
function valider_statut_inscription(array $corps): array
{
    $statut = $corps['statut'] ?? 'importe';
    $reponse = array_key_exists('reponse', $corps) ? (is_string($corps['reponse']) ? trim($corps['reponse']) : false) : null;
    $erreurs = [];
    if (!in_array($statut, STATUTS_INSCRIPTION, true)) {
        $erreurs['statut'] = 'Statut invalide (' . implode(', ', STATUTS_INSCRIPTION) . ').';
    }
    if ($reponse === false || ($reponse !== null && mb_strlen($reponse) > REPONSE_INSCRIPTION_MAX)) {
        $erreurs['reponse'] = 'Réponse du cabinet : texte de ' . REPONSE_INSCRIPTION_MAX . ' caractères au plus.';
    }
    return [$statut, $reponse === false ? null : $reponse, $erreurs];
}

/**
 * Applique un changement de statut. Retourne 'ok', 'introuvable', ou 'pris' (réactivation d'un rendez-vous annulé
 * dont le créneau a été repris entre-temps).
 */
function changer_statut_inscription(PDO $bdd, int $id, string $statut, ?string $reponse): string
{
    if (!lire_inscription($bdd, $id)) {
        return 'introuvable';
    }
    try {
        $bdd->prepare('UPDATE inscriptions SET statut = :statut, occupe = IF(:annule, NULL, 1),
                       reponse = IF(:garder, reponse, :reponse) WHERE id = :id')
            ->execute(['statut' => $statut, 'annule' => (int) ($statut === 'annule'), 'garder' => (int) ($reponse === null),
                'reponse' => $reponse === '' ? null : $reponse, 'id' => $id]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? 0) === 1062) {
            return 'pris';
        }
        throw $e;
    }
    return 'ok';
}

/** « mardi 24 novembre 2026 à 14 h 12 » (affichage de « Mon dossier » et de l'attestation de dépôt). */
function libelle_rendez_vous(string $jour, string $horaire): string
{
    $date = (new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris'))
        ->format(new DateTime($jour));
    return $date . ' à ' . str_replace(':', ' h ', substr($horaire, 0, 5));
}

/**
 * Dépôt d'une inscription depuis une page du site (le Guichet), sans JavaScript : la page appelle l'API côté serveur,
 * comme contact.php, en lui présentant la session du visiteur (cookie + jeton CSRF). L'identifiant du client est
 * donc lu par l'API dans la session, jamais dans le corps de la requête.
 * La session est refermée pendant l'appel : l'API rouvre la même, et le fichier de session ne doit pas rester verrouillé.
 * Retourne la réponse de l'API (['code' => 201 | 401 | 403 | 409 | 422 | 429…, 'data' => …]).
 */
function deposer_inscription(array $champs): array
{
    $entetes = [
        'Cookie: ' . session_name() . '=' . session_id(),
        'X-CSRF-Token: ' . csrf_jeton(),
        'X-Client-IP: ' . ($_SERVER['REMOTE_ADDR'] ?? ''), // limite de débit par visiteur (appel local, voir ip_client())
    ];
    session_write_close();
    $reponse = appeler_api('POST', url_interne() . '/api/inscriptions.php', $champs, $entetes);
    session_start();
    return $reponse;
}
