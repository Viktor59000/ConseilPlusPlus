// Bouton clair / sombre (site et Bureau). Le choix est gardé dans le navigateur ; le thème initial est posé
// avant l'affichage par le script de includes/theme.php. Sans JavaScript, le thème suit le système.
(() => {
  const racine = document.documentElement;
  const bouton = document.querySelector('[data-theme-bouton]');
  const couleurs = { clair: '#F5F6FA', sombre: '#0E1220' };
  const systeme = matchMedia('(prefers-color-scheme: dark)');

  const appliquer = (theme, enFondu) => {
    if (enFondu) {
      racine.classList.add('bascule-theme');
      setTimeout(() => racine.classList.remove('bascule-theme'), 400);
    }
    racine.dataset.themeEffectif = theme;
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', couleurs[theme]);
    if (bouton) bouton.setAttribute('aria-label', theme === 'sombre' ? 'Passer en thème clair' : 'Passer en thème sombre');
  };

  appliquer(racine.dataset.themeEffectif || 'clair', false);
  if (!bouton) return;
  bouton.hidden = false;
  bouton.addEventListener('click', () => {
    const theme = racine.dataset.themeEffectif === 'sombre' ? 'clair' : 'sombre';
    racine.dataset.theme = theme;
    try { localStorage.setItem('conseilplus-theme', theme); } catch { /* navigation privée : choix non retenu */ }
    appliquer(theme, true);
  });
  // Sans choix enregistré, on suit le système s'il change pendant la visite
  systeme.addEventListener('change', (e) => {
    if (!racine.dataset.theme) appliquer(e.matches ? 'sombre' : 'clair', true);
  });
})();
