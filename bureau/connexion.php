<?php
/**
 * bureau/connexion.php — Connexion des agents (bureau/index.php y mène tant qu'aucun agent n'est connecté).
 * Tant qu'aucun agent n'existe, la page propose la création du premier compte, protégée par la clé d'API
 * du site (connue seulement de l'administrateur) : personne d'autre ne peut s'approprier le Bureau en ligne.
 * Présentation : la gravure de la salle de tri à gauche, le formulaire sur une feuille de papier à droite.
 */
require_once __DIR__ . '/includes/bdd.php';
require_once __DIR__ . '/includes/session.php';

if (isset($_SESSION['agent'])) {
    rediriger('tableau.php');
}

$installation = (int) $bdd->query('SELECT COUNT(*) FROM agents')->fetchColumn() === 0;
$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $mdp = (string) ($_POST['mdp'] ?? '');

    if (!csrf_valide()) {
        $erreur = 'Votre session a expiré. Rechargez la page et recommencez.';
    } elseif ($installation) {
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $cle = (string) ($_POST['cle'] ?? '');
        if (!hash_equals(BUREAU_API_KEY, $cle)) {
            $erreur = 'Clé d\'installation incorrecte.';
        } elseif ($nom === '' || mb_strlen($nom) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($mdp) < 10) {
            $erreur = 'Indiquez un nom, une adresse email valide et un mot de passe d\'au moins 10 caractères.';
        } else {
            $bdd->prepare('INSERT INTO agents (nom, email, mdp) VALUES (?, ?, ?)')
                ->execute([$nom, $email, password_hash($mdp, PASSWORD_DEFAULT)]);
            annoncer('Compte créé. Le Bureau vous souhaite une prise de poste moyennement agréable.');
            $installation = false;
        }
    }

    if (!$installation && $erreur === '') {
        $stmt = $bdd->prepare('SELECT id, nom, mdp FROM agents WHERE email = ?');
        $stmt->execute([$email]);
        $agent = $stmt->fetch();
        if ($agent && password_verify($mdp, $agent['mdp'])) {
            session_regenerate_id(true);
            $_SESSION['agent'] = ['id' => (int) $agent['id'], 'nom' => $agent['nom']];
            rediriger('tableau.php');
        }
        $agent = null;
        $erreur = 'Identifiants incorrects. Le Bureau ne vous reconnaît pas, ce qui ne veut rien dire.';
    }
}

$titre = $installation ? 'Installation' : 'Connexion';
$classe_corps = 'bureau--connexion';
require __DIR__ . '/includes/haut.php';
?>
<div class="connexion">
  <figure class="connexion__scene">
    <?= scene('salle-de-tri', 'La salle de tri du Bureau : des casiers jusqu\'au plafond, trois corbeilles sur la table, une lampe en laiton et un tampon qui attend. Rien n\'est dans l\'ordre, mais tout est étiqueté.', ['prioritaire' => true, 'classe' => 'scene--libre', 'tailles' => '(min-width: 1024px) 58vw, 100vw']) ?>
  </figure>

  <section class="connexion__poste" aria-labelledby="titre-connexion">
    <a class="connexion__marque" href="<?= e(url_publique()) ?>/"><?= ui_logo('logo-connexion') ?></a>
    <div class="connexion__feuille" data-papier>
      <p class="connexion__ref"><?= $installation ? 'Formulaire B-0' : 'Formulaire B-1' ?></p>
      <h1 id="titre-connexion"><?= $installation ? 'Créer le premier agent' : 'Prise de poste' ?></h1>
      <p class="connexion__chapeau"><?= $installation
          ? 'Aucun agent n\'est encore enregistré. Pour ouvrir le Bureau, présentez la clé d\'API du site (API_KEY, dans config.php ou dans les réglages de Render).'
          : 'Accès réservé aux agents du cabinet. Ici, on trie, on tamponne et on répond ; la base du site ne se lit que par son API.' ?></p>
<?php if ($erreur): ?>
      <?= bloc_alerte($erreur, 'erreur') ?>
<?php endif; ?>
      <form method="post" class="connexion__formulaire">
        <?= csrf_champ() ?>
<?php if ($installation): ?>
        <?= champ('cle', 'Clé d\'installation', [], ['type' => 'password', 'requis' => true, 'officiel' => true, 'autocomplete' => 'off']) ?>
        <?= champ('nom', 'Nom de l\'agent', [], ['requis' => true, 'maxlength' => 100, 'autocomplete' => 'name']) ?>
<?php endif; ?>
        <?= champ('email', 'Adresse email', [], ['type' => 'email', 'requis' => true, 'maxlength' => 191, 'autocomplete' => 'username', 'valeur' => $email, 'officiel' => true]) ?>
        <?= champ('mdp', 'Mot de passe', [], ['type' => 'password', 'requis' => true, 'autocomplete' => $installation ? 'new-password' : 'current-password',
            'aide' => $installation ? '10 caractères au moins. Le Bureau ne le relira jamais, il le compare.' : null,
            'attributs' => ['minlength' => $installation ? 10 : 1]]) ?>
        <?= bouton($installation ? 'Créer le compte et entrer' : 'Entrer au Bureau', null, ['large' => true, 'fleche' => false]) ?>
      </form>
      <p class="connexion__pied">Toute tentative est consignée, puis classée.</p>
    </div>
    <p class="connexion__retour"><a href="<?= e(url_publique()) ?>/">Retour au site du cabinet</a></p>
  </section>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
