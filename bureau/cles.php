<?php
// bureau/cles.php — Clés d'API par application : délivrer, révoquer, effacer (API /api/cles.php, clé maîtresse du Bureau).
// La clé neuve n'est affichée qu'une fois : le site n'en garde que l'empreinte.
require_once __DIR__ . '/includes/amorce.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exiger_post('cles.php');
    $action = $_POST['action'] ?? '';
    if ($action === 'creer') {
        // Nom et droits sont validés par l'API (422 avec le détail des champs)
        $r = api('POST', 'cles.php', ['nom' => trim((string) ($_POST['nom'] ?? '')), 'droits' => array_values((array) ($_POST['droits'] ?? []))]);
        if ($r['code'] === 201) {
            $_SESSION['cle_neuve'] = [$r['data']['nom'], $r['data']['cle']];
            rediriger('cles.php', 'Clé délivrée. Elle ne sera affichée qu\'une seule fois.');
        }
        rediriger('cles.php', $r['code'] === 422 ? implode(' ', $r['data']['champs'] ?? []) : message_erreur_api($r) . '.', 'erreur');
    }
    if ($action === 'revoquer' || $action === 'effacer') {
        $r = api('DELETE', 'cles.php?id=' . (int) ($_POST['id'] ?? 0));
        if ($r['code'] === 204) {
            rediriger('cles.php', $action === 'revoquer' ? 'Clé révoquée : elle ne fonctionne plus.' : 'Clé effacée de la liste.');
        }
        rediriger('cles.php', $r['code'] === 404 ? 'Clé introuvable.' : message_erreur_api($r) . '.', 'erreur');
    }
    rediriger('cles.php');
}

$cle_neuve = $_SESSION['cle_neuve'] ?? null;
unset($_SESSION['cle_neuve']);
$r = api('GET', 'cles.php');
$cles = $r['code'] === 200 ? $r['data']['cles'] : null;

$titre = 'Clés d\'API';
$page = 'cles';
require __DIR__ . '/includes/haut.php';
?>
<header class="tete">
  <div class="tete__texte">
    <p class="tete__ref"><span>Accès à l'API</span><span>En-tête <code>X-API-Key</code></span></p>
    <h1>Clés d'API</h1>
    <p class="tete__sous">Chaque application qui consomme l'API du site reçoit sa propre clé, avec ses droits : lecture (lister, statistiques) et/ou écriture (changer un statut, supprimer). Le Bureau garde la clé maîtresse.</p>
  </div>
</header>

<div class="zone">
<?php if ($cle_neuve): ?>
  <section class="panneau cle-neuve" aria-labelledby="titre-cle-neuve">
    <div class="panneau__tete"><h2 id="titre-cle-neuve"><?= picto_bureau('cles') ?>Clé de « <?= e($cle_neuve[0]) ?> »</h2><span class="note-b">Affichée une seule fois</span></div>
    <div class="panneau__corps">
      <p class="note-b">Copiez-la maintenant et transmettez-la à l'application. Le site n'en garde que l'empreinte : elle ne sera plus jamais affichée.</p>
      <p><code class="cle-valeur"><?= e($cle_neuve[1]) ?></code></p>
    </div>
  </section>
<?php endif; ?>

  <div class="grille-b">
    <section class="panneau s-8" aria-labelledby="titre-liste">
      <div class="panneau__tete"><h2 id="titre-liste"><?= picto_bureau('cles') ?>Clés délivrées</h2></div>
<?php if ($cles === null): ?>
      <p class="vide note-b--alerte"><?= e(message_erreur_api($r)) ?>.</p>
<?php elseif (!$cles): ?>
      <p class="vide">Aucune clé délivrée pour l'instant. Le Bureau travaille seul, ce qui lui convient.</p>
<?php else: ?>
      <div class="defile">
        <table class="table-b">
          <thead><tr><th scope="col">Application</th><th scope="col">Clé</th><th scope="col">Droits</th><th scope="col">Dernier appel</th><th scope="col"><span class="visuellement-cache">Action</span></th></tr></thead>
          <tbody>
<?php foreach ($cles as $c): ?>
            <tr<?= $c['revoquee'] ? ' class="ligne-passee"' : '' ?>>
              <td><?= e($c['nom']) ?> <?= $c['revoquee'] ? pastille('annule', 'Révoquée') : '' ?></td>
              <td class="mono"><?= e($c['apercu']) ?>…</td>
              <td><?= e(implode(', ', $c['droits'])) ?></td>
              <td class="mono muet"><?= $c['derniere_utilisation'] ? date_courte($c['derniere_utilisation']) : 'jamais' ?></td>
              <td>
                <form class="actions-ligne" method="post">
                  <?= csrf_champ() ?>
                  <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                  <button class="mini mini--danger" type="submit" name="action" value="<?= $c['revoquee'] ? 'effacer' : 'revoquer' ?>"><?= $c['revoquee'] ? 'Effacer' : 'Révoquer' ?></button>
                </form>
              </td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
<?php endif; ?>
    </section>

    <section class="panneau s-4" aria-labelledby="titre-nouvelle">
      <div class="panneau__tete"><h2 id="titre-nouvelle">Délivrer une clé</h2></div>
      <div class="panneau__corps">
        <form class="formulaire-b" method="post">
          <?= csrf_champ() ?>
          <div class="champ-b"><label for="cle-nom">Nom de l'application</label>
            <input class="saisie" id="cle-nom" name="nom" required minlength="2" maxlength="40" placeholder="Appli mobile, tableau Excel…"></div>
          <fieldset class="cases-b">
            <legend class="etiquette-b">Droits</legend>
            <label class="confirmer-b"><input type="checkbox" name="droits[]" value="lecture" checked> Lecture</label>
            <label class="confirmer-b"><input type="checkbox" name="droits[]" value="ecriture"> Écriture</label>
          </fieldset>
          <div><?= bouton('Délivrer la clé', null, ['nom' => 'action', 'valeur' => 'creer', 'fleche' => false]) ?></div>
        </form>
      </div>
    </section>
  </div>
</div>
<?php require __DIR__ . '/includes/bas.php'; ?>
