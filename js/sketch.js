// Le Petit Moyen : joue un sketch réplique par réplique, avec un temps d'arrêt avant la chute,
// et (en option) les voix de synthèse du navigateur : conseiller grave et lent, stagiaire aigu et pressé.
// Sans JavaScript, le script complet reste affiché.
(() => {
  const synthese = 'speechSynthesis' in window ? window.speechSynthesis : null;
  const pause = (ms) => new Promise((r) => setTimeout(r, ms));

  function voixFrancaises() {
    return synthese ? synthese.getVoices().filter((v) => v.lang.toLowerCase().startsWith('fr')) : [];
  }

  const REGLAGES = {
    conseiller: { voix: 0, pitch: 0.75, rate: 0.9 },
    stagiaire: { voix: 1, pitch: 1.35, rate: 1.08 },
    duo: { voix: 0, pitch: 1, rate: 0.85 },
    relance: { voix: 1, pitch: 1.35, rate: 1.08 },
  };

  /** Lit une réplique à voix haute ; se termine à la fin de la lecture (ou après un délai de sécurité). */
  function dire(texte, qui) {
    return new Promise((fin) => {
      const voix = voixFrancaises();
      const reglage = REGLAGES[qui];
      const enonce = new SpeechSynthesisUtterance(texte.replace(/…/g, '... '));
      enonce.lang = 'fr-FR';
      if (voix.length) enonce.voice = voix[reglage.voix % voix.length];
      enonce.pitch = reglage.pitch;
      enonce.rate = reglage.rate;
      const secours = setTimeout(fin, 2000 + texte.length * 120);
      enonce.onend = enonce.onerror = () => { clearTimeout(secours); fin(); };
      synthese.speak(enonce);
    });
  }

  /** Temps de lecture silencieuse d'une ligne. */
  const duree = (texte) => Math.min(4500, Math.max(900, 500 + texte.length * 38));

  for (const sketch of document.querySelectorAll('[data-sketch]')) {
    const commandes = sketch.querySelector('.sketch-commandes');
    const bouton = sketch.querySelector('[data-jouer]');
    const caseVoix = sketch.querySelector('[data-voix]');
    const lignes = [...sketch.querySelectorAll('.sketch-court [data-qui]')];
    let seance = 0; // incrémenté à chaque lancement : arrête proprement une lecture en cours

    commandes.hidden = false;
    if (!synthese) caseVoix.closest('label').hidden = true;

    const arreter = () => {
      seance++;
      if (synthese) synthese.cancel();
      sketch.classList.remove('en-scene');
      lignes.forEach((l) => l.classList.remove('vu', 'parle'));
      bouton.textContent = '▶ Jouer la scène';
    };

    bouton.addEventListener('click', async () => {
      if (sketch.classList.contains('en-scene')) {
        arreter();
        return;
      }
      const ma = ++seance;
      const avecVoix = synthese && caseVoix.checked;
      sketch.classList.add('en-scene');
      bouton.textContent = '■ Arrêter';

      for (const ligne of lignes) {
        if (ma !== seance) return;
        const qui = ligne.dataset.qui;
        const texte = (ligne.querySelector('.replique-texte') || ligne).textContent.trim();
        if (ligne.classList.contains('replique-chute')) await pause(900); // le silence avant la chute
        if (ma !== seance) return;
        ligne.classList.add('vu');
        ligne.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        if (avecVoix && REGLAGES[qui]) {
          ligne.classList.add('parle');
          await dire(texte, qui);
          ligne.classList.remove('parle');
          await pause(250);
        } else {
          await pause(qui === 'scene' ? Math.min(3200, duree(texte)) : duree(texte));
        }
      }
      if (ma === seance) {
        sketch.classList.remove('en-scene');
        lignes.forEach((l) => l.classList.remove('vu'));
        bouton.textContent = '↻ Rejouer la scène';
      }
    });
  }

  // Demande la liste des voix tout de suite : certains navigateurs la chargent en différé
  if (synthese) synthese.getVoices();
})();
