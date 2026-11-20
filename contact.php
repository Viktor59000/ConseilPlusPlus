<?php
/**
 * contact.php — Réclamations : le formulaire de contact du cabinet, accueilli par Mireille (son standard : standard.php).
 * La demande est envoyée en POST à l'API (api/contacts.php), qui la valide et l'enregistre
 * avec le statut « nouveau ». Le logiciel de gestion la récupère ensuite via l'API.
 * Client connecté : la demande lui est rattachée (id_utilisateur) pour qu'il lise la réponse dans Mon dossier (compte.php).
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/bdd.php';
require_once __DIR__ . '/includes/contacts.php';
require_once __DIR__ . '/includes/http.php';
require_once __DIR__ . '/includes/courriel.php';
require_once __DIR__ . '/includes/formulaire.php';
require_once __DIR__ . '/includes/textes.php';

$valeurs = ['nom' => '', 'email' => '', 'message' => '', 'type_demande' => ''];
$erreurs = [];
$erreur_api = false;
$erreur_session = false;
$trop_de_demandes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valeurs as $champ => $_) {
        $valeurs[$champ] = trim((string) ($_POST[$champ] ?? ''));
    }

    if (!csrf_valide()) {
        $erreur_session = true;
    } elseif (($_POST['site_web'] ?? '') !== '') {
        // Champ piège invisible rempli : c'est un robot. On fait semblant d'accepter sans rien enregistrer.
        header('location: contact.php?envoye=1');
        exit();
    } else {
        // L'adresse IP du visiteur est transmise à l'API pour sa limite d'envois (l'appel part du serveur)
        $reponse = appeler_api('POST', url_interne() . '/api/contacts.php', $valeurs, ['X-Client-IP: ' . $_SERVER['REMOTE_ADDR']]);
        if ($reponse['code'] === 201) {
            $id = (int) ($reponse['data']['id'] ?? 0);
            // Client connecté : la demande rejoint Mon dossier (compte.php), où il lira la réponse du cabinet
            if (isset($_SESSION['user_id']) && $id) {
                rattacher_contact(bdd(), $id, (int) $_SESSION['user_id']);
            }
            envoyer_accuse_reception(bdd(), $valeurs, $id);
            // Redirection après envoi pour éviter un double enregistrement au rechargement
            header('location: contact.php?envoye=1');
            exit();
        } elseif ($reponse['code'] === 429) {
            $trop_de_demandes = true;
        } elseif ($reponse['code'] === 422) {
            $erreurs = $reponse['data']['champs'] ?? [];
        } else {
            $erreur_api = true;
        }
    }
}

$types = ['' => 'Choisir…'] + array_combine(TYPES_DEMANDE, TYPES_DEMANDE);
$delai = ucfirst(texte_hasard('delais'));

page_debut([
    'titre' => 'Réclamations',
    'description' => 'Les Réclamations de ConseilPlus+ : une question, une réclamation, un devis Premium ? Mireille enregistre votre demande. Réponse sous 3 jours ou jamais.',
    'page' => 'reclamations',
    'css' => ['css/v2/pages/formulaire.css', 'css/v2/pages/reclamations.css'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <p class="reference"><span>Service des réclamations</span><span>Guichet n° 1</span></p>
      <h1 id="heros-titre">Réclamations</h1>
      <p class="heros__chapeau">Une question, une réclamation, un besoin de conseil ? Mireille enregistre votre demande, lui attribue un délai et la transmet au cabinet. Elle sera lue. Un jour.</p>
      <div class="boutons">
        <?= bouton('Écrire au cabinet', '#formulaire') ?>
        <?= bouton('Joindre le standard', 'standard.php', ['variante' => 'secondaire', 'icone_html' => picto('telephone')]) ?>
      </div>
      <dl class="heros__faits">
        <div><dt>Délai de réponse</dt><dd><?= e($delai) ?></dd></div>
        <div><dt>Réclamations perdues</dt><dd>0 (en attente)</dd></div>
      </dl>
    </div>
    <div class="heros__visuel">
      <?= scene('standard', 'Le standard de Mireille : des fiches rouges branchées au hasard, un combiné décroché, une pile de formulaires, une sonnette et un café qui refroidit depuis 2009.', ['prioritaire' => true, 'tailles' => '(min-width: 1024px) 46vw, 100vw']) ?>
      <div class="heros__pose accueil-mireille" data-papier>
        <img src="assets/equipe/delais.webp" alt="" width="640" height="640" decoding="async">
        <div>
          <p class="machine">Accueil</p>
          <p class="accueil-mireille__nom">Mireille</p>
          <p class="accueil-mireille__role">Standardiste, responsable des délais</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="formulaire" aria-labelledby="titre-contact">
  <div class="conteneur grille">
    <form class="c-7 formulaire" action="contact.php#formulaire" method="POST" novalidate aria-labelledby="titre-contact">
      <?= csrf_champ() ?>
      <!-- Champ piège : invisible pour les humains, rempli par les robots -->
      <div class="piege" aria-hidden="true">
        <label for="ct-site">Ne pas remplir</label>
        <input id="ct-site" type="text" name="site_web" tabindex="-1" autocomplete="off">
      </div>
      <div class="ecran__tete">
        <p class="reference"><span>Formulaire R-1</span><span>Demande au cabinet</span></p>
        <h2 id="titre-contact">Formulaire de demande</h2>
        <p class="muet">Tous les champs sont obligatoires.<?= isset($_SESSION['user_id']) ? ' Vous êtes connecté : la réponse arrivera aussi dans Mon dossier.' : '' ?></p>
      </div>
<?php if (isset($_GET['envoye'])): ?>
      <?= bloc_alerte('Demande enregistrée sous le statut « nouveau ». Nous la traiterons avec la lenteur qui nous caractérise.'
          . (isset($_SESSION['user_id']) ? ' Vous suivrez sa réponse dans Mon dossier.' : ''), 'succes', ['titre' => 'Demande reçue']) ?>
<?php elseif ($erreur_session): ?>
      <?= bloc_alerte('Le formulaire a expiré. Merci de le renvoyer.', 'erreur', ['titre' => 'Formulaire expiré']) ?>
<?php elseif ($trop_de_demandes): ?>
      <?= bloc_alerte('Vous avez envoyé beaucoup de demandes en peu de temps. Même nous, nous avons des limites : réessayez dans une minute.', 'attention', ['titre' => 'Guichet saturé']) ?>
<?php elseif ($erreur_api): ?>
      <?= bloc_alerte('Notre guichet est momentanément fermé (service indisponible). Réessayez dans quelques instants.', 'erreur', ['titre' => 'Guichet fermé']) ?>
<?php elseif ($erreurs): ?>
      <?= bloc_alerte('Le formulaire contient ' . count($erreurs) . ' erreur(s). Corrigez les champs indiqués puis renvoyez-le.', 'erreur', ['titre' => 'Dossier incomplet']) ?>
<?php endif; ?>
      <div class="formulaire__duo">
        <?= champ('nom', 'Nom', $erreurs, ['id' => 'ct-nom', 'valeur' => $valeurs['nom'], 'requis' => true, 'maxlength' => 100, 'autocomplete' => 'name', 'officiel' => true]) ?>
        <?= champ('email', 'Adresse email', $erreurs, ['id' => 'ct-email', 'type' => 'email', 'valeur' => $valeurs['email'], 'requis' => true, 'maxlength' => 150, 'autocomplete' => 'email', 'officiel' => true]) ?>
      </div>
      <?= champ('type_demande', 'Type de demande', $erreurs, ['id' => 'ct-type', 'type' => 'select', 'options' => $types, 'valeur' => $valeurs['type_demande'], 'requis' => true]) ?>
      <?= champ('message', 'Message', $erreurs, ['id' => 'ct-message', 'type' => 'textarea', 'rows' => 6, 'valeur' => $valeurs['message'], 'requis' => true,
          'aide' => 'Une réclamation à la fois : le cabinet n\'en lit qu\'une par jour, de préférence la vôtre.']) ?>
      <div class="boutons"><?= bouton('Envoyer ma demande', null, ['type' => 'submit']) ?></div>
    </form>

    <aside class="d-9 coordonnees" aria-labelledby="titre-coordonnees">
      <h2 id="titre-coordonnees" class="coordonnees__titre">Coordonnées</h2>
      <dl>
        <div><dt>Courrier</dt><dd>12 bis, impasse du Consensus Mou<br>Bâtiment C, escalier D, au fond à gauche</dd></div>
        <div><dt>Horaires</dt><dd>Du lundi au vendredi, de 14h12 à 14h47.<br>Fermé les jours de vent.</dd></div>
        <div><dt>Délai de réponse</dt><dd><?= e($delai) ?>.</dd></div>
        <div><dt>Par téléphone</dt><dd><?= lien_fleche('Joindre le standard de Mireille', 'standard.php') ?></dd></div>
      </dl>
    </aside>
  </div>
</section>
<?php page_fin(); ?>
