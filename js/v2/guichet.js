// « Le Guichet » (refonte 2026-11) : comportements communs du nouveau gabarit (includes/gabarit.php).
// Menu, régime du Guichet adapté, entrée du héros, tampon qui s'abat, phrase qui s'encre, titre d'onglet.
// Tout est facultatif : sans JavaScript, la page est entière et immobile. Rien n'apparaît en fondu au défilement,
// sauf la phrase-manifeste (data-encre), le moment choisi de la page, qui s'encre une fois quand on y arrive.
// API réutilisable par les autres scripts : window.Guichet.tamponner(element), window.Guichet.encrer(element),
// window.Guichet.mouvementPermis().
(() => {
  const racine = document.documentElement;
  const CLE_REGIME = 'conseilplus-regime';
  const REGIMES = ['ordinaire', 'allege', 'prioritaire'];
  const mouvementPermis = () => racine.dataset.regime !== 'allege';

  // ---------- Menu (moins de 1024 px) ----------
  const entete = document.querySelector('[data-entete]');
  const menuBouton = entete?.querySelector('.menu-bouton');
  if (entete && menuBouton) {
    const basculer = (ouvrir) => {
      menuBouton.setAttribute('aria-expanded', String(ouvrir));
      menuBouton.querySelector('.visuellement-cache').textContent = ouvrir ? 'Fermer le menu' : 'Menu';
      entete.classList.toggle('entete--ouvert', ouvrir);
    };
    menuBouton.addEventListener('click', () => basculer(menuBouton.getAttribute('aria-expanded') !== 'true'));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && menuBouton.getAttribute('aria-expanded') === 'true') { basculer(false); menuBouton.focus(); }
    });
    entete.querySelectorAll('.entete__nav a').forEach((a) => a.addEventListener('click', () => basculer(false)));
  }

  // ---------- Le Guichet adapté : régime ordinaire / allégé / prioritaire ----------
  // Le régime de départ est posé avant l'affichage (includes/theme.php). Ici : les cases du CERFA 0-A, le choix gardé,
  // et le suivi des réglages du système tant que rien n'est choisi.
  const formulaires = document.querySelectorAll('[data-regime-formulaire]');
  const volet = document.querySelector('[data-regime-volet]');
  let choisi = null;
  try { choisi = localStorage.getItem(CLE_REGIME); } catch { /* navigation privée */ }

  const cocher = () => {
    formulaires.forEach((f) => f.querySelectorAll('input[type="radio"]').forEach((r) => { r.checked = r.value === racine.dataset.regime; }));
  };
  const allegerImages = () => {
    // Images légères : les gravures paresseuses pas encore chargées prennent leur plus petite largeur
    document.querySelectorAll('picture.scene source[srcset]').forEach((s) => {
      const img = s.parentElement.querySelector('img');
      if (img && img.loading === 'lazy' && !img.complete) s.sizes = '240px'; // l'image du héros (eager) n'est pas rechargée
    });
  };
  const appliquerRegime = (regime, enregistrer) => {
    if (!REGIMES.includes(regime)) return;
    racine.dataset.regime = regime;
    if (enregistrer) {
      choisi = regime;
      try { localStorage.setItem(CLE_REGIME, regime); } catch { /* choix non retenu */ }
    }
    if (regime === 'allege') allegerImages();
    cocher();
  };
  formulaires.forEach((f) => {
    f.hidden = false;
    f.addEventListener('submit', (e) => e.preventDefault());
    f.addEventListener('change', (e) => { if (e.target.name?.startsWith('regime-')) appliquerRegime(e.target.value, true); });
  });
  document.querySelectorAll('[data-regime-sans-js]').forEach((p) => { p.hidden = true; });
  if (volet) {
    volet.hidden = false;
    document.addEventListener('click', (e) => { if (volet.open && !volet.contains(e.target)) volet.open = false; });
    volet.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && volet.open) { volet.open = false; volet.querySelector('summary').focus(); }
    });
  }
  if (racine.dataset.regime === 'allege') allegerImages();
  cocher();
  const systeme = () => {
    if (choisi) return;
    const prioritaire = matchMedia('(prefers-contrast: more)').matches;
    appliquerRegime(prioritaire ? 'prioritaire' : (matchMedia('(prefers-reduced-motion: reduce)').matches ? 'allege' : 'ordinaire'), false);
  };
  matchMedia('(prefers-contrast: more)').addEventListener?.('change', systeme);
  matchMedia('(prefers-reduced-motion: reduce)').addEventListener?.('change', systeme);

  // ---------- Découpe en mots (titre du héros, phrase qui s'encre) ----------
  // Remplace les nœuds texte par des <span> par mot ; les éléments enfants (<em>, <a>…) gardent leur place.
  const decouperMots = (el, fabriquer) => {
    const mots = [];
    const parcourir = (noeud) => {
      [...noeud.childNodes].forEach((n) => {
        if (n.nodeType === Node.ELEMENT_NODE) { parcourir(n); return; }
        if (n.nodeType !== Node.TEXT_NODE || !n.textContent.trim()) return;
        const fragment = document.createDocumentFragment();
        n.textContent.split(/(\s+)/).forEach((morceau) => {
          if (!morceau) return;
          if (/^\s+$/.test(morceau)) { fragment.append(morceau); return; }
          const span = fabriquer(morceau, n.parentElement);
          mots.push(span);
          fragment.append(span);
        });
        n.replaceWith(fragment);
      });
    };
    parcourir(el);
    return mots;
  };

  // ---------- Entrée du héros, une fois au chargement ----------
  // Le titre monte ligne par ligne hors de ses masques ; le reste (CSS) suit. Le texte d'origine est rendu ensuite.
  const titre = document.querySelector('[data-enter] h1');
  if (titre && mouvementPermis() && !titre.hasAttribute('data-enter-whole')) {
    const original = titre.innerHTML;
    const mots = decouperMots(titre, (mot) => {
      const masque = document.createElement('span');
      masque.className = 'entree-mot';
      const interieur = document.createElement('span');
      interieur.textContent = mot;
      masque.append(interieur);
      return masque;
    });
    // Numéro de ligne de chaque mot (même hauteur = même ligne), 80 ms d'écart entre deux lignes
    let ligne = -1;
    let haut = null;
    mots.forEach((m) => {
      const y = Math.round(m.offsetTop);
      if (y !== haut) { ligne += 1; haut = y; }
      m.style.setProperty('--ligne', String(Math.min(ligne, 3)));
    });
    titre.classList.add('entre');
    const dernier = mots[mots.length - 1]?.firstElementChild;
    const rendre = () => { titre.innerHTML = original; };
    if (dernier) dernier.addEventListener('animationend', rendre, { once: true });
    setTimeout(rendre, 1600);
  } else if (titre) {
    titre.classList.add('entre');
  }

  // ---------- Le tampon s'abat (moment signature) ----------
  // tamponner(el) : le tampon tombe, frappe, l'encre bave ; la feuille qui le porte encaisse le coup.
  // En régime allégé (ou mouvement réduit) : un simple fondu. Rejouable (chaque appel recommence).
  const tamponner = (el) => {
    if (!el) return;
    el.classList.remove('tampon--abat', 'tampon--fondu');
    el.removeAttribute('data-tampon');
    void el.offsetWidth; // relance l'animation
    if (!mouvementPermis()) { el.classList.add('tampon--fondu'); return; }
    el.classList.add('tampon--abat');
    const papier = el.closest('.attestation, .dossier__feuille, .ticket, [data-papier]');
    if (papier) {
      setTimeout(() => {
        papier.classList.remove('papier--choc');
        void papier.offsetWidth;
        papier.classList.add('papier--choc');
      }, 290);
    }
  };
  // Tampons posés au chargement (après l'entrée du héros) ; boutons qui déclenchent un tampon : data-tamponner="#id"
  document.querySelectorAll('[data-tampon="au-chargement"]').forEach((el, i) => setTimeout(() => tamponner(el), 650 + i * 220));
  document.addEventListener('click', (e) => {
    const declencheur = e.target.closest('[data-tamponner]');
    if (declencheur) tamponner(document.querySelector(declencheur.dataset.tamponner));
  });

  // ---------- La phrase qui s'encre mot à mot (second moment) ----------
  // encrer(el) : chaque mot passe de l'encre pâle (déjà lisible) à l'encre pleine, l'un après l'autre.
  // Les mots marqués <em> prennent le rouge du stylo. Déclenché une fois, quand la phrase est à l'écran.
  const preparerEncre = (el) => {
    if (el.dataset.encrePrete) return;
    el.dataset.encrePrete = '1';
    decouperMots(el, (mot, parent) => {
      const span = document.createElement('span');
      span.className = 'encre-mot' + (parent.closest('em') ? ' encre-mot--accent' : '');
      span.textContent = mot;
      return span;
    }).forEach((s, i) => s.style.setProperty('--i', String(i)));
  };
  const encrer = (el) => {
    if (!el) return;
    preparerEncre(el);
    const mots = el.querySelectorAll('.encre-mot');
    if (!mouvementPermis()) { mots.forEach((m) => { m.style.transitionDelay = '0ms'; m.classList.add('est-encre'); }); return; }
    mots.forEach((m) => m.classList.remove('est-encre'));
    requestAnimationFrame(() => requestAnimationFrame(() => mots.forEach((m) => m.classList.add('est-encre'))));
  };
  const phrases = document.querySelectorAll('[data-encre]');
  if (phrases.length) {
    if (!mouvementPermis() || !('IntersectionObserver' in window)) {
      phrases.forEach((p) => p.dataset.encre !== 'manuel' && encrer(p));
    } else {
      phrases.forEach(preparerEncre);
      const observateur = new IntersectionObserver((entrees) => {
        entrees.forEach(({ target, isIntersecting }) => {
          if (!isIntersecting) return;
          observateur.unobserve(target);
          encrer(target);
        });
      }, { threshold: 0.6 });
      phrases.forEach((p) => p.dataset.encre !== 'manuel' && observateur.observe(p));
    }
  }

  // ---------- Le titre de l'onglet réclame le visiteur quand il regarde ailleurs ----------
  let textes = { onglet: [] };
  try { textes = JSON.parse(document.getElementById('textes-site')?.textContent || '{}'); } catch { /* pas de surprise */ }
  const titreOnglet = document.title;
  document.addEventListener('visibilitychange', () => {
    const liste = textes.onglet || [];
    document.title = document.hidden && liste.length ? `${liste[Math.floor(Math.random() * liste.length)]} — ConseilPlus+` : titreOnglet;
  });

  window.Guichet = Object.freeze({ tamponner, encrer, mouvementPermis });
})();
