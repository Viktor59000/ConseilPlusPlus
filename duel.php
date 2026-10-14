<?php
/**
 * duel.php — Mode Duel : partie en 100 points contre le cabinet.
 * Le joueur joue un coup « Je… et je… » ; le code le note selon un barème réel (forme, longueur,
 * mot imposé, absence de logique, verbe inédit, humeur de l'arbitre) ; l'arbitre IA annonce le verdict
 * et le cabinet joue son coup. Le premier à 100 points gagne. Partie gardée en session.
 * Réservé aux clients connectés. Hommage non officiel au Kamoulox de Kad Merad et Olivier Baroux.
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

require("db.php");
require("includes/duel.php");
require("includes/csrf.php");

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
        }
    }
}

$suggestion = au_hasard(duel_donnees()['suggestions']);
$titre = 'Duel';
$page = 'client';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Salle des duels</p>
  <h1>Le Duel</h1>
  <p class="chapo">Affrontez le cabinet en 100 points. Jouez des coups absurdes, respectez le barème, utilisez le mot imposé : le premier à 100 points l'emporte.</p>
  <div class="tableau-score" aria-label="Score">
    <div><span class="mono">Vous</span><strong><?= $partie['joueur'] ?></strong></div>
    <div><span class="mono">Le cabinet</span><strong><?= $partie['cabinet'] ?></strong></div>
    <div><span class="mono">Manches</span><strong><?= $partie['manches'] ?></strong></div>
  </div>
</section>

<?php if ($partie['gagnant'] !== null): ?>
<section class="conteneur">
  <div class="fin-partie <?= $partie['gagnant'] === 'joueur' ? 'fin-victoire' : 'fin-defaite' ?>" role="status">
    <span class="tampon" aria-hidden="true"><?= $partie['gagnant'] === 'joueur' ? 'Victoire' : 'Défaite' ?></span>
    <h2><?= $partie['gagnant'] === 'joueur'
        ? 'Victoire officielle. Le cabinet reconnaît sa défaite, sous réserve d\'appel.'
        : 'Le cabinet remporte la partie. Une réclamation peut être déposée, puis égarée.' ?></h2>
    <form action="duel.php" method="POST">
      <?= csrf_champ() ?>
      <button class="btn btn-tampon" type="submit" name="nouvelle_partie" value="1">Nouvelle partie</button>
    </form>
  </div>
</section>
<?php endif; ?>

<?php if ($manche): ?>
<section class="conteneur">
  <article class="fiche fiche-grande duel" aria-labelledby="manche-titre">
    <header class="fiche-entete">
      <h2 id="manche-titre">Manche n° <?= $partie['manches'] ?></h2>
      <span class="mono">Vous : <?= sprintf('%+d', $note['total']) ?><?= $points_cabinet ? ' · Cabinet : +' . $points_cabinet : '' ?></span>
    </header>
    <dl class="duel-deroule">
      <div>
        <dt class="mono">Votre coup</dt>
        <dd><?= htmlspecialchars($coup) ?></dd>
      </div>
      <div>
        <dt class="mono">Barème</dt>
        <dd>
          <table class="bareme">
<?php foreach ($note['lignes'] as [$libelle, $pts]): ?>
            <tr class="<?= $pts > 0 ? 'bonus' : ($pts < 0 ? 'malus' : '') ?>"><td><?= htmlspecialchars($libelle) ?></td><td class="mono"><?= sprintf('%+d', $pts) ?></td></tr>
<?php endforeach; ?>
            <tr class="total"><td>Total de la manche</td><td class="mono"><?= sprintf('%+d', $note['total']) ?></td></tr>
          </table>
        </dd>
      </div>
      <div>
        <dt class="mono">Verdict de l'arbitre</dt>
        <dd class="duel-verdict"><?= htmlspecialchars($manche['verdict']) ?></dd>
      </div>
<?php if ($points_cabinet): ?>
      <div>
        <dt class="mono">Coup du cabinet (+<?= $points_cabinet ?>)</dt>
        <dd class="fiche-conseil"><?= htmlspecialchars($manche['coup']) ?> <strong>Kamoulox !</strong></dd>
      </div>
<?php endif; ?>
    </dl>
    <span class="tampon tampon-anime" aria-hidden="true"><?= $note['total'] >= 25 ? 'Homologué' : ($note['total'] >= 0 ? 'Passable' : 'Pénalité') ?></span>
    <p class="fiche-pied mono"><?= $manche['source'] === 'ia' ? 'Arbitrage : intelligence artificielle moyenne' : 'Arbitre indisponible. Arbitrage : le stagiaire' ?></p>
  </article>
</section>
<?php endif; ?>

<section class="conteneur guichets">
<?php if ($partie['gagnant'] === null): ?>
  <form class="formulaire" action="duel.php" method="POST" aria-labelledby="titre-coup">
    <?= csrf_champ() ?>
    <h2 id="titre-coup"><?= $manche ? 'Manche suivante' : 'Votre coup' ?></h2>
    <p class="mot-impose">Mot imposé : <strong><?= htmlspecialchars($partie['mot']) ?></strong> <span class="mono">(+15)</span></p>
<?php if ($erreur): ?>
    <p class="alerte alerte-erreur" role="alert"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>
    <div class="champ">
      <label for="d-coup">Votre coup <span class="facultatif">(« Je… et je… »)</span></label>
      <textarea id="d-coup" name="coup" rows="3" maxlength="200" required placeholder="<?= htmlspecialchars($suggestion) ?>" aria-describedby="d-aide"><?= $manche ? '' : htmlspecialchars($coup) ?></textarea>
      <p class="aide" id="d-aide">Champ vide : appuyez sur <kbd>Tab</kbd> pour reprendre la suggestion, puis modifiez-la.</p>
    </div>
    <button class="btn btn-tampon" type="submit">Jouer le coup</button>
  </form>
<?php endif; ?>

  <aside class="formulaire formulaire-info" aria-label="Barème">
    <h2>Barème officiel</h2>
    <table class="bareme">
      <tr><td>Forme « Je… et je… »</td><td class="mono">+10 / −5</td></tr>
      <tr><td>Entre 8 et 18 mots</td><td class="mono">+5 / −5</td></tr>
      <tr><td>Mot imposé utilisé</td><td class="mono">+15</td></tr>
      <tr><td>Aucune logique (car, donc, pour…)</td><td class="mono">+5 / −10</td></tr>
      <tr><td>Verbe d'ouverture inédit</td><td class="mono">+5 / −5</td></tr>
      <tr><td>Élision oubliée (« Je agrafe »)</td><td class="mono">−5</td></tr>
      <tr><td>Humeur de l'arbitre</td><td class="mono">−5 à +10</td></tr>
    </table>
    <p class="mono">Le cabinet marque entre 10 et 35 points par manche. Premier à 100 points.</p>
    <form action="duel.php" method="POST">
      <?= csrf_champ() ?>
      <button class="lien-action" type="submit" name="nouvelle_partie" value="1">Recommencer la partie</button>
    </form>
  </aside>
</section>

<script src="js/duel.js" defer></script>

<?php include("includes/footer.php"); ?>
