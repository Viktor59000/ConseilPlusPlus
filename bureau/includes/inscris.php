<?php
/**
 * Le Bureau — inscriptions aux rendez-vous du guichet (table inscris) : Synchro par l'API, statuts renvoyés au site,
 * planning de la semaine, filtres et export.
 *
 * Correspondance des statuts (Le Bureau → site) : a_traiter → importe, confirme → confirme, annule → annule.
 */
require_once __DIR__ . '/api.php';

const STATUTS_INSCRIS = [
    'a_traiter' => 'À traiter',
    'confirme' => 'Confirmé',
    'annule' => 'Annulé',
];
const STATUT_SITE_INSCRIS = ['a_traiter' => 'importe', 'confirme' => 'confirme', 'annule' => 'annule'];
// Réponse proposée à l'agent, renvoyée au site avec le statut (le client la lit dans « Mon dossier »)
const REPONSES_INSCRIS = [
    'a_traiter' => '',
    'confirme' => 'Rendez-vous confirmé. Présentez-vous au guichet trois minutes avant l\'heure : ni deux, ni quatre.',
    'annule' => 'Rendez-vous annulé par le cabinet. Le créneau a été attribué à un dossier plus moyen que le vôtre.',
];
const REPONSE_INSCRIS_MAX = 500;

/**
 * Synchro : lit les inscriptions « nouveau » du site, les range dans inscris (sans doublon : remote_id unique),
 * puis les marque « importe » sur le site. Reporte aussi les annulations faites par les clients sur le site.
 * Une inscription dont le marquage a échoué reste « nouveau » sur le site et sera re-marquée à la synchro suivante.
 * Retourne ['importees', 'deja', 'annulees', 'erreurs' => n, 'detail' => texte] et trace l'opération au journal.
 */
