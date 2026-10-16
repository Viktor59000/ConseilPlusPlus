<?php
/**
 * Thème clair / sombre, partagé par le site et Le Bureau.
 * - theme_script() : à placer dans <head>, avant tout affichage (pas de flash de la mauvaise couleur).
 *   Choix enregistré dans le navigateur (localStorage) ; sans choix, le réglage du système s'applique.
 * - theme_bouton() : le bouton soleil / lune (comportement dans js/theme.js).
 */
function theme_script(): string
{
    return "<script>(function(){var d=document.documentElement,t=null;try{t=localStorage.getItem('conseilplus-theme')}catch(e){}"
        . "if(t==='clair'||t==='sombre')d.dataset.theme=t;"
        . "d.dataset.themeEffectif=t||(matchMedia('(prefers-color-scheme: dark)').matches?'sombre':'clair');"
        . "try{if(!sessionStorage.getItem('conseilplus-logo')){d.classList.add('premiere-visite');sessionStorage.setItem('conseilplus-logo','1')}}catch(e){}"
        . "d.classList.add('avec-js')})()</script>";
}

/**
 * Adresse d'un script avec l'empreinte de son contenu (« js/site.js?v=1a2b3c4d ») : après une mise à jour,
 * l'adresse change, donc le navigateur et le service worker chargent la nouvelle version tout de suite
 * au lieu de garder l'ancienne en cache. $chemin : depuis la page (« js/… » ou « ../js/… » pour Le Bureau).
 */
function fichier_versionne(string $chemin): string
{
    static $empreintes = [];
    $fichier = __DIR__ . '/../' . preg_replace('#^(\.\./)+#', '', $chemin);
    $empreintes[$fichier] ??= is_file($fichier) ? substr(md5_file($fichier), 0, 8) : '0';
    return $chemin . '?v=' . $empreintes[$fichier];
}

/**
 * Politique de sécurité du contenu (CSP) : seuls les fichiers du site sont chargés ; le seul script écrit dans la
 * page (celui du thème) est autorisé par son empreinte. Les styles intégrés restent permis (feuille intégrée,
 * largeurs des barres du Bureau). À envoyer avant tout affichage.
 */
function envoyer_csp(): void
{
    $script = preg_replace('#^<script>|</script>$#', '', theme_script());
    $empreinte = base64_encode(hash('sha256', $script, true));
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'sha256-$empreinte'; "
        . "style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self'; connect-src 'self'; "
        . "manifest-src 'self'; worker-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
}

function theme_bouton(): string
{
    return '<button class="theme-bouton" type="button" data-theme-bouton aria-label="Passer en thème sombre" hidden>'
        . '<svg class="icone-soleil" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">'
        . '<circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M5.3 18.7l1.6-1.6M17.1 6.9l1.6-1.6"/></svg>'
        . '<svg class="icone-lune" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">'
        . '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/></svg>'
        . '</button>';
}

/** Feuilles de style minifiées pour être intégrées à la page (commentaires et espaces inutiles retirés). */
function css_integre(string ...$fichiers): string
{
    $css = '';
    foreach ($fichiers as $f) {
        $css .= file_get_contents($f);
    }
    $css = preg_replace('#/\*.*?\*/#s', '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\s*([{};,>])\s*/', '$1', $css);
    return str_replace(';}', '}', trim($css));
}
