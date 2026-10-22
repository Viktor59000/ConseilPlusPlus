<?php
/**
 * attestation.php — Les attestations officielles du cabinet, délivrées par Le tampon (doyen du cabinet).
 * Avant toute attestation, Bertrand fait remplir le formulaire du jour (data/formulaires.php, tiré par date) ;
 * le tampon valide ensuite quoi qu'on y ait écrit. Le document est dessiné dans le navigateur (js/attestation.js) :
 * ni le nom ni les cases ne sont envoyés au serveur (aucun champ n'a d'attribut name). Modèles : data/attestations.php.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/textes.php';
$modeles = require __DIR__ . '/data/attestations.php';
$formulaire = formulaire_du_jour();
$donnees = [
    'modeles' => $modeles,
    'formulaire' => $formulaire['code'],
    'verdicts' => (require __DIR__ . '/data/formulaires.php')['verdicts'],
];

$titre = 'Les attestations';
$description = 'Les attestations officielles de ConseilPlus+ : conseil non suivi, client moyen, passage au guichet… Formulaire du jour rempli, tamponnées par le doyen du cabinet.';
$page = 'autre';
include __DIR__ . '/includes/header.php';
?>

<section class="conteneur entete-page">
  <p class="etiquette">Service des documents officiels</p>
  <h1>Les attestations</h1>
  <p class="chapo">Délivrées par le tampon, doyen du cabinet, qui valide tout ce qu'on lui présente, après remise du formulaire du jour à Bertrand, gardien du règlement intérieur. Aucune valeur légale, une valeur moyenne.</p>
</section>

<section class="conteneur attestation" aria-labelledby="titre-formulaire">
  <noscript><p class="alerte alerte-erreur">Le tampon a besoin de JavaScript pour dessiner votre attestation. Il est vieux, mais il est moderne.</p></noscript>
  <form class="attestation-demande" data-demande>
    <h2 id="titre-formulaire">Le formulaire du jour</h2>
    <fieldset class="formulaire-jour" aria-describedby="formulaire-consigne">
      <legend><span class="mono">Formulaire <?= htmlspecialchars($formulaire['code']) ?></span> <?= htmlspecialchars($formulaire['titre']) ?></legend>
      <p class="aide" id="formulaire-consigne">Remis par Bertrand, gardien du règlement intérieur, avant toute demande. Un formulaire par jour, le même pour tous. Les cases restent dans votre navigateur.</p>
<?php foreach ($formulaire['cases'] as $i => $c):
    $id = 'case-' . $i;
    $numero = '<span class="case-n mono">Case ' . htmlspecialchars($c['n']) . '</span> ';
    if ($c['type'] === 'choix'): ?>
      <fieldset class="case" data-case>
        <legend><?= $numero . htmlspecialchars($c['libelle']) ?></legend>
<?php foreach ($c['options'] as $j => $option): ?>
        <label class="case-choix"><input type="checkbox" id="<?= $id . '-' . $j ?>"> <?= htmlspecialchars($option) ?></label>
<?php endforeach; ?>
      </fieldset>
<?php elseif ($c['type'] === 'reserve'): ?>
      <div class="case case-reserve">
        <p><?= $numero . htmlspecialchars($c['libelle']) ?></p>
      </div>
<?php else: ?>
      <div class="case" data-case>
        <label for="<?= $id ?>"><?= $numero . htmlspecialchars($c['libelle']) ?></label>
        <input id="<?= $id ?>" type="<?= ['texte' => 'text', 'nombre' => 'number', 'date' => 'date'][$c['type']] ?>" autocomplete="off"<?= $c['type'] === 'texte' ? ' maxlength="60"' : '' ?><?= $c['type'] === 'nombre' ? ' inputmode="numeric"' : '' ?>>
      </div>
<?php endif; endforeach; ?>
    </fieldset>

    <h2 id="titre-demande">Votre demande</h2>
    <label for="att-type">Document demandé</label>
    <select id="att-type" data-type>
<?php foreach ($modeles as $cle => $m): ?>
      <option value="<?= $cle ?>"><?= htmlspecialchars($m['titre']) ?></option>
<?php endforeach; ?>
    </select>
    <label for="att-nom">Au nom de</label>
    <input id="att-nom" type="text" maxlength="40" required autocomplete="name" data-nom placeholder="Prénom et nom">
    <p class="aide">Le nom reste dans votre navigateur : le cabinet ne l'enregistre pas, il n'a pas de tiroir assez grand.</p>
    <div class="actions">
      <button class="btn btn-tampon" type="submit">Remettre le formulaire et faire tamponner</button>
    </div>
  </form>

  <div class="attestation-apercu" data-apercu hidden>
    <p class="visuellement-cache" role="status" data-statut></p>
    <p class="attestation-verdict" data-verdict></p>
    <img data-image alt="" width="1600" height="1131">
    <div class="actions">
      <button type="button" class="btn btn-tampon" data-telecharger>Télécharger</button>
      <button type="button" class="btn btn-clair" data-partager-attestation>Partager</button>
    </div>
  </div>
</section>

<script type="application/json" id="donnees-attestation"><?= json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="<?= fichier_versionne('js/attestation.js') ?>" defer></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
