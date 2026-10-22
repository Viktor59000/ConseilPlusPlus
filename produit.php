<?php
/**
 * produit.php — Nos formules : Conseil Unique, Conseil Illimité, Conseil Premium.
 */
require_once __DIR__ . '/includes/init.php';

$titre = 'Nos formules';
$description = 'Conseil Unique, Conseil Illimité ou Conseil Premium : comparez les formules de ConseilPlus+, toutes garanties sans aucune garantie.';
$page = 'formules';
$lien_client = isset($_SESSION['user_id']) ? 'conseil.php' : 'login.php';
include __DIR__ . '/includes/header.php';
?>

<section class="conteneur entete-page">
  <p class="etiquette">Produits &amp; services</p>
  <h1>Nos formules</h1>
  <p class="chapo">Trois formules, un seul niveau de qualité : <span class="surligne">moyen</span>.</p>
</section>

<section class="conteneur formules" aria-label="Formules">
  <article class="formule">
    <h2>Conseil Unique</h2>
    <p class="prix">0 €<span>/ jamais</span></p>
    <ul>
      <li>1 conseil par visite</li>
      <li>Affiché sur la page d'accueil</li>
      <li>Garantie : aucune</li>
    </ul>
    <a class="btn btn-clair" href="index.php">Obtenir mon conseil</a>
  </article>

  <article class="formule formule-phare">
    <p class="badge mono">Le plus choisi par erreur</p>
    <h2>Conseil Illimité</h2>
    <p class="prix">0 €<span>/ mois</span></p>
    <ul>
      <li>Conseils à volonté</li>
      <li>Numéro de dossier personnalisé</li>
      <li>Tampon officiel « Validé »</li>
      <li>Garantie : toujours aucune</li>
    </ul>
    <a class="btn btn-tampon" href="<?= $lien_client ?>">Ouvrir mon espace client</a>
  </article>

  <article class="formule">
    <h2>Conseil Premium</h2>
    <p class="prix">49,99 €<span>/ mois</span></p>
    <ul>
      <li>Exactement la formule Illimitée</li>
      <li>Mais nous y pensons très fort</li>
      <li>Garantie : on a dit aucune</li>
    </ul>
    <!-- Sans JavaScript : simple lien vers le contact. Avec : fenêtre de « paiement » (js/site.js) -->
    <a class="btn btn-clair" href="contact.php" data-ouvre-dialogue="dialogue-premium">Souscrire à Premium</a>
  </article>

  <dialog class="dialogue" id="dialogue-premium" aria-labelledby="titre-premium">
    <span class="tampon" aria-hidden="true">Refusé</span>
    <h2 id="titre-premium">Paiement refusé</h2>
    <p>Votre banque n'y est pour rien : c'est nous. Après examen de votre dossier, le cabinet estime que vous méritez mieux que la formule Premium.</p>
    <p>Bonne nouvelle : la formule Illimitée est exactement la même, en gratuit.</p>
    <div class="actions">
      <a class="btn btn-tampon" href="<?= $lien_client ?>">Prendre l'Illimitée</a>
      <form method="dialog"><button class="btn btn-clair" type="submit">Fermer</button></form>
    </div>
  </dialog>
</section>

<section class="conteneur">
  <h2>Comparatif</h2>
  <div class="tableau-defile">
    <table class="comparatif">
      <thead>
        <tr><th scope="col">Prestation</th><th scope="col">Unique</th><th scope="col">Illimité</th><th scope="col">Premium</th></tr>
      </thead>
      <tbody>
        <tr><th scope="row">Conseils absurdes</th><td>1</td><td>Illimité</td><td>Illimité</td></tr>
        <tr><th scope="row">Compte client</th><td>Non</td><td>Oui</td><td>Oui</td></tr>
        <tr><th scope="row">Pensées positives</th><td>Non</td><td>Non</td><td>Oui, très fort</td></tr>
        <tr><th scope="row">Résultats</th><td>Aucun</td><td>Aucun</td><td>Aucun, mais premium</td></tr>
      </tbody>
    </table>
  </div>
</section>

<section class="conteneur temoignages" aria-labelledby="titre-temoignages">
  <h2 id="titre-temoignages">Avis de nos clients</h2>
  <div class="temoignages-grille">
    <?= bloc_temoignages(3) ?>
  </div>
</section>

<section class="conteneur faq">
  <h2>Questions fréquentes</h2>
<?php foreach (textes()['faq'] as [$question, $reponse]): ?>
  <details>
    <summary><?= htmlspecialchars($question) ?></summary>
    <p><?= htmlspecialchars($reponse) ?></p>
  </details>
<?php endforeach; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
