<?php
/**
 * duel.php — Mode Duel : partie en 100 points contre le cabinet.
 * Le joueur joue un coup « Je… et je… » ; le code le note selon un barème réel (forme, longueur,
 * mot imposé, absence de logique, verbe inédit, humeur de l'arbitre) ; l'arbitre IA annonce le verdict
 * et le cabinet joue son coup. Le premier à 100 points gagne. Partie gardée en session.
 * Réservé aux clients connectés. Hommage non officiel au Kamoulox de Kad Merad et Olivier Baroux.
 */
require_once __DIR__ . '/includes/init.php';
$utilisateur = exiger_connexion();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/duel.php';
require_once __DIR__ . '/includes/formulaire.php';

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
$titre = 'Le Duel';
$description = 'Affrontez le cabinet ConseilPlus+ en 100 points, à coups de phrases absurdes. Règlement disponible sur demande, puis égaré.';
$page = 'espace';
$onglet = 'duel';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/espace-nav.php';
?>

<section class="conteneur entete-page">
  <h1>Le Duel</h1>
  <p class="chapo">Affrontez le cabinet en 100 points. Jouez un coup absurde : l'arbitre rend son verdict selon un règlement que personne n'a lu.</p>
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
    <p class="mot-impose">Mot imposé : <strong><?= htmlspecialchars($partie['mot']) ?></strong></p>
<?php if ($erreur): ?>
    <?= alerte($erreur) ?>
<?php endif; ?>
    <div class="champ">
      <label for="d-coup">Votre coup <span class="facultatif">(« Je… et je… »)</span></label>
      <textarea id="d-coup" name="coup" rows="3" maxlength="200" required placeholder="<?= htmlspecialchars($suggestion) ?>" aria-describedby="d-aide"><?= $manche ? '' : htmlspecialchars($coup) ?></textarea>
      <p class="aide" id="d-aide">Champ vide : appuyez sur <kbd>Tab</kbd> pour reprendre la suggestion, puis modifiez-la.</p>
    </div>
    <button class="btn btn-tampon" type="submit">Jouer le coup</button>
  </form>
<?php endif; ?>

  <aside class="formulaire formulaire-info" aria-label="Règlement">
    <h2>Règlement (extrait)</h2>
    <ol class="liste-info">
      <li>Le coup se joue à la première personne.</li>
      <li>Les points sont attribués selon un barème confidentiel, y compris pour l'arbitre.</li>
      <li>Le mot imposé est imposé.</li>
      <li>Toute réclamation est examinée, puis égarée.</li>
    </ol>
    <form action="duel.php" method="POST">
      <?= csrf_champ() ?>
      <button class="lien-action" type="submit" name="nouvelle_partie" value="1">Recommencer la partie</button>
    </form>
  </aside>
</section>

<section class="conteneur" aria-labelledby="titre-honneur">
  <h2 id="titre-honneur">Les victoires les plus rapides</h2>
<?php if (!$honneur): ?>
  <p class="aide">Personne n'a encore battu le cabinet. Le cabinet n'en tire aucune fierté particulière, mais il le note.</p>
<?php else: ?>
  <ol class="honneur">
<?php foreach ($honneur as $i => $h): ?>
    <li>
      <span class="honneur-rang<?= $i === 0 ? ' entoure' : '' ?>" aria-hidden="true"><?= $i + 1 ?><?= $i === 0 ? boucle_stylo() : '' ?></span>
      <strong><?= htmlspecialchars($h['prenom']) ?></strong>
      <span><?= (int) $h['manches'] ?> manche<?= $h['manches'] > 1 ? 's' : '' ?> · <?= (int) $h['joueur'] ?> points</span>
      <span class="mono"><?= (new DateTime($h['date_partie']))->format('d/m/Y') ?></span>
    </li>
<?php endforeach; ?>
  </ol>
<?php endif; ?>
<?php if ($bilan['parties']): ?>
  <p class="aide">Bilan officiel : <?= (int) $bilan['parties'] ?> partie<?= $bilan['parties'] > 1 ? 's' : '' ?> terminée<?= $bilan['parties'] > 1 ? 's' : '' ?>, dont <?= (int) $bilan['cabinet'] ?> gagnée<?= $bilan['cabinet'] > 1 ? 's' : '' ?> par le cabinet, qui estime ce chiffre « moyennement représentatif ».</p>
<?php endif; ?>
</section>

<script src="<?= fichier_versionne('js/duel.js') ?>" defer></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
