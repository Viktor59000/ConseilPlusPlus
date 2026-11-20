// Le Bureau : comportements communs (thème « archives », menu sur téléphone, filtre instantané des tableaux).
// Sans JavaScript, tout reste utilisable : le thème suit le cookie (sombre par défaut), la navigation est dans le flux,
// les filtres passent par le formulaire (GET).
(() => {
  const racine = document.documentElement;

  // ---------- Thème : sombre par défaut, le choix clair est gardé un an dans un cookie lu par le serveur ----------
  const bouton = document.querySelector('[data-theme-bouton]');
  const meta = document.querySelector('meta[name="theme-color"]');
  const libeller = () => {
    const sombre = racine.dataset.themeEffectif === 'sombre';
    bouton?.setAttribute('aria-label', sombre ? 'Passer en thème clair' : 'Passer en thème sombre');
    meta?.setAttribute('content', sombre ? meta.dataset.sombre : meta.dataset.clair);
  };
  if (bouton) {
    bouton.hidden = false;
    libeller();
    bouton.addEventListener('click', () => {
      const theme = racine.dataset.themeEffectif === 'sombre' ? 'clair' : 'sombre';
      racine.classList.add('bascule-theme');
      setTimeout(() => racine.classList.remove('bascule-theme'), 400);
      racine.dataset.theme = theme;
      racine.dataset.themeEffectif = theme;
      document.cookie = `bureau_theme=${theme}; path=/; max-age=31536000; SameSite=Lax`;
      libeller();
    });
  }

  // ---------- Menu (moins de 1024 px) : la barre latérale devient un volet ----------
  const appli = document.querySelector('[data-appli]');
  const menu = document.querySelector('[data-menu-bureau]');
  const laterale = document.getElementById('laterale');
  if (appli && menu && laterale) {
    menu.hidden = false;
    const ouvrir = (oui) => {
      menu.setAttribute('aria-expanded', String(oui));
      menu.querySelector('span').textContent = oui ? 'Fermer' : 'Menu';
      appli.classList.toggle('appli--menu', oui);
      if (oui) laterale.querySelector('a, button')?.focus();
    };
    menu.addEventListener('click', () => ouvrir(menu.getAttribute('aria-expanded') !== 'true'));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && menu.getAttribute('aria-expanded') === 'true') { ouvrir(false); menu.focus(); }
    });
    document.addEventListener('click', (e) => {
      if (menu.getAttribute('aria-expanded') === 'true' && !laterale.contains(e.target) && !menu.contains(e.target)) ouvrir(false);
    });
  }

  // ---------- Filtre instantané : <input data-filtre="#id-du-tableau"> masque les lignes qui ne contiennent pas le texte ----------
  const normaliser = (t) => t.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
  document.querySelectorAll('[data-filtre]').forEach((champ) => {
    const tableau = document.querySelector(champ.dataset.filtre);
    const compte = document.querySelector(champ.dataset.filtreCompte || '[data-filtre-compte]');
    if (!tableau) return;
    champ.closest('[data-filtre-bloc]')?.removeAttribute('hidden');
    champ.addEventListener('input', () => {
      const cherche = normaliser(champ.value.trim());
      let visibles = 0;
      tableau.querySelectorAll('tbody tr').forEach((ligne) => {
        const garde = !cherche || normaliser(ligne.textContent).includes(cherche);
        ligne.hidden = !garde;
        visibles += garde ? 1 : 0;
      });
      if (compte) compte.textContent = String(visibles);
    });
  });
})();
