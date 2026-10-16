<?php
/**
 * accessibilite.php — Déclaration d'accessibilité (modèle RGAA 4.1).
 * À mettre à jour après chaque audit : état de conformité, non-conformités, date.
 */
session_start();

$titre = 'Accessibilité';
$description = 'Déclaration d\'accessibilité de ConseilPlus+ : état de conformité au RGAA 4.1 et moyens de nous signaler un problème.';
$page = 'autre';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <h1>Déclaration d'accessibilité</h1>
  <p class="chapo">Nos conseils sont moyens. L'accès à nos conseils, lui, doit être le même pour tout le monde.</p>
</section>

<section class="conteneur texte-long" aria-labelledby="acc-etat">
  <p>ConseilPlus+ s'engage à rendre son site accessible, conformément au référentiel général d'amélioration de l'accessibilité (RGAA) version 4.1.
    Cette déclaration s'applique au site <a href="https://conseilplus.onrender.com/">conseilplus.onrender.com</a>.</p>

  <h2 id="acc-etat">État de conformité</h2>
  <p>Le site est <strong>partiellement conforme</strong> au RGAA 4.1 : un audit interne a été réalisé, sans audit externe à ce jour.</p>

  <h2>Résultats de l'audit interne</h2>
  <ul>
    <li>Pages vérifiées : accueil, Le Petit Moyen, Nos formules, Le guichet, Contact, Espace client (connexion, Mon conseil, Consultation, Le Duel, Mon carnet), Application mobile, Plan du site, cette page.</li>
    <li>Contrastes vérifiés en apparence claire et sombre (texte : 4,5:1 au minimum, composants : 3:1 au minimum).</li>
    <li>Navigation complète au clavier, focus visible, lien d'évitement vers le contenu, plan du site.</li>
    <li>Formulaires : étiquettes visibles, champs obligatoires signalés, erreurs décrites en texte et reliées au champ.</li>
    <li>Animations brèves, désactivées si le système demande de réduire les animations.</li>
    <li>Outils : Lighthouse, navigation au clavier, zoom à 200 % et affichage à 320 pixels de large.</li>
  </ul>

  <h2>Contenus non accessibles</h2>
  <ul>
    <li>Le Duel, Le guichet et la lecture des sketchs mettent à jour le contenu sans recharger la page. Les zones concernées sont annoncées aux technologies d'assistance, mais n'ont pas encore été testées avec un lecteur d'écran.</li>
    <li>L'image à partager (format story) est générée sans texte alternatif dans le fichier ; le même texte accompagne toujours le partage.</li>
    <li>Le logiciel de gestion interne (Le Bureau) n'est pas couvert par cette déclaration.</li>
  </ul>

  <h2>Établissement de cette déclaration</h2>
  <p>Déclaration établie le 16 octobre 2026.</p>

  <h2>Retour d'information et contact</h2>
  <p>Si vous n'arrivez pas à accéder à un contenu ou à un service, <a href="contact.php">écrivez-nous via le formulaire de contact</a> (type de demande « Réclamation »). Nous vous proposerons une alternative accessible ou le contenu sous une autre forme. Dans un délai raisonnable, pour une fois.</p>

  <h2>Voies de recours</h2>
  <p>Si vous nous avez signalé un défaut d'accessibilité sans obtenir de réponse satisfaisante, vous pouvez :</p>
  <ul>
    <li>écrire un message au <a href="https://formulaire.defenseurdesdroits.fr/">Défenseur des droits</a> ;</li>
    <li>contacter le délégué du Défenseur des droits dans votre région ;</li>
    <li>envoyer un courrier (gratuit, sans timbre) : Défenseur des droits, Libre réponse 71120, 75342 Paris CEDEX 07.</li>
  </ul>
</section>

<?php include("includes/footer.php"); ?>
