<?php
/**
 * Le Bureau — aides d'affichage partagées par les pages (présentation seulement : aucune règle métier ici).
 * La mise en page (barre latérale, thème « archives ») est dans haut.php / bas.php, les styles dans css/v2/bureau.css
 * (coquille, composants denses, casiers) et bureau/css/bureau.css (pages : connexion, planning…).
 * Les composants du site (includes/ui : bouton, champ, tampon, graphiques, pictogrammes) viennent du gabarit commun.
 */
require_once __DIR__ . '/../../includes/gabarit.php'; // css_v2(), filtre_encre(), includes/ui/*, thème, url_publique()

/** Pictogrammes propres au Bureau (24 × 24, trait fin, même dessin que includes/ui/pictos.php). */
const PICTOS_BUREAU = [
    // Le tableau : quatre tuiles inégales
    'tableau' => 'M4 4h7v7H4ZM13 4h7v4h-7ZM13 10h7v10h-7ZM4 13h7v7H4Z',
    // Le planning : un calendrier à anneaux
    'planning' => 'M4 6.5h16V20H4ZM4 10.5h16M8.5 3.5v5M15.5 3.5v5M7.5 14h2M11 14h2M14.5 14h2M7.5 17h2M11 17h2',
    // Les inscrits : une personne et son registre
    'inscrits' => 'M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.5 19.5c.6-3.2 2.8-5 5.5-5s4.9 1.8 5.5 5M15.5 7h5M15.5 11h5M17.5 15h3',
    // La synchro : deux flèches qui se suivent
    'synchro' => 'M19.5 9.5A8 8 0 0 0 5.3 7.2M4.5 3.8v3.8h3.8M4.5 14.5a8 8 0 0 0 14.2 2.3M19.5 20.2v-3.8h-3.8',
    // L'export : un bac et la flèche qui en sort
    'export' => 'M12 3.5v11M8 7.5l4-4 4 4M4 13.5v5.5a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-5.5',
    // Le journal : un registre relié
    'journal' => 'M5 4.5A1.5 1.5 0 0 1 6.5 3H19v15H6.5A1.5 1.5 0 0 0 5 19.5ZM5 19.5A1.5 1.5 0 0 0 6.5 21H19M9 7.5h6M9 10.5h4',
    // Les statistiques : quatre barres sur une ligne
    'statistiques' => 'M3.5 20.5h17M6.5 17v-5M10.5 17V7M14.5 17v-7M18.5 17V4',
    // Les clés : une clé à deux dents
    'cles' => 'M8 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM11.5 12h9M17.5 12v3M20.5 12v2.5',
    // La déconnexion : une porte et la flèche qui sort
    'deconnexion' => 'M13.5 4H6a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h7.5M10 12h10.5M17 8.5l3.5 3.5-3.5 3.5',
    // Le site : une fenêtre qui s'ouvre ailleurs
    'site' => 'M13.5 4.5h6v6M19.5 4.5l-8 8M17.5 13.5v5a1 1 0 0 1-1 1h-11a1 1 0 0 1-1-1v-11a1 1 0 0 1 1-1h5',
    // Le courrier : une enveloppe
    'courrier' => 'M3.5 6h17v12h-17ZM3.5 6.5l8.5 6.5 8.5-6.5',
    // L'horloge (retard)
    'horloge' => 'M12 20.5a8.5 8.5 0 1 0 0-17 8.5 8.5 0 0 0 0 17ZM12 7.5V12l3 2',
];

/** Onglets du tableau de la séance (en tête de la barre latérale) : clé de page => [adresse, libellé, pictogramme]. */
const ONGLETS_BUREAU = [
    'tableau' => ['tableau.php', 'Tableau', 'tableau'],
    'planning' => ['planning.php', 'Planning', 'planning'],
    'inscrits' => ['inscrits.php', 'Inscrits', 'inscrits'],
    'synchro' => ['synchro.php', 'Synchro', 'synchro'],
    'export' => ['export-inscrits.php', 'Export', 'export'],
];

/** Le reste du Bureau (second plan) : mêmes clés. */
const RESTE_BUREAU = [
    'salle-de-tri' => ['salle-de-tri.php', 'Salle de tri', 'casier'],
    'dossiers' => ['dossiers.php', 'Registre des dossiers', 'chemise'],
    'petit-moyen' => ['petit-moyen.php', 'Le Petit Moyen', 'plume'],
    'journal' => ['journal.php', 'Journal', 'journal'],
    'statistiques' => ['statistiques.php', 'Statistiques', 'statistiques'],
    'cles' => ['cles.php', 'Clés d\'API', 'cles'],
];

