<?php
/**
 * login.php — Connexion et inscription.
 * Formulaires en POST vers auth/connexion.php et auth/inscription.php.
 * Alertes via ?error=1, ?error=mail, ?success=1, ?reinitialise=1 (mot de passe changé) ou ?supprime=1 (compte supprimé).
 */
require_once __DIR__ . '/includes/init.php';

// Redirection si déjà authentifié
if (isset($_SESSION['user_id'])) {
    header('location: conseil.php');
    exit();
}

require_once __DIR__ . '/includes/compte.php'; // MDP_LONGUEUR_MIN
require_once __DIR__ . '/includes/formulaire.php';

$titre = 'Espace client';
$description = 'Connectez-vous à votre espace client ConseilPlus+ ou créez un compte pour accéder à des conseils absurdes illimités.';
$page = 'client';
$erreur = $_GET['error'] ?? null;
include __DIR__ . '/includes/header.php';
?>

<section class="conteneur entete-page">
  <p class="etiquette">Guichet n° 3</p>
  <h1>Espace client</h1>
  <p class="chapo">Connectez-vous pour accéder à des conseils illimités. Ou créez un compte, c'est gratuit et tout aussi inutile.</p>
</section>

<section class="conteneur guichets">
  <form class="formulaire" action="auth/connexion.php" method="POST" aria-labelledby="titre-connexion">
    <?= csrf_champ() ?>
    <h2 id="titre-connexion">Connexion</h2>
    <p class="obligatoire">Tous les champs sont obligatoires.</p>
<?php if (isset($_GET['success'])): ?>
    <?= alerte('Inscription enregistrée. Vous pouvez vous connecter.', 'ok') ?>
<?php elseif (isset($_GET['reinitialise'])): ?>
    <?= alerte('Nouveau mot de passe enregistré. Vous pouvez vous connecter avec.', 'ok') ?>
<?php elseif (isset($_GET['supprime'])): ?>
    <?= alerte('Votre compte et votre carnet ont été supprimés. Le cabinet vous regrette, moyennement.', 'ok') ?>
<?php elseif ($erreur === 'session'): ?>
    <?= alerte('Le formulaire a expiré. Merci de le renvoyer.') ?>
<?php elseif ($erreur === 'trop'): ?>
    <?= alerte("Trop d'essais en peu de temps. Le guichet fait une pause : réessayez dans un quart d'heure, ou utilisez « Mot de passe oublié ».") ?>
<?php elseif ($erreur === '1'): ?>
    <?= alerte('Email ou mot de passe incorrect. Vérifiez votre saisie, ou créez un compte ci-contre.') ?>
<?php endif; ?>
    <div class="champ">
      <label for="co-email">Adresse email</label>
      <input id="co-email" type="email" name="email" autocomplete="email" required>
    </div>
    <div class="champ">
      <label for="co-mdp">Mot de passe</label>
      <input id="co-mdp" type="password" name="mdp" autocomplete="current-password" required>
    </div>
    <button class="btn btn-tampon" type="submit">Se connecter</button>
    <p class="aide"><a href="mot-de-passe-oublie.php">Mot de passe oublié ?</a></p>
  </form>

  <form class="formulaire" action="auth/inscription.php" method="POST" aria-labelledby="titre-inscription">
    <?= csrf_champ() ?>
    <h2 id="titre-inscription">Ouvrir un dossier</h2>
    <p class="obligatoire">Tous les champs sont obligatoires.</p>
<?php if ($erreur === 'mail'): ?>
    <?= alerte('Cet email possède déjà un dossier. Connectez-vous avec, ou utilisez une autre adresse.') ?>
<?php endif; ?>
    <div class="champ-duo">
      <div class="champ">
        <label for="in-prenom">Prénom</label>
        <input id="in-prenom" type="text" name="prenom" autocomplete="given-name" required>
      </div>
      <div class="champ">
        <label for="in-nom">Nom</label>
        <input id="in-nom" type="text" name="nom" autocomplete="family-name" required>
      </div>
    </div>
    <div class="champ">
      <label for="in-email">Adresse email</label>
      <input id="in-email" type="email" name="email" autocomplete="email" required>
    </div>
    <div class="champ">
      <label for="in-mdp">Mot de passe <span class="facultatif">(<?= MDP_LONGUEUR_MIN ?> caractères minimum)</span></label>
      <input id="in-mdp" type="password" name="mdp" minlength="<?= MDP_LONGUEUR_MIN ?>" autocomplete="new-password" required>
    </div>
    <button class="btn btn-clair" type="submit">Créer mon compte</button>
  </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
