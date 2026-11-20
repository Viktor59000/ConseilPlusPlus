<?php
/**
 * index.php — Le Hall (accueil du Guichet) : la démonstration en moins d'une minute.
 * Héros (gravure du cabinet + ticket de la file, lu dans les rendez-vous), conseil du jour (générateur : un autre
 * conseil sans recharger, partage), la phrase-manifeste qui s'encre et l'article du règlement du jour, le parcours d'un
 * dossier en quatre étapes illustrées, le mot du jour en bref et l'épisode de la semaine du feuilleton, puis
 * l'invitation à prendre rendez-vous. Styles : css/v2/pages/hall.css.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/tirage.php';
require_once __DIR__ . '/includes/mot-du-jour.php';
require_once __DIR__ . '/includes/textes.php';
require_once __DIR__ . '/includes/feuilleton.php';
require_once __DIR__ . '/includes/guichets.php';

$conseil = tirer_conseil($bdd);
$mot = mot_du_jour($bdd);
$article = article_du_jour();
$episode = episode_de_la_semaine();
$file = file_du_guichet($bdd);
$dossier = isset($_SESSION['user_id']) ? 'compte.php' : 'login.php';
$aujourdhui = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));

page_debut([
    'titre_complet' => 'ConseilPlus+, le conseiller moyen — Le Hall',
    'description' => 'ConseilPlus+, le conseiller moyen : prenez rendez-vous au guichet, déposez votre problème, suivez votre dossier et repartez avec un verdict tamponné.',
    'page' => 'hall',
    'css' => ['css/v2/pages/hall.css'],
    'scripts' => ['js/conseil.js', 'js/partage.js'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Votre problème mérite un dossier</h1>
      <p class="heros__chapeau">Prenez rendez-vous au guichet du cabinet. Votre demande est reçue, tamponnée, instruite, puis tranchée, et vous suivez chaque étape dans Mon dossier, comme un colis, en plus lent.</p>
      <div class="boutons">
        <?= bouton('Prendre rendez-vous', 'guichet.php#rendez-vous') ?>
        <?= bouton('Suivre mon dossier', $dossier, ['variante' => 'secondaire']) ?>
      </div>
    </div>
    <div class="heros__visuel">
      <?= scene('cabinet', 'Le bureau du cabinet : quatre-vingt-quatre tiroirs, une horloge sans chiffres, une lampe allumée sur une pile de dossiers en attente.', ['prioritaire' => true, 'tailles' => '(min-width: 1024px) 46vw, 100vw']) ?>
      <div class="heros__pose"><?= ticket($file['numero'], ['guichet' => '1', 'libelle' => 'Prochain numéro',
          'date' => 'Délivré le ' . $aujourdhui->format('d/m/Y'), 'attente' => 'Personnes avant vous : ' . $file['attente']]) ?></div>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="conseil" aria-labelledby="conseil-titre">
  <div class="conteneur grille hall-conseil">
    <h2 id="conseil-titre">Le conseil du jour</h2>
    <div class="c-8 hall-conseil__fiche">
      <?= fiche_conseil($conseil, [
          'titre' => 'Conseil du jour',
          'actions_html' => bouton('Un autre conseil', 'index.php#conseil', ['taille' => 'compacte', 'attributs' => ['data-autre-conseil' => '#conseil-du-jour']])
              . bouton('Partager le conseil', null, ['type' => 'button', 'variante' => 'secondaire', 'taille' => 'compacte',
                  'attributs' => ['data-partager' => 'conseil', 'data-source' => '#conseil-du-jour', 'hidden' => true]]),
      ]) ?>
    </div>
    <div class="d-10 hall-conseil__marge">
      <p class="chapeau">Trois idées tirées au sort, qui ne se connaissaient pas, présentées les unes aux autres.</p>
      <p>Le conseil ne concerne pas votre problème, mais il est disponible tout de suite, et un autre attend derrière.</p>
      <p class="hall-conseil__mention"><?= e(texte_hasard('mentions')) ?></p>
    </div>
  </div>
</section>

<section class="ecran" id="reglement" aria-labelledby="reglement-titre">
  <div class="conteneur hall-reglement">
    <h2 id="reglement-titre" class="visuellement-cache">Le règlement intérieur</h2>
    <p class="phrase-encre" data-encre>Ici, chaque problème est reçu, numéroté, tamponné, et traité <em>avec le plus grand sérieux</em>, ce qui ne veut pas dire correctement.</p>
    <div class="hall-article">
      <p class="hall-article__numero">Article <?= (int) $article['numero'] ?></p>
      <p class="hall-article__texte"><?= e($article['texte']) ?></p>
      <p class="hall-article__garde">Affiché ce matin par Bertrand, gardien du règlement, qui en connaît chaque article et n'en applique aucun à lui-même.</p>
    </div>
  </div>
</section>

<section class="ecran ecran--fond-2" id="parcours" aria-labelledby="parcours-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="parcours-titre">Un dossier en quatre étapes</h2>
      <p class="chapeau">Le circuit est le même pour tout le monde. Seule la durée change, et personne au cabinet ne sait de quoi elle dépend.</p>
    </div>
    <div class="rangee rangee--classeur hall-parcours" style="--par: 4">
      <?= carte(['etiquette' => 'Étape 1', 'titre' => 'Prendre rendez-vous', 'niveau' => 3,
          'texte' => 'Choisissez un jour et un horaire au guichet, puis déposez votre problème en une phrase. Le cabinet préfère les phrases courtes.',
          'scene' => ['nom' => 'guichet', 'alt' => 'Le guichet du cabinet : une grille en laiton, un tampon suspendu au-dessus d\'une feuille vierge, un cordon rouge pour contenir une foule qui n\'est pas venue.']]) ?>
      <?= carte(['etiquette' => 'Étape 2', 'titre' => 'Recevoir le reçu', 'niveau' => 3,
          'texte' => 'Le tampon « Reçu » s\'abat sur votre attestation de dépôt. Elle ne prouve rien, mais elle le prouve officiellement.',
          'scene' => ['nom' => 'methode-1', 'alt' => 'Une enveloppe tombe dans une corbeille en laiton, entre deux piles de formulaires et une sonnette que personne n\'ose actionner.']]) ?>
      <?= carte(['etiquette' => 'Étape 3', 'titre' => 'Attendre l\'instruction', 'niveau' => 3,
          'texte' => 'Bertrand relève votre demande au Bureau et la range dans le bon casier, puis dans un autre, par prudence.',
          'scene' => ['nom' => 'salle-de-tri', 'alt' => 'La salle de tri du Bureau : des casiers jusqu\'au plafond, chacun étiqueté, aucun dans l\'ordre.']]) ?>
      <?= carte(['etiquette' => 'Étape 4', 'titre' => 'Lire le verdict', 'niveau' => 3,
          'texte' => 'Le cabinet confirme votre rendez-vous et vous répond dans Mon dossier. La réponse est tamponnée, donc définitive.',
          'scene' => ['nom' => 'dossier', 'alt' => 'Une chemise kraft sanglée de rouge, un tampon encreur et un stylo plume : tout ce qu\'il faut pour décider sans lire.']]) ?>
    </div>
    <p class="hall-parcours__suite"><?= lien_fleche('Ouvrir Mon dossier', $dossier) ?></p>
  </div>
</section>

<section class="ecran" id="publications" aria-labelledby="publications-titre">
  <div class="conteneur grille hall-publications">
    <div class="c-5 hall-publications__tete">
      <h2 id="publications-titre">À lire en attendant votre tour</h2>
      <p class="chapeau">Les deux publications du cabinet : un dictionnaire tenu au jour le jour, et un feuilleton joué par l'équipe en costume de carton.</p>
    </div>
    <div class="d-7 hall-publications__liste">
<?php if ($mot): ?>
      <article class="hall-entree" aria-labelledby="mot-titre">
        <h3 id="mot-titre">Le mot du jour</h3>
        <p class="hall-entree__article"><b class="hall-entree__mot"><?= e($mot['mot']) ?></b>, <i><?= e($mot['nature']) ?></i>
<?php if ($mot['format'] === 'sketch'): ?>
          Mot joué en sketch par le conseiller et le stagiaire, qui conclut : « <?= e((string) chute_sketch($mot)) ?> »</p>
<?php else: ?>
          <?= e($mot['definition']) ?></p>
<?php endif; ?>
        <p><?= lien_fleche('Lire Le Petit Moyen', 'petit-moyen.php') ?></p>
      </article>
<?php endif; ?>
      <article class="hall-entree" aria-labelledby="feuilleton-titre">
        <h3 id="feuilleton-titre">Le feuilleton de la semaine</h3>
        <p class="hall-entree__article"><b>La Plume et le Tampon</b>, épisode <?= (int) $episode['numero'] ?>, « <?= e($episode['titre']) ?> ». <?= e($episode['heraut']) ?></p>
        <p><?= lien_fleche('Voir l\'épisode', 'petit-moyen.php#feuilleton') ?></p>
      </article>
    </div>
  </div>
</section>

<section class="ecran ecran--papier hall-fermeture" id="ouvert" aria-labelledby="ouvert-titre">
  <div class="conteneur">
    <div class="hall-fermeture__tete">
      <div class="ecran__tete">
        <h2 id="ouvert-titre">Les horaires du guichet</h2>
        <p class="chapeau">Du lundi au vendredi, six créneaux par jour, jamais à l'heure ronde : le cabinet tient à sa réputation de sérieux. Il suffit d'un compte, d'un jour libre et d'un problème, même petit.</p>
      </div>
      <?= tampon('Ouvert', ['taille' => 'grand', 'angle' => -8, 'mention' => 'Guichet n° 1', 'decoratif' => true, 'classe' => 'hall-fermeture__tampon']) ?>
    </div>
    <div class="hall-tableau">
      <ul class="hall-tableau__horaires" aria-label="Les six horaires du guichet">
<?php foreach (HORAIRES_GUICHET as $horaire): ?>
        <li><?= e(libelle_horaire($horaire)) ?></li>
<?php endforeach; ?>
      </ul>
    </div>
    <div class="boutons hall-fermeture__action"><?= bouton('Prendre rendez-vous', 'guichet.php#rendez-vous') ?></div>
  </div>
</section>
<?php page_fin(); ?>
