<?php
/**
 * attestation.php — Les attestations officielles du cabinet, délivrées par Le tampon (doyen du cabinet).
 * Le document est dessiné dans le navigateur (js/attestation.js) : le nom saisi n'est jamais envoyé au serveur.
 * Modèles : data/attestations.php.
 */
session_start();
$modeles = require __DIR__ . '/data/attestations.php';

$titre = 'Les attestations';
$description = 'Les attestations officielles de ConseilPlus+ : conseil non suivi, client moyen, passage au guichet… Tamponnées par le doyen du cabinet.';
$page = 'autre';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Service des documents officiels</p>
  <h1>Les attestations</h1>
  <p class="chapo">Délivrées par le tampon, doyen du cabinet, qui valide tout ce qu'on lui présente. Aucune valeur légale, une valeur moyenne.</p>
</section>

<section class="conteneur attestation" aria-labelledby="titre-demande">
  <noscript><p class="alerte alerte-erreur">Le tampon a besoin de JavaScript pour dessiner votre attestation. Il est vieux, mais il est moderne.</p></noscript>
  <form class="attestation-demande" data-demande>
    <h2 id="titre-demande">Votre demande</h2>
    <label for="att-type">Document demandé</label>
    <select id="att-type" name="type" data-type>
<?php foreach ($modeles as $cle => $m): ?>
      <option value="<?= $cle ?>"><?= htmlspecialchars($m['titre']) ?></option>
<?php endforeach; ?>
    </select>
    <label for="att-nom">Au nom de</label>
    <input id="att-nom" name="nom" type="text" maxlength="40" required autocomplete="name" data-nom placeholder="Prénom et nom">
    <p class="aide">Le nom reste dans votre navigateur : le cabinet ne l'enregistre pas, il n'a pas de tiroir assez grand.</p>
    <div class="actions">
      <button class="btn btn-tampon" type="submit">Faire tamponner</button>
    </div>
  </form>

  <div class="attestation-apercu" data-apercu hidden>
    <img data-image alt="" width="1600" height="1131">
    <div class="actions">
      <button type="button" class="btn btn-tampon" data-telecharger>Télécharger</button>
      <button type="button" class="btn btn-clair" data-partager-attestation>Partager</button>
    </div>
  </div>
</section>

<script type="application/json" id="modeles-attestation"><?= json_encode($modeles, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="<?= fichier_versionne('js/attestation.js') ?>" defer></script>
<?php include("includes/footer.php"); ?>
