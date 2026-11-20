<?php
/**
 * login.php — Connexion et inscription : l'entrée de Mon dossier (compte obligatoire pour déposer et suivre un dossier).
 * Formulaires en POST vers auth/connexion.php et auth/inscription.php (code du cours, non modifié).
 * Alertes via ?error=1, ?error=mail, ?error=session, ?error=trop, ?success=1, ?reinitialise=1 (mot de passe changé)
 * ou ?supprime=1 (compte supprimé).
 */
require_once __DIR__ . '/includes/init.php';

// Déjà authentifié : direction Mon dossier
if (isset($_SESSION['user_id'])) {
    header('location: compte.php');
    exit();
}

require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/compte.php'; // MDP_LONGUEUR_MIN
require_once __DIR__ . '/includes/formulaire.php';

$erreur = $_GET['error'] ?? null;

page_debut([
    'titre' => 'Mon dossier : connexion',
    'description' => 'Connectez-vous à Mon dossier ConseilPlus+, ou ouvrez-en un : il faut un compte pour déposer une demande au guichet et suivre son instruction.',
    'page' => 'dossier',
    'css' => ['css/v2/pages/formulaire.css'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Mon dossier</h1>
      <p class="heros__chapeau">Pour déposer une demande au guichet et suivre son instruction, le cabinet exige un compte. Ce n'est pas lui qui l'a voulu : c'est la procédure, et la procédure l'a voulu avant lui. L'ouverture est gratuite, immédiate, et sans pièce à fournir.</p>
      <p class="formulaire__aide">Pas encore de compte ? <a class="lien" href="#inscription">Ouvrir un dossier</a>.</p>
    </div>
    <div class="heros__visuel">
      <form class="guichet-feuille formulaire" action="auth/connexion.php" method="POST" aria-labelledby="titre-connexion">
        <?= csrf_champ() ?>
        <div class="ecran__tete">
          <p class="reference reference--coin"><span>Formulaire C-1</span></p>
          <h2 id="titre-connexion">Connexion</h2>
          <p class="muet">Tous les champs sont obligatoires.</p>
        </div>
<?php if (isset($_GET['success'])): ?>
        <?= bloc_alerte('Inscription enregistrée. Vous pouvez vous connecter.', 'succes', ['titre' => 'Dossier ouvert']) ?>
<?php elseif (isset($_GET['reinitialise'])): ?>
        <?= bloc_alerte('Nouveau mot de passe enregistré. Vous pouvez vous connecter avec.', 'succes', ['titre' => 'Mot de passe changé']) ?>
<?php elseif (isset($_GET['supprime'])): ?>
        <?= bloc_alerte('Votre compte et votre carnet ont été supprimés. Le cabinet vous regrette, moyennement.', 'succes', ['titre' => 'Compte supprimé']) ?>
<?php elseif ($erreur === 'session'): ?>
        <?= bloc_alerte('Le formulaire a expiré. Merci de le renvoyer.', 'erreur', ['titre' => 'Formulaire expiré']) ?>
<?php elseif ($erreur === 'trop'): ?>
        <?= bloc_alerte("Trop d'essais en peu de temps. Le guichet fait une pause : réessayez dans un quart d'heure, ou utilisez « Mot de passe oublié ».", 'erreur', ['titre' => 'Guichet en pause']) ?>
<?php elseif ($erreur === '1'): ?>
        <?= bloc_alerte('Email ou mot de passe incorrect. Vérifiez votre saisie, ou ouvrez un dossier plus bas.', 'erreur', ['titre' => 'Connexion refusée']) ?>
<?php endif; ?>
        <?= champ('email', 'Adresse email', [], ['id' => 'co-email', 'type' => 'email', 'requis' => true, 'autocomplete' => 'email', 'officiel' => true]) ?>
        <?= champ('mdp', 'Mot de passe', [], ['id' => 'co-mdp', 'type' => 'password', 'requis' => true, 'autocomplete' => 'current-password']) ?>
        <div class="boutons">
          <?= bouton('Se connecter', null, ['type' => 'submit']) ?>
          <?= lien_fleche('Mot de passe oublié', 'mot-de-passe-oublie.php') ?>
        </div>
      </form>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="inscription" aria-labelledby="titre-inscription">
  <div class="conteneur grille login-inscription">
    <div class="ecran__tete">
      <h2 id="titre-inscription">Ouvrir un dossier</h2>
      <p class="chapeau">Un prénom, un nom, une adresse et un mot de passe. Le cabinet ne vous demandera rien d'autre, sauf de patienter.</p>
    </div>
    <form class="c-7 guichet-feuille formulaire" action="auth/inscription.php" method="POST" aria-labelledby="titre-inscription">
      <?= csrf_champ() ?>
      <p class="reference reference--coin"><span>Formulaire O-1</span></p>
      <p class="muet">Tous les champs sont obligatoires.</p>
<?php if ($erreur === 'mail'): ?>
      <?= bloc_alerte('Cet email possède déjà un dossier. Connectez-vous avec, ou utilisez une autre adresse.', 'erreur', ['titre' => 'Dossier existant']) ?>
<?php endif; ?>
      <div class="formulaire__duo">
        <?= champ('prenom', 'Prénom', [], ['id' => 'in-prenom', 'requis' => true, 'autocomplete' => 'given-name', 'officiel' => true]) ?>
        <?= champ('nom', 'Nom', [], ['id' => 'in-nom', 'requis' => true, 'autocomplete' => 'family-name', 'officiel' => true]) ?>
      </div>
      <?= champ('email', 'Adresse email', [], ['id' => 'in-email', 'type' => 'email', 'requis' => true, 'autocomplete' => 'email', 'officiel' => true]) ?>
      <?= champ('mdp', 'Mot de passe', [], ['id' => 'in-mdp', 'type' => 'password', 'requis' => true, 'autocomplete' => 'new-password',
          'aide' => MDP_LONGUEUR_MIN . ' caractères minimum. Le cabinet ne le lira pas : il ne garde qu\'une empreinte.', 'attributs' => ['minlength' => MDP_LONGUEUR_MIN]]) ?>
      <div class="boutons"><?= bouton('Ouvrir mon dossier', null, ['type' => 'submit', 'variante' => 'secondaire']) ?></div>
    </form>
    <div class="d-9 login-scene"><?= scene('dossier', 'Une chemise cartonnée kraft, encore vide, qui attend son premier problème.', ['tailles' => '(min-width: 1024px) 25vw, 100vw']) ?></div>
  </div>
</section>
<?php page_fin(); ?>
