<?php
/**
 * En-tête des pages du Bureau : application à barre latérale fine (« La Salle de tri »).
 * Attend $titre ; si un agent est connecté : $agent, $bdd et $page (clé de ONGLETS_BUREAU ou RESTE_BUREAU).
 * Facultatif : $scripts (scripts de bureau/js/ en plus de bureau.js), $classe_corps.
 * Sans agent (connexion), la page n'a pas de barre latérale : elle remplit seule le <main>.
 * Thème : « archives » sombre par défaut ; le choix clair est gardé dans le cookie bureau_theme (bureau/js/bureau.js),
 * lu ici pour poser data-theme avant tout affichage (le script de thème du site le respecte).
 * Application séparée possible (docker/bureau) : les ressources du site sont lues sous ../js et ../assets (alias /js et
 * /assets en ligne), le CSS est intégré depuis les fichiers du disque (css_v2(), css_integre()).
 */
$agent ??= null;
$page ??= '';
$scripts ??= [];
require_once __DIR__ . '/vues.php';
ui_racine('../');
$theme = ($_COOKIE['bureau_theme'] ?? '') === 'clair' ? 'clair' : 'sombre';
$css = css_v2(['css/v2/jetons.css', 'css/v2/socle.css', 'css/v2/composants.css', 'css/v2/bureau.css'], '../')
    . '@layer page{' . str_replace('../../assets/', '../assets/', css_integre(__DIR__ . '/../css/bureau.css')) . '}';
$compteurs = $agent && isset($bdd) ? compteurs_laterale($bdd) : [];
envoyer_csp();
?>
<!DOCTYPE html>
<html lang="fr" data-espace="bureau" data-theme="<?= $theme ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titre) ?> — Le Bureau</title>
<link rel="icon" href="../assets/icons/favicon.svg" type="image/svg+xml">
<meta name="theme-color" content="<?= $theme === 'clair' ? '#E8E8E3' : '#0E0E0C' ?>" data-clair="#E8E8E3" data-sombre="#0E0E0C">
<?= theme_script() ?>

<link rel="preload" href="../assets/fonts/schibsted-grotesk.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="../assets/fonts/jetbrains-mono-500.woff2" as="font" type="font/woff2" crossorigin>
<style><?= $css ?></style>
</head>
<body class="<?= e(trim('bureau ' . ($classe_corps ?? ''))) ?>">
<a class="evitement" href="#contenu">Aller au contenu</a>
<?= filtre_encre() ?>
<?php if ($agent): ?>
<div class="appli" data-appli>
  <header class="barre-haut">
    <a class="barre-haut__marque" href="tableau.php"><?= logo_bureau('logo-haut') ?><span>Le Bureau</span></a>
    <button class="barre-haut__menu" type="button" aria-expanded="false" aria-controls="laterale" data-menu-bureau hidden>
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg><span>Menu</span>
    </button>
  </header>
  <nav class="laterale" id="laterale" aria-label="Le Bureau">
    <a class="laterale__marque" href="tableau.php"><?= logo_bureau('logo-laterale') ?>
      <span class="laterale__nom"><strong>Le Bureau</strong><span>La Salle de tri</span></span></a>
    <p class="laterale__groupe" id="nav-onglets">Onglets</p>
    <ul aria-labelledby="nav-onglets">
<?php foreach (ONGLETS_BUREAU as $cle => [$adresse, $libelle, $picto]): ?>
      <li><a class="nav-b" href="<?= $adresse ?>"<?= $page === $cle ? ' aria-current="page"' : '' ?>><?= picto_bureau($picto) ?><span><?= e($libelle) ?></span><?php if (!empty($compteurs[$cle])): ?><span class="nav-b__compte" title="Rendez-vous à venir à traiter"><?= $compteurs[$cle] ?><span class="visuellement-cache"> à traiter</span></span><?php endif; ?></a></li>
<?php endforeach; ?>
    </ul>
    <p class="laterale__groupe" id="nav-reste">Le reste du Bureau</p>
    <ul aria-labelledby="nav-reste">
<?php foreach (RESTE_BUREAU as $cle => [$adresse, $libelle, $picto]): ?>
      <li><a class="nav-b" href="<?= $adresse ?>"<?= $page === $cle ? ' aria-current="page"' : '' ?>><?= picto_bureau($picto) ?><span><?= e($libelle) ?></span><?php if (!empty($compteurs[$cle])): ?><span class="nav-b__compte" title="Dossiers à traiter"><?= $compteurs[$cle] ?><span class="visuellement-cache"> à traiter</span></span><?php endif; ?></a></li>
<?php endforeach; ?>
    </ul>
    <div class="laterale__pied">
      <a class="nav-b" href="<?= e(url_publique()) ?>/"><?= picto_bureau('site') ?><span>Voir le site</span></a>
      <div class="laterale__agent">
        <span class="avatar" aria-hidden="true"><?= e(initiales($agent['nom'])) ?></span>
        <span class="laterale__qui"><strong><?= e($agent['nom']) ?></strong><span>Agent connecté</span></span>
        <?= theme_bouton() ?>
      </div>
      <form method="post" action="deconnexion.php">
        <?= csrf_champ() ?>
        <button class="nav-b" type="submit"><?= picto_bureau('deconnexion') ?><span>Déconnexion</span></button>
      </form>
    </div>
  </nav>
  <main id="contenu" class="travail" tabindex="-1">
<?php else: ?>
<main id="contenu" class="accueil-agent">
<?php endif; ?>
<?php if ($a = annonce()): ?>
    <div class="annonces" data-annonce><?= bloc_alerte($a[0], $a[1] === 'erreur' ? 'erreur' : 'succes') ?></div>
<?php endif; ?>
