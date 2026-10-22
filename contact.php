<?php
/**
 * contact.php — Formulaire de contact.
 * La demande est envoyée en POST à l'API (api/contacts.php), qui la valide et l'enregistre
 * avec le statut « nouveau ». Le logiciel de gestion la récupère ensuite via l'API.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/bdd.php';
require_once __DIR__ . '/includes/contacts.php';
require_once __DIR__ . '/includes/http.php';
require_once __DIR__ . '/includes/courriel.php';
require_once __DIR__ . '/includes/formulaire.php';

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
        $reponse = appeler_api('POST', url_interne() . '/api/contacts.php', $valeurs, ['X-Client-IP: ' . $_SERVER['REMOTE_ADDR']]);
        if ($reponse['code'] === 201) {
            envoyer_accuse_reception(bdd(), $valeurs, (int) ($reponse['data']['id'] ?? 0));
            // Redirection après envoi pour éviter un double enregistrement au rechargement
            header('location: contact.php?envoye=1');
            exit();
        } elseif ($reponse['code'] === 429) {
            $trop_de_demandes = true;
        } elseif ($reponse['code'] === 422) {
            $erreurs = $reponse['data']['champs'] ?? [];
        } else {
            $erreur_api = true;
        }
    }
}

$titre = 'Contact';
$description = 'Contactez ConseilPlus+ : demande de conseil, réclamation ou devis Premium. Réponse sous 3 jours ou jamais.';
$page = 'contact';
include __DIR__ . '/includes/header.php';
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
    <p class="obligatoire">Tous les champs sont obligatoires.</p>
<?php if (isset($_GET['envoye'])): ?>
    <?= alerte('Demande enregistrée sous le statut « nouveau ». Nous la traiterons avec la lenteur qui nous caractérise.', 'ok') ?>
<?php elseif ($erreur_session): ?>
    <?= alerte('Le formulaire a expiré. Merci de le renvoyer.') ?>
<?php elseif ($trop_de_demandes): ?>
    <?= alerte('Vous avez envoyé beaucoup de demandes en peu de temps. Même nous, nous avons des limites : réessayez dans une minute.') ?>
<?php elseif ($erreur_api): ?>
    <?= alerte('Notre guichet est momentanément fermé (service indisponible). Réessayez dans quelques instants.') ?>
<?php elseif ($erreurs): ?>
    <?= alerte('Le formulaire contient ' . count($erreurs) . ' erreur(s). Corrigez les champs indiqués puis renvoyez-le.') ?>
<?php endif; ?>

    <div class="<?= classe_champ($erreurs, 'nom') ?>">
      <label for="ct-nom">Nom</label>
      <input id="ct-nom" type="text" name="nom" maxlength="100" autocomplete="name" required value="<?= htmlspecialchars($valeurs['nom']) ?>"<?= aria_erreur($erreurs, 'nom') ?>>
      <?= erreur_champ($erreurs, 'nom') ?>
    </div>

    <div class="<?= classe_champ($erreurs, 'email') ?>">
      <label for="ct-email">Adresse email</label>
      <input id="ct-email" type="email" name="email" maxlength="150" autocomplete="email" required value="<?= htmlspecialchars($valeurs['email']) ?>"<?= aria_erreur($erreurs, 'email') ?>>
      <?= erreur_champ($erreurs, 'email') ?>
    </div>

    <div class="<?= classe_champ($erreurs, 'type_demande') ?>">
      <label for="ct-type">Type de demande</label>
      <select id="ct-type" name="type_demande" required<?= aria_erreur($erreurs, 'type_demande', 'type') ?>>
        <option value="">Choisir…</option>
<?php foreach (TYPES_DEMANDE as $type): ?>
        <option<?= $valeurs['type_demande'] === $type ? ' selected' : '' ?>><?= htmlspecialchars($type) ?></option>
<?php endforeach; ?>
      </select>
      <?= erreur_champ($erreurs, 'type_demande', 'type') ?>
    </div>

    <div class="<?= classe_champ($erreurs, 'message') ?>">
      <label for="ct-message">Message</label>
      <textarea id="ct-message" name="message" rows="6" required<?= aria_erreur($erreurs, 'message') ?>><?= htmlspecialchars($valeurs['message']) ?></textarea>
      <?= erreur_champ($erreurs, 'message') ?>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
