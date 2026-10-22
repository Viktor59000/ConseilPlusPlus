// Comportements communs du site : menu mobile, en-tête au défilement, apparitions, surligneur, boucles de stylo.
// Tout est facultatif : sans JavaScript, le contenu est simplement affiché tel quel.
(() => {
  const calme = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---------- Menu repliable (petit écran) ----------
  const bouton = document.querySelector('.menu-bouton');
  const nav = document.getElementById('nav-principale');
  if (bouton && nav) {
    const basculer = (ouvrir) => {
      bouton.setAttribute('aria-expanded', String(ouvrir));
      nav.classList.toggle('ouvert', ouvrir);
    };
    bouton.addEventListener('click', () => basculer(bouton.getAttribute('aria-expanded') !== 'true'));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && bouton.getAttribute('aria-expanded') === 'true') {
        basculer(false);
        bouton.focus();
      }
    });
  }

  // ---------- Fenêtres : un lien [data-ouvre-dialogue="id"] ouvre le <dialog> au lieu de changer de page ----------
  document.querySelectorAll('[data-ouvre-dialogue]').forEach((lien) => {
    const dialogue = document.getElementById(lien.dataset.ouvreDialogue);
    if (!dialogue || typeof dialogue.showModal !== 'function') return;
    lien.addEventListener('click', (e) => {
      e.preventDefault();
      dialogue.showModal();
      const tampon = dialogue.querySelector('.tampon');
      if (tampon) { tampon.classList.remove('tampon-anime'); void tampon.offsetWidth; tampon.classList.add('tampon-anime'); }
    });
    // Un clic sur le fond assombri ferme la fenêtre (Échap aussi, nativement)
    dialogue.addEventListener('click', (e) => {
      const r = dialogue.getBoundingClientRect();
      const dehors = e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
      if (e.target === dialogue && dehors) dialogue.close();
    });
  });

  // ---------- En-tête : filet dès qu'on a défilé ----------
  const entete = document.querySelector('.entete');
  if (entete) {
    const maj = () => entete.classList.toggle('defile', window.scrollY > 8);
    requestAnimationFrame(maj);
    addEventListener('scroll', maj, { passive: true });
  }

  // ---------- Petites surprises ----------
  let textes = { onglet: [], secrets: [] };
  try { textes = JSON.parse(document.getElementById('textes-site')?.textContent || '{}'); } catch { /* sans textes, pas de surprise */ }
  const auHasard = (liste) => liste[Math.floor(Math.random() * liste.length)];

  // Le titre de l'onglet réclame le visiteur quand il regarde ailleurs
  const titre = document.title;
  document.addEventListener('visibilitychange', () => {
    document.title = document.hidden && textes.onglet?.length ? `${auHasard(textes.onglet)} — ConseilPlus+` : titre;
  });

  // Cinq clics sur un tampon : le cabinet sort le grand tampon
  let clics = 0;
  let minuterie;
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.fiche .tampon')) return;
    clics += 1;
    clearTimeout(minuterie);
    minuterie = setTimeout(() => { clics = 0; }, 1500);
    if (clics < 5) return;
    clics = 0;
    const geant = document.createElement('div');
    geant.className = 'tampon-geant';
    geant.setAttribute('role', 'status');
    geant.textContent = 'Insistant';
    document.body.append(geant);
    setTimeout(() => geant.remove(), 1800);
  });

  // Code Konami (↑ ↑ ↓ ↓ ← → ← → B A) : le conseil premium caché
  const KONAMI = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
  let position = 0;
  document.addEventListener('keydown', (e) => {
    const touche = e.key.length === 1 ? e.key.toLowerCase() : e.key;
    position = touche === KONAMI[position] ? position + 1 : (touche === KONAMI[0] ? 1 : 0);
    if (position < KONAMI.length || !textes.secrets?.length) return;
    position = 0;
    const dialogue = document.createElement('dialog');
    dialogue.className = 'dialogue';
    dialogue.setAttribute('aria-labelledby', 'titre-secret');
    dialogue.innerHTML = '<span class="tampon tampon-anime" aria-hidden="true">Confidentiel</span>'
      + '<h2 id="titre-secret">Conseil premium caché</h2><p></p>'
      + '<form method="dialog" class="actions"><button class="btn btn-tampon" type="submit">Je n\'ai rien vu</button></form>';
    dialogue.querySelector('p').textContent = auHasard(textes.secrets);
    dialogue.addEventListener('close', () => dialogue.remove());
    document.body.append(dialogue);
    dialogue.showModal();
  });

  if (calme || !('IntersectionObserver' in window)) return;

  // Préparé quand la page est au repos : rien ne retarde le premier affichage ni les premiers clics
  const plusTard = window.requestIdleCallback || ((f) => setTimeout(f, 200));
  plusTard(preparer, { timeout: 800 });

  function preparer() {
    const SELECTEURS = {
      reveler: ['main section > .conteneur > h2', 'main section > h2', '.mot-du-jour-intro', '.entree-mot', '.etapes li', '.formule',
        '.tableau-defile', '.faq details', '.formulaire', '.carnet-item', '.installation-carte', '.archives > li',
        '.appel', '.guichet > *', '.plan section', '.temoignage', '.membre', '.valeur', '.texte-long > *', '.duel-deroule > *', '.sommaire'].join(','),
      surligner: '.surligne, .fiche-conseil mark, .entree-mot mark',
      entourer: '.entoure',
      tamponner: '.tampon:not(.tampon-anime), .sketch-decoupage',
    };
    const role = new Map();
    for (const [nom, sel] of Object.entries(SELECTEURS)) {
      document.querySelectorAll(sel).forEach((el) => {
        if (nom === 'tamponner' && el.closest('.sketch-court')) return;
        role.set(el, [...(role.get(el) || []), nom]);
      });
    }

    // Premier signal de l'observateur : déjà à l'écran → on n'y touche pas (le haut de page est animé en CSS) ;
    // hors écran → on le prépare (caché, surligneur vide, boucle non tracée) et on attend qu'il arrive.
    const prepares = new Set();
    const rang = new Map();
    const observateur = new IntersectionObserver((entrees) => {
      for (const { target: el, isIntersecting } of entrees) {
        const roles = role.get(el);
        if (!prepares.has(el)) {
          if (isIntersecting) { observateur.unobserve(el); continue; }
          prepares.add(el);
          if (roles.includes('reveler')) {
            const n = rang.get(el.parentElement) || 0;
            rang.set(el.parentElement, n + 1);
            el.style.setProperty('--i', Math.min(n, 5));
            el.classList.add('a-reveler');
          }
          if (roles.includes('surligner')) el.classList.add('a-surligner');
          if (roles.includes('entourer')) el.classList.add('a-entourer');
          continue;
        }
        if (!isIntersecting) continue;
        if (roles.includes('reveler')) {
          el.classList.add('revele');
          el.addEventListener('transitionend', () => {
            el.classList.remove('a-reveler', 'revele');
            el.style.removeProperty('--i');
          }, { once: true });
        }
        if (roles.includes('surligner')) el.classList.add('surligne-anime');
        if (roles.includes('entourer')) el.classList.add('entoure-anime');
        if (roles.includes('tamponner')) el.classList.add('tampon-anime');
        observateur.unobserve(el);
      }
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.15 });
    role.forEach((_, el) => observateur.observe(el));
  }
})();
