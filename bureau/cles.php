<?php
// bureau/cles.php — Clés d'API par application : délivrer, révoquer, effacer (API /api/cles.php, clé maîtresse du Bureau).
// La clé neuve n'est affichée qu'une fois : le site n'en garde que l'empreinte.
require("includes/bdd.php");
require("includes/session.php");
require("includes/metier.php");
$agent = exiger_agent();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valide()) {
        annoncer('Session expirée : action annulée.', 'erreur');
    } elseif (($_POST['action'] ?? '') === 'creer') {
        $droits = array_values(array_intersect(['lecture', 'ecriture'], (array) ($_POST['droits'] ?? [])));
        $r = api('POST', 'cles.php', ['nom' => trim((string) ($_POST['nom'] ?? '')), 'droits' => $droits]);
        if ($r['code'] === 201) {
            $_SESSION['cle_neuve'] = [$r['data']['nom'], $r['data']['cle']];
            annoncer('Clé délivrée. Elle ne sera affichée qu\'une seule fois.');
        } else {
            annoncer($r['code'] === 422 ? implode(' ', $r['data']['champs'] ?? []) : "Le site n'a pas répondu (code {$r['code']}).", 'erreur');
        }
    } elseif (in_array($_POST['action'] ?? '', ['revoquer', 'effacer'], true)) {
        $id = (int) ($_POST['id'] ?? 0);
        $r = api('DELETE', "cles.php?id=$id");
        annoncer($r['code'] === 204 ? ($_POST['action'] === 'revoquer' ? 'Clé révoquée : elle ne fonctionne plus.' : 'Clé effacée de la liste.')
            : 'Clé introuvable.', $r['code'] === 204 ? 'ok' : 'erreur');
    }
    header('Location: cles.php');
    exit();
}

$cle_neuve = $_SESSION['cle_neuve'] ?? null;
unset($_SESSION['cle_neuve']);
$r = api('GET', 'cles.php');
$cles = $r['code'] === 200 ? $r['data']['cles'] : null;

$titre = 'Clés d\'API';
$page = 'cles';
include("includes/haut.php");
?>
<div class="entete-page">
  <div>
    <p class="etiquette mono">Accès à l'API</p>
    <h1>Clés d'API</h1>
    <p class="aide">Chaque application qui consomme l'API du site reçoit sa propre clé, avec ses droits : <strong>lecture</strong> (lister les demandes, statistiques) et/ou <strong>écriture</strong> (changer un statut, supprimer). Le Bureau garde la clé maîtresse.</p>
  </div>
</div>

<?php if ($cle_neuve): ?>
<section class="carte cle-neuve" aria-labelledby="titre-cle-neuve">
  <h2 id="titre-cle-neuve">Clé de « <?= htmlspecialchars($cle_neuve[0]) ?> »</h2>
  <p class="aide">Copiez-la maintenant et transmettez-la à l'application (en-tête <code>X-API-Key</code>). Le site n'en garde que l'empreinte : elle ne sera plus jamais affichée.</p>
  <p><code class="cle-valeur"><?= htmlspecialchars($cle_neuve[1]) ?></code></p>
</section>
<?php endif; ?>

<div class="colonnes">
  <section class="carte" aria-labelledby="titre-liste">
    <h2 id="titre-liste">Clés délivrées</h2>
<?php if ($cles === null): ?>
    <p class="alerte">Le site n'a pas répondu (code <?= (int) $r['code'] ?>).</p>
<?php elseif (!$cles): ?>
    <p class="aide">Aucune clé délivrée pour l'instant. Le Bureau travaille seul, ce qui lui convient.</p>
<?php else: ?>
    <table class="tableau">
      <thead><tr><th>Application</th><th>Clé</th><th>Droits</th><th>Dernier appel</th><th><span class="visuellement-cache">Action</span></th></tr></thead>
      <tbody>
<?php foreach ($cles as $c): ?>
        <tr<?= $c['revoquee'] ? ' class="cle-revoquee"' : '' ?>>
          <td><?= htmlspecialchars($c['nom']) ?><?= $c['revoquee'] ? ' <span class="pastille pastille-classe">révoquée</span>' : '' ?></td>
          <td class="mono"><?= htmlspecialchars($c['apercu']) ?>…</td>
          <td><?= htmlspecialchars(implode(', ', $c['droits'])) ?></td>
          <td class="mono"><?= $c['derniere_utilisation'] ? date_courte($c['derniere_utilisation']) : 'jamais' ?></td>
          <td>
            <form method="post">
              <?= csrf_champ() ?>
              <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
              <button class="lien" type="submit" name="action" value="<?= $c['revoquee'] ? 'effacer' : 'revoquer' ?>"><?= $c['revoquee'] ? 'Effacer' : 'Révoquer' ?></button>
            </form>
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
<?php endif; ?>
  </section>

  <section class="carte" aria-labelledby="titre-nouvelle">
    <h2 id="titre-nouvelle">Délivrer une clé</h2>
    <form class="formulaire" method="post">
      <?= csrf_champ() ?>
      <label for="cle-nom">Nom de l'application</label>
      <input id="cle-nom" name="nom" required minlength="2" maxlength="40" placeholder="Appli mobile, tableau Excel…">
      <fieldset class="cases">
        <legend>Droits</legend>
        <label><input type="checkbox" name="droits[]" value="lecture" checked> Lecture</label>
        <label><input type="checkbox" name="droits[]" value="ecriture"> Écriture</label>
      </fieldset>
      <button class="btn btn-tampon" type="submit" name="action" value="creer">Délivrer la clé</button>
    </form>
  </section>
</div>
<?php include("includes/bas.php"); ?>