/** Pictogramme du Bureau ou du site (includes/ui/pictos.php), décoratif (à côté d'un libellé visible). */
function picto_bureau(string $nom): string
{
    if (!isset(PICTOS_BUREAU[$nom])) {
        return picto($nom);
    }
    return '<svg class="picto" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="' . PICTOS_BUREAU[$nom] . '"/></svg>';
}

/** Le logo réduit au C+ entouré au stylo (le mot sort du cadre), décoratif. */
function logo_bureau(string $id): string
{
    return str_replace(['class="logo-svg" viewBox="0 0 984 256"'], ['class="logo-svg logo-cplus" viewBox="0 0 256 256"'], ui_logo($id, true));
}

/** Pastille de statut (Linear) : $cle donne la couleur et le repère (a_traiter, en_cours, classe, confirme, annule…). */
function pastille(string $cle, string $libelle): string
{
    return '<span class="pastille pastille--' . e(preg_replace('/[^a-z_]/', '', $cle)) . '">' . e($libelle) . '</span>';
}

/**
 * Répartition par statut : un titre, le total, une barre segmentée (décorative) et la légende chiffrée (le texte porte
 * les valeurs). $lignes : liste de [libellé, valeur] dans l'ordre du circuit ; la première (ce qui attend l'agent) prend
 * l'accent, les suivantes deux tons d'encre (.r-1 à .r-3, css/v2/bureau.css).
 */
function repartition(string $titre, array $lignes, string $singulier, string $pluriel, string $id): string
{
    $total = array_sum(array_column($lignes, 1));
    $barre = '';
    $legende = '';
    foreach (array_values($lignes) as $k => [$libelle, $valeur]) {
        $r = 'r-' . min(3, $k + 1);
        if ($valeur > 0) {
            $barre .= '<span class="' . $r . '" style="flex: ' . (int) $valeur . ' 1 0"></span>';
        }
        $legende .= '<li class="' . $r . '"><span>' . e($libelle) . '</span><b>' . (int) $valeur . '</b></li>';
    }
    return '<section class="repartition" aria-labelledby="' . e($id) . '"><div class="repartition__tete"><h3 id="' . e($id) . '">' . e($titre) . '</h3>'
        . '<span>' . e($total === 0 ? 'aucun ' . $singulier : $total . ' ' . ($total > 1 ? $pluriel : $singulier)) . '</span></div>'
        . '<div class="repartition__barre" aria-hidden="true">' . $barre . '</div>'
        . '<ul class="repartition__legende">' . $legende . '</ul></section>';
}

/** Initiales d'un agent pour sa pastille ronde (« Viktor Venet » → « VV »). */
function initiales(string $nom): string
{
    $mots = preg_split('/[\s\-]+/u', trim($nom), -1, PREG_SPLIT_NO_EMPTY) ?: ['?'];
    return mb_strtoupper(mb_substr($mots[0], 0, 1) . (count($mots) > 1 ? mb_substr(end($mots), 0, 1) : ''));
}

/** « 3 dossiers », « 1 dossier », « aucun dossier » (accords simples, pluriel régulier par défaut). */
function nombre_de(int $n, string $singulier, ?string $pluriel = null): string
{
    return $n === 0 ? 'aucun ' . $singulier : $n . ' ' . ($n > 1 ? ($pluriel ?? $singulier . 's') : $singulier);
}

/** Compteurs de la barre latérale (dossiers à traiter, rendez-vous à venir à traiter). */
function compteurs_laterale(PDO $bdd): array
{
    return [
        'salle-de-tri' => (int) $bdd->query("SELECT COUNT(*) FROM dossiers WHERE statut = 'a_traiter'")->fetchColumn(),
        'inscrits' => (int) $bdd->query("SELECT COUNT(*) FROM inscris WHERE statut = 'a_traiter' AND jour >= CURDATE()")->fetchColumn(),
    ];
}

/**
 * Débit du guichet : dépôts reçus par jour sur les $jours derniers jours (demandes de contact + inscriptions aux
 * rendez-vous, d'après leur date de dépôt sur le site), pour graphique_barres(). Le dernier jour est aujourd'hui.
 */
function debit_guichet(PDO $bdd, int $jours = 14): array
{
    $debut = date('Y-m-d', strtotime('-' . ($jours - 1) . ' day'));
    $compte = [];
    for ($i = 0; $i < $jours; $i++) {
        $compte[date('Y-m-d', strtotime("$debut +$i day"))] = 0;
    }
    foreach (['SELECT DATE(date_demande) AS j, COUNT(*) AS n FROM dossiers WHERE date_demande >= ? GROUP BY j',
              'SELECT DATE(create_at) AS j, COUNT(*) AS n FROM inscris WHERE create_at >= ? GROUP BY j'] as $sql) {
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$debut]);
        foreach ($stmt->fetchAll() as $l) {
            if (isset($compte[$l['j']])) {
                $compte[$l['j']] += (int) $l['n'];
            }
        }
    }
    $lignes = [];
    foreach ($compte as $jour => $n) {
        $lignes[] = ['libelle' => date('d', strtotime($jour)), 'valeur' => $n];
    }
    return $lignes;
}

