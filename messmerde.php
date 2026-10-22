<?php
/**
 * messmerde.php — Les expériences de Messmerde, mentaliste du cabinet : il « devine » des évidences
 * avec le plus grand sérieux, et tamponne « PRÉVU » à chaque fois. Tout se passe dans le navigateur
 * (js/messmerde.js) ; sans JavaScript, Messmerde l'avait évidemment prévu.
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/horoscope.php';
$horoscope = horoscope_du_jour();

$titre = 'Les expériences de Messmerde';
$description = "Messmerde, mentaliste du cabinet ConseilPlus+, lit dans vos pensées avec une précision moyenne : une vingtaine d'expériences, un interrogatoire, que des évidences.";
$page = 'autre';
include __DIR__ . '/includes/header.php';
?>

<section class="conteneur entete-page messmerde-entete">
  <div>
    <p class="etiquette">Service de divination</p>
    <h1>Les expériences de Messmerde</h1>
    <p class="chapo">Messmerde, mentaliste du cabinet, lit dans les pensées depuis qu'il a appris à lire. Chaque séance : sept expériences tirées au sort, dont un interrogatoire, menées avec une précision moyenne et une assurance totale.</p>
  </div>
  <img class="messmerde-portrait" src="assets/equipe/messmerde-480.webp" srcset="assets/equipe/messmerde-360.webp 360w, assets/equipe/messmerde-480.webp 480w, assets/equipe/messmerde.webp 640w" sizes="(max-width: 760px) 240px, 360px" alt="Messmerde en pleine concentration, deux doigts sur la tempe, une boule à neige à la main" width="640" height="640" decoding="async">
</section>

<section class="conteneur messmerde" aria-labelledby="titre-experience">
  <noscript>
    <div class="experience">
      <p class="experience-num mono">Expérience n° 0</p>
      <h2>Le navigateur</h2>
      <p class="experience-revelation">Je sens que vous avez désactivé JavaScript.</p>
      <p class="experience-tampon">Prévu</p>
    </div>
  </noscript>

  <div class="experience" data-experience hidden>
    <p class="experience-num mono" data-num>Expérience n° 1 sur 7</p>
    <h2 id="titre-experience" data-titre>…</h2>
    <p class="experience-consigne" data-consigne></p>
    <div class="experience-scene" data-scene></div>
    <p class="experience-revelation" data-revelation aria-live="polite"></p>
    <p class="experience-tampon" data-tampon hidden>Prévu</p>

    <div class="experience-avis" data-avis hidden>
      <p class="mono">Votre verdict</p>
      <div class="actions">
        <button type="button" class="btn btn-tampon" data-verdict="bluffant">Bluffant</button>
        <button type="button" class="btn btn-clair" data-verdict="moyen">Moyennement bluffant</button>
      </div>
    </div>
  </div>

  <aside class="credibilite" data-credibilite hidden aria-label="Crédibilité de Messmerde">
    <p class="mono">Indice de crédibilité</p>
    <p class="credibilite-valeur" data-score>100 %</p>
    <p class="aide" data-commentaire>Calculé par Messmerde lui-même.</p>
  </aside>
</section>

<section class="conteneur" aria-labelledby="titre-horoscope" id="horoscope">
  <h2 id="titre-horoscope">L'horoscope de Messmerde</h2>
  <p class="chapo">Chaque matin, Messmerde consulte les astres, puis le calendrier. Ses prédictions se réalisent toutes : il n'en fait que de sûres.</p>
  <ul class="horoscope">
<?php foreach ($horoscope as $s): ?>
    <li class="signe">
      <h3><?= htmlspecialchars($s['nom']) ?> <span class="signe-dates mono"><?= htmlspecialchars($s['dates']) ?></span></h3>
      <p class="signe-prediction"><?= htmlspecialchars($s['prediction']) ?></p>
      <p class="signe-infos mono">Chiffre : <?= $s['chiffre'] ?> · Couleur : <?= htmlspecialchars($s['couleur']) ?></p>
      <button type="button" class="btn btn-clair btn-petit" hidden data-partager="texte" data-nom="horoscope-<?= $s['cle'] ?>"
        data-titre="<?= htmlspecialchars($s['nom']) ?>" data-texte="<?= htmlspecialchars($s['prediction']) ?>"
        data-detail="Chiffre porte-bonheur : <?= $s['chiffre'] ?>. Couleur : <?= htmlspecialchars($s['couleur']) ?>."
        data-etiquette="L'horoscope de Messmerde — <?= date('d/m/Y') ?>" data-url="messmerde.php#horoscope">Partager</button>
    </li>
<?php endforeach; ?>
  </ul>
</section>

<script src="<?= fichier_versionne('js/messmerde.js') ?>" defer></script>
<script src="<?= fichier_versionne('js/partage.js') ?>" defer></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
