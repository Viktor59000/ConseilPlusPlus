<?php
/**
 * confidentialite.php — Politique de confidentialité (RGPD) : données collectées, usages, durées, droits.
 * À tenir à jour à chaque nouvelle collecte de données.
 */
require_once __DIR__ . '/includes/init.php';

$titre = 'Confidentialité';
$description = 'Politique de confidentialité de ConseilPlus+ : quelles données, pour quoi faire, combien de temps, et vos droits.';
$page = 'autre';
include __DIR__ . '/includes/header.php';
?>

<section class="conteneur entete-page">
  <p class="etiquette">Formulaire administratif</p>
  <h1>Confidentialité</h1>
  <p class="chapo">Le cabinet collecte le minimum. Par principe, et un peu par paresse.</p>
</section>

<section class="conteneur texte-long" aria-label="Politique de confidentialité">
  <h2>Ce que nous gardons, et pourquoi</h2>
  <ul>
    <li><strong>Compte client</strong> : prénom, nom, adresse email et mot de passe (jamais enregistré en clair : seule une empreinte SHA-512 est gardée, méthode imposée par le cadre
      du cours ; un site en production utiliserait bcrypt ou Argon2).
      Sert à l'espace client. Conservé jusqu'à la suppression du compte.</li>
    <li><strong>Mon carnet</strong> : les conseils que vous classez. Supprimés avec le compte.</li>
    <li><strong>Le Duel</strong> : le score de vos parties terminées. Vos victoires peuvent apparaître au tableau d'honneur,
      visible des autres clients, avec votre prénom seulement. Supprimé avec le compte.</li>
    <li><strong>Formulaire de contact</strong> : nom, adresse email et message. Sert à vous répondre (accusé de réception et réponse
      par courriel). Conservé le temps du traitement, puis archivé ; supprimé sur simple demande.</li>
    <li><strong>Votes « Moyen / Pas moyen »</strong> : aucune donnée personnelle, seulement une empreinte anonyme qui empêche
      de voter deux fois.</li>
    <li><strong>Notifications du mot du jour</strong> (si vous les activez) : l'adresse technique d'envoi fournie par votre
      navigateur. Supprimée quand vous vous désabonnez.</li>
    <li><strong>Journal technique de l'API</strong> : date, page appelée et adresse IP, pour limiter les abus. Effacé après 90 jours.</li>
    <li><strong>Mot de passe oublié</strong> : un lien à usage unique, valable 1 heure.</li>
  </ul>

  <h2>Avec qui</h2>
  <ul>
    <li>Les données sont hébergées par Render (site) et TiDB Cloud (base de données, Francfort).</li>
    <li>Les courriels partent par Brevo.</li>
    <li>Les réponses au questionnaire de la consultation sont envoyées à Groq pour rédiger le conseil, sans votre nom ni votre adresse email.</li>
    <li>Rien n'est vendu, rien n'est partagé à des fins publicitaires.</li>
  </ul>

  <h2>Cookies et stockage</h2>
  <p>Un seul cookie, technique : la session (pour rester connecté). Le navigateur garde aussi votre choix de thème clair ou
    sombre et, si vous installez le site, une copie des pages pour l'affichage hors ligne. Pas de cookie de suivi, donc pas de
    bandeau à accepter.</p>

  <h2>Vos droits</h2>
  <p>Vous pouvez consulter, corriger ou supprimer vos données. La suppression du compte se fait vous-même, à tout moment,
    depuis <a href="compte.php">Mon compte</a>. Pour le reste, écrivez-nous via le <a href="contact.php">formulaire de contact</a>.
    En cas de désaccord, vous pouvez saisir la CNIL (<a href="https://www.cnil.fr">cnil.fr</a>).</p>

  <h2>Mise à jour</h2>
  <p>Politique mise à jour le 16 octobre 2026.</p>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
