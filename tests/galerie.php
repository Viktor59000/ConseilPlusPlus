<?php
/**
 * tests/galerie.php — Galerie des composants du nouveau site « Le Guichet » (lot 1 de la refonte), pour la revue.
 * Aucune base de données. tests/ n'est pas déployé.
 *   C:\wamp64\bin\php\php8.3.14\php.exe -S 127.0.0.1:8150 -t .     puis http://127.0.0.1:8150/tests/galerie.php
 * Paramètres : ?theme=clair|sombre et ?regime=ordinaire|allege|prioritaire forcent le thème et le régime
 * (écrits sur <html> par le serveur : utile aux captures sans navigateur interactif).
 */
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/gabarit.php';

$forces = [];
if (in_array($_GET['theme'] ?? '', ['clair', 'sombre'], true)) {
    $forces['data-theme'] = $_GET['theme'];
}
if (in_array($_GET['regime'] ?? '', ['ordinaire', 'allege', 'prioritaire'], true)) {
    $forces['data-regime'] = $_GET['regime'];
}
$erreurs = ['courriel' => 'Écrivez une adresse électronique avec un @ (le cabinet n\'en fournit pas).'];

page_debut([
    'titre' => 'Galerie des composants',
    'description' => 'Les pièces du nouveau Guichet, réunies pour la revue : boutons, tampons, tickets, dossiers, attestations.',
    'page' => 'guichet',
    'racine' => '../',
    'canonical' => false,
    'robots' => 'noindex',
    'css' => ['tests/galerie.css'],
    'html_attributs' => $forces,
]);
?>
<section class="heros" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <p class="reference"><span>Formulaire CP+ 1</span><span>Galerie des composants</span><span>Lot 1</span></p>
      <h1 id="heros-titre">Votre problème mérite un dossier</h1>
      <p class="heros__chapeau">Déposez-le au guichet. Il sera reçu, tamponné, instruit, puis tranché par un cabinet qui ne se trompe jamais deux fois de la même façon.</p>
      <div class="boutons">
        <?= bouton('Tirer un ticket', '#papiers') ?>
        <?= bouton('Suivre mon dossier', '#dossiers', ['variante' => 'secondaire']) ?>
      </div>
      <dl class="heros__faits">
        <div><dt>Délai moyen</dt><dd>3 jours ouvrés</dd></div>
        <div><dt>Dossiers perdus</dt><dd>0 (retrouvés)</dd></div>
        <div><dt>Guichets ouverts</dt><dd>1 sur 4</dd></div>
      </dl>
    </div>
    <div class="heros__visuel">
      <?= scene('cabinet', 'Le bureau du cabinet : quatre-vingt-quatre tiroirs, une horloge sans chiffres, une lampe allumée sur une pile de dossiers en attente.', ['prioritaire' => true, 'tailles' => '(min-width: 1024px) 46vw, 100vw']) ?>
      <div class="heros__pose"><?= ticket(47, ['guichet' => '3 bis', 'date' => '20/11/2026 · 10:42', 'attente' => 'Attente estimée : moyenne']) ?></div>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="revue" aria-labelledby="revue-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <p class="reference"><span>Réglages de la revue</span></p>
      <h2 id="revue-titre">Deux thèmes, trois régimes</h2>
      <p class="chapeau muet">Chaque lien recharge la galerie avec le thème et le régime écrits par le serveur. Le bouton soleil et le CERFA 0-A de l'en-tête font la même chose pour de vrai.</p>
    </div>
    <div class="galerie-reglages">
<?php foreach (['clair' => 'Clair', 'sombre' => 'Sombre'] as $theme => $nom_theme): ?>
      <div class="galerie-reglages__ligne">
        <p class="galerie-reglages__nom"><?= e($nom_theme) ?></p>
<?php foreach (REGIMES as $regime => [$nom_regime]): ?>
        <?= lien_fleche($nom_regime, '?theme=' . $theme . '&regime=' . $regime . '#revue') ?>
<?php endforeach; ?>
      </div>
<?php endforeach; ?>
    </div>
    <div class="galerie-nuancier" aria-label="Nuancier">
<?php foreach (['fond' => 'Papier journal', 'papier' => 'Papier', 'kraft' => 'Kraft', 'laiton' => 'Laiton', 'encre' => 'Encre', 'rouge' => 'Rouge tampon', 'vert' => 'Vert administratif', 'encre-2' => 'Encre grise'] as $jeton => $nom): ?>
      <div class="galerie-nuance"><span class="galerie-nuance__pastille" style="background: var(--<?= e($jeton) ?>)"></span><span><?= e($nom) ?></span><span class="mono muet">--<?= e($jeton) ?></span></div>
