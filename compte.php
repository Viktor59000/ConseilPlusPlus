<?php
/**
 * compte.php — Mon compte : informations du client, ses demandes de contact (avec la réponse du cabinet, relayée
 * par Le Bureau via l'API) et suppression définitive du compte (RGPD).
 * La suppression efface le carnet, les liens de réinitialisation, les duels, les demandes et la ligne utilisateurs.
 * Confirmation obligatoire + jeton CSRF (suppression du compte comme d'une demande).
 */
require_once __DIR__ . '/includes/init.php';
$utilisateur = exiger_connexion();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/formulaire.php';
require_once __DIR__ . '/includes/contacts.php';

$erreur = '';
$annonce_demandes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_demande'])) {
    if (!csrf_valide()) {
        $annonce_demandes = ['Le formulaire a expiré. Merci de le renvoyer.', 'erreur'];
    } elseif (supprimer_demande_client($bdd, (int) $_POST['supprimer_demande'], $utilisateur)) {
        // Redirection : un rechargement ne renvoie pas la suppression
        header('location: compte.php?demande_supprimee=1#mes-demandes');
        exit();
    } else {
        $annonce_demandes = ['Demande introuvable. Elle a peut-être déjà été supprimée, ou elle n\'est pas à vous.', 'erreur'];
    }
}
if (isset($_GET['demande_supprimee'])) {
    $annonce_demandes = ['Demande supprimée du site. Le cabinet ne s\'en souviendra pas plus que d\'habitude.', 'ok'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_compte'])) {
    if (!csrf_valide()) {
        $erreur = 'Le formulaire a expiré. Merci de le renvoyer.';
    } elseif (($_POST['confirmation'] ?? '') !== 'oui') {
        $erreur = 'Cochez la case de confirmation pour supprimer votre compte.';
    } else {
        // Effacement explicite (les clés étrangères ne sont pas appliquées partout : MyISAM sous WAMP)
        foreach (['carnet', 'reinitialisations', 'duels', 'contacts'] as $table) {
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

$stmt = $bdd->prepare('SELECT prenom, nom, mail FROM utilisateurs WHERE id = ?');
$stmt->execute([$utilisateur]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
$nb = $bdd->prepare('SELECT COUNT(*) FROM carnet WHERE id_utilisateur = ?');
$nb->execute([$utilisateur]);
$nb_conseils = (int) $nb->fetchColumn();
$demandes = demandes_du_client($bdd, $utilisateur);

$titre = 'Mon compte';
$description = 'Vos informations et la suppression de votre compte ConseilPlus+.';
$page = 'espace';
$onglet = 'compte';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/espace-nav.php';
?>

<section class="conteneur entete-page">
  <p class="etiquette">Dossier client</p>
  <h1>Mon compte</h1>
  <p class="chapo">Ce que le cabinet sait de vous. C'est-à-dire pas grand-chose, et c'est très bien comme ça.</p>
</section>

<section class="conteneur guichets">
  <div class="formulaire">
    <h2>Vos informations</h2>
    <dl class="coordonnees coordonnees-pile">
      <div><dt class="mono">Nom</dt><dd><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></dd></div>
      <div><dt class="mono">Adresse email</dt><dd><?= htmlspecialchars($client['mail']) ?></dd></div>
      <div><dt class="mono">Carnet</dt><dd><?= $nb_conseils ?> conseil<?= $nb_conseils > 1 ? 's' : '' ?> classé<?= $nb_conseils > 1 ? 's' : '' ?></dd></div>
    </dl>
    <p class="aide">Pour changer de mot de passe : <a href="mot-de-passe-oublie.php">recevoir un lien par courriel</a>.
      Le détail des données conservées est dans la <a href="confidentialite.php">politique de confidentialité</a>.</p>
  </div>

  <form class="formulaire" action="compte.php" method="POST" aria-labelledby="titre-suppression">
    <?= csrf_champ() ?>
    <h2 id="titre-suppression">Supprimer mon compte</h2>
    <p>La suppression est définitive : votre compte, votre carnet et vos demandes sont effacés tout de suite. Aucun conseil ne pourra être récupéré, ce qui n'est pas une grande perte.</p>
<?php if ($erreur): ?>
    <?= alerte($erreur) ?>
<?php endif; ?>
    <div class="champ champ-case">
      <input id="confirmation" type="checkbox" name="confirmation" value="oui" required>
      <label for="confirmation">Je veux supprimer définitivement mon compte</label>
    </div>
    <button class="btn btn-danger" type="submit" name="supprimer_compte" value="1">Supprimer mon compte</button>
  </form>
</section>

<section class="conteneur" id="mes-demandes" aria-labelledby="titre-demandes">
  <h2 id="titre-demandes">Mes demandes</h2>
  <p class="aide">Les demandes envoyées depuis le <a href="contact.php">formulaire de contact</a> pendant que vous étiez connecté, et la réponse officielle du cabinet quand il y en a une.
    Une demande supprimée ici est effacée du site ; si le cabinet l'a déjà relevée, son dossier suit la <a href="confidentialite.php">politique de confidentialité</a>.</p>
<?php if ($annonce_demandes): ?>
  <?= alerte(...$annonce_demandes) ?>
<?php endif; ?>
<?php if (!$demandes): ?>
  <p>Aucune demande pour l'instant. Le cabinet n'attend rien de vous, et c'est réciproque.</p>
<?php else: ?>
  <ul class="carnet">
<?php foreach ($demandes as $d): ?>
    <li class="carnet-item">
      <div class="carnet-entete mono">
        <span>Demande n° <?= sprintf('%04d', $d['id']) ?> · <?= htmlspecialchars($d['type_demande']) ?></span>
        <span><?= date('d/m/Y', strtotime($d['date_creation'])) ?></span>
      </div>
      <p><?= nl2br(htmlspecialchars($d['message'])) ?></p>
<?php if ($d['statut'] === 'repondu' && $d['reponse'] !== null): ?>
      <h3>Réponse du cabinet, le <?= date('d/m/Y', strtotime($d['date_reponse'])) ?></h3>
      <p><?= nl2br(htmlspecialchars($d['reponse'])) ?></p>
<?php else: ?>
      <p class="aide">Pas encore de réponse. Délai estimé : <?= htmlspecialchars(delai_estime((int) $d['id'])) ?>.</p>
<?php endif; ?>
      <div class="carnet-pied">
        <span class="mono">Statut : <?= LIBELLES_STATUT[$d['statut']] ?></span>
        <form action="compte.php#mes-demandes" method="POST">
          <?= csrf_champ() ?>
          <button class="lien-action" type="submit" name="supprimer_demande" value="<?= (int) $d['id'] ?>">Supprimer ma demande</button>
        </form>
      </div>
    </li>
<?php endforeach; ?>
  </ul>
<?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
