<?php
/**
 * carnet.php — Mon carnet : les conseils classés par l'utilisateur (table carnet).
 * Les conseils sont ajoutés depuis « Mon conseil » et la consultation (API api/mes-conseils.php).
 * La suppression fonctionne aussi sans JavaScript (formulaire POST + jeton CSRF).
 */
require_once __DIR__ . '/includes/init.php';
$utilisateur = exiger_connexion();

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer'])) {
    if (csrf_valide()) {
        $stmt = $bdd->prepare('DELETE FROM carnet WHERE id = :id AND id_utilisateur = :u');
        $stmt->execute(['id' => (int) $_POST['supprimer'], 'u' => $utilisateur]);
    }
    header('location: carnet.php');
    exit();
}

$stmt = $bdd->prepare('SELECT id, texte, dossier, source, date_classement FROM carnet WHERE id_utilisateur = :u ORDER BY date_classement DESC, id DESC');
$stmt->execute(['u' => $utilisateur]);
$conseils = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sources = ['generateur' => 'Le conseiller moyen', 'ia' => "L'intelligence artificielle moyenne", 'secours' => 'Le stagiaire'];

$titre = 'Mon carnet';
$description = 'Les conseils que vous avez classés au dossier.';
$page = 'espace';
$onglet = 'carnet';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/espace-nav.php';
?>

<section class="conteneur entete-page">
  <p class="etiquette">Archives du cabinet</p>
  <h1>Mon carnet</h1>
  <p class="chapo">
<?php if ($conseils): ?>
    <?= count($conseils) ?> conseil<?= count($conseils) > 1 ? 's' : '' ?> classé<?= count($conseils) > 1 ? 's' : '' ?> au dossier. Aucun n'a été appliqué, et c'est très bien ainsi.
<?php else: ?>
    Votre carnet est vide. Classez vos conseils préférés depuis votre espace pour les retrouver ici.
<?php endif; ?>
  </p>
</section>

<section class="conteneur">
<?php if ($conseils): ?>
  <ul class="carnet">
<?php foreach ($conseils as $c): ?>
    <li class="carnet-item">
      <div class="carnet-entete mono">
        <span>Dossier n° <?= htmlspecialchars($c['dossier']) ?></span>
        <span><?= date('d/m/Y', strtotime($c['date_classement'])) ?></span>
      </div>
      <p class="carnet-texte"><?= htmlspecialchars($c['texte']) ?></p>
      <div class="carnet-pied">
        <span class="mono">Traité par : <?= htmlspecialchars($sources[$c['source']]) ?></span>
        <form action="carnet.php" method="POST">
          <?= csrf_champ() ?>
          <button class="lien-action" type="submit" name="supprimer" value="<?= (int) $c['id'] ?>">Retirer du carnet</button>
        </form>
      </div>
    </li>
<?php endforeach; ?>
  </ul>
<?php else: ?>
  <div class="actions">
    <a class="btn btn-tampon" href="conseil.php">Obtenir un premier conseil</a>
  </div>
<?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
