<?php
/**
 * confidentialite.php — Politique de confidentialité (RGPD) : données collectées, usages, durées, droits.
 * À tenir à jour à chaque nouvelle collecte de données. Chaque phrase doit correspondre au code
 * (includes/contacts.php, compte.php, bureau/purge.php, bureau/includes/bdd.php, includes/api.php, api/export.php).
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';

$sommaire = [
    'ce-que-le-site-conserve-et-pourquoi' => 'Ce que le site conserve, et pourquoi',
    'le-bureau-logiciel-de-gestion-interne' => 'Le Bureau, logiciel de gestion interne',
    'avec-qui' => 'Avec qui',
    'cookies-et-stockage' => 'Cookies et stockage',
    'vos-droits' => 'Vos droits',
    'mise-a-jour' => 'Mise à jour',
];

page_debut([
    'titre' => 'Confidentialité',
    'description' => 'Politique de confidentialité de ConseilPlus+ : quelles données, pour quoi faire, combien de temps, et vos droits.',
    'css' => ['css/v2/pages/document.css'],
]);
?>
<section class="doc-entete" data-enter aria-labelledby="doc-titre">
  <div class="conteneur">
    <h1 id="doc-titre">Confidentialité</h1>
    <p class="chapeau">Ce que le site et Le Bureau conservent, pour quoi faire, combien de temps, et comment le faire effacer.</p>
  </div>
</section>

<div class="document">
  <div class="conteneur grille">
    <nav class="document__sommaire" aria-labelledby="sommaire-titre">
      <h2 id="sommaire-titre">Sommaire</h2>
      <ol>
<?php foreach ($sommaire as $ancre => $libelle): ?>
        <li><a href="#<?= $ancre ?>"><?= e($libelle) ?></a></li>
<?php endforeach; ?>
      </ol>
    </nav>
    <div class="document__texte texte">
      <h2 id="ce-que-le-site-conserve-et-pourquoi">Ce que le site conserve, et pourquoi</h2>
      <ul>
        <li><strong>Compte client</strong> : prénom, nom, adresse email et mot de passe (jamais enregistré en clair : seule une empreinte SHA-512 est gardée, méthode imposée par le cadre
          du cours ; un site en production utiliserait bcrypt ou Argon2).
          Sert à l'espace client. Conservé jusqu'à la suppression du compte.</li>
        <li><strong>Mon carnet</strong> : les conseils que vous classez. Supprimés avec le compte.</li>
        <li><strong>Le Duel</strong> : le score de vos parties terminées. Vos victoires peuvent apparaître au tableau d'honneur,
          visible des autres clients, avec votre prénom seulement. Supprimé avec le compte.</li>
        <li><strong>Formulaire de contact</strong> : nom, adresse email, type de demande et message. Sert à vous répondre (accusé de réception et réponse
          par courriel). Une demande envoyée en étant connecté est rattachée à votre compte : vous y lisez la réponse du cabinet
          et pouvez la supprimer depuis « Mon compte ». La demande reste sur le site jusqu'à sa suppression par vous (depuis « Mon compte »
          ou avec le compte) ou par le cabinet. Pour les demandes classées depuis plus de 6 mois, Le Bureau propose une purge
          à ses agents : la suppression du site a lieu quand un agent la déclenche, elle n'est pas automatique.</li>
        <li><strong>Rendez-vous au guichet</strong> (compte obligatoire) : nom, prénom, adresse email, téléphone, jour et horaire
          choisis, et le problème que vous déposez. Sert à organiser le rendez-vous et à vous répondre ; vous en suivez l'état
          dans « Mon compte ». Annuler un rendez-vous le marque « annulé » et libère le créneau, sans effacer la demande ;
          elle est effacée du site avec le compte.</li>
        <li><strong>Votes « Moyen / Pas moyen »</strong> : une empreinte calculée à partir de votre adresse IP et du mot voté, qui empêche
          de voter deux fois. L'adresse IP elle-même n'est pas enregistrée avec le vote.</li>
        <li><strong>Notifications du mot du jour</strong> (si vous les activez) : l'adresse technique d'envoi fournie par votre
          navigateur. Supprimée quand vous vous désabonnez.</li>
        <li><strong>Journal technique de l'API</strong> : date, page appelée et adresse IP, pour limiter les abus. Les entrées de plus de 90 jours
          sont effacées lors d'un nettoyage périodique.</li>
        <li><strong>Mot de passe oublié</strong> : un lien à usage unique, valable 1 heure.</li>
      </ul>

      <h2 id="le-bureau-logiciel-de-gestion-interne">Le Bureau, logiciel de gestion interne</h2>
      <p>Les demandes de contact sont relevées par Le Bureau, le logiciel de gestion interne du cabinet. Il en garde une copie
        dans sa propre base de données : nom, adresse email, type de demande, message, réponse du cabinet, ainsi que les notes internes
        des agents qui traitent le dossier. Il relève de la même façon les rendez-vous (« synchro ») et en garde une copie dans sa
        table des inscrits : nom, prénom, adresse email, téléphone, jour, horaire, date de dépôt et statut.</p>
      <ul>
        <li><strong>Durée de conservation</strong> : aujourd'hui, aucune suppression automatique n'est prévue dans Le Bureau.
          Sa copie est conservée tant qu'elle n'est pas effacée à la main.</li>
        <li><strong>Suppression depuis « Mon compte »</strong> : supprimer une demande, ou tout le compte, efface la copie du site.
          Si la demande avait déjà été relevée par Le Bureau, sa copie au Bureau n'est pas effacée par cette action.</li>
        <li><strong>Purge du site</strong> : quand un agent purge une demande classée, elle est retirée du site, mais le dossier reste au Bureau.</li>
        <li><strong>Effacement complet</strong> : pour faire effacer aussi la copie du Bureau, écrivez-nous via le
          <a href="contact.php">formulaire de contact</a>. La demande est traitée à la main par le cabinet.</li>
        <li><strong>Sauvegardes</strong> : les agents du Bureau peuvent télécharger une sauvegarde des bases. Celle du site ne contient
          ni les empreintes des mots de passe, ni les liens de réinitialisation.</li>
      </ul>

      <h2 id="avec-qui">Avec qui</h2>
      <ul>
        <li>Les données sont hébergées par Render (site et Bureau) et TiDB Cloud (bases de données, Francfort).</li>
        <li>Les courriels partent par Brevo.</li>
        <li>Les réponses au questionnaire de la consultation sont envoyées à Groq pour rédiger le conseil, sans votre nom ni votre adresse email.</li>
        <li>Rien n'est vendu, rien n'est partagé à des fins publicitaires.</li>
      </ul>

      <h2 id="cookies-et-stockage">Cookies et stockage</h2>
      <p>Un seul cookie, technique : la session (pour rester connecté). Le navigateur garde aussi votre choix de thème clair ou
        sombre, vos votes (pour les afficher) et, si vous installez le site, une copie des pages pour l'affichage hors ligne.
        Pas de cookie de suivi, donc pas de bandeau à accepter.</p>

      <h2 id="vos-droits">Vos droits</h2>
      <p>Vous pouvez consulter, corriger ou supprimer vos données. La suppression du compte se fait vous-même, à tout moment,
        depuis <a href="compte.php">Mon compte</a> ; elle n'efface pas les copies déjà relevées par Le Bureau (voir ci-dessus).
        Pour tout le reste, dont l'effacement des dossiers du Bureau, écrivez-nous via le <a href="contact.php">formulaire de contact</a>.
        En cas de désaccord, vous pouvez saisir la CNIL (<a href="https://www.cnil.fr">cnil.fr</a>).</p>

      <h2 id="mise-a-jour">Mise à jour</h2>
      <p>Politique mise à jour le 22 octobre 2026.</p>
    </div>
  </div>
</div>
<?php page_fin(); ?>
