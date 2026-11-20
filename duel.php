<?php
/**
 * duel.php — Guichet n° 4 : le Duel, partie en 100 points contre le cabinet.
 * Le joueur joue un coup « Je… et je… » ; le code le note selon un barème réel (forme, longueur, mot imposé, absence
 * de logique, verbe inédit, humeur de l'arbitre) ; l'arbitre IA annonce le verdict et le cabinet joue son coup.
 * Le premier à 100 points gagne. Partie gardée en session. Réservé aux clients connectés.
 * Se conclut par le verdict tamponné de la partie et le lien vers l'attestation (avis favorable, ou présence).
 * Hommage non officiel au Kamoulox de Kad Merad et Olivier Baroux. Styles : css/v2/pages/epreuves.css.
 */
require_once __DIR__ . '/includes/init.php';
$utilisateur = exiger_connexion();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/duel.php';
require_once __DIR__ . '/includes/gabarit.php';
require_once __DIR__ . '/includes/guichets.php';

$g = guichet('duel');
// Ancien format de session (avant les parties en 100 points) : on repart de zéro
if (!isset($_SESSION['duel']['mot'])) {
    $_SESSION['duel'] = nouvelle_partie();
}
$partie = &$_SESSION['duel'];
$coup = '';
$erreur = null;
$manche = null;
$note = null;
$points_cabinet = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valide()) {
        $erreur = 'Le formulaire a expiré. Merci de rejouer votre coup.';
    } elseif (isset($_POST['nouvelle_partie'])) {
        $_SESSION['duel'] = nouvelle_partie();
        header('location: duel.php');
        exit();
    } elseif ($partie['gagnant'] === null) {
        $coup = nettoyer_coup((string) ($_POST['coup'] ?? ''));
        if (mb_strlen($coup) < 8) {
            $erreur = 'Votre coup est trop court. Exemple : « Je ponce une endive et je vouvoie le Luxembourg. »';
        } else {
            $mot = $partie['mot'];
            $note = noter_coup($coup, $mot, $partie['verbes']);
            $resume = implode(' ; ', array_map(fn($l) => sprintf('%s %+d', $l[0], $l[1]), $note['lignes']));
            $manche = jouer_manche($coup, $note['total'], $resume);

            $partie['manches']++;
            $partie['joueur'] += $note['total'];
            $partie['verbes'][] = $note['verbe'];
            if ($partie['joueur'] >= SCORE_VICTOIRE) {
                $partie['gagnant'] = 'joueur';
            } else {
                $points_cabinet = random_int(10, 35);
                $partie['cabinet'] += $points_cabinet;
                if ($partie['cabinet'] >= SCORE_VICTOIRE) {
                    $partie['gagnant'] = 'cabinet';
                }
            }
            $partie['mot'] = au_hasard(array_diff(duel_donnees()['mots_imposes'], [$mot]));
            if ($partie['gagnant'] !== null) {
                // Partie terminée : résultat gardé pour le tableau d'honneur (prénom seulement)
                $bdd->prepare('INSERT INTO duels (id_utilisateur, prenom, joueur, cabinet, manches, victoire) VALUES (?, ?, ?, ?, ?, ?)')
                    ->execute([$utilisateur, $_SESSION['prenom'], $partie['joueur'], $partie['cabinet'], $partie['manches'],
                        (int) ($partie['gagnant'] === 'joueur')]);
            }
        }
    }
}

$suggestion = au_hasard(duel_donnees()['suggestions']);

