<?php
/**
 * Limite le nombre d'essais sur la connexion et l'inscription, sans modifier le code du cours (auth/).
 * Ce fichier s'exécute juste AVANT auth/connexion.php et auth/inscription.php (directive PHP auto_prepend_file :
 * docker/apache.conf en ligne, auth/.user.ini sous WAMP). Il ne fait que compter, et renvoie vers l'espace
 * client quand la limite est atteinte ; sinon le code du cours s'exécute normalement.
 *   connexion   : 10 essais par adresse IP et par quart d'heure (contre les essais de mots de passe à la chaîne)
 *   inscription : 5 comptes par adresse IP et par heure (contre les comptes créés en masse)
 * Compteurs dans des petits fichiers du dossier temporaire (remis à zéro à chaque redémarrage, c'est voulu).
 */
(function (): void {
    $limites = ['connexion.php' => [10, 900], 'inscription.php' => [5, 3600]];
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($limites[$script])) {
        return;
    }
    [$maximum, $fenetre] = $limites[$script];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';
    $fichier = sys_get_temp_dir() . '/conseilplus-essais-' . pathinfo($script, PATHINFO_FILENAME) . '-' . hash('sha256', $ip) . '.json';

    $f = fopen($fichier, 'c+');
    if ($f === false) {
        return; // compteur indisponible : on n'empêche jamais de se connecter pour autant
    }
    flock($f, LOCK_EX);
    $essais = json_decode(stream_get_contents($f) ?: '[]', true) ?: [];
    $essais = array_values(array_filter($essais, fn($t) => $t > time() - $fenetre));
    $bloque = count($essais) >= $maximum;
    if (!$bloque) {
        $essais[] = time();
    }
    ftruncate($f, 0);
    rewind($f);
    fwrite($f, json_encode($essais));
    flock($f, LOCK_UN);
    fclose($f);

    if ($bloque) {
        header('location: ../login.php?error=trop');
        exit();
    }
})();
