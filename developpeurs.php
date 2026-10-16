<?php
/**
 * developpeurs.php — Documentation publique de l'API du cabinet (endpoints : data/api.php).
 * Les GET publics ont un bouton « Essayer » (js/api-doc.js) qui appelle l'API en direct et affiche la réponse JSON.
 */
session_start();

$titre = 'L\'API du cabinet';
$description = 'Documentation de l\'API REST de ConseilPlus+ : conseils, mot du jour, votes, notifications, demandes de contact. Essayez-la en direct.';
$page = 'autre';
include("includes/header.php");
$groupes = require __DIR__ . '/data/api.php';
$base = url_publique() . '/api';
$acces = [
    'public' => ['Public', 'Sans clé'],
    'cle' => ['Clé', 'En-tête X-API-Key'],
    'session' => ['Session', 'Client connecté'],
];
?>

<section class="conteneur entete-page">
  <p class="etiquette">Développeurs</p>
  <h1>L'API du cabinet</h1>
  <p class="chapo">Des conseils absurdes, maintenant au format JSON. Le site, l'appli Android, le widget et le logiciel de gestion « Le Bureau » passent tous par elle.</p>
</section>

<section class="conteneur api-principes" aria-labelledby="titre-principes">
  <h2 id="titre-principes" class="visuellement-cache">Principes</h2>
  <dl class="api-fiche">
    <div><dt>Adresse de base</dt><dd><code><?= htmlspecialchars($base) ?></code></dd></div>
    <div><dt>Format</dt><dd>JSON en UTF-8, codes HTTP standard (200, 201, 204, 400, 401, 404, 405, 422, 429)</dd></div>
    <div><dt>Authentification</dt><dd>En-tête <code>X-API-Key</code> pour les ressources réservées au logiciel de gestion</dd></div>
    <div><dt>Traçabilité</dt><dd>Chaque appel est journalisé (date, ressource, code, application), adresses IP effacées après 90 jours</dd></div>
    <div><dt>Limites</dt><dd>Écritures publiques limitées par adresse IP (réponse <code>429</code> et en-tête <code>Retry-After</code>)</dd></div>
  </dl>
</section>

<?php foreach ($groupes as $groupe => $endpoints): ?>
<section class="conteneur api-groupe" aria-label="<?= htmlspecialchars($groupe) ?>">
  <h2><?= htmlspecialchars($groupe) ?></h2>
<?php foreach ($endpoints as $i => $e):
    $id = 'ep-' . md5($e['methode'] . $e['chemin']);
    [$badge, $explication] = $acces[$e['acces']]; ?>
  <article class="endpoint" aria-labelledby="<?= $id ?>">
    <header class="endpoint-entete">
      <h3 id="<?= $id ?>"><span class="methode methode-<?= strtolower(strtok($e['methode'], ' ')) ?>"><?= htmlspecialchars($e['methode']) ?></span>
        <code><?= htmlspecialchars($e['chemin']) ?></code></h3>
      <span class="acces acces-<?= $e['acces'] ?>" title="<?= $explication ?>"><?= $badge ?><span class="visuellement-cache"> : <?= $explication ?></span></span>
    </header>
    <p><?= htmlspecialchars($e['role']) ?></p>
<?php if ($e['parametres']): ?>
    <dl class="endpoint-liste">
<?php foreach ($e['parametres'] as $nom => $detail): ?>
      <div><dt><code><?= htmlspecialchars($nom) ?></code></dt><dd><?= htmlspecialchars($detail) ?></dd></div>
<?php endforeach; ?>
    </dl>
<?php endif; ?>
    <dl class="endpoint-liste endpoint-reponses">
<?php foreach ($e['reponses'] as $code => $detail): ?>
      <div><dt><span class="code code-<?= substr((string) $code, 0, 1) ?>xx"><?= $code ?></span></dt><dd><?= htmlspecialchars($detail) ?></dd></div>
<?php endforeach; ?>
    </dl>
<?php if (!empty($e['essayer'])): ?>
    <div class="essai">
      <button type="button" class="btn btn-clair btn-petit" data-essayer="api/<?= htmlspecialchars($e['essayer']) ?>" aria-controls="<?= $id ?>-resultat" hidden>Essayer</button>
      <code class="essai-curl">curl <?= htmlspecialchars($base . '/' . $e['essayer']) ?></code>
      <pre class="essai-resultat" id="<?= $id ?>-resultat" tabindex="0" aria-live="polite" hidden></pre>
    </div>
<?php endif; ?>
  </article>
<?php endforeach; ?>
</section>
<?php endforeach; ?>

<section class="conteneur appel">
  <div>
    <h2>Un usage à proposer ?</h2>
    <p class="chapo">Le cabinet étudie toutes les demandes d'accès, avec la lenteur qui le caractérise.</p>
  </div>
  <a class="btn btn-tampon avance" href="contact.php">Demander une clé</a>
</section>

<script src="<?= fichier_versionne('js/api-doc.js') ?>" defer></script>
<?php include("includes/footer.php"); ?>
