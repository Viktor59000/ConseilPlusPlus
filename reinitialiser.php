<?php
/**
 * reinitialiser.php — Choix d'un nouveau mot de passe depuis le lien reçu par courriel (?jeton=…).
 * Le jeton doit exister, ne pas avoir expiré, et ne sert qu'une fois (tous les jetons du compte sont effacés).
 * Le mot de passe est enregistré avec la même méthode que l'inscription du cours (includes/compte.php).
 */
session_start();
require("db.php");
require("includes/csrf.php");
require("includes/courriel.php");
require("includes/compte.php");

$jeton = (string) ($_GET['jeton'] ?? $_POST['jeton'] ?? '');
$demande = false;
if (preg_match('/^[0-9a-f]{64}$/', $jeton)) {
    $stmt = $bdd->prepare('SELECT r.id_utilisateur, u.prenom FROM reinitialisations r JOIN utilisateurs u ON u.id = r.id_utilisateur
                           WHERE r.jeton = ? AND r.expire > NOW()');
    $stmt->execute([hash('sha256', $jeton)]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
}

$erreurs = [];
if ($demande && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $mdp = (string) ($_POST['mdp'] ?? '');
    if (!csrf_valide()) {
        $erreurs['session'] = 'Le formulaire a expiré. Merci de le renvoyer.';
    } elseif (mb_strlen($mdp) < MDP_LONGUEUR_MIN) {
        $erreurs['mdp'] = 'Choisissez au moins ' . MDP_LONGUEUR_MIN . ' caractères.';
    } elseif ($mdp !== (string) ($_POST['mdp2'] ?? '')) {
        $erreurs['mdp2'] = 'Les deux mots de passe ne sont pas identiques.';
    } else {
        $bdd->prepare('UPDATE utilisateurs SET mdp = ? WHERE id = ?')->execute([empreinte_comme_le_cours($mdp), $demande['id_utilisateur']]);
        $bdd->prepare('DELETE FROM reinitialisations WHERE id_utilisateur = ?')->execute([$demande['id_utilisateur']]);
        header('Location: login.php?reinitialise=1');
        exit();
    }
}

$titre = 'Nouveau mot de passe';
$description = 'Choisissez un nouveau mot de passe pour votre compte ConseilPlus+.';
$page = 'client';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Guichet n° 3 bis</p>
  <h1>Nouveau mot de passe</h1>
<?php if ($demande): ?>
  <p class="chapo">Bonjour <?= htmlspecialchars($demande['prenom']) ?>. Choisissez un mot de passe que vous oublierez moins vite.</p>
<?php else: ?>
  <p class="chapo">Ce lien n'est plus valable : il a expiré (1 heure) ou a déjà servi.</p>
<?php endif; ?>
</section>

<section class="conteneur guichets">
<?php if ($demande): ?>
  <form class="formulaire" action="reinitialiser.php" method="POST" aria-labelledby="titre-nouveau">
    <?= csrf_champ() ?>
    <input type="hidden" name="jeton" value="<?= htmlspecialchars($jeton) ?>">
    <h2 id="titre-nouveau">Votre nouveau mot de passe</h2>
    <p class="obligatoire">Tous les champs sont obligatoires.</p>
<?php if (isset($erreurs['session'])): ?>
    <p class="alerte alerte-erreur" role="alert"><?= htmlspecialchars($erreurs['session']) ?></p>
<?php endif; ?>
    <div class="champ<?= isset($erreurs['mdp']) ? ' champ-erreur' : '' ?>">
      <label for="re-mdp">Nouveau mot de passe <span class="facultatif">(<?= MDP_LONGUEUR_MIN ?> caractères minimum)</span></label>
      <input id="re-mdp" type="password" name="mdp" minlength="<?= MDP_LONGUEUR_MIN ?>" autocomplete="new-password" required<?= isset($erreurs['mdp']) ? ' aria-invalid="true" aria-describedby="err-mdp"' : '' ?>>
<?php if (isset($erreurs['mdp'])): ?>
      <p class="champ-msg" id="err-mdp"><?= htmlspecialchars($erreurs['mdp']) ?></p>
<?php endif; ?>
    </div>
    <div class="champ<?= isset($erreurs['mdp2']) ? ' champ-erreur' : '' ?>">
      <label for="re-mdp2">Le même, une seconde fois</label>
      <input id="re-mdp2" type="password" name="mdp2" minlength="<?= MDP_LONGUEUR_MIN ?>" autocomplete="new-password" required<?= isset($erreurs['mdp2']) ? ' aria-invalid="true" aria-describedby="err-mdp2"' : '' ?>>
<?php if (isset($erreurs['mdp2'])): ?>
      <p class="champ-msg" id="err-mdp2"><?= htmlspecialchars($erreurs['mdp2']) ?></p>
<?php endif; ?>
    </div>
    <button class="btn btn-tampon" type="submit">Enregistrer le mot de passe</button>
  </form>
<?php else: ?>
  <div class="formulaire">
    <h2>Lien expiré</h2>
    <p>Demandez-en un nouveau : le cabinet en a d'autres.</p>
    <a class="btn btn-tampon avance" href="mot-de-passe-oublie.php">Recevoir un nouveau lien</a>
  </div>
<?php endif; ?>
</section>

<?php include("includes/footer.php"); ?>
