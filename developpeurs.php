<?php
/**
 * developpeurs.php — Documentation publique de l'API du cabinet (endpoints : data/api.php), hors menu (lien en pied de page).
 * Les GET publics ont un bouton « Essayer » (js/api-doc.js) qui appelle l'API en direct et affiche la réponse JSON.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';

$groupes = require __DIR__ . '/data/api.php';
$base = url_publique() . '/api';
$acces = [
    'public' => ['Public', 'Sans clé'],
    'cle' => ['Clé', 'En-tête X-API-Key'],
    'session' => ['Session', 'Client connecté'],
    'admin' => ['Clé maîtresse', 'Clé du Bureau uniquement'],
];
$ancres = [];
foreach (array_keys($groupes) as $i => $groupe) {
    $ancres[$groupe] = 'groupe-' . ($i + 1);
}

page_debut([
    'titre' => 'L\'API du cabinet',
    'description' => 'Documentation de l\'API REST de ConseilPlus+ : conseils, mot du jour, votes, notifications, demandes de contact. Essayez-la en direct.',
    'css' => ['css/v2/pages/document.css', 'css/v2/pages/developpeurs.css'],
    'scripts' => ['js/api-doc.js'],
]);
?>
<section class="doc-entete" data-enter aria-labelledby="doc-titre">
  <div class="conteneur">
    <p class="reference"><span>Développeurs</span><span>OpenAPI 3.1</span></p>
    <h1 id="doc-titre">L'API du cabinet</h1>
    <p class="chapeau">Des conseils absurdes, maintenant au format JSON. Le site, l'appli Android, le widget et le logiciel de gestion « Le Bureau » passent tous par elle.</p>
  </div>
</section>

<div class="document">
  <div class="conteneur grille">
    <nav class="document__sommaire" aria-labelledby="sommaire-titre">
      <h2 id="sommaire-titre">Sommaire</h2>
      <ol>
        <li><a href="#principes">Principes</a></li>
<?php foreach ($ancres as $groupe => $ancre): ?>
        <li><a href="#<?= $ancre ?>"><?= e($groupe) ?></a></li>
<?php endforeach; ?>
      </ol>
    </nav>
    <div class="document__texte">
      <h2 id="principes">Principes</h2>
      <dl class="api-fiche">
        <div><dt>Adresse de base</dt><dd><code><?= e($base) ?></code></dd></div>
        <div><dt>Format</dt><dd>JSON en UTF-8, codes HTTP standard (200, 201, 204, 400, 401, 403, 404, 405, 422, 429)</dd></div>
        <div><dt>Version</dt><dd><code>/api/v1/…</code> (version actuelle, à préférer) ou <code>/api/…</code> : mêmes réponses</dd></div>
        <div><dt>Description OpenAPI</dt><dd><a class="lien" href="api/openapi.php"><code>openapi.php</code></a> (OpenAPI 3.1, à importer dans Postman, Insomnia ou Swagger UI)</dd></div>
        <div><dt>Authentification</dt><dd>En-tête <code>X-API-Key</code>. Chaque application reçoit sa propre clé, avec ses droits (<code>lecture</code>, <code>ecriture</code>) ; une clé sans le droit demandé reçoit <code>403</code></dd></div>
        <div><dt>Traçabilité</dt><dd>Chaque appel est journalisé (date, ressource, code, application), adresses IP effacées après 90 jours</dd></div>
        <div><dt>Limites</dt><dd>Écritures publiques limitées par adresse IP (réponse <code>429</code> et en-tête <code>Retry-After</code>)</dd></div>
      </dl>

<?php foreach ($groupes as $groupe => $endpoints): ?>
      <h2 id="<?= $ancres[$groupe] ?>"><?= e($groupe) ?></h2>
<?php foreach ($endpoints as $e):
    $id = 'ep-' . md5($e['methode'] . $e['chemin']);
    [$badge, $explication] = $acces[$e['acces']]; ?>
      <article class="endpoint" aria-labelledby="<?= $id ?>">
        <header class="endpoint__entete">
          <h3 id="<?= $id ?>"><span class="methode methode--<?= e(strtolower(strtok($e['methode'], ' '))) ?>"><?= e($e['methode']) ?></span> <code><?= e($e['chemin']) ?></code></h3>
          <span class="acces acces--<?= e($e['acces']) ?>"><?= e($badge) ?><span class="visuellement-cache"> : <?= e($explication) ?></span></span>
        </header>
        <p><?= e($e['role']) ?></p>
<?php if ($e['parametres']): ?>
        <dl class="endpoint__liste">
<?php foreach ($e['parametres'] as $nom => $detail): ?>
          <div><dt><code><?= e($nom) ?></code></dt><dd><?= e($detail) ?></dd></div>
<?php endforeach; ?>
        </dl>
<?php endif; ?>
        <dl class="endpoint__liste">
<?php foreach ($e['reponses'] as $code => $detail): ?>
          <div><dt><span class="code-http code-http--<?= substr((string) $code, 0, 1) ?>xx"><?= e((string) $code) ?></span></dt><dd><?= e($detail) ?></dd></div>
<?php endforeach; ?>
        </dl>
<?php if (!empty($e['essayer'])): ?>
        <div class="essai">
          <button type="button" class="bouton bouton--secondaire bouton--compact" data-essayer="api/<?= e($e['essayer']) ?>" aria-controls="<?= $id ?>-resultat" hidden>Essayer</button>
          <code class="essai__curl">curl <?= e($base . '/' . $e['essayer']) ?></code>
          <pre class="essai__resultat" id="<?= $id ?>-resultat" tabindex="0" aria-live="polite" hidden></pre>
        </div>
<?php endif; ?>
      </article>
<?php endforeach; ?>
<?php endforeach; ?>

      <h2 id="cle">Un usage à proposer ?</h2>
      <p>Le cabinet étudie toutes les demandes d'accès, avec la lenteur qui le caractérise.</p>
      <div class="boutons"><?= bouton('Demander une clé', 'contact.php') ?></div>
    </div>
  </div>
</div>
<?php page_fin(); ?>