<?php endforeach; ?>
    </div>
    <div class="galerie-echelle">
      <p class="galerie-echelle__h1">Titre 1 · 72</p>
      <p class="galerie-echelle__h2">Titre 2 · 48</p>
      <p class="galerie-echelle__h3">Titre 3 · 28</p>
      <p class="chapeau">Chapeau · 20. Le cabinet répond à toutes les questions, y compris celles qu'on ne lui a pas posées.</p>
      <p>Corps · 17. Toute demande déposée au guichet reçoit un numéro, un tampon et une réponse, dans cet ordre ou dans un autre.</p>
      <p class="mono">Machine · JetBrains Mono · N° 2026-0147</p>
    </div>
  </div>
</section>

<section class="ecran" id="actions" aria-labelledby="actions-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="actions-titre">Boutons, liens, alertes</h2>
      <p class="chapeau muet">Un seul bouton plein par vue : l'action du moment. Il monte d'un pixel au survol, s'enfonce au clic.</p>
    </div>
    <div class="grille">
      <div class="c-6 galerie-pile">
        <div class="boutons">
          <?= bouton('Déposer ma demande', '#') ?>
          <?= bouton('Consulter le règlement', '#', ['variante' => 'secondaire']) ?>
        </div>
        <div class="boutons">
          <?= bouton('Tamponner', null, ['type' => 'button', 'taille' => 'compacte']) ?>
          <?= bouton('Plus tard', null, ['type' => 'button', 'variante' => 'secondaire', 'taille' => 'compacte']) ?>
          <?= bouton('Indisponible', null, ['type' => 'button', 'variante' => 'secondaire', 'taille' => 'compacte', 'attributs' => ['disabled' => true]]) ?>
        </div>
        <p class="texte">Un lien dans le texte <a href="#actions">se souligne d'un trait d'encre</a> au survol. Un lien d'action : <?= lien_fleche('Voir le dossier', '#dossiers') ?></p>
        <p class="galerie-pictos"><?php foreach (array_keys(PICTOS) as $nom): ?><span><?= picto($nom) ?><?= machine($nom === 'telephone' ? 'téléphone' : $nom) ?></span><?php endforeach; ?></p>
        <p class="galerie-statuts"><?= statut('nouveau') ?> <?= statut('importe') ?> <?= statut('repondu') ?> <?= statut('refuse', ['libelle' => 'Classé sans suite']) ?></p>
      </div>
      <div class="c-6 galerie-pile">
        <?= bloc_alerte('Le cabinet a bien reçu votre demande. Il la lira avec la plus grande attention dès qu\'il aura fini son café.', 'succes', ['titre' => 'Demande reçue']) ?>
        <?= bloc_alerte('Le formulaire contient 1 erreur. Elle est signalée sous le champ concerné.', 'erreur', ['titre' => 'Dossier incomplet']) ?>
        <?= bloc_alerte('Le guichet 2 est fermé pour cause d\'ouverture du guichet 3.', 'attention', ['titre' => 'Fermeture exceptionnelle']) ?>
        <?= bloc_alerte('Les réponses sont rendues dans l\'ordre d\'arrivée des questions, sauf exception.', 'info') ?>
      </div>
    </div>
  </div>
</section>

<section class="ecran ecran--fond-2" id="papiers" aria-labelledby="papiers-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="papiers-titre">Tampons et tickets</h2>
      <p class="chapeau muet">Le moment signature : le tampon s'abat, l'encre bave un peu, la feuille encaisse. En régime allégé, il se pose en fondu.</p>
    </div>
    <div class="grille">
      <div class="c-7 galerie-pile">
        <div class="galerie-tampons" data-papier>
          <?= tampon('Reçu') ?>
          <?= tampon('Conforme', ['couleur' => 'vert', 'angle' => 4, 'mention' => '20 nov. 2026']) ?>
          <?= tampon('Vu', ['couleur' => 'encre', 'angle' => -2]) ?>
          <?= tampon('À peu près', ['couleur' => 'laiton', 'angle' => 7, 'taille' => 'petit']) ?>
          <?= tampon('Classé', ['taille' => 'geant', 'angle' => -9, 'id' => 'tampon-demo']) ?>
        </div>
        <div class="boutons">
          <?= bouton('Abattre le tampon', null, ['type' => 'button', 'attributs' => ['data-tamponner' => '#tampon-demo']]) ?>
        </div>
      </div>
      <div class="c-4 d-9 galerie-tickets">
        <?= ticket(48, ['guichet' => '1', 'libelle' => 'Votre numéro', 'date' => '20/11/2026 · 10:43', 'attente' => 'Personnes avant vous : 112',
            'tampon_html' => tampon('Appelé', ['angle' => -14, 'taille' => 'petit'])]) ?>
      </div>
    </div>
  </div>
