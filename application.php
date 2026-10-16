<?php
/**
 * application.php — Installer ConseilPlus+ sur son téléphone (application web installable).
 * Le bouton « Installer » n'apparaît que si le navigateur le permet (Android, Chrome, Edge) : js/pwa.js.
 */
session_start();

$titre = "L'application";
$description = "Installez ConseilPlus+ sur votre téléphone : le conseil et le mot (presque) du jour, à portée de pouce.";
$page = 'application';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Application mobile</p>
  <h1>Le cabinet, dans votre poche</h1>
  <p class="chapo">Le conseil, le mot (presque) du jour, un widget sur votre écran d'accueil et une notification chaque matin. Le cabinet se met à jour tout seul, à son rythme.</p>
</section>

<section class="conteneur installation" aria-labelledby="titre-android">
  <h2 id="titre-android">Android : l'application et son widget</h2>
  <div class="actions">
    <a class="btn btn-tampon avance" href="https://github.com/Viktor59000/ConseilPlusPlus/releases/download/android/conseilplus.apk" download>Télécharger l'application Android</a>
  </div>
  <div class="installation-grille">
    <article class="installation-carte">
      <h3>1. Installer</h3>
      <ol>
        <li>Touchez <strong>Télécharger l'application Android</strong>, puis ouvrez le fichier <strong>conseilplus.apk</strong>.</li>
        <li>Android demande d'autoriser l'installation depuis votre navigateur : <strong>Paramètres</strong> → <strong>Autoriser</strong>. Une seule fois.</li>
        <li>Touchez <strong>Installer</strong>. L'icône C++ apparaît avec vos applications.</li>
      </ol>
    </article>
    <article class="installation-carte">
      <h3>2. Poser le widget</h3>
      <ol>
        <li>Appui long sur un espace vide de l'écran d'accueil → <strong>Widgets</strong>.</li>
        <li>Cherchez <strong>ConseilPlus+</strong> et faites glisser le widget sur l'écran.</li>
        <li>C'est une carte à deux faces : le <strong>conseil du jour</strong> et le <strong>mot du jour</strong>. <strong>⇄</strong> retourne la carte, <strong>↻</strong> donne un autre conseil. Posez-en deux pour voir les deux faces.</li>
      </ol>
    </article>
  </div>
  <p class="installation-note">L'application ne vient pas du Play Store : Android le signale, c'est normal. Elle n'accède qu'au site du cabinet.</p>
</section>

<section class="conteneur installation" aria-labelledby="titre-notifications" data-notifications>
  <h2 id="titre-notifications">Le mot du jour en notification</h2>
  <p class="chapo">Chaque matin, vers 7 h, le cabinet vous envoie le mot (presque) du jour. Une notification par jour, pas une de plus.</p>
  <div class="actions">
    <button type="button" class="btn btn-tampon" data-notif-bouton hidden>Recevoir le mot du jour chaque matin</button>
  </div>
  <p class="installation-etat" data-notif-etat role="status"></p>
</section>

<section class="conteneur installation" aria-labelledby="titre-installation">
  <h2 id="titre-installation">Sans téléchargement (iPhone et Android)</h2>
  <p class="chapo">Le site s'installe aussi directement depuis le navigateur, sans widget.</p>
  <div class="actions">
    <button type="button" class="btn btn-clair" id="installer" hidden>Installer depuis le navigateur</button>
  </div>
  <p class="installation-etat" id="installation-etat" role="status"></p>
  <div class="installation-grille">
    <article class="installation-carte">
      <h3>Android</h3>
      <ol>
        <li>Ouvrez ce site dans <strong>Chrome</strong>.</li>
        <li>Touchez <strong>Installer depuis le navigateur</strong> ci-dessus.<br>S'il n'apparaît pas : menu <strong>⋮</strong> en haut à droite, puis <strong>Ajouter à l'écran d'accueil</strong> → <strong>Installer</strong>.</li>
      </ol>
    </article>
    <article class="installation-carte">
      <h3>iPhone</h3>
      <ol>
        <li>Ouvrez ce site dans <strong>Safari</strong>.</li>
        <li>Touchez le bouton <strong>Partager</strong> (le carré avec une flèche vers le haut).</li>
        <li>Choisissez <strong>Sur l'écran d'accueil</strong>, puis <strong>Ajouter</strong>.</li>
      </ol>
    </article>
  </div>
  <p class="mono installation-note">Sans réseau, le site installé affiche les derniers contenus consultés.</p>
</section>

<?php
$api_widget = 'https://conseilplus.onrender.com/api/widget.php';
$formules = [
    'Le mot du jour' => '.mot',
    'La définition (ou la chute du sketch)' => '.ligne1',
    'Le conseil' => '.conseil',
];
?>
<section class="conteneur installation">
  <details class="sommaire">
  <summary>Pour les bricoleurs : widget personnalisé avec KWGT</summary>
  <p>Le mot du jour et un conseil dans un widget dessiné par vos soins, grâce à l'application gratuite KWGT.</p>
  <div class="installation-grille">
    <article class="installation-carte">
      <h3>1. Poser le widget</h3>
      <ol>
        <li>Installez <strong>KWGT Kustom Widget Maker</strong> depuis le Play Store.</li>
        <li>Appui long sur l'écran d'accueil → <strong>Widgets</strong> → <strong>KWGT</strong> → taille <strong>4×2</strong>, posez-le.</li>
        <li>Touchez le widget vide : l'éditeur KWGT s'ouvre.</li>
      </ol>
    </article>
    <article class="installation-carte">
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
      <span class="kwgt-libelle"><?= htmlspecialchars($libelle) ?></span>
      <code><?= htmlspecialchars($formule) ?></code>
      <button type="button" class="btn btn-clair btn-petit" data-copier="<?= htmlspecialchars($formule) ?>" hidden>Copier</button>
    </li>
<?php endforeach; ?>
  </ul>
  <p class="installation-note">Couleurs du cabinet : fond <code>#FFFFFF</code>, texte <code>#1B2440</code>, stylo rouge <code>#C8102E</code>, surligneur <code>#FFE14D</code>. Le widget se met à jour tout seul ; le premier chargement de la journée peut prendre une minute (le cabinet se réveille).</p>
  </details>
</section>

<script src="js/notifications.js" defer></script>
<?php include("includes/footer.php"); ?>
