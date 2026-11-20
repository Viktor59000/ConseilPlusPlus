<?php
/**
 * compte.php — Mon dossier : la chemise kraft du client connecté.
 *   - ses rendez-vous au guichet (includes/inscriptions.php) et leur suivi façon colis : déposé → en instruction →
 *     confirmé (ou annulé), avec la réponse du cabinet relayée par Le Bureau via l'API ; annulation par le client ;
 *   - ses réclamations (formulaire de contact envoyé connecté) : suivi, réponse du Bureau, suppression ;
 *   - le conseil du jour (ancien conseil.php : un autre conseil, garder au carnet, partager) ;
 *   - ses conseils gardés (ancien carnet.php : retrait sans JavaScript, POST + CSRF) ;
 *   - son compte : informations, mot de passe, déconnexion, suppression définitive (RGPD).
 * conseil.php et carnet.php redirigent ici (301). Après la connexion (auth/ redirige vers conseil.php), un retour
 * retenu par le Guichet (guichet.php?connexion=…) est consommé ici. Styles : css/v2/pages/dossier.css.
 */
require_once __DIR__ . '/includes/init.php';
$utilisateur = exiger_connexion();

// Retour retenu avant la connexion (le Guichet) : une seule fois, et seulement vers une adresse du site connue
if (isset($_SESSION['retour_apres_connexion'])) {
    $retour = $_SESSION['retour_apres_connexion'];
    unset($_SESSION['retour_apres_connexion']);
    if ($retour === 'guichet.php#rendez-vous') {
        header('location: ' . $retour);
        exit();
    }
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/contacts.php';
require_once __DIR__ . '/includes/guichets.php';
require_once __DIR__ . '/includes/tirage.php';
require_once __DIR__ . '/includes/textes.php';

$avis = []; // ancre de section => [texte, type, titre]
$erreur_compte = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valide()) {
        $avis['compte'] = ['Le formulaire a expiré. Merci de le renvoyer.', 'erreur', 'Formulaire expiré'];
    } elseif (isset($_POST['annuler_rendez_vous'])) {
        $resultat = annuler_inscription_client($bdd, (int) $_POST['annuler_rendez_vous'], $utilisateur);
        if ($resultat === 'annulee') {
            header('location: compte.php?rendez_vous_annule=1#rendez-vous');
            exit();
        }
        $avis['rendez-vous'] = [$resultat === 'impossible' ? 'Ce rendez-vous est déjà annulé ou passé : le cabinet ne revient pas en arrière.'
            : 'Rendez-vous introuvable parmi les vôtres.', 'erreur', 'Annulation impossible'];
    } elseif (isset($_POST['supprimer_demande'])) {
        if (supprimer_demande_client($bdd, (int) $_POST['supprimer_demande'], $utilisateur)) {
            header('location: compte.php?demande_supprimee=1#demandes');
            exit();
        }
        $avis['demandes'] = ['Demande introuvable. Elle a peut-être déjà été supprimée, ou elle n\'est pas à vous.', 'erreur', 'Suppression impossible'];
    } elseif (isset($_POST['retirer_conseil'])) {
        $bdd->prepare('DELETE FROM carnet WHERE id = :id AND id_utilisateur = :u')->execute(['id' => (int) $_POST['retirer_conseil'], 'u' => $utilisateur]);
        header('location: compte.php?conseil_retire=1#carnet');
        exit();
    } elseif (isset($_POST['supprimer_compte'])) {
        if (empty($_POST['confirmation'])) {
            $erreur_compte = 'Cochez la case de confirmation pour supprimer votre compte.';
        } else {
            // Effacement explicite (les clés étrangères ne sont pas appliquées partout : MyISAM sous WAMP)
            foreach (['carnet', 'reinitialisations', 'duels', 'contacts', 'inscriptions'] as $table) {
                $bdd->prepare("DELETE FROM $table WHERE id_utilisateur = ?")->execute([$utilisateur]);
            }
            $bdd->prepare('DELETE FROM utilisateurs WHERE id = ?')->execute([$utilisateur]);
            // Fin de session (comme auth/logout.php), puis retour à l'espace client avec un message
            $_SESSION = [];
            session_destroy();
            header('location: login.php?supprime=1');
            exit();
        }
    }
}
if (isset($_GET['rendez_vous_annule'])) {
    $avis['rendez-vous'] = ['Rendez-vous annulé. Le créneau est rendu au guichet, qui le reprête volontiers.', 'succes', 'Annulation enregistrée'];
}
if (isset($_GET['demande_supprimee'])) {
    $avis['demandes'] = ['Demande supprimée du site. Le cabinet ne s\'en souviendra pas plus que d\'habitude.', 'succes', 'Demande supprimée'];
}
if (isset($_GET['conseil_retire'])) {
    $avis['carnet'] = ['Conseil retiré du carnet. Il n\'avait pas été appliqué, il ne sera pas regretté.', 'succes', 'Conseil retiré'];
}

