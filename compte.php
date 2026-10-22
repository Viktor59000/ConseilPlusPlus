<?php
/**
 * compte.php — Mon compte : informations du client et suppression définitive du compte (RGPD).
 * La suppression efface le carnet, les liens de réinitialisation et la ligne utilisateurs.
 * Confirmation obligatoire + jeton CSRF.
 */
require_once __DIR__ . '/includes/init.php';
$utilisateur = exiger_connexion();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/formulaire.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_compte'])) {
    if (!csrf_valide()) {
        $erreur = 'Le formulaire a expiré. Merci de le renvoyer.';
    } elseif (($_POST['confirmation'] ?? '') !== 'oui') {
        $erreur = 'Cochez la case de confirmation pour supprimer votre compte.';
    } else {
        // Effacement explicite (les clés étrangères ne sont pas appliquées partout : MyISAM sous WAMP)
        foreach (['carnet', 'reinitialisations', 'duels'] as $table) {
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
    <p>La suppression est définitive : votre compte et votre carnet sont effacés tout de suite. Aucun conseil ne pourra être récupéré, ce qui n'est pas une grande perte.</p>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
