<?php
/**
 * messmerde.php — Les expériences de Messmerde, mentaliste du cabinet : il « devine » des évidences
 * avec le plus grand sérieux, et tamponne « PRÉVU » à chaque fois. Tout se passe dans le navigateur
 * (js/messmerde.js) ; sans JavaScript, Messmerde l'avait évidemment prévu. Suivent ses prédictions rétroactives
 * (includes/messmerde.php : ce qui vient d'arriver, certifié après coup) et, en encart, son horoscope.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/horoscope.php';
require_once __DIR__ . '/includes/messmerde.php';
$horoscope = horoscope_du_jour();
$retro = prediction_retroactive();

page_debut([
    'titre' => 'Les expériences de Messmerde',
    'description' => "Messmerde, mentaliste du cabinet ConseilPlus+, lit dans vos pensées avec une précision moyenne : une vingtaine d'expériences, un interrogatoire, des prédictions rétroactives, que des évidences.",
    'css' => ['css/v2/pages/messmerde.css'],
    'scripts' => ['js/messmerde.js', 'js/partage.js'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <p class="reference"><span>Service de divination</span><span>Bureau 6</span></p>
      <h1 id="heros-titre">Les expériences de Messmerde</h1>
      <p class="heros__chapeau">Messmerde, mentaliste du cabinet, lit dans les pensées depuis qu'il a appris à lire. Chaque séance : sept expériences tirées au sort, dont un interrogatoire, menées avec une précision moyenne et une assurance totale.</p>
      <div class="boutons">
        <?= bouton('Commencer la séance', '#seance') ?>
        <?= bouton('Prédictions rétroactives', '#retroactives', ['variante' => 'secondaire']) ?>
      </div>
      <p class="messmerde-hommage">Hommage parodique non officiel, sans lien avec un artiste existant.</p>
    </div>
    <div class="heros__visuel">
      <figure class="messmerde-portrait">
        <img src="assets/equipe/messmerde.webp" srcset="assets/equipe/messmerde-360.webp 360w, assets/equipe/messmerde-480.webp 480w, assets/equipe/messmerde.webp 640w"
          sizes="(min-width: 1024px) 40vw, 90vw" width="640" height="640" fetchpriority="high" decoding="async"
          alt="Messmerde en pleine concentration, deux doigts sur la tempe et les yeux fermés, consulte une boule à neige qui contient un château.">
      </figure>
      <div class="heros__pose"><?= tampon('Prévu', ['taille' => 'grand', 'angle' => -10, 'mouvement' => 'au-chargement', 'mention' => 'la veille', 'decoratif' => true]) ?></div>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="seance" aria-labelledby="titre-experience">
  <div class="conteneur grille messmerde">
    <noscript>
      <div class="experience c-8">
        <p class="experience__num machine">Expérience n° 0</p>
        <h2>Le navigateur</h2>
        <p class="experience__revelation">Je sens que vous avez désactivé JavaScript.</p>
        <p class="experience__tampon"><?= tampon('Prévu', ['angle' => 8]) ?></p>
      </div>
    </noscript>

    <div class="experience c-8" data-experience hidden>
      <p class="experience__num machine" data-num>Expérience n° 1 sur 7</p>
      <h2 id="titre-experience" data-titre>…</h2>
      <p class="experience__consigne" data-consigne></p>
      <div class="experience__scene" data-scene></div>
      <p class="experience__revelation" data-revelation aria-live="polite"></p>
      <p class="experience__tampon" data-tampon hidden><?= tampon('Prévu', ['angle' => 8]) ?></p>

      <div class="experience__avis" data-avis hidden>
        <p class="machine">Votre verdict</p>
        <div class="boutons">
          <button type="button" class="bouton bouton--principal" data-verdict="bluffant">Bluffant</button>
          <button type="button" class="bouton bouton--secondaire" data-verdict="moyen">Moyennement bluffant</button>
        </div>
      </div>
    </div>

    <aside class="credibilite d-9" data-credibilite hidden aria-label="Crédibilité de Messmerde">
      <p class="machine">Indice de crédibilité</p>
      <p class="credibilite__valeur" data-score>100 %</p>
      <p class="credibilite__commentaire" data-commentaire>Calculé par Messmerde lui-même.</p>
    </aside>
  </div>
</section>

<section class="ecran" id="retroactives" aria-labelledby="titre-retroactives">
  <div class="conteneur grille">
    <div class="c-5">
      <div class="ecran__tete">
        <p class="reference"><span>Registre des prédictions</span></p>
        <h2 id="titre-retroactives">Les prédictions rétroactives</h2>
        <p class="chapeau muet">Messmerde prédit ce qui vient d'arriver, puis fait certifier sa prédiction. Taux de réussite : cent pour cent, constaté après coup.</p>
      </div>
      <div class="boutons">
        <button type="button" class="bouton bouton--secondaire" data-retro-autre hidden>Une autre prédiction</button>
        <?= lien_fleche('Faire certifier la prédiction', 'attestation.php?document=prediction') ?>
      </div>
    </div>
    <div class="d-7">
      <article class="certificat" aria-labelledby="certificat-titre" data-papier>
        <header class="certificat__entete">
          <p class="certificat__num mono" id="certificat-titre">Prédiction rétroactive n° <span data-retro="numero"><?= e($retro['numero']) ?></span></p>
          <p class="certificat__quand">Événement visé : <span data-retro="quand"><?= e($retro['quand']) ?></span></p>
        </header>
        <blockquote class="certificat__texte"><p data-retro="texte"><?= e($retro['texte']) ?></p></blockquote>
        <dl class="certificat__dates">
          <div><dt>Événement survenu le</dt><dd class="mono" data-retro="evenement"><?= e($retro['evenement']) ?></dd></div>
          <div><dt>Prédiction déposée le</dt><dd class="mono" data-retro="depot"><?= e($retro['depot']) ?></dd></div>
          <div><dt>Avance de la prédiction</dt><dd class="mono">− <span data-retro="ecart"><?= e($retro['ecart']) ?></span>, dans les tolérances du cabinet</dd></div>
        </dl>
        <p class="certificat__tampon" data-retro-tampon><?= tampon('Prévu', ['taille' => 'grand', 'angle' => 8, 'mention' => 'après coup']) ?></p>
      </article>
      <p class="visuellement-cache" role="status" data-retro-statut></p>
    </div>
  </div>
</section>

<section class="ecran ecran--fond-2" id="horoscope" aria-labelledby="titre-horoscope">
  <div class="conteneur">
    <div class="encart">
      <div class="encart__tete">
        <p class="reference"><span>Encart</span><span><?= e(date('d/m/Y')) ?></span></p>
        <h2 id="titre-horoscope">L'horoscope de Messmerde</h2>
        <p class="muet">Chaque matin, Messmerde consulte les astres, puis le calendrier. Ses prédictions se réalisent toutes : il n'en fait que de sûres.</p>
      </div>
      <ul class="horoscope" role="list">
<?php foreach ($horoscope as $s): ?>
        <li class="signe">
          <h3 class="signe__nom"><?= e($s['nom']) ?> <span class="signe__dates mono"><?= e($s['dates']) ?></span></h3>
          <p class="signe__prediction"><?= e($s['prediction']) ?></p>
          <p class="signe__infos mono">Chiffre : <?= (int) $s['chiffre'] ?> · Couleur : <?= e($s['couleur']) ?></p>
          <button type="button" class="bouton bouton--secondaire bouton--compact" hidden data-partager="texte" data-nom="horoscope-<?= e($s['cle']) ?>"
            data-titre="<?= e($s['nom']) ?>" data-texte="<?= e($s['prediction']) ?>"
            data-detail="Chiffre porte-bonheur : <?= (int) $s['chiffre'] ?>. Couleur : <?= e($s['couleur']) ?>."
            data-etiquette="L'horoscope de Messmerde — <?= date('d/m/Y') ?>" data-url="messmerde.php#horoscope">Partager<span class="visuellement-cache"> l'horoscope <?= e($s['nom']) ?></span></button>
        </li>
<?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<script type="application/json" id="retroactives-donnees"><?= json_encode(predictions_retroactives(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<?php page_fin(); ?>
