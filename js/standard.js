// Le standard téléphonique : un répondeur à choix cliquables dont tous les chemins mènent à la machine à café.
// Arborescence et textes : data/standard.php (JSON dans la page). Voix et musique d'attente facultatives.
(() => {
  const tel = document.querySelector('[data-telephone]');
  const source = document.getElementById('standard-donnees');
  if (tel && source) {
    const D = JSON.parse(source.textContent);
    const A = D.appareil;
    const elEtat = tel.querySelector('[data-etat]');
    const elDuree = tel.querySelector('[data-duree]');
    const elMessage = tel.querySelector('[data-message]');
    const elChoix = tel.querySelector('[data-choix]');
    const decrocher = tel.querySelector('[data-decrocher]');
    const caseVoix = tel.querySelector('[data-voix]');
    const caseMusique = tel.querySelector('[data-musique]');
    const journal = document.querySelector('[data-journal]');
    const synthese = 'speechSynthesis' in window ? window.speechSynthesis : null;
    const auHasard = (l) => l[Math.floor(Math.random() * l.length)];
    const dort = (ms) => new Promise((r) => setTimeout(r, ms));
    const remplir = (texte, valeurs) => texte.replace(/\{(\w+)\}/g, (m, cle) => valeurs[cle] ?? m);
    if (!synthese) caseVoix.closest('label').hidden = true;

    let enLigne = false;
    let debut = 0;
    let horloge = null;
    let jeton = 0; // change à chaque nouvel écran : annule les suites programmées de l'écran précédent

    // ---------- Musique d'attente (composée ici, quelques notes au synthétiseur) ----------
    let audio = null;
    let musique = null;
    function jouerMusique() {
      if (!caseMusique.checked || musique) return;
      audio ??= new (window.AudioContext || window.webkitAudioContext)();
      const notes = [523, 659, 784, 659, 587, 698, 880, 698, 523, 659, 784, 1046, 988, 784, 659, 587];
      let i = 0;
      const jouer = () => {
        const o = audio.createOscillator();
        const g = audio.createGain();
        o.type = 'triangle';
        o.frequency.value = notes[i++ % notes.length];
        g.gain.setValueAtTime(0.0001, audio.currentTime);
        g.gain.exponentialRampToValueAtTime(0.06, audio.currentTime + 0.02);
        g.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + 0.32);
        o.connect(g).connect(audio.destination);
        o.start();
        o.stop(audio.currentTime + 0.34);
      };
      jouer();
      musique = setInterval(jouer, 340);
    }
    function couperMusique() {
      clearInterval(musique);
      musique = null;
    }
    caseMusique.addEventListener('change', () => { if (!caseMusique.checked) couperMusique(); });

    // ---------- Affichage, voix, transcription ----------
    function noter(texte, qui = 'standard') {
      const li = document.createElement('li');
      if (qui === 'vous') li.className = 'journal-vous';
      li.textContent = texte;
      journal.append(li);
    }

    function dire(texte) {
      elMessage.textContent = texte;
      noter(texte);
      if (synthese && caseVoix.checked) {
        synthese.cancel();
        const e = new SpeechSynthesisUtterance(texte.replace(/«|»/g, ''));
        e.lang = 'fr-FR';
        e.rate = 0.98;
        synthese.speak(e);
      }
    }

    /** Remplace les boutons de choix ; le focus suit si l'on pilotait déjà au clavier depuis les choix. */
    function proposer(choix = []) {
      const garderFocus = elChoix.contains(document.activeElement) || document.activeElement === document.body;
      elChoix.replaceChildren(...choix.map(([libelle, cible]) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'btn btn-clair btn-petit standard-bouton';
        b.textContent = libelle;
        b.addEventListener('click', () => {
          if (!enLigne) return;
          noter(remplir(A.vous, { choix: libelle }), 'vous');
          afficher(cible);
        });
        return b;
      }));
      if (choix.length && garderFocus && enLigne) elChoix.firstChild.focus();
    }

    // ---------- La machine d'états ----------
    async function afficher(nom, valeurs = {}) {
      const e = D.ecrans[nom];
      jeton += 1;
      const moi = jeton;
      couperMusique();
      proposer();
      valeurs.delai ??= auHasard(D.delais);
      valeurs.numero ??= 'R-' + String(Math.floor(1000 + Math.random() * 9000));
      dire(remplir(e.texte, valeurs));

      if (e.action === 'conseil') {
        let conseil = D.conseil_secours;
        try {
          const r = await fetch('api/conseils.php', { headers: { Accept: 'application/json' } });
          if (r.ok) conseil = (await r.json()).conseil;
        } catch { /* conseil de secours */ }
        if (moi === jeton) afficher(e.suite, { conseil });
        return;
      }
      if (e.action === 'attente') {
        jouerMusique();
        let rang = 4 + Math.floor(Math.random() * 6);
        for (let i = 0; i < 4; i++) {
          await dort(3500);
          if (moi !== jeton) return;
          rang = Math.max(1, rang + auHasard([-1, -1, -2, 1]));
          elMessage.textContent = i % 2
            ? remplir(D.annonce, { annonce: auHasard(D.annonces) })
            : remplir(D.file, { rang: rang === 1 ? '1er' : `${rang}e`, delai: auHasard(D.delais) });
        }
        await dort(3000);
        if (moi === jeton) afficher(e.suite);
        return;
      }
      if (e.pause) {
        await dort(e.pause);
        if (moi !== jeton) return;
        if (e.fin) raccrocher(e.fin);
        else afficher(e.suite);
        return;
      }
      proposer(e.choix);
    }

    // ---------- Combiné ----------
    function majDuree() {
      const s = Math.floor((Date.now() - debut) / 1000);
      elDuree.textContent = `${String(Math.floor(s / 60)).padStart(2, '0')}:${String(s % 60).padStart(2, '0')}`;
    }

    function decrocherAppel() {
      enLigne = true;
      debut = Date.now();
      journal.replaceChildren();
      elEtat.textContent = A.en_ligne;
      majDuree();
      horloge = setInterval(majDuree, 1000);
      decrocher.textContent = A.raccrocher;
      tel.classList.add('en-ligne');
      afficher('accueil');
      elChoix.firstChild?.focus();
    }

    function raccrocher(raison = A.vous_raccrochez) {
      const avaitFocus = elChoix.contains(document.activeElement) || document.activeElement === document.body;
      enLigne = false;
      jeton += 1;
      clearInterval(horloge);
      couperMusique();
      if (synthese) synthese.cancel();
      proposer();
      elEtat.textContent = A.raccroche;
      elMessage.textContent = remplir(A.bilan, { raison, duree: elDuree.textContent || '00:00' });
      noter(raison + '.');
      decrocher.textContent = A.rappeler;
      tel.classList.remove('en-ligne');
      if (avaitFocus) decrocher.focus();
    }

    decrocher.addEventListener('click', () => (enLigne ? raccrocher() : decrocherAppel()));
    tel.hidden = false;
  }
})();
