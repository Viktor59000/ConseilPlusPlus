<?php
/**
 * contact.php — Formulaire de contact.
 * La demande est envoyée en POST à l'API (api/contacts.php), qui la valide et l'enregistre
 * avec le statut « nouveau ». Le logiciel de gestion la récupère ensuite via l'API.
 */
session_start();
require("includes/contacts.php");
require("includes/http.php");
require("includes/csrf.php");

$types = TYPES_DEMANDE;
$valeurs = ['nom' => '', 'email' => '', 'message' => '', 'type_demande' => ''];
$erreurs = [];
$erreur_api = false;
$erreur_session = false;
$trop_de_demandes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valeurs as $champ => $_) {
        $valeurs[$champ] = trim((string) ($_POST[$champ] ?? ''));
    }

    if (!csrf_valide()) {
        $erreur_session = true;
    } elseif (($_POST['site_web'] ?? '') !== '') {
        // Champ piège invisible rempli : c'est un robot. On fait semblant d'accepter sans rien enregistrer.
        header('location: contact.php?envoye=1');
        exit();
    } else {
        // L'adresse IP du visiteur est transmise à l'API pour sa limite d'envois (l'appel part du serveur)
        $reponse = appeler_api('POST', url_site() . '/api/contacts.php', $valeurs, ['X-Client-IP: ' . $_SERVER['REMOTE_ADDR']]);
    }

    if ($erreur_session) {
        // message affiché plus bas
    } elseif ($reponse['code'] === 429) {
        $trop_de_demandes = true;
    } elseif ($reponse['code'] === 201) {
        // Redirection après envoi pour éviter un double enregistrement au rechargement
        header('location: contact.php?envoye=1');
        exit();
    } elseif ($reponse['code'] === 422) {
        $erreurs = $reponse['data']['champs'] ?? [];
    } else {
        $erreur_api = true;
    }
}

$titre = 'Contact';
$description = 'Contactez ConseilPlus+ : demande de conseil, réclamation ou devis Premium. Réponse sous 3 jours ou jamais.';
$page = 'contact';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Guichet n° 1</p>
  <h1>Nous contacter</h1>
  <p class="chapo">Une question, une réclamation, un besoin de conseil ? Remplissez le formulaire. Il sera lu. Un jour.</p>
</section>

<section class="conteneur guichets">
  <form class="formulaire" action="contact.php" method="POST" novalidate aria-labelledby="titre-contact">
    <?= csrf_champ() ?>
    <!-- Champ piège : invisible pour les humains, rempli par les robots -->
    <div class="piege" aria-hidden="true">
      <label for="ct-site">Ne pas remplir</label>
      <input id="ct-site" type="text" name="site_web" tabindex="-1" autocomplete="off">
    </div>
    <h2 id="titre-contact">Formulaire de demande</h2>
<?php if (isset($_GET['envoye'])): ?>
    <p class="alerte alerte-ok" role="status">Demande enregistrée sous le statut « nouveau ». Nous la traiterons avec la lenteur qui nous caractérise.</p>
<?php elseif ($erreur_session): ?>
    <p class="alerte alerte-erreur" role="alert">Le formulaire a expiré. Merci de le renvoyer.</p>
<?php elseif ($trop_de_demandes): ?>
    <p class="alerte alerte-erreur" role="alert">Vous avez envoyé beaucoup de demandes en peu de temps. Même nous, nous avons des limites : réessayez dans une minute.</p>
<?php elseif ($erreur_api): ?>
    <p class="alerte alerte-erreur" role="alert">Notre guichet est momentanément fermé (service indisponible). Réessayez dans quelques instants.</p>
<?php elseif ($erreurs): ?>
    <p class="alerte alerte-erreur" role="alert">Le formulaire contient <?= count($erreurs) ?> erreur(s). Corrigez les champs indiqués puis renvoyez-le.</p>
<?php endif; ?>

    <div class="champ<?= isset($erreurs['nom']) ? ' champ-erreur' : '' ?>">
      <label for="ct-nom">Nom</label>
      <input id="ct-nom" type="text" name="nom" maxlength="100" autocomplete="name" required value="<?= htmlspecialchars($valeurs['nom']) ?>"<?= isset($erreurs['nom']) ? ' aria-invalid="true" aria-describedby="err-nom"' : '' ?>>
<?php if (isset($erreurs['nom'])): ?>
      <p class="champ-msg" id="err-nom"><?= htmlspecialchars($erreurs['nom']) ?></p>
<?php endif; ?>
    </div>

    <div class="champ<?= isset($erreurs['email']) ? ' champ-erreur' : '' ?>">
      <label for="ct-email">Adresse email</label>
      <input id="ct-email" type="email" name="email" maxlength="150" autocomplete="email" required value="<?= htmlspecialchars($valeurs['email']) ?>"<?= isset($erreurs['email']) ? ' aria-invalid="true" aria-describedby="err-email"' : '' ?>>
<?php if (isset($erreurs['email'])): ?>
      <p class="champ-msg" id="err-email"><?= htmlspecialchars($erreurs['email']) ?></p>
<?php endif; ?>
    </div>

    <div class="champ<?= isset($erreurs['type_demande']) ? ' champ-erreur' : '' ?>">
      <label for="ct-type">Type de demande</label>
      <select id="ct-type" name="type_demande" required<?= isset($erreurs['type_demande']) ? ' aria-invalid="true" aria-describedby="err-type"' : '' ?>>
        <option value="">Choisir…</option>
<?php foreach ($types as $type): ?>
        <option<?= $valeurs['type_demande'] === $type ? ' selected' : '' ?>><?= htmlspecialchars($type) ?></option>
<?php endforeach; ?>
      </select>
<?php if (isset($erreurs['type_demande'])): ?>
      <p class="champ-msg" id="err-type"><?= htmlspecialchars($erreurs['type_demande']) ?></p>
<?php endif; ?>
    </div>

    <div class="champ<?= isset($erreurs['message']) ? ' champ-erreur' : '' ?>">
      <label for="ct-message">Message</label>
      <textarea id="ct-message" name="message" rows="6" required<?= isset($erreurs['message']) ? ' aria-invalid="true" aria-describedby="err-message"' : '' ?>><?= htmlspecialchars($valeurs['message']) ?></textarea>
<?php if (isset($erreurs['message'])): ?>
      <p class="champ-msg" id="err-message"><?= htmlspecialchars($erreurs['message']) ?></p>
<?php endif; ?>
    </div>

    <button class="btn btn-tampon" type="submit">Envoyer ma demande</button>
  </form>

  <aside class="formulaire formulaire-info" aria-label="Coordonnées">
    <h2>Coordonnées</h2>
    <dl class="coordonnees coordonnees-pile">
      <div>
        <dt class="mono">Courrier</dt>
        <dd>12 bis, impasse du Consensus Mou<br>Bâtiment C, escalier D, au fond à gauche</dd>
      </div>
      <div>
        <dt class="mono">Horaires</dt>
        <dd>Du lundi au vendredi, de 14h12 à 14h47.<br>Fermé les jours de vent.</dd>
      </div>
      <div>
        <dt class="mono">Délai de réponse</dt>
        <dd>Entre 3 jours et jamais.</dd>
      </div>
    </dl>
  </aside>
</section>

<?php include("includes/footer.php"); ?>
