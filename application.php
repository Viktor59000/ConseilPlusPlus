<?php
/**
 * application.php — Installer ConseilPlus+ sur son téléphone : l'appli Android et son widget, la notification du mot
 * du jour (js/notifications.js), l'application web installable (bouton « Installer » seulement si le navigateur
 * le permet : js/pwa.js, chargé par le gabarit), et un widget KWGT pour les bricoleurs (bouton « Copier » : js/pwa.js).
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';

$api_widget = 'https://conseilplus.onrender.com/api/widget.php';
$formules = [
    'Le mot du jour' => '.mot',
    'La définition (ou la chute du sketch)' => '.ligne1',
    'Le conseil' => '.conseil',
];
$sommaire = ['android' => 'Android : l\'application et son widget', 'notifications' => 'Le mot du jour en notification',
    'navigateur' => 'Sans téléchargement (iPhone et Android)', 'kwgt' => 'Widget personnalisé avec KWGT'];

page_debut([
    'titre' => 'L\'application',
    'description' => 'Installez ConseilPlus+ sur votre téléphone : le conseil et le mot (presque) du jour, à portée de pouce.',
    'css' => ['css/v2/pages/document.css', 'css/v2/pages/application.css'],
    'scripts' => ['js/notifications.js'],
]);
?>
<section class="doc-entete" data-enter aria-labelledby="doc-titre">
  <div class="conteneur">
    <h1 id="doc-titre">Le cabinet, dans votre poche</h1>
    <p class="chapeau">Le conseil, le mot (presque) du jour, un widget sur votre écran d'accueil et une notification chaque matin. Le cabinet se met à jour tout seul, à son rythme.</p>
    <div class="boutons"><?= bouton('Télécharger l\'application Android', 'https://github.com/Viktor59000/ConseilPlusPlus/releases/download/android/conseilplus.apk', ['attributs' => ['download' => true]]) ?></div>
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
    <div class="document__texte">
      <h2 id="android">Android : l'application et son widget</h2>
      <div class="etapes-install">
        <article>
          <h3>1. Installer</h3>
          <ol>
            <li>Touchez <strong>Télécharger l'application Android</strong>, puis ouvrez le fichier <strong>conseilplus.apk</strong>.</li>
            <li>Android demande d'autoriser l'installation depuis votre navigateur : <strong>Paramètres</strong> → <strong>Autoriser</strong>. Une seule fois.</li>
            <li>Touchez <strong>Installer</strong>. L'icône C++ apparaît avec vos applications.</li>
          </ol>
        </article>
        <article>
          <h3>2. Poser le widget</h3>
          <ol>
            <li>Appui long sur un espace vide de l'écran d'accueil → <strong>Widgets</strong>.</li>
            <li>Cherchez <strong>ConseilPlus+</strong> et faites glisser le widget sur l'écran.</li>
            <li>C'est une carte à deux faces : le <strong>conseil du jour</strong> et le <strong>mot du jour</strong>. <strong>⇄</strong> retourne la carte, <strong>↻</strong> donne un autre conseil. Posez-en deux pour voir les deux faces.</li>
          </ol>
        </article>
      </div>
      <p class="muet">L'application ne vient pas du Play Store : Android le signale, c'est normal. Elle n'accède qu'au site du cabinet.</p>

      <section data-notifications aria-labelledby="notifications">
        <h2 id="notifications">Le mot du jour en notification</h2>
        <p>Chaque matin, vers 7 h, le cabinet vous envoie le mot (presque) du jour. Une notification par jour, pas une de plus.</p>
        <div class="boutons"><button type="button" class="bouton bouton--secondaire" data-notif-bouton hidden>Recevoir le mot du jour chaque matin</button></div>
        <p class="muet" data-notif-etat role="status"></p>
      </section>

      <h2 id="navigateur">Sans téléchargement (iPhone et Android)</h2>
      <p>Le site s'installe aussi directement depuis le navigateur, sans widget.</p>
      <div class="boutons"><button type="button" class="bouton bouton--secondaire" id="installer" hidden>Installer depuis le navigateur</button></div>
      <p class="muet" id="installation-etat" role="status"></p>
      <div class="etapes-install">
        <article>
          <h3>Android</h3>
          <ol>
            <li>Ouvrez ce site dans <strong>Chrome</strong>.</li>
            <li>Touchez <strong>Installer depuis le navigateur</strong> ci-dessus.<br>S'il n'apparaît pas : menu <strong>⋮</strong> en haut à droite, puis <strong>Ajouter à l'écran d'accueil</strong> → <strong>Installer</strong>.</li>
          </ol>
        </article>
        <article>
          <h3>iPhone</h3>
          <ol>
            <li>Ouvrez ce site dans <strong>Safari</strong>.</li>
            <li>Touchez le bouton <strong>Partager</strong> (le carré avec une flèche vers le haut).</li>
            <li>Choisissez <strong>Sur l'écran d'accueil</strong>, puis <strong>Ajouter</strong>.</li>
          </ol>
        </article>
      </div>
      <p class="muet">Sans réseau, le site installé affiche les derniers contenus consultés.</p>

      <h2 id="kwgt">Widget personnalisé avec KWGT</h2>
      <p>Pour les bricoleurs : le mot du jour et un conseil dans un widget dessiné par vos soins, grâce à l'application gratuite KWGT.</p>
      <div class="etapes-install">
        <article>
          <h3>1. Poser le widget</h3>
          <ol>
            <li>Installez <strong>KWGT Kustom Widget Maker</strong> depuis le Play Store.</li>
            <li>Appui long sur l'écran d'accueil → <strong>Widgets</strong> → <strong>KWGT</strong> → taille <strong>4×2</strong>, posez-le.</li>
            <li>Touchez le widget vide : l'éditeur KWGT s'ouvre.</li>
          </ol>
        </article>
        <article>
          <h3>2. Le remplir</h3>
          <ol>
            <li>Touchez <strong>+</strong> en haut à droite, choisissez <strong>Texte</strong>.</li>
            <li>Touchez ce texte, puis le champ <strong>Texte</strong>, et collez une formule ci-dessous.</li>
            <li>Recommencez pour chaque ligne voulue, puis enregistrez (icône disquette).</li>
          </ol>
        </article>
      </div>
      <ul class="kwgt">
<?php foreach ($formules as $libelle => $champ):
    $formule = '$wg("' . $api_widget . '", json, "' . $champ . '")$'; ?>
        <li>
          <span class="kwgt__libelle"><?= e($libelle) ?></span>
          <code><?= e($formule) ?></code>
          <button type="button" class="bouton bouton--secondaire bouton--compact" data-copier="<?= e($formule) ?>" hidden>Copier</button>
        </li>
<?php endforeach; ?>
      </ul>
      <p class="muet">Couleurs du cabinet : papier <code>#F7F7F3</code>, encre <code>#181715</code>, rouge tampon <code>#C8102E</code>, laiton <code>#BFB21A</code>. Le widget se met à jour tout seul ; le premier chargement de la journée peut prendre une minute (le cabinet se réveille).</p>
    </div>
  </div>
</div>
<?php page_fin(); ?>
