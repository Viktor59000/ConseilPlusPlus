<?php
/**
 * guichet.php — Le Guichet : la prise de rendez-vous (exigence du cours) et les épreuves du cabinet.
 *
 * Rendez-vous : réservé aux clients connectés. Hors connexion, la page invite à se connecter ou à ouvrir un compte
 * (?connexion=connexion|inscription retient le retour au guichet en session, compte.php le consomme après la connexion :
 * auth/ redirige toujours vers conseil.php, qui mène à Mon dossier). Le formulaire est envoyé en POST à l'API
 * api/inscriptions.php côté serveur (deposer_inscription(), avec la session du visiteur et son jeton CSRF), comme
 * contact.php le fait pour les réclamations. Au dépôt (201) : redirection vers ?depose=<id>, où le tampon « Reçu »
 * s'abat sur l'attestation de dépôt (téléchargeable, partageable : js/attestation.js), avec le lien vers Mon dossier.
 * Les créneaux complets sont grisés par js/v2/rendez-vous.js ; sans JavaScript, l'API refuse un créneau déjà pris (409).
 *
 * Épreuves : la liste des guichets numérotés (includes/guichets.php), chacun garde sa page.
 * Styles : css/v2/pages/guichet.css.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/guichets.php';

// Invitation à se connecter : le retour au guichet est retenu, puis on passe à l'espace client
if (isset($_GET['connexion']) && !isset($_SESSION['user_id'])) {
    $_SESSION['retour_apres_connexion'] = 'guichet.php#rendez-vous';
    header('location: login.php' . ($_GET['connexion'] === 'inscription' ? '#titre-inscription' : ''));
    exit();
}

$utilisateur = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$champs = ['nom', 'prenom', 'email', 'telephone', 'jour', 'horaire', 'message'];
$valeurs = array_fill_keys($champs, '');
$erreurs = [];
$avis = null; // [texte, type, titre]

if ($utilisateur !== null) {
    $valeurs = array_merge($valeurs, coordonnees_client($bdd, $utilisateur) ?? []);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $utilisateur !== null) {
    foreach ($champs as $champ) {
        $valeurs[$champ] = trim((string) ($_POST[$champ] ?? ''));
    }
    if (!csrf_valide()) {
        $avis = ['Le formulaire a expiré pendant que vous le remplissiez. Le guichet vous prie de le renvoyer.', 'erreur', 'Formulaire expiré'];
    } else {
        $reponse = deposer_inscription($valeurs);
        if ($reponse['code'] === 201) {
            // Redirection après dépôt : un rechargement ne redépose pas la demande
            header('location: guichet.php?depose=' . (int) $reponse['data']['id'] . '#depot');
            exit();
        }
        if ($reponse['code'] === 422) {
            $erreurs = $reponse['data']['champs'] ?? [];
            $avis = ['Le formulaire contient ' . count($erreurs) . ' erreur' . (count($erreurs) > 1 ? 's' : '') . '. Chacune est signalée sous son champ.', 'erreur', 'Dossier incomplet'];
        } elseif ($reponse['code'] === 409) {
            $avis = [(string) ($reponse['data']['erreur'] ?? 'Ce créneau n\'est plus libre.'), 'erreur', 'Créneau indisponible'];
            $valeurs['horaire'] = '';
        } elseif ($reponse['code'] === 429) {
            $avis = ['Vous avez déposé beaucoup de demandes en peu de temps. Le guichet souffle une minute, puis il reprend.', 'erreur', 'Guichet saturé'];
        } elseif ($reponse['code'] === 401) {
            $avis = ['Votre session a pris fin. Reconnectez-vous : le guichet ne reçoit que les dossiers ouverts.', 'erreur', 'Session terminée'];
        } else {
            $avis = ['Le guichet est momentanément fermé (service indisponible). Réessayez dans quelques instants.', 'erreur', 'Guichet fermé'];
        }
    }
}

// Dépôt réussi : l'attestation de dépôt (seulement pour le client qui a déposé)
$depot = null;
if ($utilisateur !== null && isset($_GET['depose'])) {
    $lu = lire_inscription($bdd, (int) $_GET['depose']);
    $depot = $lu && (int) $lu['id_utilisateur'] === $utilisateur ? $lu : null;
}

// Rendez-vous à venir du client : au-delà du maximum, le guichet ne prend plus de demande
$a_venir = 0;
if ($utilisateur !== null) {
    foreach (inscriptions_du_client($bdd, $utilisateur) as $i) {
        $a_venir += $i['statut'] !== 'annule' && $i['jour'] >= date('Y-m-d') ? 1 : 0;
    }
}
$libres = creneaux_libres($bdd);
$jours = ['' => 'Choisir un jour…'];
foreach ($libres as $jour => $horaires) {
    $n = count($horaires);
    $jours[$jour] = ucfirst(libelle_jour_court($jour)) . ($n ? ' — ' . $n . ' créneau' . ($n > 1 ? 'x' : '') . ' libre' . ($n > 1 ? 's' : '') : ' — complet');
}
$horaires = ['' => 'Choisir un horaire…'];
foreach (HORAIRES_GUICHET as $h) {
    $horaires[$h] = libelle_horaire($h);
}

page_debut([
    'titre' => $depot ? 'Demande reçue au guichet' : 'Le Guichet',
    'description' => 'Le Guichet de ConseilPlus+ : prenez rendez-vous, déposez votre problème et repartez avec une attestation de dépôt tamponnée. Cinq épreuves vous attendent aux guichets voisins.',
    'page' => 'guichet',
    'robots' => $depot ? 'noindex' : null,
    'css' => ['css/v2/pages/guichet.css'],
    'scripts' => ['js/v2/rendez-vous.js', 'js/attestation.js'],
]);

if ($depot):
    $nom = $depot['prenom'] . ' ' . $depot['nom'];
    $numero = numero_rendez_vous($depot);
    $rendez_vous = libelle_rendez_vous($depot['jour'], $depot['horaire']);
    $date_depot = (new DateTime($depot['date_creation']))->format('d/m/Y à H:i');
    $objet = $depot['message'] !== null && $depot['message'] !== '' ? $depot['message'] : 'Non précisé (le cabinet devinera)';
    $champs_attestation = ['Déposant' => $nom, 'Rendez-vous' => $rendez_vous, 'Guichet' => 'n° 1', 'Ticket' => sprintf('%03d', $depot['id']), 'Objet' => $objet];
    $document = [
        'fichier' => 'attestation-depot-' . strtolower($numero), 'titre' => 'Attestation de dépôt', 'service' => 'Service de l\'accueil, guichet n° 1',
        'corps' => 'Le cabinet certifie avoir reçu une demande de rendez-vous au nom de ' . $nom . '. Il ne certifie rien d\'autre, et surtout pas la demande.',
        'champs' => array_map(null, array_keys($champs_attestation), array_values($champs_attestation)),
        'mention' => 'Suivi du dossier : Mon dossier, sur le site du cabinet.', 'tampon' => 'Reçu',
        'references' => ['N° ' . $numero, 'Déposé le ' . $date_depot],
        'signataire' => 'Bertrand', 'fonction' => 'Agent d\'accueil, gardien du règlement',
    ];
?>
<section class="heros guichet-depot" id="depot" data-enter aria-labelledby="depot-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="depot-titre">Votre demande est reçue</h1>
      <p class="heros__chapeau">Le tampon a parlé. Votre rendez-vous du <?= e($rendez_vous) ?> est enregistré sous le n° <?= e($numero) ?>. Suivez son instruction dans Mon dossier : déposé, en instruction, puis confirmé.</p>
      <div class="boutons" data-document="<?= e(json_encode($document, JSON_UNESCAPED_UNICODE)) ?>">
        <?= bouton('Ouvrir Mon dossier', 'compte.php#rendez-vous') ?>
        <?= bouton('Télécharger l\'attestation', null, ['type' => 'button', 'variante' => 'secondaire', 'attributs' => ['data-telecharger' => true, 'hidden' => true]]) ?>
        <?= bouton('Partager', null, ['type' => 'button', 'variante' => 'secondaire', 'attributs' => ['data-partager-attestation' => true, 'hidden' => true]]) ?>
      </div>
      <p class="guichet-depot__note muet">Le document est dessiné dans votre navigateur : rien ne repart au cabinet, qui en garde déjà un double.</p>
    </div>
    <div class="heros__visuel">
      <?= attestation([
          'id' => 'attestation-depot', 'titre' => 'Attestation de dépôt', 'niveau' => 2, 'reference' => $numero,
          'service' => 'Service de l\'accueil, guichet n° 1',
          'corps' => 'Le cabinet certifie avoir reçu une demande de rendez-vous. Il ne certifie rien d\'autre, et surtout pas la demande.',
          'champs' => $champs_attestation,
          'signataire' => 'Bertrand', 'fonction' => 'agent d\'accueil', 'paraphe' => 'B.',
          'tampon' => ['texte' => 'Reçu', 'mention' => (new DateTime($depot['date_creation']))->format('d/m/Y'), 'mouvement' => 'au-chargement', 'id' => 'tampon-recu'],
      ]) ?>
    </div>
  </div>
</section>
<?php else: ?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Prenez rendez-vous au guichet</h1>
      <p class="heros__chapeau">Un jour du lundi au vendredi, un horaire à la minute près, et votre problème en une phrase. Le cabinet vous remet une attestation de dépôt tamponnée, puis instruit votre demande avec une lenteur réglementaire.</p>
      <div class="boutons">
        <?= bouton('Prendre rendez-vous', '#rendez-vous') ?>
        <?= bouton('Voir les épreuves', '#epreuves', ['variante' => 'secondaire']) ?>
      </div>
    </div>
    <div class="heros__visuel">
      <?= scene('guichet', 'Le guichet n° 1 : une grille en laiton, une lampe allumée, un tampon suspendu au-dessus d\'une feuille vierge, et un cordon rouge pour contenir une foule qui n\'est pas venue.', ['prioritaire' => true, 'tailles' => '(min-width: 1024px) 46vw, 100vw']) ?>
      <div class="heros__pose"><?= plaque_guichet('1', 'Rendez-vous') ?></div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!$depot): ?>
<section class="ecran ecran--papier" id="rendez-vous" aria-labelledby="rendez-vous-titre">
  <div class="conteneur grille guichet-rdv">
    <div class="ecran__tete">
      <h2 id="rendez-vous-titre">Prendre rendez-vous</h2>
      <p class="chapeau">Le cabinet reçoit du lundi au vendredi, sur les <?= JOURS_PROPOSES ?> jours ouvrés à venir, à six horaires choisis pour ne jamais tomber juste.</p>
    </div>
    <ul class="d-10 guichet-rdv__regles" aria-label="Conditions du guichet">
        <li>Un compte est exigé : votre dossier vous suit, pas l'inverse.</li>
        <li><?= INSCRIPTIONS_ACTIVES_MAX ?> rendez-vous à venir au plus par personne.</li>
        <li>Annulation libre dans Mon dossier, jusqu'au jour même.</li>
    </ul>
    <div class="c-8 guichet-rdv__feuille">
<?php if ($utilisateur === null): ?>
      <div class="guichet-porte">
        <h3 class="guichet-porte__titre">Le guichet ne reçoit que les dossiers ouverts</h3>
        <p>Connectez-vous à votre espace, ou ouvrez un dossier en une minute. Vous revenez ensuite ici, au même guichet, sans reprendre de ticket.</p>
        <div class="boutons">
          <?= bouton('Me connecter', 'guichet.php?connexion=connexion') ?>
          <?= bouton('Ouvrir un dossier', 'guichet.php?connexion=inscription', ['variante' => 'secondaire']) ?>
        </div>
        <h4 class="guichet-porte__libres">Créneaux libres cette semaine</h4>
        <ul class="guichet-porte__jours">
<?php foreach (array_slice($libres, 0, 5, true) as $jour => $h): ?>
          <li><span><?= e(ucfirst(libelle_jour_court($jour))) ?></span><span><?= count($h) ?> sur <?= count(HORAIRES_GUICHET) ?></span></li>
<?php endforeach; ?>
        </ul>
      </div>
<?php elseif ($a_venir >= INSCRIPTIONS_ACTIVES_MAX): ?>
      <?= bloc_alerte('Vous avez déjà ' . INSCRIPTIONS_ACTIVES_MAX . ' rendez-vous à venir. Le cabinet ne reçoit pas plus souvent la même personne : il craindrait de s\'attacher.', 'attention', ['titre' => 'Agenda complet']) ?>
      <p class="guichet-rdv__suite"><?= lien_fleche('Voir mes rendez-vous dans Mon dossier', 'compte.php#rendez-vous') ?></p>
<?php else: ?>
      <form class="guichet-formulaire" action="guichet.php#rendez-vous" method="POST" novalidate aria-labelledby="formulaire-titre">
        <?= csrf_champ() ?>
        <div class="guichet-formulaire__entete">
          <h3 id="formulaire-titre">Formulaire de rendez-vous</h3>
          <p class="reference reference--coin"><span>Cerfa n° 1-RDV</span></p>
          <p class="muet">Tous les champs sont obligatoires, sauf le problème.</p>
        </div>
<?php if ($avis): ?>
        <?= bloc_alerte($avis[0], $avis[1], ['titre' => $avis[2]]) ?>
<?php endif; ?>
        <div class="guichet-formulaire__grille">
          <?= champ('nom', 'Nom', $erreurs, ['requis' => true, 'valeur' => $valeurs['nom'], 'autocomplete' => 'family-name', 'maxlength' => 100, 'id' => 'rdv-nom']) ?>
          <?= champ('prenom', 'Prénom', $erreurs, ['requis' => true, 'valeur' => $valeurs['prenom'], 'autocomplete' => 'given-name', 'maxlength' => 100, 'id' => 'rdv-prenom']) ?>
          <?= champ('email', 'Adresse email', $erreurs, ['type' => 'email', 'requis' => true, 'valeur' => $valeurs['email'], 'autocomplete' => 'email', 'maxlength' => 150, 'id' => 'rdv-email']) ?>
          <?= champ('telephone', 'Téléphone', $erreurs, ['type' => 'tel', 'requis' => true, 'valeur' => $valeurs['telephone'], 'autocomplete' => 'tel', 'inputmode' => 'tel', 'officiel' => true, 'aide' => 'Dix chiffres, ex. 06 12 34 56 78.', 'id' => 'rdv-telephone']) ?>
          <?= champ('jour', 'Jour', $erreurs, ['type' => 'select', 'requis' => true, 'options' => $jours, 'valeur' => $valeurs['jour'], 'id' => 'rdv-jour', 'attributs' => ['data-rdv-jour' => true]]) ?>
          <?= champ('horaire', 'Horaire', $erreurs, ['type' => 'select', 'requis' => true, 'options' => $horaires, 'valeur' => $valeurs['horaire'], 'officiel' => true, 'id' => 'rdv-horaire', 'attributs' => ['data-rdv-horaire' => true]]) ?>
        </div>
        <?= champ('message', 'Votre problème, en une phrase', $erreurs, ['type' => 'textarea', 'rows' => 3, 'valeur' => $valeurs['message'], 'maxlength' => MESSAGE_INSCRIPTION_MAX, 'id' => 'rdv-message', 'aide' => 'Facultatif. Le cabinet lit vite, mais une seule fois.']) ?>
        <div class="boutons"><?= bouton('Déposer ma demande', null, ['type' => 'submit']) ?></div>
      </form>
      <script type="application/json" id="creneaux-libres"><?= json_encode($libres, JSON_HEX_TAG) ?></script>
<?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="ecran" id="epreuves" aria-labelledby="epreuves-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="epreuves-titre">Les épreuves du guichet</h2>
      <p class="chapeau">Chaque guichet rend son verdict, tamponné, et vous délivre le document qui va avec. Aucun n'est obligatoire, tous sont officiels.</p>
    </div>
    <ol class="guichets-tableau">
<?php foreach (GUICHETS as $cle => $g): ?>
      <li>
        <a class="guichets-tableau__ligne" href="<?= e($g['adresse']) ?>">
          <span class="guichets-tableau__numero" aria-hidden="true"><?= e($g['numero']) ?></span>
          <span class="guichets-tableau__nom"><span class="visuellement-cache">Guichet n° <?= e($g['numero']) ?> : </span><?= e($g['nom']) ?></span>
          <span class="guichets-tableau__detail"><?= e($g['detail']) ?></span>
          <span class="guichets-tableau__acces"><?= $g['connexion'] ? 'Compte exigé' : 'Accès libre' ?></span>
        </a>
      </li>
<?php endforeach; ?>
    </ol>
  </div>
</section>
<?php page_fin(); ?>
