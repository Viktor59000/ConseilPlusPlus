// La Salle de tri (bureau/salle-de-tri.php) : glisser une fiche d'un casier à l'autre, ou la déplacer avec sa liste
// « Déplacer vers » + « Tamponner », sans recharger la page. Dans les deux cas, c'est le formulaire de la fiche (le POST
// existant de dossier.php ou inscrits.php, jeton CSRF compris) qui part ; la page renvoyée (retour=salle) donne les
// casiers à jour et l'annonce du Bureau. À la confirmation, le tampon s'abat sur la fiche (Guichet.tamponner).
// Le détail s'ouvre dans un tiroir (dialog). Sans JavaScript : formulaires classiques et lien vers la fiche.
(() => {
  const plateau = document.querySelector('[data-casiers]');
  if (!plateau) return;
  const tiroir = document.getElementById('tiroir');
  const corps = tiroir?.querySelector('[data-tiroir-corps]');
  const direct = document.querySelector('[data-annonce-tri]');
  let prise = null;

  const passages = (fiche) => { try { return JSON.parse(fiche.dataset.vers || '{}'); } catch { return {}; } };

  // Fiches glissables : seulement celles qui ont un passage possible (un formulaire « Déplacer vers »)
  const preparer = () => {
    plateau.querySelectorAll('[data-fiche]').forEach((fiche) => {
      const mobile = Boolean(fiche.querySelector('[data-deplacer]'));
      fiche.draggable = mobile;
      fiche.querySelectorAll('a').forEach((a) => { a.draggable = false; });
    });
  };
  preparer();

  // ---------- Annonces : lues par les lecteurs d'écran, montrées un instant en bas à droite ----------
  let minuterie;
  const avis = (alerte, texte) => {
    if (direct) { direct.textContent = ''; setTimeout(() => { direct.textContent = texte; }, 50); }
    document.querySelector('.avis-b')?.remove();
    if (!alerte) return;
    const boite = document.createElement('div');
    boite.className = 'avis-b';
    boite.append(alerte);
    alerte.removeAttribute('role'); // la région polie ci-dessus annonce déjà le texte
    document.body.append(boite);
    clearTimeout(minuterie);
    minuterie = setTimeout(() => boite.remove(), 6500);
  };
  const alerteLocale = (texte) => {
    const a = document.createElement('div');
    a.className = 'alerte alerte--attention';
    const p = document.createElement('p');
    p.textContent = texte;
    a.append(p);
    return a;
  };

  // ---------- Le tampon s'abat sur la fiche arrivée ----------
  const tamponner = (fiche, passage) => {
    const t = document.createElement('span');
    t.className = 'tampon tampon--petit' + (passage.couleur && passage.couleur !== 'rouge' ? ` tampon--${passage.couleur}` : '');
    t.style.setProperty('--angle', '-7deg');
    t.setAttribute('aria-hidden', 'true');
    t.dataset.tampon = 'en-attente';
    const texte = document.createElement('span');
    texte.className = 'tampon__texte';
    texte.textContent = passage.tampon;
    t.append(texte);
    fiche.append(t);
    if (window.Guichet) window.Guichet.tamponner(t);
    else t.removeAttribute('data-tampon');
  };

  // ---------- Envoi du formulaire de la fiche, puis casiers à jour ----------
  const envoyer = async (form, passage, garderFocus) => {
    const fiche = form.closest('[data-fiche]');
    const id = fiche.id;
    fiche.classList.add('fiche-tri--envoi');
    let html;
    try {
      // getAttribute : le formulaire a un champ nommé « action », qui masque form.action
      const reponse = await fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), credentials: 'same-origin' });
      if (!reponse.ok) throw new Error(String(reponse.status));
      html = await reponse.text();
    } catch {
      form.submit(); // repli : l'envoi classique, page rechargée
      return;
    }
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const casiers = doc.querySelector('[data-casiers]');
    if (!casiers) { window.location.reload(); return; }
    plateau.replaceChildren(...casiers.childNodes);
    // Compteurs de la tête (segments) et de la barre latérale
    const segments = doc.querySelector('.tete .segments');
    if (segments) document.querySelector('.tete .segments')?.replaceChildren(...segments.childNodes);
    const listes = doc.querySelectorAll('#laterale ul');
    document.querySelectorAll('#laterale ul').forEach((ul, i) => { if (listes[i]) ul.replaceChildren(...listes[i].childNodes); });
    preparer();

    const alerte = doc.querySelector('[data-annonce] .alerte');
    const reussi = alerte && !alerte.classList.contains('alerte--erreur');
    const arrivee = document.getElementById(id);
    if (reussi && arrivee && passage) tamponner(arrivee, passage);
    avis(alerte, alerte ? alerte.textContent.trim() : 'Rien n\'a changé.');
    if (garderFocus) (arrivee?.querySelector('select, a') || plateau.querySelector('select, a'))?.focus();
  };

  // Au clavier ou à la souris : « Déplacer vers » + « Tamponner »
  plateau.addEventListener('submit', (e) => {
    const form = e.target.closest('[data-deplacer]');
    if (!form) return;
    e.preventDefault();
    const valeur = form.querySelector('select')?.value;
    const passage = Object.values(passages(form.closest('[data-fiche]'))).find((p) => p.valeur === valeur);
    envoyer(form, passage, true);
  });

  // ---------- Glisser-déposer ----------
  const casierDe = (el) => el.closest?.('[data-casier]');
  const nettoyer = () => {
    prise?.classList.remove('fiche-tri--prise');
    plateau.querySelectorAll('[data-casier]').forEach((c) => c.classList.remove('casier--survol', 'casier--interdit'));
    prise = null;
  };
  plateau.addEventListener('dragstart', (e) => {
    const fiche = e.target.closest?.('[data-fiche]');
    if (!fiche || !fiche.draggable) return;
    prise = fiche;
    fiche.classList.add('fiche-tri--prise');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', fiche.id);
    const vers = passages(fiche);
    const depart = casierDe(fiche).dataset.casier;
    plateau.querySelectorAll('[data-casier]').forEach((c) => {
      c.classList.toggle('casier--interdit', c.dataset.casier !== depart && !vers[c.dataset.casier]);
    });
  });
  plateau.addEventListener('dragend', nettoyer);
  plateau.addEventListener('dragover', (e) => {
    const casier = casierDe(e.target);
    if (!prise || !casier || !passages(prise)[casier.dataset.casier]) return;
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    plateau.querySelectorAll('[data-casier]').forEach((c) => c.classList.toggle('casier--survol', c === casier));
  });
  plateau.addEventListener('dragleave', (e) => {
    const casier = casierDe(e.target);
    if (casier && !casier.contains(e.relatedTarget)) casier.classList.remove('casier--survol');
  });
  plateau.addEventListener('drop', (e) => {
    const casier = casierDe(e.target);
    const fiche = prise;
    if (!casier || !fiche) return;
    e.preventDefault();
    const passage = passages(fiche)[casier.dataset.casier];
    nettoyer();
    if (!passage) {
      if (casier !== casierDe(fiche)) avis(alerteLocale('Ce passage n\'est pas prévu par le règlement. Pour classer, rédigez d\'abord la réponse.'), 'Passage impossible.');
      return;
    }
    const form = fiche.querySelector('[data-deplacer]');
    const select = form.querySelector('select');
    select.value = passage.valeur;
    casier.querySelector('.casier__liste')?.prepend(fiche); // la fiche se pose tout de suite dans son nouveau casier
    envoyer(form, passage, false);
  });

  // ---------- Tiroir de détail ----------
  plateau.addEventListener('click', (e) => {
    const lien = e.target.closest('[data-tiroir]');
    if (!lien || !tiroir?.showModal || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
    const modele = document.getElementById(lien.dataset.tiroir);
    if (!modele) return;
    e.preventDefault();
    corps.replaceChildren(modele.content.cloneNode(true));
    tiroir.showModal();
  });
  tiroir?.addEventListener('click', (e) => { if (e.target === tiroir) tiroir.close(); });
})();
