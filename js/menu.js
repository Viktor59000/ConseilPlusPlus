// Menu principal repliable sur petit écran (le CSS ne le replie que si JavaScript est actif).
(() => {
  const bouton = document.querySelector('.menu-bouton');
  const nav = document.getElementById('nav-principale');
  if (!bouton || !nav) return;

  const basculer = (ouvrir) => {
    bouton.setAttribute('aria-expanded', String(ouvrir));
    nav.classList.toggle('ouvert', ouvrir);
  };
  bouton.addEventListener('click', () => basculer(bouton.getAttribute('aria-expanded') !== 'true'));
  // Échap referme le menu et rend le focus au bouton
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && bouton.getAttribute('aria-expanded') === 'true') {
      basculer(false);
      bouton.focus();
    }
  });
})();