$stmt = $bdd->prepare('SELECT prenom, nom, mail FROM utilisateurs WHERE id = ?');
$stmt->execute([$utilisateur]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
$rendez_vous = inscriptions_du_client($bdd, $utilisateur);
$demandes = demandes_du_client($bdd, $utilisateur);
$stmt = $bdd->prepare('SELECT id, texte, dossier, source, date_classement FROM carnet WHERE id_utilisateur = :u ORDER BY date_classement DESC, id DESC');
$stmt->execute(['u' => $utilisateur]);
$carnet = $stmt->fetchAll(PDO::FETCH_ASSOC);
$conseil = tirer_conseil($bdd);
$sources = ['generateur' => 'Le conseiller moyen', 'ia' => 'L\'intelligence artificielle moyenne', 'secours' => 'Le stagiaire'];
$aujourdhui = date('Y-m-d');
$a_venir = array_filter($rendez_vous, fn ($r) => $r['statut'] !== 'annule' && $r['jour'] >= $aujourdhui);
$prochain = $a_venir ? reset($a_venir) : null;
$numero_client = sprintf('CL-%05d', $utilisateur);
$pluriel = fn (int $n, string $mot) => $n . ' ' . $mot . ($n > 1 ? 's' : '');

page_debut([
    'titre' => 'Mon dossier',
    'description' => 'Mon dossier ConseilPlus+ : le suivi de vos rendez-vous au guichet et de vos réclamations, vos conseils gardés et votre compte.',
    'page' => 'dossier',
    'robots' => 'noindex',
    'css' => ['css/v2/pages/dossier.css'],
    'scripts' => ['js/conseil.js', 'js/carnet.js', 'js/partage.js'],
]);
?>
<section class="heros dossier-heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Le dossier de <?= e($client['prenom']) ?></h1>
      <p class="heros__chapeau">Tout ce que le cabinet a ouvert à votre nom : vos rendez-vous, vos réclamations, vos conseils gardés. Rien n'est perdu, tout est rangé, presque au même endroit.</p>
      <div class="boutons">
        <?= bouton('Prendre rendez-vous', 'guichet.php#rendez-vous') ?>
        <?= bouton('Se déconnecter', 'auth/logout.php', ['variante' => 'secondaire']) ?>
      </div>
      <nav class="dossier-sommaire" aria-label="Sommaire du dossier">
        <ol>
          <li><a href="#rendez-vous"><span>Rendez-vous</span><span class="mono"><?= count($rendez_vous) ?></span></a></li>
          <li><a href="#demandes"><span>Réclamations</span><span class="mono"><?= count($demandes) ?></span></a></li>
          <li><a href="#conseil"><span>Conseil du jour</span><span class="mono">1</span></a></li>
          <li><a href="#carnet"><span>Conseils gardés</span><span class="mono"><?= count($carnet) ?></span></a></li>
          <li><a href="#compte"><span>Mon compte</span><span class="mono">—</span></a></li>
        </ol>
      </nav>
    </div>
    <div class="heros__visuel dossier-heros__chemise">
<?php
$resume = '<dl class="dossier-resume">'
    . '<div><dt>Titulaire</dt><dd>' . e($client['prenom'] . ' ' . $client['nom']) . '</dd></div>'
    . '<div><dt>Prochain rendez-vous</dt><dd>' . ($prochain ? e(libelle_rendez_vous($prochain['jour'], $prochain['horaire'])) : 'aucun, pour l\'instant') . '</dd></div>'
    . '<div><dt>Pièces au dossier</dt><dd>' . e($pluriel(count($rendez_vous), 'rendez-vous') . ', ' . $pluriel(count($demandes), 'réclamation') . ', ' . $pluriel(count($carnet), 'conseil')) . '</dd></div>'
    . '</dl>';
echo dossier($numero_client, $prochain['statut'] ?? 'nouveau', $resume, [
    'titre' => 'Dossier de ' . $client['prenom'] . ' ' . $client['nom'], 'niveau' => 2,
    'statut_libelle' => $prochain ? ETAPES_RENDEZ_VOUS[$prochain['statut']]['libelle'] : 'Ouvert',
    'meta' => ['Classement' => 'moyen', 'Archivage' => 'jamais'],
    'tampon_html' => tampon('Ouvert', ['couleur' => 'vert', 'angle' => -7, 'taille' => 'petit', 'decoratif' => true]),
]);
?>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="rendez-vous" aria-labelledby="rendez-vous-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="rendez-vous-titre">Mes rendez-vous</h2>
      <p class="chapeau">Chaque demande suit le même circuit : déposée au guichet, relevée par Le Bureau, puis confirmée par le cabinet. Vous la suivez ici, comme un colis, mais sans livreur.</p>
    </div>
<?php if (isset($avis['rendez-vous'])): ?>
    <?= bloc_alerte($avis['rendez-vous'][0], $avis['rendez-vous'][1], ['titre' => $avis['rendez-vous'][2]]) ?>
<?php endif; ?>
<?php if (!$rendez_vous): ?>
    <div class="dossier-vide">
      <p>Aucun rendez-vous pour l'instant. Le guichet est ouvert, et il ne demande qu'un jour, un horaire et un problème, même petit.</p>
      <?= bouton('Prendre rendez-vous', 'guichet.php#rendez-vous', ['variante' => 'secondaire']) ?>
    </div>
<?php else: ?>
    <div class="dossier-pile">
<?php foreach ($rendez_vous as $r):
    $annule = $r['statut'] === 'annule';
    $passe = $r['jour'] < $aujourdhui;
    $etapes = $annule ? ETAPES_RENDEZ_VOUS_ANNULE : ETAPES_RENDEZ_VOUS;
    $libelle = $annule ? 'Annulé' : ETAPES_RENDEZ_VOUS[$r['statut']]['libelle'];
    $feuille = '<p class="dossier-feuille__quand"><span class="mono">Rendez-vous</span> ' . e(libelle_rendez_vous($r['jour'], $r['horaire'])) . ($passe ? ' <span class="muet">(passé)</span>' : '') . '</p>'
        . etapes_statut($r['statut'], ['etapes' => $etapes, 'arret' => $annule, 'libelle' => 'Suivi du rendez-vous ' . numero_rendez_vous($r),
            'dates' => ['nouveau' => (new DateTime($r['date_creation']))->format('d/m/Y · H:i')]])
        . '<div class="dossier-feuille__texte"><p class="mono muet">Problème déposé</p><p>' . ($r['message'] !== null && $r['message'] !== '' ? nl2br(e($r['message'])) : '<span class="muet">Non précisé. Le cabinet devinera.</span>') . '</p></div>';
    if ($r['reponse'] !== null && $r['reponse'] !== '') {
        $feuille .= '<div class="dossier-feuille__reponse"><p class="mono">Réponse du cabinet</p><p>' . nl2br(e($r['reponse'])) . '</p></div>';
    }
    if (!$annule && !$passe) {
        $feuille .= '<form class="dossier-feuille__action" action="compte.php#rendez-vous" method="POST">' . csrf_champ()
            . bouton('Annuler ce rendez-vous', null, ['variante' => 'secondaire', 'taille' => 'compacte', 'nom' => 'annuler_rendez_vous', 'valeur' => (string) $r['id']]) . '</form>';
    }
    $tampon = match ($r['statut']) {
        'confirme' => tampon('Confirmé', ['couleur' => 'vert', 'angle' => -8, 'taille' => 'petit', 'decoratif' => true]),
        'annule' => tampon('Annulé', ['angle' => 6, 'taille' => 'petit', 'decoratif' => true]),
        default => '',
    };
?>
      <?= dossier(numero_rendez_vous($r), $r['statut'], $feuille, [
          'titre' => 'Rendez-vous du ' . libelle_jour_court($r['jour']) . ' à ' . libelle_horaire($r['horaire']),
          'statut_libelle' => $libelle, 'tampon_html' => $tampon, 'classe' => 'dossier--rendez-vous',
          'meta' => ['Guichet' => 'n° 1', 'Ticket' => sprintf('%03d', $r['id']), 'Déposé le' => (new DateTime($r['date_creation']))->format('d/m/Y')],
      ]) ?>
<?php endforeach; ?>
    </div>
<?php endif; ?>
  </div>
</section>

<section class="ecran" id="demandes" aria-labelledby="demandes-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="demandes-titre">Mes réclamations</h2>
      <p class="chapeau">Les demandes envoyées au <a class="lien" href="contact.php">service des réclamations</a> pendant que vous étiez connecté, et la réponse officielle du cabinet quand elle arrive. Une demande supprimée ici est effacée du site ; si Le Bureau l'a déjà relevée, son dossier suit la <a class="lien" href="confidentialite.php">politique de confidentialité</a>.</p>
    </div>
<?php if (isset($avis['demandes'])): ?>
    <?= bloc_alerte($avis['demandes'][0], $avis['demandes'][1], ['titre' => $avis['demandes'][2]]) ?>
<?php endif; ?>
<?php if (!$demandes): ?>
    <div class="dossier-vide">
      <p>Aucune réclamation. Le cabinet n'attend rien de vous, et c'est réciproque.</p>
      <?= lien_fleche('Écrire au service des réclamations', 'contact.php') ?>
    </div>
<?php else: ?>
    <div class="dossier-pile">
<?php foreach ($demandes as $d):
    $feuille = etapes_statut($d['statut'], ['libelle' => 'Suivi de la demande ' . sprintf('%04d', $d['id']),
            'dates' => array_filter(['nouveau' => (new DateTime($d['date_creation']))->format('d/m/Y'),
                'repondu' => $d['date_reponse'] ? (new DateTime($d['date_reponse']))->format('d/m/Y') : null])])
        . '<div class="dossier-feuille__texte"><p class="mono muet">' . e($d['type_demande']) . '</p><p>' . nl2br(e($d['message'])) . '</p></div>';
    if ($d['statut'] === 'repondu' && $d['reponse'] !== null) {
        $feuille .= '<div class="dossier-feuille__reponse"><p class="mono">Réponse du cabinet, le ' . e((new DateTime($d['date_reponse']))->format('d/m/Y')) . '</p><p>' . nl2br(e($d['reponse'])) . '</p></div>';
    } else {
        $feuille .= '<p class="muet">Pas encore de réponse. Délai estimé : ' . e(delai_estime((int) $d['id'])) . '.</p>';
    }
    $feuille .= '<form class="dossier-feuille__action" action="compte.php#demandes" method="POST">' . csrf_champ()
        . bouton('Supprimer cette demande', null, ['variante' => 'secondaire', 'taille' => 'compacte', 'nom' => 'supprimer_demande', 'valeur' => (string) $d['id']]) . '</form>';
?>
      <?= dossier(sprintf('REC-%04d', $d['id']), $d['statut'], $feuille, [
          'titre' => $d['type_demande'] . ' du ' . (new DateTime($d['date_creation']))->format('d/m/Y'),
          'tampon_html' => $d['statut'] === 'repondu' ? tampon('Répondu', ['couleur' => 'vert', 'angle' => -8, 'taille' => 'petit', 'decoratif' => true]) : '',
      ]) ?>
<?php endforeach; ?>
    </div>
<?php endif; ?>
  </div>
</section>

<section class="ecran ecran--fond-2" id="conseil" aria-labelledby="conseil-titre">
  <div class="conteneur grille dossier-conseil">
    <div class="c-5 ecran__tete">
      <h2 id="conseil-titre">Le conseil du jour</h2>
      <p class="chapeau">Votre dossier a été traité avec le plus grand sérieux. Voici la recommandation du cabinet. Si elle vous plaît, gardez-la au carnet : elle y restera, inappliquée, aussi longtemps que vous voudrez.</p>
    </div>
    <div class="d-7">
      <?= fiche_conseil($conseil, [
          'id' => 'recommandation', 'titre' => 'Recommandation officielle',
          'actions_html' => bouton('Un autre conseil', 'compte.php#conseil', ['taille' => 'compacte', 'fleche' => false, 'attributs' => ['data-autre-conseil' => '#recommandation']])
              . bouton('Garder au carnet', null, ['type' => 'button', 'variante' => 'secondaire', 'taille' => 'compacte',
                  'attributs' => ['data-classer' => '#recommandation', 'data-source' => 'generateur', 'hidden' => true]])
              . bouton('Partager', null, ['type' => 'button', 'variante' => 'secondaire', 'taille' => 'compacte',
                  'attributs' => ['data-partager' => 'conseil', 'data-source' => '#recommandation', 'hidden' => true]]),
      ]) ?>
    </div>
  </div>
</section>

<section class="ecran" id="carnet" aria-labelledby="carnet-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="carnet-titre">Mes conseils gardés</h2>
      <p class="chapeau"><?= $carnet ? e($pluriel(count($carnet), 'conseil') . ' classé' . (count($carnet) > 1 ? 's' : '') . ' au dossier. Aucun n\'a été appliqué, et c\'est très bien ainsi.')
          : 'Votre carnet est vide. Gardez le conseil du jour, ou un verdict de la consultation, pour le retrouver ici.' ?></p>
    </div>
<?php if (isset($avis['carnet'])): ?>
    <?= bloc_alerte($avis['carnet'][0], $avis['carnet'][1], ['titre' => $avis['carnet'][2]]) ?>
<?php endif; ?>
<?php if ($carnet): ?>
    <ul class="rangee rangee--classeur dossier-carnet" style="--par: 3">
<?php foreach ($carnet as $c): ?>
      <li class="carte dossier-carnet__fiche">
        <p class="carte__etiquette">Dossier n° <?= e($c['dossier']) ?></p>
        <p class="dossier-carnet__texte"><?= e($c['texte']) ?></p>
        <div class="carte__pied">
          <span class="dossier-carnet__source"><?= e($sources[$c['source']] ?? 'Le cabinet') ?>, le <?= date('d/m/Y', strtotime($c['date_classement'])) ?></span>
          <form action="compte.php#carnet" method="POST">
            <?= csrf_champ() ?>
            <button class="dossier-carnet__retirer" type="submit" name="retirer_conseil" value="<?= (int) $c['id'] ?>">Retirer<span class="visuellement-cache"> le conseil du dossier n° <?= e($c['dossier']) ?></span></button>
          </form>
        </div>
      </li>
<?php endforeach; ?>
    </ul>
<?php else: ?>
    <div class="dossier-vide"><?= lien_fleche('Aller au conseil du jour', '#conseil') ?></div>
<?php endif; ?>
  </div>
</section>

<section class="ecran ecran--papier" id="compte" aria-labelledby="compte-titre">
  <div class="conteneur grille dossier-compte">
    <div class="c-5">
      <div class="ecran__tete">
        <h2 id="compte-titre">Mon compte</h2>
        <p class="chapeau">Ce que le cabinet sait de vous. C'est-à-dire pas grand-chose, et c'est très bien comme ça.</p>
      </div>
      <dl class="dossier-fiche">
        <div><dt>Nom</dt><dd><?= e($client['prenom'] . ' ' . $client['nom']) ?></dd></div>
        <div><dt>Adresse email</dt><dd><?= e($client['mail']) ?></dd></div>
        <div><dt>N° de client</dt><dd><?= e($numero_client) ?></dd></div>
      </dl>
      <p class="dossier-compte__aide">Pour changer de mot de passe : <a class="lien" href="mot-de-passe-oublie.php">recevoir un lien par courriel</a>. Le détail des données conservées est dans la <a class="lien" href="confidentialite.php">politique de confidentialité</a>.</p>
    </div>
    <form class="d-7 dossier-suppression" action="compte.php#compte" method="POST" aria-labelledby="suppression-titre">
      <?= csrf_champ() ?>
      <h3 id="suppression-titre">Supprimer mon compte</h3>
      <p>La suppression est définitive : votre compte, vos rendez-vous, vos réclamations et votre carnet sont effacés tout de suite. Aucun conseil ne pourra être récupéré, ce qui n'est pas une grande perte.</p>
<?php if ($erreur_compte || isset($avis['compte'])): ?>
      <?= bloc_alerte($erreur_compte ?: $avis['compte'][0], 'erreur', ['titre' => 'Suppression non effectuée']) ?>
<?php endif; ?>
      <?= champ('confirmation', 'Je veux supprimer définitivement mon compte', $erreur_compte ? ['confirmation' => $erreur_compte] : [], ['type' => 'case']) ?>
      <div class="boutons"><?= bouton('Supprimer mon compte', null, ['variante' => 'secondaire', 'nom' => 'supprimer_compte', 'valeur' => '1', 'classe' => 'bouton--danger']) ?></div>
    </form>
  </div>
</section>
<?php page_fin(); ?>
