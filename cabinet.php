<?php
/**
 * cabinet.php — Le Personnel : l'équipe du cabinet en collection (portrait, poste, fiche, étape du dossier tenue
 * et services), le circuit d'un dossier, les valeurs, quelques chiffres et des témoignages.
 * Contenus : data/textes.php (« equipe », « valeurs », « temoignages ») et data/personnel.php (includes/personnel.php).
 * Portraits : assets/equipe/<cle>.webp ; sans portrait, un monogramme au tampon (Bertrand, en attendant le sien).
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/personnel.php';

$t = textes();
$personnel = personnel();
$fondateur = $personnel['membres'][0];

page_debut([
    'titre' => 'Le Personnel',
    'description' => 'Le personnel du cabinet ConseilPlus+ : un conseiller moyen, un stagiaire depuis 2009, Mireille, Bertrand, un tampon, un mentaliste et une machine à café.',
    'page' => 'personnel',
    'css' => ['css/v2/pages/personnel.css'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Le Personnel</h1>
      <p class="heros__chapeau">Fondé un mardi, faute de mieux. Depuis, sept agents instruisent chaque dossier avec un sérieux exemplaire et un résultat moyen. Le stagiaire est en poste depuis 2009 ; le huitième bureau attend son titulaire depuis la fondation.</p>
      <div class="boutons">
        <?= bouton('Voir l\'annuaire', '#annuaire') ?>
        <?= bouton('Le circuit d\'un dossier', '#circuit', ['variante' => 'secondaire']) ?>
      </div>
    </div>
    <div class="heros__visuel">
      <figure class="portrait portrait--heros">
        <?= portrait_membre($fondateur, '', '(min-width: 1024px) 46vw, 100vw') ?>
      </figure>
      <div class="heros__pose personnel-plaque" data-papier>
        <p class="machine">Poste <?= e($fondateur['poste']) ?></p>
        <p class="personnel-plaque__nom"><?= e($fondateur['nom']) ?></p>
        <p class="personnel-plaque__role"><?= e($fondateur['role']) ?></p>
        <?= tampon('Fondateur', ['taille' => 'petit', 'angle' => -9, 'mouvement' => 'au-chargement', 'decoratif' => true]) ?>
      </div>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="annuaire" style="--h: 2" aria-labelledby="annuaire-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="annuaire-titre">Sept agents, une étape chacun</h2>
      <p class="chapeau">Chaque agent tient une étape de votre dossier, et le service qui va avec. Ils ne se parlent pas : c'est ce qui garantit leur indépendance.</p>
    </div>
    <ul class="collection" role="list">
<?php foreach ($personnel['membres'] as $m):
    $etape = $personnel['circuit'][$m['etape']] ?? null; ?>
      <li class="fiche-agent" id="agent-<?= e($m['cle']) ?>">
        <div class="portrait"><?= portrait_membre($m) ?></div>
        <p class="libelle">Poste <?= e($m['poste']) ?><?= $etape ? ', étape « ' . e($etape['titre']) . ' »' : '' ?></p>
        <h3 class="fiche-agent__nom"><?= e($m['nom']) ?></h3>
        <p class="fiche-agent__role"><?= e($m['role']) ?></p>
        <p class="fiche-agent__bio"><?= e($m['bio']) ?></p>
        <dl class="fiche-agent__fiche">
<?php foreach ($m['fiche'] as $intitule => $valeur): ?>
          <div><dt><?= e($intitule) ?></dt><dd><?= e($valeur) ?></dd></div>
<?php endforeach; ?>
        </dl>
<?php if (!empty($m['liens'])): ?>
        <ul class="fiche-agent__liens" aria-label="Services tenus par <?= e($m['nom']) ?>">
<?php foreach ($m['liens'] as [$lien, $libelle]): ?>
          <li><?= lien_fleche($libelle, $lien) ?></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
      </li>
<?php endforeach; ?>
      <li class="fiche-agent fiche-agent--vacant">
        <div class="portrait portrait--vacant"><span aria-hidden="true">?</span></div>
        <p class="libelle">Poste 08, vacant</p>
        <h3 class="fiche-agent__nom">Poste à pourvoir</h3>
        <p class="fiche-agent__bio">Ouvert depuis la fondation. Le jury se réunit dès qu'il aura un président, lequel sera choisi parmi les candidats retenus.</p>
        <ul class="fiche-agent__liens" aria-label="Candidater">
          <li><?= lien_fleche('Déposer une candidature', 'contact.php') ?></li>
        </ul>
      </li>
    </ul>
  </div>
</section>

<section class="ecran" id="circuit" aria-labelledby="circuit-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="circuit-titre">Le circuit d'un dossier</h2>
      <p class="chapeau">Cinq étapes, cinq guichets intérieurs. Un dossier les franchit toutes, dans l'ordre, sauf quand il les franchit dans un autre.</p>
    </div>
    <ol class="circuit">
<?php $n = 0; foreach ($personnel['circuit'] as $cle => $etape): $n++; ?>
      <li class="circuit__etape">
        <p class="circuit__numero mono" aria-hidden="true"><?= sprintf('%02d', $n) ?></p>
        <h3 class="circuit__titre"><?= e($etape['titre']) ?></h3>
        <p class="circuit__texte"><?= e($etape['texte']) ?></p>
        <p class="circuit__agents"><span class="visuellement-cache">Tenue par : </span><?= e(implode(', ', array_map(fn ($m) => $m['nom'], membres_de_l_etape($cle)))) ?></p>
        <p class="circuit__lien"><?= lien_fleche($etape['lien'][1], $etape['lien'][0]) ?></p>
      </li>
<?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="ecran ecran--fond-2" id="valeurs" aria-labelledby="valeurs-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="valeurs-titre" class="visuellement-cache">Nos valeurs</h2>
    </div>
    <p class="phrase-encre" data-encre>Le cabinet a quatre valeurs. Il les applique <em>toutes à la fois</em>, ce qui explique les résultats.</p>
    <ol class="valeurs">
<?php foreach ($t['valeurs'] as $i => [$valeur, $explication]): ?>
      <li class="valeurs__item">
        <h3 class="valeurs__nom"><?= e($valeur) ?></h3>
        <p class="valeurs__texte"><?= e($explication) ?></p>
      </li>
<?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="ecran" id="releve" aria-labelledby="releve-titre">
  <div class="conteneur grille">
    <div class="c-5">
      <div class="ecran__tete">
        <h2 id="releve-titre">Le relevé annuel</h2>
      </div>
      <ul class="releve">
        <li>À la connaissance du cabinet, aucun de ses conseils n'a jamais été suivi.</li>
        <li>Le stagiaire est le même depuis 2009. Il n'a pas été titularisé, par prudence.</li>
        <li>Délai moyen de traitement : <?= e(lcfirst($t['delais'][0])) ?>.</li>
        <li>Tous les dossiers portent un numéro, y compris ceux qui n'existent pas.</li>
      </ul>
      <div class="boutons releve__appel">
        <?= bouton('Une réclamation sur le personnel', 'contact.php', ['variante' => 'secondaire']) ?>
      </div>
    </div>
    <div class="d-7 temoignages">
      <h3 class="temoignages__titre">Ils en parlent mieux que nous</h3>
      <?= bloc_temoignages(3) ?>
    </div>
  </div>
</section>
<?php page_fin(); ?>
