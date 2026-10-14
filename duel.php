<?php
/**
 * duel.php — Mode Duel : le joueur joue un coup absurde, l'arbitre (IA) rend son verdict
 * avec des points fantômes et joue le sien. Score officieux gardé en session.
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

$_SESSION['duel'] ??= ['score' => 0, 'manches' => 0];
$coup = '';
$erreur = null;
$manche = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valide()) {
        $erreur = 'Le formulaire a expiré. Merci de rejouer votre coup.';
    } elseif (isset($_POST['reinitialiser'])) {
        $_SESSION['duel'] = ['score' => 0, 'manches' => 0];
        header('location: duel.php');
        exit();
    } else {
        $coup = nettoyer_coup((string) ($_POST['coup'] ?? ''));
        if (mb_strlen($coup) < 8) {
            $erreur = 'Votre coup est trop court. Exemple : « Je ponce une endive et je vouvoie le Luxembourg. »';
        } else {
            $manche = jouer_manche($coup);
            $_SESSION['duel']['score'] += $manche['points'];
            $_SESSION['duel']['manches']++;
        }
    }
}

$score = $_SESSION['duel'];
$titre = 'Duel';
$page = 'client';
include("includes/header.php");
?>

<section class="conteneur entete-page">
  <p class="etiquette">Salle des duels</p>
  <h1>Le Duel</h1>
  <p class="chapo">Jouez un coup absurde. L'arbitre du cabinet rend son verdict, attribue des points selon un règlement que personne n'a lu, puis joue le sien.</p>
  <p class="score mono">Score officieux : <strong><?= $score['score'] ?> point<?= abs($score['score']) > 1 ? 's' : '' ?></strong> · <?= $score['manches'] ?> manche<?= $score['manches'] > 1 ? 's' : '' ?></p>
</section>

<?php if ($manche): ?>
<section class="conteneur">
  <article class="fiche fiche-grande duel" aria-labelledby="manche-titre">
    <header class="fiche-entete">
      <h2 id="manche-titre">Manche n° <?= $score['manches'] ?></h2>
      <span class="mono"><?= $manche['points'] > 0 ? '+' : '' ?><?= $manche['points'] ?> pts</span>
    </header>
    <dl class="duel-deroule">
      <div>
        <dt class="mono">Votre coup</dt>
        <dd><?= htmlspecialchars($coup) ?></dd>
      </div>
      <div>
        <dt class="mono">Verdict de l'arbitre</dt>
        <dd class="duel-verdict"><?= htmlspecialchars($manche['verdict']) ?></dd>
      </div>
      <div>
        <dt class="mono">Coup du cabinet</dt>
        <dd class="fiche-conseil"><?= htmlspecialchars($manche['coup']) ?> <strong>Kamoulox !</strong></dd>
      </div>
    </dl>
    <span class="tampon tampon-anime" aria-hidden="true"><?= $manche['points'] >= 0 ? 'Homologué' : 'Pénalité' ?></span>
    <p class="fiche-pied mono"><?= $manche['source'] === 'ia' ? 'Arbitrage : intelligence artificielle moyenne' : 'Arbitre indisponible. Arbitrage : le stagiaire' ?></p>
  </article>
</section>
<?php endif; ?>

<section class="conteneur guichets">
  <form class="formulaire" action="duel.php" method="POST" aria-labelledby="titre-coup">
    <?= csrf_champ() ?>
    <h2 id="titre-coup"><?= $manche ? 'Rejouer' : 'Votre coup' ?></h2>
<?php if ($erreur): ?>
    <p class="alerte alerte-erreur" role="alert"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>
    <div class="champ">
      <label for="d-coup">Votre coup <span class="facultatif">(« Je… et je… »)</span></label>
      <textarea id="d-coup" name="coup" rows="3" maxlength="200" required placeholder="Je ponce une endive et je vouvoie le Luxembourg."><?= $manche ? '' : htmlspecialchars($coup) ?></textarea>
    </div>
    <button class="btn btn-tampon" type="submit">Jouer le coup</button>
  </form>

  <aside class="formulaire formulaire-info" aria-label="Règlement">
    <h2>Règlement (extrait)</h2>
    <ol class="liste-info">
      <li>Le coup se joue à la première personne : « Je [fais ceci] et je [fais cela] ».</li>
      <li>Les deux actions ne doivent avoir strictement aucun rapport.</li>
      <li>Les points sont attribués selon un barème confidentiel, y compris pour l'arbitre.</li>
      <li>Toute réclamation est examinée, puis égarée.</li>
    </ol>
    <form action="duel.php" method="POST">
      <?= csrf_champ() ?>
      <button class="lien-action" type="submit" name="reinitialiser" value="1">Remettre le score à zéro</button>
    </form>
  </aside>
</section>

<?php include("includes/footer.php"); ?>
