<?php
/**
 * produit.php — Nos formules (page exigée par le cours) : les tarifs du guichet. Conseil Unique, Conseil Illimité,
 * Conseil Premium ; chaque formule mène à la prise de rendez-vous au guichet (guichet.php).
 * Premium : sans JavaScript, simple lien vers le guichet ; avec, une fenêtre de « paiement » refusé (js/v2/formules.js).
 */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';

$rendez_vous = 'guichet.php';
$tarifs = [
    ['code' => 'CU-0', 'nom' => 'Conseil Unique', 'prix' => '0 €', 'periode' => 'par visite, une fois',
        'lignes' => ['1 conseil par visite', 'Remis au guichet, en main propre', 'Garantie : aucune']],
    ['code' => 'CI-0', 'nom' => 'Conseil Illimité', 'prix' => '0 €', 'periode' => 'par mois', 'phare' => true,
        'lignes' => ['Conseils à volonté', 'Numéro de dossier personnalisé', 'Tampon officiel « Validé »', 'Suivi dans Mon dossier', 'Garantie : toujours aucune']],
    ['code' => 'CP-49', 'nom' => 'Conseil Premium', 'prix' => '49,99 €', 'periode' => 'par mois',
        'lignes' => ['Exactement la formule Illimitée', 'Mais nous y pensons très fort', 'Garantie : on a dit aucune']],
];