// Tableau d'honneur : les victoires les plus rapides, et le bilan du cabinet
$honneur = $bdd->query('SELECT prenom, joueur, manches, date_partie FROM duels WHERE victoire = 1
                        ORDER BY manches, joueur DESC, date_partie LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
$bilan = $bdd->query('SELECT COUNT(*) AS parties, COALESCE(SUM(victoire = 0), 0) AS cabinet FROM duels')->fetch(PDO::FETCH_ASSOC);
$victoire = $partie['gagnant'] === 'joueur';

page_debut([
    'titre' => 'Le Duel',
    'description' => 'Guichet n° 4 : affrontez le cabinet ConseilPlus+ en 100 points, à coups de phrases absurdes. Règlement disponible sur demande, puis égaré.',
    'page' => 'guichet',
    'robots' => 'noindex',
    'css' => ['css/v2/pages/epreuves.css'],
    'scripts' => ['js/duel.js'],
]);
echo bandeau_guichets('duel');
?>
<section class="heros epreuve" data-enter aria-labelledby="heros-titre">
  <div class="conteneur grille">
    <div class="heros__texte">
      <h1 id="heros-titre">Le Duel</h1>
      <p class="heros__chapeau">Affrontez le cabinet. Jouez un coup absurde : l'arbitre rend son verdict selon un règlement que personne n'a lu, pas même lui.</p>
      <dl class="score" aria-label="Score">
        <div><dt>Vous</dt><dd><?= (int) $partie['joueur'] ?></dd></div>
        <div><dt>Le cabinet</dt><dd><?= (int) $partie['cabinet'] ?></dd></div>
        <div><dt>Manches</dt><dd><?= (int) $partie['manches'] ?></dd></div>
      </dl>
      <div class="epreuve__plaque"><?= plaque_guichet($g['numero'], 'Duel') ?></div>
    </div>
    <div class="epreuve__poste">
<?php if ($partie['gagnant'] !== null): ?>
      <div class="epreuve__verdict" role="status">
        <?= attestation([
            'type' => 'verdict', 'titre' => $victoire ? 'Victoire officielle' : 'Le cabinet l\'emporte', 'niveau' => 2,
            'reference' => 'DUEL-' . date('Y') . '-' . sprintf('%03d', $partie['manches']), 'service' => 'Le Duel, guichet n° 4',
            'corps' => $victoire ? 'Le cabinet reconnaît sa défaite, sous réserve d\'appel, et d\'un second appel s\'il perd le premier.'
                : 'Le cabinet remporte la partie. Une réclamation peut être déposée, puis égarée selon la procédure habituelle.',
            'champs' => ['Vous' => $partie['joueur'] . ' points', 'Le cabinet' => $partie['cabinet'] . ' points', 'Manches' => $partie['manches']],
            'signataire' => 'L\'arbitre', 'fonction' => 'impartial, à peu près', 'paraphe' => 'L\'arb.',
            'tampon' => ['texte' => $victoire ? 'Victoire' : 'Défaite', 'couleur' => $victoire ? 'vert' : 'rouge', 'mouvement' => 'au-chargement', 'angle' => -8],
            'apres_html' => '<div class="boutons verdict__actions">'
                . bouton($victoire ? 'Obtenir mon avis favorable' : 'Obtenir mon attestation de présence', 'attestation.php?document=' . ($victoire ? $g['document'] : 'presence'))
                . '<form action="duel.php" method="POST">' . csrf_champ() . bouton('Nouvelle partie', null, ['variante' => 'secondaire', 'nom' => 'nouvelle_partie', 'valeur' => '1']) . '</form>'
                . '</div>',
        ]) ?>
      </div>
<?php endif; ?>
<?php if ($manche): ?>
      <article class="feuille" data-papier aria-labelledby="manche-titre">
        <div class="feuille__entete">
          <h2 id="manche-titre">Manche n° <?= (int) $partie['manches'] ?></h2>
          <p class="mono">Vous : <?= sprintf('%+d', $note['total']) ?><?= $points_cabinet ? ' · Cabinet : +' . $points_cabinet : '' ?> · <?= $manche['source'] === 'ia' ? 'Arbitrage : intelligence artificielle moyenne' : 'Arbitre indisponible. Arbitrage : le stagiaire' ?></p>
        </div>
        <dl class="duel-deroule">
          <div><dt>Votre coup</dt><dd><?= e($coup) ?></dd></div>
          <div><dt>Verdict de l'arbitre</dt><dd><strong><?= e($manche['verdict']) ?></strong></dd></div>
<?php if ($points_cabinet): ?>
          <div><dt>Coup du cabinet (+<?= $points_cabinet ?>)</dt><dd><?= e($manche['coup']) ?> <strong>Kamoulox !</strong></dd></div>
<?php endif; ?>
        </dl>
        <div class="duel-tampon"><?= tampon($note['total'] >= 25 ? 'Homologué' : ($note['total'] >= 0 ? 'Passable' : 'Pénalité'), ['mouvement' => 'au-chargement', 'couleur' => $note['total'] >= 25 ? 'vert' : 'rouge', 'angle' => -6, 'decoratif' => true]) ?></div>
      </article>
<?php endif; ?>
<?php if ($partie['gagnant'] === null): ?>
      <form class="feuille" action="duel.php" method="POST" aria-labelledby="coup-titre">
        <?= csrf_champ() ?>
        <div class="feuille__entete">
          <h2 id="coup-titre"><?= $manche ? 'Manche suivante' : 'Votre coup' ?></h2>
          <p class="duel-mot"><span class="mono">Mot imposé</span><strong><?= e($partie['mot']) ?></strong></p>
        </div>
<?php if ($erreur): ?>
        <?= bloc_alerte($erreur, 'erreur', ['titre' => 'Coup refusé']) ?>
<?php endif; ?>
        <?= champ('coup', 'Votre coup (« Je… et je… »)', $erreur ? ['coup' => $erreur] : [], ['type' => 'textarea', 'rows' => 3, 'maxlength' => 200, 'requis' => true, 'id' => 'd-coup',
            'placeholder' => $suggestion, 'valeur' => $manche ? '' : $coup, 'aide' => 'Champ vide : la touche Tab reprend la suggestion, que vous modifiez ensuite.']) ?>
        <div class="boutons"><?= bouton('Jouer le coup', null, ['type' => 'submit']) ?></div>
      </form>
<?php endif; ?>
    </div>
  </div>
</section>

<section class="ecran ecran--papier" id="reglement" aria-labelledby="reglement-titre">
  <div class="conteneur grille epreuve-annexe">
    <div class="c-5">
      <div class="ecran__tete">
        <h2 id="reglement-titre">Le règlement du Duel</h2>
      </div>
      <ol class="liste-reglement">
        <li>Le coup se joue à la première personne.</li>
        <li>Les points sont attribués selon un barème confidentiel, y compris pour l'arbitre.</li>
        <li>Le mot imposé est imposé.</li>
        <li>Toute réclamation est examinée, puis égarée.</li>
      </ol>
<?php if ($partie['gagnant'] === null): ?>
      <form action="duel.php" method="POST">
        <?= csrf_champ() ?>
        <button class="lien-bouton" type="submit" name="nouvelle_partie" value="1">Recommencer la partie</button>
      </form>
<?php endif; ?>
    </div>
    <div class="d-7">
      <h3 class="ecran__sous-titre">Les victoires les plus rapides</h3>
<?php if (!$honneur): ?>
      <p class="muet">Personne n'a encore battu le cabinet. Le cabinet n'en tire aucune fierté particulière, mais il le note.</p>
<?php else: ?>
      <ol class="honneur">
<?php foreach ($honneur as $i => $h): ?>
        <li>
          <span class="honneur__rang" aria-hidden="true"><?= $i + 1 ?></span>
          <strong><?= e($h['prenom']) ?></strong>
          <span><?= (int) $h['manches'] ?> manche<?= $h['manches'] > 1 ? 's' : '' ?> · <?= (int) $h['joueur'] ?> points</span>
          <span class="mono"><?= (new DateTime($h['date_partie']))->format('d/m/Y') ?></span>
        </li>
<?php endforeach; ?>
      </ol>
<?php endif; ?>
<?php if ($bilan['parties']): ?>
      <p class="muet epreuve__note">Bilan officiel : <?= (int) $bilan['parties'] ?> partie<?= $bilan['parties'] > 1 ? 's' : '' ?> terminée<?= $bilan['parties'] > 1 ? 's' : '' ?>, dont <?= (int) $bilan['cabinet'] ?> gagnée<?= $bilan['cabinet'] > 1 ? 's' : '' ?> par le cabinet, qui estime ce chiffre « moyennement représentatif ».</p>
<?php endif; ?>
    </div>
  </div>
</section>
<?php page_fin(); ?>
