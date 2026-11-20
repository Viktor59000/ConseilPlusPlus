<?php
/**
 * reinitialiser.php — Choix d'un nouveau mot de passe depuis le lien reçu par courriel (?jeton=…).
 * Le jeton doit exister, ne pas avoir expiré, et ne sert qu'une fois (tous les jetons du compte sont effacés).
 * Le mot de passe est enregistré avec la même méthode que l'inscription du cours (includes/compte.php).
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/compte.php';
require_once __DIR__ . '/includes/formulaire.php';

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

page_debut([
    'titre' => 'Nouveau mot de passe',
    'description' => 'Choisissez un nouveau mot de passe pour votre compte ConseilPlus+.',
    'page' => 'dossier',
    'robots' => 'noindex',
    'css' => ['css/v2/pages/formulaire.css'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Nouveau mot de passe</h1>
<?php if ($demande): ?>
      <p class="heros__chapeau">Bonjour <?= e($demande['prenom']) ?>. Choisissez un mot de passe que vous oublierez moins vite.</p>
<?php else: ?>
      <p class="heros__chapeau">Ce lien n'est plus valable : il a expiré (1 heure) ou a déjà servi.</p>
<?php endif; ?>
    </div>
    <div class="heros__visuel">
<?php if ($demande): ?>
      <form class="guichet-feuille formulaire" action="reinitialiser.php" method="POST" aria-labelledby="titre-nouveau">
        <?= csrf_champ() ?>
        <input type="hidden" name="jeton" value="<?= e($jeton) ?>">
        <div class="ecran__tete">
          <p class="reference reference--coin"><span>Formulaire P-1</span></p>
          <h2 id="titre-nouveau">Votre nouveau mot de passe</h2>
          <p class="muet">Tous les champs sont obligatoires.</p>
        </div>
<?php if (isset($erreurs['session'])): ?>
        <?= bloc_alerte($erreurs['session'], 'erreur', ['titre' => 'Formulaire expiré']) ?>
<?php endif; ?>
        <?= champ('mdp', 'Nouveau mot de passe', $erreurs, ['id' => 're-mdp', 'type' => 'password', 'requis' => true, 'autocomplete' => 'new-password',
            'aide' => MDP_LONGUEUR_MIN . ' caractères minimum.', 'attributs' => ['minlength' => MDP_LONGUEUR_MIN]]) ?>
        <?= champ('mdp2', 'Le même, une seconde fois', $erreurs, ['id' => 're-mdp2', 'type' => 'password', 'requis' => true, 'autocomplete' => 'new-password', 'attributs' => ['minlength' => MDP_LONGUEUR_MIN]]) ?>
        <div class="boutons"><?= bouton('Enregistrer le mot de passe', null, ['type' => 'submit']) ?></div>
      </form>
<?php else: ?>
      <div class="guichet-feuille formulaire">
        <h2>Lien expiré</h2>
        <p>Demandez-en un nouveau : le cabinet en a d'autres.</p>
        <?= tampon('Périmé', ['angle' => -8, 'mouvement' => 'au-chargement', 'decoratif' => true, 'classe' => 'feuille-tampon']) ?>
        <div class="boutons"><?= bouton('Recevoir un nouveau lien', 'mot-de-passe-oublie.php') ?></div>
      </div>
<?php endif; ?>
    </div>
  </div>
</section>
<?php page_fin(); ?>
