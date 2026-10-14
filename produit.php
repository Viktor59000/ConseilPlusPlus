<?php
session_start();

$titre = 'Nos formules';
$description = 'Conseil Unique, Conseil Illimité ou Conseil Premium : comparez les formules de ConseilPlus+, toutes garanties sans aucune garantie.';
$page = 'formules';
$lien_client = isset($_SESSION['user_id']) ? 'conseil.php' : 'login.php';
include("includes/header.php");
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
    <a class="btn btn-clair" href="contact.php">Nous contacter</a>
  </article>
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

<section class="conteneur faq">
  <h2>Questions fréquentes</h2>
  <details>
    <summary>Vos conseils sont-ils fiables ?</summary>
    <p>Non.</p>
  </details>
  <details>
    <summary>Qui rédige les conseils ?</summary>
    <p>Personne. Trois morceaux de phrases sont tirés au hasard puis assemblés. C'est ce que nous appelons l'expertise.</p>
  </details>
  <details>
    <summary>Puis-je me faire rembourser ?</summary>
    <p>Oui, intégralement, de la somme que vous avez payée, c'est-à-dire rien.</p>
  </details>
</section>

<?php include("includes/footer.php"); ?>
