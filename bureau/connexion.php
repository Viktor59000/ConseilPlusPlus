<?php
/**
 * bureau/connexion.php — Connexion des agents.
 * Tant qu'aucun agent n'existe, la page propose la création du premier compte, protégée par la clé d'API
 * du site (connue seulement de l'administrateur) : personne d'autre ne peut s'approprier le Bureau en ligne.
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
        $erreur = 'Identifiants incorrects. Le Bureau ne vous reconnaît pas, ce qui ne veut rien dire.';
    }
}

$titre = $installation ? 'Installation' : 'Connexion';
require __DIR__ . '/includes/haut.php';
?>
<section class="carte carte-etroite">
  <p class="etiquette mono"><?= $installation ? 'Première ouverture du Bureau' : 'Accès réservé aux agents' ?></p>
  <h1><?= $installation ? 'Créer le premier agent' : 'Prise de poste' ?></h1>
<?php if ($installation): ?>
  <p class="aide">Aucun agent n'est encore enregistré. Pour créer le premier compte, saisissez la clé d'API du site (<code>API_KEY</code>, dans <code>config.php</code> ou dans les variables de Render).</p>
<?php endif; ?>
<?php if ($erreur): ?>
  <p class="annonce annonce-erreur" role="alert"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>
  <form method="post" class="formulaire">
    <?= csrf_champ() ?>
<?php if ($installation): ?>
    <label for="cle">Clé d'installation</label>
    <input id="cle" name="cle" type="password" required autocomplete="off">
    <label for="nom">Nom de l'agent</label>
    <input id="nom" name="nom" type="text" required maxlength="100" autocomplete="name">
<?php endif; ?>
    <label for="email">Adresse email</label>
    <input id="email" name="email" type="email" required maxlength="191" autocomplete="username" value="<?= htmlspecialchars($email) ?>">
    <label for="mdp">Mot de passe<?= $installation ? ' (10 caractères minimum)' : '' ?></label>
    <input id="mdp" name="mdp" type="password" required minlength="<?= $installation ? 10 : 1 ?>" autocomplete="<?= $installation ? 'new-password' : 'current-password' ?>">
    <button class="btn btn-tampon" type="submit"><?= $installation ? 'Créer le compte et entrer' : 'Entrer au Bureau' ?></button>
  </form>
</section>
<?php require __DIR__ . '/includes/bas.php'; ?>