</section>

<section class="ecran" id="dossiers" aria-labelledby="dossiers-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="dossiers-titre">Dossiers et suivi</h2>
      <p class="chapeau muet">Le circuit noté du cours, vu du visiteur : déposé (statut « nouveau »), en instruction (« importe »), répondu.</p>
    </div>
    <div class="galerie-pile galerie-pile--large">
      <?= etapes_statut('importe', ['dates' => ['nouveau' => '20/11 · 10:42', 'importe' => '20/11 · 14:05']]) ?>
      <div class="rangee">
        <?= dossier('2026-0146', 'nouveau', '<p>Mon voisin parle à ses tomates. Elles poussent mieux que les miennes.</p>', ['titre' => 'Tomates bavardes', 'meta' => ['Déposé le' => '20/11/2026']]) ?>
        <?= dossier('2026-0147', 'importe', '<p>Je range mes chaussettes par paires, mais il en reste toujours une.</p>', ['titre' => 'Chaussette orpheline', 'meta' => ['Agent' => 'Bertrand']]) ?>
        <?= dossier('2026-0148', 'repondu', '<p>Mon réveil sonne trop tôt, même quand je le règle plus tard.</p>', ['titre' => 'Réveil impatient', 'meta' => ['Répondu le' => '21/11/2026'],
            'tampon_html' => tampon('Répondu', ['couleur' => 'vert', 'angle' => -9, 'taille' => 'petit'])]) ?>
      </div>
      <?= etapes_statut('repondu', ['libelle' => 'Suivi du dossier 2026-0148', 'dates' => ['nouveau' => '20/11 · 10:44', 'importe' => '20/11 · 16:30', 'repondu' => '21/11 · 09:12']]) ?>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="documents" aria-labelledby="documents-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="documents-titre">Attestation et verdict</h2>
      <p class="chapeau muet">Le document que le visiteur emporte. Le second tampon tombe à la demande.</p>
    </div>
    <div class="grille galerie-documents">
      <div class="c-6">
        <?= attestation([
            'titre' => 'Attestation de dépôt', 'reference' => 'ATT-2026-0147', 'service' => 'Service des attestations',
            'corps' => 'Le cabinet certifie avoir reçu une demande à la date ci-dessous. Il ne certifie rien d\'autre, et surtout pas la demande.',
            'champs' => ['Déposant' => 'M. Dupont', 'Objet' => 'Chaussette orpheline', 'Guichet' => '3 bis', 'Ticket' => '047'],
            'signataire' => 'Bertrand', 'fonction' => 'agent d\'accueil', 'paraphe' => 'B. Lemoyen',
            'tampon' => ['texte' => 'Reçu', 'mention' => '20 nov. 2026'],
        ]) ?>
      </div>
      <div class="c-6">
        <?= attestation([
            'type' => 'verdict', 'titre' => 'Verdict', 'reference' => 'VER-2026-0147', 'service' => 'Le conseiller moyen',
            'corps' => 'Après examen attentif du dossier, le cabinet recommande de ranger la chaussette seule avec une autre chaussette seule. Elles ne vont pas ensemble, mais elles ne le savent pas.',
            'champs' => ['Dossier' => '2026-0147', 'Décision' => 'Favorable, à peu près'],
            'signataire' => 'Le conseiller moyen', 'fonction' => 'fondateur', 'paraphe' => 'C. M.',
            'tampon' => ['texte' => 'Validé', 'mouvement' => 'en-attente', 'id' => 'tampon-verdict', 'angle' => -7],
        ]) ?>
        <div class="boutons galerie-sous-document"><?= bouton('Tamponner le verdict', null, ['type' => 'button', 'attributs' => ['data-tamponner' => '#tampon-verdict']]) ?></div>
      </div>
    </div>
  </div>
</section>