page_debut([
    'titre' => 'Nos formules',
    'description' => 'Les tarifs du guichet de ConseilPlus+ : Conseil Unique, Conseil Illimité ou Conseil Premium, toutes garanties sans aucune garantie.',
    'css' => ['css/v2/pages/formules.css'],
    'scripts' => ['js/v2/formules.js'],
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <p class="reference"><span>Tarifs du guichet</span><span>Affichage obligatoire</span></p>
      <h1 id="heros-titre">Nos formules</h1>
      <p class="heros__chapeau">Trois formules, un seul niveau de qualité : <span class="surligne">moyen</span>. Toutes se règlent au guichet, sur rendez-vous, et aucune ne se règle vraiment.</p>
      <div class="boutons">
        <?= bouton('Prendre rendez-vous', $rendez_vous) ?>
        <?= bouton('Voir les tarifs', '#tarifs', ['variante' => 'secondaire']) ?>
      </div>
      <dl class="heros__faits">
        <div><dt>Formules</dt><dd>3</dd></div>
        <div><dt>Niveaux de qualité</dt><dd>1</dd></div>
        <div><dt>Remboursement</dt><dd>intégral (0 €)</dd></div>
      </dl>
    </div>
    <div class="heros__visuel">
      <?= scene('guichet', 'Le guichet du cabinet : une vitre, un hygiaphone, une sonnette que personne n\'ose actionner, et la grille des tarifs scotchée de travers.', ['prioritaire' => true, 'tailles' => '(min-width: 1024px) 46vw, 100vw']) ?>
      <div class="heros__pose"><?= ticket('0 €', ['guichet' => '1', 'libelle' => 'Prix de la formule la plus choisie', 'attente' => 'Valable jusqu\'à nouvel ordre']) ?></div>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="tarifs" aria-labelledby="tarifs-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <p class="reference"><span>Grille tarifaire</span><span>En vigueur depuis un mardi</span></p>
      <h2 id="tarifs-titre">Les tarifs du guichet</h2>
      <p class="chapeau muet">Choisissez une formule, puis prenez rendez-vous au guichet : un agent l'enregistrera, la tamponnera et vous la rendra telle quelle.</p>
    </div>
    <div class="tarifs">
<?php foreach ($tarifs as $t): ?>
      <article class="tarif<?= !empty($t['phare']) ? ' tarif--phare' : '' ?>" aria-labelledby="tarif-<?= e($t['code']) ?>">
        <p class="carte__etiquette">Formule <?= e($t['code']) ?></p>
        <h3 class="tarif__nom" id="tarif-<?= e($t['code']) ?>"><?= e($t['nom']) ?></h3>
        <p class="tarif__prix"><span class="tarif__montant"><?= e($t['prix']) ?></span> <span class="tarif__periode"><?= e($t['periode']) ?></span></p>
        <ul class="tarif__lignes">
<?php foreach ($t['lignes'] as $ligne): ?>
          <li><?= e($ligne) ?></li>
<?php endforeach; ?>
        </ul>
<?php if (!empty($t['phare'])): ?>
        <?= tampon('Le plus choisi par erreur', ['taille' => 'petit', 'angle' => 6, 'decoratif' => true, 'classe' => 'tarif__tampon']) ?>
        <div class="tarif__action"><?= bouton('Prendre rendez-vous au guichet', $rendez_vous, ['large' => true]) ?></div>
<?php elseif ($t['code'] === 'CP-49'): ?>
        <div class="tarif__action"><?= bouton('Souscrire à Premium', $rendez_vous, ['variante' => 'secondaire', 'large' => true, 'attributs' => ['data-ouvre-dialogue' => 'dialogue-premium']]) ?></div>
<?php else: ?>
        <div class="tarif__action"><?= bouton('Prendre rendez-vous', $rendez_vous, ['variante' => 'secondaire', 'large' => true]) ?></div>
<?php endif; ?>
      </article>
<?php endforeach; ?>
    </div>

    <dialog class="dialogue" id="dialogue-premium" aria-labelledby="titre-premium">
      <?= tampon('Refusé', ['taille' => 'grand', 'angle' => -8, 'mouvement' => 'en-attente', 'id' => 'tampon-refuse', 'decoratif' => true, 'classe' => 'dialogue__tampon']) ?>
      <p class="machine">Avis de paiement n° CP-49</p>
      <h2 id="titre-premium">Paiement refusé</h2>
      <p>Votre banque n'y est pour rien : c'est nous. Après examen de votre dossier, le cabinet estime que vous méritez mieux que la formule Premium.</p>
      <p>Bonne nouvelle : la formule Illimitée est exactement la même, en gratuit.</p>
      <div class="boutons">
        <?= bouton('Prendre l\'Illimitée', $rendez_vous) ?>
        <form method="dialog"><?= bouton('Fermer', null, ['variante' => 'secondaire']) ?></form>
      </div>
    </dialog>
  </div>
</section>

<section class="ecran" id="comparatif" aria-labelledby="comparatif-titre">
  <div class="conteneur grille">
    <div class="c-5">
      <div class="ecran__tete">
        <p class="reference"><span>Tableau comparatif</span></p>
        <h2 id="comparatif-titre">Comparatif</h2>
        <p class="chapeau muet">Le cabinet a comparé ses trois formules avec la plus grande rigueur. Elles sont toutes excellentes, à peu près.</p>
      </div>
    </div>
    <div class="d-7 tableau-defile">
      <table class="comparatif">
        <caption class="visuellement-cache">Prestations comprises dans chaque formule</caption>
        <thead>
          <tr><th scope="col">Prestation</th><th scope="col">Unique</th><th scope="col">Illimité</th><th scope="col">Premium</th></tr>
        </thead>
        <tbody>
          <tr><th scope="row">Conseils absurdes</th><td>1</td><td>Illimité</td><td>Illimité</td></tr>
          <tr><th scope="row">Rendez-vous au guichet</th><td>Oui</td><td>Oui</td><td>Oui, en pensée</td></tr>
          <tr><th scope="row">Compte client (Mon dossier)</th><td>Non</td><td>Oui</td><td>Oui</td></tr>
          <tr><th scope="row">Pensées positives</th><td>Non</td><td>Non</td><td>Oui, très fort</td></tr>
          <tr><th scope="row">Résultats</th><td>Aucun</td><td>Aucun</td><td>Aucun, mais premium</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section class="ecran ecran--fond-2" id="questions" aria-labelledby="questions-titre">
  <div class="conteneur grille">
    <div class="c-5">
      <div class="ecran__tete">
        <p class="reference"><span>Avis et questions</span></p>
        <h2 id="questions-titre">Questions fréquentes</h2>
      </div>
      <div class="avis"><?= bloc_temoignages(2) ?></div>
    </div>
    <div class="d-7 faq">
<?php foreach (textes()['faq'] as [$question, $reponse]): ?>
      <details class="faq__item">
        <summary><?= e($question) ?></summary>
        <p><?= e($reponse) ?></p>
      </details>
<?php endforeach; ?>
    </div>
  </div>
</section>
<?php page_fin(); ?>
