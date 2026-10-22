<?php
/**
 * Le Bureau — règles métier : relevé du courrier (import via l'API), réponses, libellés.
 */
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/../../includes/courriel.php'; // gabarit et envoi des courriels, partagés avec le site
require_once __DIR__ . '/../../includes/textes.php';   // delai_estime(), partagé avec l'accusé de réception du site

const STATUTS_DOSSIER = [
    'a_traiter' => 'À traiter',
    'en_cours' => 'En cours d\'examen',
    'classe' => 'Classé',
];

/**
 * Relève le courrier : récupère les demandes « nouveau » du site, les enregistre dans la base du Bureau,
 * puis les marque « importe » sur le site. Sans risque de doublon : un dossier déjà présent (id_site unique)
 * n'est pas recréé, et une demande dont le marquage a échoué sera simplement re-marquée au relevé suivant.
 * Retourne ['importes' => n, 'erreurs' => n, 'detail' => texte].
 */
function relever_courrier(PDO $bdd, ?int $id_agent): array
{
    $liste = api('GET', 'contacts.php?statut=nouveau');
    if ($liste['code'] !== 200 || !isset($liste['data']['contacts'])) {
        return journaliser_releve($bdd, 0, 1, message_erreur_api($liste) . '.', $id_agent);
    }

    $inserer = $bdd->prepare('INSERT IGNORE INTO dossiers (id_site, nom, email, type_demande, message, date_demande)
                              VALUES (:id_site, :nom, :email, :type_demande, :message, :date_demande)');
    $importes = 0;
    $erreurs = 0;
    foreach ($liste['data']['contacts'] as $c) {
        $inserer->execute([
            'id_site' => (int) $c['id'], 'nom' => $c['nom'], 'email' => $c['email'], 'type_demande' => $c['type_demande'],
            'message' => $c['message'], 'date_demande' => $c['date_creation'],
        ]);
        $nouveau = $inserer->rowCount() > 0;
        $marque = api('PATCH', 'contacts.php?id=' . (int) $c['id'], ['statut' => 'importe']);
        if ($marque['code'] !== 200) {
            $erreurs++;
        } elseif ($nouveau) {
            $importes++;
        }
    }

    $total = count($liste['data']['contacts']);
    $detail = $total === 0 ? 'Boîte aux lettres vide.' : "$total demande(s) trouvée(s) sur le site.";
    return journaliser_releve($bdd, $importes, $erreurs, $detail, $id_agent);
}

function journaliser_releve(PDO $bdd, int $importes, int $erreurs, string $detail, ?int $id_agent): array
{
    $bdd->prepare('INSERT INTO releves (importes, erreurs, detail, id_agent) VALUES (?, ?, ?, ?)')
        ->execute([$importes, $erreurs, mb_substr($detail, 0, 255), $id_agent]);
    return ['importes' => $importes, 'erreurs' => $erreurs, 'detail' => $detail];
}

/** Un conseil du cabinet, tiré par l'API publique du site (texte de secours si le site ne répond pas). */
function conseil_du_cabinet(): string
{
    $r = api('GET', 'conseils.php', null, false);
    return $r['data']['conseil'] ?? 'Patientez. Si cela ne suffit pas, patientez davantage.';
}

/** Tons de réponse proposés à l'agent (dossier.php). */
const TONS_REPONSE = [
    'standard' => 'Standard',
    'administratif' => 'Très administratif',
    'chaleureux' => 'Chaleureux (moyennement)',
];

/** Projet de réponse officielle, à relire et modifier par l'agent avant envoi. $ton : clé de TONS_REPONSE. */
function rediger_reponse(array $dossier, string $ton = 'standard'): string
{
    $date = (new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Paris'))
        ->format(new DateTime($dossier['date_demande']));
    $objet = match ($dossier['type_demande']) {
        'Réclamation' => 'Nous avons bien noté votre mécontentement, qui a été classé par ordre d\'arrivée.',
        'Devis Premium' => 'Votre demande de devis Premium a retenu toute notre attention, ce qui est rare.',
        'Demande de conseil' => 'Votre demande de conseil a été examinée par un conseiller moyennement qualifié.',
        default => 'Votre demande, dont la nature nous échappe encore, a été examinée avec le plus grand sérieux.',
    };
    $conseil = '« ' . conseil_du_cabinet() . ' »';
    $numero = $dossier['id_site'];
    return match ($ton) {
        'administratif' => "Objet : suite à donner à votre demande n° $numero (réf. CP+/$numero/B-12)\n\n"
            . "Madame, Monsieur {$dossier['nom']},\n\n"
            . "Conformément aux dispositions de la procédure interne en vigueur, et après instruction de votre demande "
            . "enregistrée le $date, nous avons l'honneur de porter à votre connaissance les éléments suivants.\n"
            . "$objet\n\n"
            . "Article 1 : la recommandation du cabinet est la suivante :\n$conseil\n"
            . "Article 2 : la présente réponse est susceptible de recours, dans un délai compris entre 3 jours et jamais.\n"
            . "Article 3 : le service courrier est chargé de l'exécution de la présente.\n\n"
            . "Nous vous prions d'agréer, Madame, Monsieur, l'assurance de notre considération distinguée, et moyenne.",
        'chaleureux' => "Bonjour {$dossier['nom']} !\n\n"
            . "Merci beaucoup pour votre demande n° $numero du $date : elle a illuminé notre matinée, ou au moins notre pause café.\n"
            . "$objet\n\n"
            . "Voici notre conseil, préparé avec tout le soin dont nous sommes à peu près capables :\n$conseil\n\n"
            . "N'hésitez pas à revenir vers nous. Nous hésiterons un peu à vous répondre, mais nous finirons par le faire.\n"
            . "Bien moyennement à vous.",
        default => "Madame, Monsieur {$dossier['nom']},\n\n"
            . "Le cabinet ConseilPlus+ accuse réception de votre demande n° $numero du $date.\n"
            . "$objet\n\n"
            . "Après délibération, voici notre recommandation :\n$conseil\n\n"
            . "Nous restons à votre disposition, dans la limite de nos disponibilités, qui sont limitées.\n"
            . "Veuillez agréer l'expression de nos salutations moyennes.",
    };
    // La signature (nom de l'agent, adresse, tampon) est ajoutée automatiquement à l'envoi : includes/courriel.php
}

/** Vrai si l'envoi d'emails est configuré (clé Brevo + adresse d'expédition) : libellés et suites de dossier.php. */
function envoi_actif(): bool
{
    return BREVO_API_KEY !== '' && filter_var(BUREAU_EXPEDITEUR, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Envoie la réponse au demandeur par l'API de Brevo, dans le gabarit du cabinet (logo, cartouche, signature de l'agent).
 * Retourne [true, ''] si l'email est parti, sinon [false, raison lisible].
 */
function envoyer_reponse(array $dossier, string $texte, string $agent): array
{
    // Sans configuration (clé, expéditeur), envoyer_courriel() renvoie [false, 'envoi par email non configuré']
    $numero = numero_dossier((int) $dossier['id_site']);
    $html = gabarit_courriel([
        'titre' => 'Réponse officielle du cabinet',
        'corps' => $texte,
        'dossier' => $numero,
        'signataire' => $agent,
        'fonction' => 'Conseiller moyen',
        'tampon' => 'Traité',
        'apercu' => "Votre dossier n° $numero a été traité. Le cabinet a délibéré.",
    ]);
    return envoyer_courriel(BREVO_API_KEY, BUREAU_EXPEDITEUR, BUREAU_EXPEDITEUR_NOM, $dossier['email'], $dossier['nom'],
        "Votre demande n° $numero — réponse du cabinet ConseilPlus+",
        $texte . signature_texte($agent, 'Conseiller moyen'), $html);
}

/** Vues rapides de la liste des dossiers (dossiers.php, export CSV). */
const VUES_DOSSIERS = [
    'moi' => 'Assignés à moi',
    'retard' => 'En retard',
];

/** Condition SQL d'un dossier en retard : échéance dépassée et pas encore classé. */
const SQL_EN_RETARD = "d.echeance < CURDATE() AND d.statut <> 'classe'";

/**
 * Critères de la liste des dossiers, lus dans $source ($_GET, ou $_POST pour l'export) et nettoyés.
 * statut, type, q (nom, email ou message), vue (VUES_DOSSIERS), tri (date | echeance).
 */
function criteres_dossiers(array $source): array
{
    $lire = fn (string $cle) => trim((string) ($source[$cle] ?? ''));
    return [
        'statut' => array_key_exists($lire('statut'), STATUTS_DOSSIER) ? $lire('statut') : '',
        'type' => $lire('type'),
        'q' => mb_substr($lire('q'), 0, 100),
        'vue' => array_key_exists($lire('vue'), VUES_DOSSIERS) ? $lire('vue') : '',
        'tri' => $lire('tri') === 'echeance' ? 'echeance' : 'date',
    ];
}

/** Dossiers correspondant aux critères (criteres_dossiers()), avec les noms du responsable et du dernier intervenant. */
function filtrer_dossiers(PDO $bdd, array $criteres, int $id_agent, int $limite = 200): array
{
    $conditions = [];
    $params = [];
    if ($criteres['statut'] !== '') {
        $conditions[] = 'd.statut = :statut';
        $params['statut'] = $criteres['statut'];
    }
    if ($criteres['type'] !== '') {
        $conditions[] = 'd.type_demande = :type';
        $params['type'] = $criteres['type'];
    }
    if ($criteres['q'] !== '') {
        $conditions[] = '(d.nom LIKE :q1 OR d.email LIKE :q2 OR d.message LIKE :q3)';
        $params += ['q1' => '%' . $criteres['q'] . '%', 'q2' => '%' . $criteres['q'] . '%', 'q3' => '%' . $criteres['q'] . '%'];
    }
    if ($criteres['vue'] === 'moi') {
        $conditions[] = 'd.id_responsable = :moi';
        $params['moi'] = $id_agent;
    } elseif ($criteres['vue'] === 'retard') {
        $conditions[] = SQL_EN_RETARD;
    }
    $ordre = $criteres['tri'] === 'echeance' ? 'd.echeance IS NULL, d.echeance, d.date_demande' : 'd.date_demande DESC';
    $stmt = $bdd->prepare('SELECT d.*, r.nom AS responsable, i.nom AS intervenant FROM dossiers d
                           LEFT JOIN agents r ON r.id = d.id_responsable LEFT JOIN agents i ON i.id = d.id_agent'
        . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . " ORDER BY $ordre LIMIT " . max(1, $limite));
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Vrai si le dossier a une échéance dépassée et n'est pas classé. */
function en_retard(array $dossier): bool
{
    return $dossier['echeance'] !== null && $dossier['statut'] !== 'classe' && $dossier['echeance'] < date('Y-m-d');
}

/** Échéance à la française (« 14/10/2026 »), ou « — ». */
function date_echeance(?string $date): string
{
    return $date ? (new DateTime($date))->format('d/m/Y') : '—';
}

/**
 * Purge du site : supprime la demande d'un dossier classé sur le site (DELETE /api/contacts.php), le dossier reste
 * au Bureau avec la date de purge. Retourne 'purgee', 'absente' (déjà supprimée sur le site : 404) ou 'erreur'.
 */
function purger_du_site(PDO $bdd, array $dossier): string
{
    $r = api('DELETE', 'contacts.php?id=' . (int) $dossier['id_site']);
    if ($r['code'] !== 204 && $r['code'] !== 404) {
        return 'erreur';
    }
    $bdd->prepare('UPDATE dossiers SET date_purge = NOW() WHERE id = ?')->execute([$dossier['id']]);
    return $r['code'] === 204 ? 'purgee' : 'absente';
}

/** Dossiers classés depuis plus de 6 mois et encore présents sur le site (candidats à la purge en lot). */
const SQL_PURGEABLES = "statut = 'classe' AND date_purge IS NULL AND date_reponse < NOW() - INTERVAL 6 MONTH";

/** Trace d'une purge dans le journal du Bureau (table releves, nature « purge »). */
function journaliser_purge(PDO $bdd, int $erreurs, string $detail, ?int $id_agent): void
{
    $bdd->prepare("INSERT INTO releves (nature, importes, erreurs, detail, id_agent) VALUES ('purge', 0, ?, ?, ?)")
        ->execute([$erreurs, mb_substr($detail, 0, 255), $id_agent]);
}

/** Cellule de CSV sûre pour Excel : une valeur commençant par = + - @ n'est jamais lue comme une formule. */
function cellule_csv(?string $valeur): string
{
    $valeur = (string) $valeur;
    return preg_match('/^[=+\-@\t\r]/', $valeur) ? "'" . $valeur : $valeur;
}

/** Numéro de dossier affiché (« 0042 ») : celui de la demande sur le site. */
function numero_dossier(int $id): string
{
    return sprintf('%04d', $id);
}

/** Date courte à la française (« 14/10/2026 à 13 h 36 »). */
function date_courte(?string $date): string
{
    return $date ? (new DateTime($date))->format('d/m/Y \à H \h i') : '—';
}