<section class="ecran" id="cartes" aria-labelledby="cartes-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="cartes-titre">Classeur et gravures</h2>
      <p class="chapeau muet">Des fiches bord à bord, séparées par des filets. Une fiche avec un lien monte de 2 px ; sa gravure avance de 2 %.</p>
    </div>
    <div class="rangee rangee--classeur">
      <?= carte(['titre' => 'Le Guichet', 'etiquette' => 'Service 1', 'texte' => 'Quelques questions, un verdict tamponné, une attestation à emporter.', 'href' => '#cartes',
          'scene' => ['nom' => 'guichet', 'alt' => 'Le guichet du cabinet : une vitre, un hygiaphone, une sonnette que personne n\'ose actionner.']]) ?>
      <?= carte(['titre' => 'Le Petit Moyen', 'etiquette' => 'Service 2', 'texte' => 'Un mot par jour, défini avec précision, à peu près.', 'href' => '#cartes',
          'scene' => ['nom' => 'dossier', 'alt' => 'Une chemise cartonnée kraft, fermée, qui en sait plus qu\'elle ne le dit.']]) ?>
      <?= carte(['titre' => 'Réclamations', 'etiquette' => 'Service 3', 'texte' => 'Mireille vous répond. Ou son répondeur, qui a la même voix.', 'href' => '#cartes',
          'scene' => ['nom' => 'standard', 'alt' => 'Le standard de Mireille : un téléphone à cadran, un carnet, une tasse refroidie depuis 2009.']]) ?>
    </div>
    <div class="galerie-salle">
      <?= scene('salle-de-tri', 'La salle de tri du Bureau : des casiers jusqu\'au plafond, chacun étiqueté, aucun dans l\'ordre.', ['tailles' => '(min-width: 1360px) 1280px, 100vw']) ?>
    </div>
  </div>
</section>

<section class="ecran ecran--fond-2" id="formulaire" aria-labelledby="formulaire-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="formulaire-titre">Champs de formulaire</h2>
      <p class="chapeau muet">Libellé visible, aide sous le libellé, erreur sous le champ, vérifiée à l'envoi.</p>
    </div>
    <form class="grille galerie-formulaire" action="#formulaire" method="get" novalidate>
      <div class="c-6 galerie-pile">
        <?= champ('nom', 'Votre nom', $erreurs, ['autocomplete' => 'name', 'requis' => true, 'valeur' => 'M. Dupont']) ?>
        <?= champ('courriel', 'Adresse électronique', $erreurs, ['type' => 'email', 'autocomplete' => 'email', 'requis' => true, 'valeur' => 'dupont.exemple']) ?>
        <?= champ('objet', 'Nature du problème', $erreurs, ['type' => 'select', 'options' => ['' => 'Choisir…', 'menager' => 'Ménager', 'vegetal' => 'Végétal', 'existentiel' => 'Existentiel'], 'valeur' => 'vegetal']) ?>
      </div>
      <div class="c-6 galerie-pile">
        <?= champ('reference', 'Numéro de ticket', $erreurs, ['officiel' => true, 'inputmode' => 'numeric', 'aide' => 'Trois chiffres, imprimés en gros sur le ticket.', 'valeur' => '047']) ?>
        <?= champ('message', 'Votre problème, en une phrase', $erreurs, ['type' => 'textarea', 'rows' => 4, 'aide' => 'Le cabinet lit vite, mais une seule fois.']) ?>
        <?= champ('accord', 'J\'accepte que mon problème soit traité moyennement.', $erreurs, ['type' => 'case', 'valeur' => true]) ?>
        <div class="boutons"><?= bouton('Déposer ma demande', null, ['type' => 'submit']) ?></div>
      </div>
    </form>
  </div>
</section>

<section class="ecran" id="graphiques" aria-labelledby="graphiques-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="graphiques-titre">Graphiques maison</h2>
      <p class="chapeau muet">Dessinés en SVG par le serveur, avec leur tableau de repli pour les lecteurs d'écran. Partagés avec Le Bureau.</p>
    </div>
    <div class="grille">
      <div class="c-7">
        <?= graphique_barres(['lun.' => 4, 'mar.' => 12, 'mer.' => 7, 'jeu.' => 9, 'ven.' => 3, 'sam.' => 0, 'dim.' => 1],
            ['titre' => 'Débit du guichet', 'sous_titre' => '7 derniers jours', 'unite' => 'demandes', 'entete' => 'Jour', 'accent' => 1]) ?>
      </div>
      <div class="c-5">
        <?= graphique_anneau([
            ['libelle' => 'Déposés', 'valeur' => 6, 'serie' => 4],
            ['libelle' => 'En instruction', 'valeur' => 3, 'serie' => 1],
            ['libelle' => 'Répondus', 'valeur' => 11, 'serie' => 3],
        ], ['titre' => 'Dossiers par statut', 'total' => 'dossiers', 'unite' => 'dossiers', 'entete' => 'Statut']) ?>
      </div>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="manifeste" aria-labelledby="manifeste-titre">
  <div class="conteneur">
    <div class="ecran__tete">
      <h2 id="manifeste-titre" class="visuellement-cache">La phrase qui s'encre</h2>
      <p class="reference"><span>Article premier</span><span>Règlement intérieur</span></p>
    </div>
    <p class="phrase-encre" data-encre>Ici, chaque problème est reçu, numéroté, tamponné, et traité <em>avec le plus grand sérieux</em>, ce qui ne veut pas dire correctement.</p>
  </div>
</section>
<?php page_fin(); ?>
