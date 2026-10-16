<?php
/**
 * mot-de-passe-oublie.php — Demande de réinitialisation du mot de passe.
 * Envoie par courriel (Brevo) un lien valable 1 heure, à usage unique, vers reinitialiser.php.
 * La réponse est toujours la même, que l'adresse ait un compte ou non (on ne révèle pas qui est inscrit).
 * Au plus 3 demandes par compte et par heure.
 */
session_start();
require("db.php");
require_once("config.php");
require("includes/csrf.php");
require("includes/courriel.php");
require("includes/compte.php");

$envoye = false;
$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if (!csrf_valide()) {
        $erreur = 'Le formulaire a expiré. Merci de le renvoyer.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Indiquez une adresse email valide.';
    } else {
        $stmt = $bdd->prepare('SELECT id, prenom, nom FROM utilisateurs WHERE mail = ?');
        $stmt->execute([$email]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($client) {
            $recentes = $bdd->prepare('SELECT COUNT(*) FROM reinitialisations WHERE id_utilisateur = ? AND date_demande > NOW() - INTERVAL 1 HOUR');
            $recentes->execute([$client['id']]);
            if ($recentes->fetchColumn() < 3) {
                // Le jeton n'est stocké que sous forme d'empreinte : une fuite de la base ne donne pas de lien valable
                $jeton = bin2hex(random_bytes(32));
                $bdd->prepare('INSERT INTO reinitialisations (id_utilisateur, jeton, expire) VALUES (?, ?, NOW() + INTERVAL 1 HOUR)')
                    ->execute([$client['id'], hash('sha256', $jeton)]);
                $lien = url_du_site() . '/reinitialiser.php?jeton=' . $jeton;
                $corps = "Bonjour {$client['prenom']},\n\n"
                    . "Une demande de nouveau mot de passe a été déposée pour votre compte ConseilPlus+. "
                    . "Votre ancien mot de passe a été égaré par nos services, ce qui arrive.\n\n"
                    . "Pour en choisir un nouveau, ouvrez ce lien (valable 1 heure, une seule fois) :\n$lien\n\n"
                    . "Si vous n'êtes pas à l'origine de cette demande, ignorez ce courriel : votre mot de passe actuel reste valable.";
                $html = gabarit_courriel([
                    'titre' => 'Nouveau mot de passe',
                    'corps' => $corps,
                    'dossier' => null,
                    'signataire' => 'Le service des mots de passe égarés',
                    'fonction' => 'Guichet n° 3',
                    'tampon' => 'Urgent',
                    'apercu' => 'Votre lien pour choisir un nouveau mot de passe (valable 1 heure).',
                ]);
                if (COURRIEL_BREVO_CLE !== '') {
                    envoyer_courriel(COURRIEL_BREVO_CLE, COURRIEL_EXPEDITEUR, COURRIEL_EXPEDITEUR_NOM, $email,
                        trim($client['prenom'] . ' ' . $client['nom']), 'Votre nouveau mot de passe ConseilPlus+',
                        $corps . signature_texte('Le service des mots de passe égarés', 'Guichet n° 3'), $html);
                } else {
                    error_log("ConseilPlus+ : envoi de courriel non configuré, lien de réinitialisation : $lien");
                }
            }
        }
        $envoye = true;
    }
}

$titre = 'Mot de passe oublié';
$description = 'Mot de passe oublié : recevez un lien pour en choisir un nouveau. Le cabinet égare parfois les choses.';
$page = 'client';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Guichet n° 3 bis</p>
  <h1>Mot de passe oublié</h1>
  <p class="chapo">Cela arrive aux meilleurs. Et à nous aussi, souvent.</p>
</section>

<section class="conteneur guichets">
  <form class="formulaire" action="mot-de-passe-oublie.php" method="POST" novalidate aria-labelledby="titre-oubli">
    <?= csrf_champ() ?>
    <h2 id="titre-oubli">Recevoir un lien</h2>
<?php if ($envoye): ?>
    <p class="alerte alerte-ok" role="status">Si un compte existe avec cette adresse, un courriel vient de partir. Le lien est valable 1 heure. Pensez à regarder dans les indésirables.</p>
<?php else: ?>
<?php if ($erreur): ?>
    <p class="alerte alerte-erreur" role="alert"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>
    <div class="champ">
      <label for="oubli-email">Adresse email du compte</label>
      <input id="oubli-email" type="email" name="email" maxlength="191" autocomplete="email" required value="<?= htmlspecialchars($email) ?>">
    </div>
    <button class="btn btn-tampon" type="submit">Envoyer le lien</button>
<?php endif; ?>
    <p class="aide"><a href="login.php">Retour à la connexion</a></p>
  </form>
</section>

<?php include("includes/footer.php"); ?>