/**
 * La Salle de tri : passages permis d'un casier à l'autre et action POST existante qui les fait.
 * Dossiers (dossier.php, champ « action ») : prise en charge, classement (il faut une réponse enregistrée), réouverture.
 * Rendez-vous (inscrits.php, champ « statut ») : tout statut vers tout autre.
 * Chaque passage : statut d'arrivée => [valeur envoyée, texte du tampon, couleur du tampon].
 */
const PASSAGES_DOSSIERS = [
    'a_traiter' => ['en_cours' => ['examiner', 'Pris en charge', 'encre']],
    'en_cours' => ['classe' => ['classer', 'Classé', 'vert']],
    'classe' => ['en_cours' => ['rouvrir', 'Rouvert', 'laiton']],
];
const PASSAGES_INSCRIS = [
    'a_traiter' => ['confirme' => ['confirme', 'Confirmé', 'vert'], 'annule' => ['annule', 'Annulé', 'rouge']],
    'confirme' => ['annule' => ['annule', 'Annulé', 'rouge'], 'a_traiter' => ['a_traiter', 'À traiter', 'laiton']],
    'annule' => ['confirme' => ['confirme', 'Confirmé', 'vert'], 'a_traiter' => ['a_traiter', 'À traiter', 'laiton']],
];

/** Dossiers rangés par casier (statut) : statut => ['total' => n, 'liste' => dossiers (au plus $limite)]. */
function casiers_dossiers(PDO $bdd, int $limite = 30): array
{
    $casiers = [];
    foreach (array_keys(STATUTS_BUREAU) as $statut) {
        $ordre = $statut === 'classe' ? 'd.date_reponse DESC, d.id DESC' : 'd.echeance IS NULL, d.echeance, d.date_demande';
        $stmt = $bdd->prepare("SELECT d.*, r.nom AS responsable FROM dossiers d LEFT JOIN agents r ON r.id = d.id_responsable
                               WHERE d.statut = ? ORDER BY $ordre LIMIT " . max(1, $limite));
        $stmt->execute([$statut]);
        $total = $bdd->prepare('SELECT COUNT(*) FROM dossiers WHERE statut = ?');
        $total->execute([$statut]);
        $casiers[$statut] = ['total' => (int) $total->fetchColumn(), 'liste' => $stmt->fetchAll()];
    }
    return $casiers;
}

/** Rendez-vous rangés par casier : à traiter (tous), confirmés et annulés à venir. */
function casiers_inscris(PDO $bdd, int $limite = 30): array
{
    $casiers = [];
    foreach (array_keys(STATUTS_INSCRIS) as $statut) {
        $condition = $statut === 'a_traiter' ? 'statut = ?' : 'statut = ? AND jour >= CURDATE()';
        $stmt = $bdd->prepare("SELECT * FROM inscris WHERE $condition ORDER BY jour, horaire, id LIMIT " . max(1, $limite));
        $stmt->execute([$statut]);
        $total = $bdd->prepare("SELECT COUNT(*) FROM inscris WHERE $condition");
        $total->execute([$statut]);
        $casiers[$statut] = ['total' => (int) $total->fetchColumn(), 'liste' => $stmt->fetchAll()];
    }
    return $casiers;
}

/** Adresse d'un script du Bureau (bureau/js/), versionnée par l'empreinte de son contenu. */
function script_bureau(string $fichier): string
{
    $chemin = __DIR__ . '/../js/' . $fichier;
    return 'js/' . $fichier . '?v=' . (is_file($chemin) ? substr(md5_file($chemin), 0, 8) : '0');
}

/**
 * Barres horizontales : une par ligne, libellé $lignes[i][$cle], valeur $lignes[i][$valeur].
 * Largeur relative à $base (par défaut la plus grande valeur, qui fait alors 100 %).
 */
function barres(array $lignes, string $cle, string $valeur = 'appels', ?int $base = null): string
{
    $base = $base ?: (max(array_column($lignes, $valeur) ?: [1]) ?: 1);
    $html = '<ul class="barres">';
    foreach ($lignes as $l) {
        $html .= '<li><span class="barres-libelle"><span>' . e((string) $l[$cle]) . '</span> <span class="mono">' . (int) $l[$valeur] . '</span></span>'
            . '<span class="barres-fond" aria-hidden="true"><span class="barres-plein" style="width: ' . round(100 * $l[$valeur] / $base) . '%"></span></span></li>';
    }
    return $html . '</ul>';
}
