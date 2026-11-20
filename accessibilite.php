<?php
/**
 * accessibilite.php — Déclaration d'accessibilité (modèle RGAA 4.1).
 * À mettre à jour après chaque audit : état de conformité, non-conformités, date.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';

$sommaire = [
    'etat' => 'État de conformité',
    'resultats' => 'Résultats de l\'audit interne',
    'non-accessibles' => 'Contenus non accessibles',
    'etablissement' => 'Établissement de cette déclaration',
    'contact' => 'Retour d\'information et contact',
    'recours' => 'Voies de recours',
];

page_debut([
    'titre' => 'Accessibilité',
    'description' => 'Déclaration d\'accessibilité de ConseilPlus+ : état de conformité au RGAA 4.1 et moyens de nous signaler un problème.',
    'css' => ['css/v2/pages/document.css'],
]);
?>
<section class="doc-entete" data-enter aria-labelledby="doc-titre">
  <div class="conteneur">
    <h1 id="doc-titre">Déclaration d'accessibilité</h1>
    <p class="chapeau">Nos conseils sont moyens. L'accès à nos conseils, lui, doit être le même pour tout le monde.</p>
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
      <p>ConseilPlus+ s'engage à rendre son site accessible, conformément au référentiel général d'amélioration de l'accessibilité (RGAA) version 4.1.
        Cette déclaration s'applique au site <a href="https://conseilplus.onrender.com/">conseilplus.onrender.com</a>.</p>

      <h2 id="etat">État de conformité</h2>
      <p>Le site est <strong>partiellement conforme</strong> au RGAA 4.1 : un audit interne a été réalisé, sans audit externe à ce jour.</p>

      <h2 id="resultats">Résultats de l'audit interne</h2>
      <ul>
        <li>Pages vérifiées (nouvelle version du site, « Le Guichet ») : Le Hall, Le Guichet (dépôt d'une demande, prise de rendez-vous, Questions pour un stagiaire, Le Duel, le diagnostic, les attestations), Mon dossier (suivi des demandes, carnet, compte), connexion et mot de passe oublié, Le Personnel, Le Petit Moyen et son feuilleton « La Plume et le Tampon », Les expériences de Messmerde (et son horoscope), Nos formules, Réclamations et le standard, L'application, L'API (développeurs), Plan du site, mentions légales, confidentialité, cette page.</li>
        <li>Contrastes vérifiés en apparence claire et sombre (texte : 4,5:1 au minimum, composants : 3:1 au minimum).</li>
        <li>Navigation complète au clavier, focus visible, lien d'évitement vers le contenu, plan du site.</li>
        <li>Formulaires : étiquettes visibles, champs obligatoires signalés, erreurs décrites en texte et reliées au champ.</li>
        <li>Contenus mis à jour sans recharger la page (expériences de Messmerde, standard, attestations) : changements annoncés aux technologies d'assistance, et le focus clavier suit le contenu quand un bouton disparaît.</li>
        <li>Le standard se pilote par des choix cliquables, à la souris, au toucher ou au clavier (Tab et Entrée) ; la voix de synthèse et la musique d'attente sont facultatives et s'arrêtent en raccrochant. Le diagnostic fonctionne sans JavaScript.</li>
        <li>Animations brèves, désactivées si le système demande de réduire les animations. Le « Guichet adapté » (formulaire CERFA 0-A, en haut et en bas de chaque page) permet aussi de choisir soi-même un régime : ordinaire, allégé (mouvement réduit, images légères) ou prioritaire (contraste renforcé, texte agrandi).</li>
        <li>Outils : Lighthouse (accessibilité 100/100 sur la version précédente ; nouvelle mesure à la fin de la refonte), navigation au clavier, zoom à 200 % et affichage à 320 pixels de large.</li>
      </ul>

      <h2 id="non-accessibles">Contenus non accessibles</h2>
      <ul>
        <li>Le Duel, la lecture des sketchs et des épisodes du feuilleton, les expériences de Messmerde et le standard mettent à jour le contenu sans recharger la page. Les zones concernées sont annoncées aux technologies d'assistance, mais n'ont pas encore été testées avec un lecteur d'écran.</li>
        <li>Les attestations demandent JavaScript (le document est dessiné dans le navigateur, pour que le nom saisi ne soit jamais envoyé au serveur) ; un message l'indique quand JavaScript est désactivé.</li>
        <li>Les images à partager (format story) et les attestations téléchargées sont des fichiers PNG sans texte alternatif intégré ; sur la page, l'aperçu a un texte alternatif, et le même texte accompagne toujours le partage.</li>
        <li>Le logiciel de gestion interne (Le Bureau) n'est pas couvert par cette déclaration.</li>
      </ul>

      <h2 id="etablissement">Établissement de cette déclaration</h2>
      <p>Déclaration établie le 16 octobre 2026, mise à jour le 20 novembre 2026 (nouvelle version du site).</p>

      <h2 id="contact">Retour d'information et contact</h2>
      <p>Si vous n'arrivez pas à accéder à un contenu ou à un service, <a href="contact.php">écrivez-nous via le formulaire de contact</a> (type de demande « Réclamation »). Nous vous proposerons une alternative accessible ou le contenu sous une autre forme. Dans un délai raisonnable, pour une fois.</p>

      <h2 id="recours">Voies de recours</h2>
      <p>Si vous nous avez signalé un défaut d'accessibilité sans obtenir de réponse satisfaisante, vous pouvez :</p>
      <ul>
        <li>écrire un message au <a href="https://formulaire.defenseurdesdroits.fr/">Défenseur des droits</a> ;</li>
        <li>contacter le délégué du Défenseur des droits dans votre région ;</li>
        <li>envoyer un courrier (gratuit, sans timbre) : Défenseur des droits, Libre réponse 71120, 75342 Paris CEDEX 07.</li>
      </ul>
    </div>
  </div>
</div>
<?php page_fin(); ?>