function synchroniser_inscriptions(PDO $bdd, ?int $id_agent): array
{
    $compte = ['importees' => 0, 'deja' => 0, 'annulees' => 0, 'erreurs' => 0];
    $liste = api('GET', 'inscriptions.php?statut=nouveau');
    if ($liste['code'] !== 200 || !isset($liste['data']['inscriptions'])) {
        return journaliser_synchro($bdd, ['erreurs' => 1] + $compte, message_erreur_api($liste) . '.', $id_agent);
    }

    $inserer = $bdd->prepare('INSERT IGNORE INTO inscris (remote_id, nom, prenom, email, telephone, jour, horaire, create_at)
                              VALUES (:remote_id, :nom, :prenom, :email, :telephone, :jour, :horaire, :create_at)');
    foreach ($liste['data']['inscriptions'] as $i) {
        $inserer->execute([
            'remote_id' => (int) $i['id'], 'nom' => $i['nom'], 'prenom' => $i['prenom'], 'email' => $i['email'],
            'telephone' => $i['telephone'], 'jour' => $i['jour'], 'horaire' => $i['horaire'], 'create_at' => $i['date_creation'],
        ]);
        $inserer->rowCount() > 0 ? $compte['importees']++ : $compte['deja']++;
        if (api('PATCH', 'inscriptions.php?id=' . (int) $i['id'], ['statut' => 'importe'])['code'] !== 200) {
            $compte['erreurs']++;
        }
    }

    // Rendez-vous à venir annulés par les clients sur le site (« Mon dossier ») : Le Bureau les passe « annule »
    $annulees = api('GET', 'inscriptions.php?statut=annule&du=' . date('Y-m-d'));
    if ($annulees['code'] === 200 && isset($annulees['data']['inscriptions'])) {
        $annuler = $bdd->prepare("UPDATE inscris SET statut = 'annule' WHERE remote_id = ? AND statut <> 'annule'");
        foreach ($annulees['data']['inscriptions'] as $i) {
            $annuler->execute([(int) $i['id']]);
            $compte['annulees'] += $annuler->rowCount();
        }
    } else {
        $compte['erreurs']++;
    }

    $total = count($liste['data']['inscriptions']);
    $detail = ($total === 0 ? 'Aucune nouvelle inscription sur le site.'
            : "$total inscription(s) nouvelle(s) sur le site : {$compte['importees']} importée(s), {$compte['deja']} déjà présente(s).")
        . ($compte['annulees'] ? " {$compte['annulees']} annulation(s) reportée(s)." : '');
    return journaliser_synchro($bdd, $compte, $detail, $id_agent);
}

/** Trace une synchro dans le journal du Bureau (table releves, nature « synchro ») et renvoie son compte rendu. */
function journaliser_synchro(PDO $bdd, array $compte, string $detail, ?int $id_agent): array
{
    $bdd->prepare("INSERT INTO releves (nature, importes, erreurs, detail, id_agent) VALUES ('synchro', ?, ?, ?, ?)")
        ->execute([$compte['importees'], $compte['erreurs'], mb_substr($detail, 0, 255), $id_agent]);
    return $compte + ['detail' => $detail];
}

/**
 * Change le statut d'un inscrit et renvoie la décision au site (PATCH /api/inscriptions.php : statut + réponse).
 * Le statut local ne change que si le site a reçu la décision (ou si l'inscription n'y existe plus : 404).
 * Retourne 'publie', 'absente' (supprimée du site), 'conflit' (créneau repris : réactivation impossible) ou 'erreur'.
 */
function changer_statut_inscrit(PDO $bdd, array $inscrit, string $statut, string $reponse): string
{
    $r = api('PATCH', 'inscriptions.php?id=' . (int) $inscrit['remote_id'], ['statut' => STATUT_SITE_INSCRIS[$statut], 'reponse' => $reponse]);
    if ($r['code'] === 200 || $r['code'] === 404) {
        $bdd->prepare('UPDATE inscris SET statut = ? WHERE id = ?')->execute([$statut, $inscrit['id']]);
        return $r['code'] === 200 ? 'publie' : 'absente';
    }
    return $r['code'] === 409 ? 'conflit' : 'erreur';
}

/** Phrase de compte rendu d'un changement de statut (changer_statut_inscrit()). */
const COMPTE_RENDU_INSCRIS = [
    'publie' => 'Le site a reçu la décision : le client la lira dans « Mon dossier ».',
    'absente' => 'L\'inscription n\'existe plus sur le site (compte supprimé) : seul Le Bureau est à jour.',
    'conflit' => 'Le créneau a été repris sur le site entre-temps : impossible de réactiver ce rendez-vous.',
    'erreur' => 'Le site ne répond pas : rien n\'a changé. Réessayez dans une minute.',
];

/** Un inscrit de la base du Bureau, ou null. */
function lire_inscrit(PDO $bdd, int $id): ?array
{
    $stmt = $bdd->prepare('SELECT * FROM inscris WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/** Critères de la liste des inscrits, lus dans $source ($_GET, ou $_POST pour l'export) : statut, jour, q. */
function criteres_inscris(array $source): array
{
    $lire = fn (string $cle) => is_string($source[$cle] ?? null) ? trim($source[$cle]) : '';
    $jour = DateTime::createFromFormat('!Y-m-d', $lire('jour'));
    return [
        'statut' => array_key_exists($lire('statut'), STATUTS_INSCRIS) ? $lire('statut') : '',
        'jour' => $jour && $jour->format('Y-m-d') === $lire('jour') ? $lire('jour') : '',
        'q' => mb_substr($lire('q'), 0, 100),
    ];
}

/** Inscrits correspondant aux critères (criteres_inscris()), par date de rendez-vous. */
function filtrer_inscris(PDO $bdd, array $criteres, int $limite = 500): array
{
    $conditions = [];
    $params = [];
    if ($criteres['statut'] !== '') {
        $conditions[] = 'statut = :statut';
        $params['statut'] = $criteres['statut'];
    }
    if ($criteres['jour'] !== '') {
        $conditions[] = 'jour = :jour';
        $params['jour'] = $criteres['jour'];
    }
    if ($criteres['q'] !== '') {
        $motif = '%' . addcslashes($criteres['q'], '%_\\') . '%';
        $conditions[] = '(nom LIKE :q1 OR prenom LIKE :q2 OR email LIKE :q3 OR telephone LIKE :q4)';
        $params += ['q1' => $motif, 'q2' => $motif, 'q3' => $motif, 'q4' => $motif];
    }
    $stmt = $bdd->prepare('SELECT * FROM inscris' . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '')
        . ' ORDER BY jour, horaire, id LIMIT ' . max(1, $limite));
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Lundi de la semaine de $date (AAAA-MM-JJ valide), sinon de la semaine en cours (la suivante le week-end). */
function lundi_de(?string $date): string
{
    $jour = is_string($date) ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
    if (!$jour || $jour->format('Y-m-d') !== $date) {
        $jour = new DateTimeImmutable('today');
        $jour = (int) $jour->format('N') >= 6 ? $jour->modify('+2 day') : $jour;
    }
    return $jour->modify('monday this week')->format('Y-m-d');
}

/**
 * Planning d'une semaine (lundi à vendredi) : ['jours' => [AAAA-MM-JJ…], 'horaires' => ['09:07'…],
 * 'grille' => [jour][horaire] => inscrits non annulés, 'liste' => tous les inscrits de la semaine].
 * Les horaires sont ceux du guichet (API publique des créneaux) complétés par ceux des rendez-vous enregistrés.
 */
function planning_semaine(PDO $bdd, string $lundi): array
{
    $jours = [];
    for ($i = 0; $i < 5; $i++) {
        $jours[] = date('Y-m-d', strtotime("$lundi +$i day"));
    }
    $stmt = $bdd->prepare("SELECT *, TIME_FORMAT(horaire, '%H:%i') AS horaire FROM inscris WHERE jour BETWEEN ? AND ? ORDER BY jour, horaire, id");
    $stmt->execute([$jours[0], $jours[4]]);
    $liste = $stmt->fetchAll();

    $creneaux = api('GET', 'inscriptions.php?creneaux=1', null, false, 5);
    $horaires = array_filter((array) ($creneaux['data']['horaires'] ?? []), 'is_string');
    $grille = [];
    foreach ($liste as $i) {
        $horaires[] = $i['horaire'];
        if ($i['statut'] !== 'annule') {
            $grille[$i['jour']][$i['horaire']][] = $i;
        }
    }
    $horaires = array_unique($horaires);
    sort($horaires);
    return ['jours' => $jours, 'horaires' => $horaires, 'grille' => $grille, 'liste' => $liste];
}

/** « mardi 24 novembre » (planning, listes). */
function jour_long(string $jour): string
{
    return (new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Europe/Paris', null, 'EEEE d MMMM'))
        ->format(new DateTime($jour));
}

/** « 14 h 12 » depuis « 14:12 » ou « 14:12:00 ». */
function horaire_court(string $horaire): string
{
    return str_replace(':', ' h ', substr($horaire, 0, 5));
}
