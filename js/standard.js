// Le standard téléphonique : un répondeur interactif dont tous les chemins mènent à la machine à café.
// Touches du clavier à l'écran ou du clavier de l'ordinateur ; voix et musique d'attente facultatives.
const tel = document.querySelector('[data-telephone]');
if (tel) {
  const elEtat = tel.querySelector('[data-etat]');
  const elDuree = tel.querySelector('[data-duree]');
  const elMessage = tel.querySelector('[data-message]');
  const touches = [...tel.querySelectorAll('[data-touche]')];
  const decrocher = tel.querySelector('[data-decrocher]');
  const caseVoix = tel.querySelector('[data-voix]');
  const caseMusique = tel.querySelector('[data-musique]');
  const journal = document.querySelector('[data-journal]');
  const synthese = 'speechSynthesis' in window ? window.speechSynthesis : null;
  const auHasard = (l) => l[Math.floor(Math.random() * l.length)];
  const dort = (ms) => new Promise((r) => setTimeout(r, ms));
  if (!synthese) caseVoix.closest('label').hidden = true;

  const DELAIS = ['entre 3 jours et jamais', 'quelques instants administratifs', 'le temps d\'un café tiède',
    'à peu près une saison', 'moins que la dernière fois, sans doute', 'non communiqué, pour votre sérénité'];

  let enLigne = false;
  let debut = 0;
  let horloge = null;
  let menu = null;
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

  // ---------- Affichage, voix, historique ----------
  function noter(texte, qui = 'standard') {
    const li = document.createElement('li');
    li.className = qui === 'vous' ? 'journal-vous' : '';
    li.textContent = texte;
    journal.prepend(li);
    while (journal.children.length > 12) journal.lastChild.remove();
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

  /** Affiche un écran (texte + touches attendues). touches = { '1': () => …, autre: () => … } */
  function ecran(texte, touchesEcran = {}) {
    jeton += 1;
    menu = touchesEcran;
    couperMusique();
    dire(texte);
  }

  // ---------- Les écrans ----------
  const ACCUEIL = () => ecran(
    'Bienvenue au standard de ConseilPlus+. Votre appel est important pour nous : il sera donc traité avec importance. '
    + 'Pour un conseil, tapez 1. Pour une réclamation, tapez 2. Pour parler au stagiaire, tapez 3. '
    + 'Pour connaître votre délai, tapez 4. Pour réécouter ce message, tapez 5. Pour un conseiller, tapez dièse.',
    { '1': CONSEIL, '2': RECLAMATION, '3': STAGIAIRE, '4': DELAI, '5': ACCUEIL, '#': ATTENTE, '0': OPERATEUR, '9': PRECEDENT },
  );

  async function CONSEIL() {
    ecran('Un instant, le conseiller cherche un conseil…');
    const moi = jeton;
    let conseil = 'Patientez. Si cela ne suffit pas, patientez davantage.';
    try {
      const r = await fetch('api/conseils.php', { headers: { Accept: 'application/json' } });
      if (r.ok) conseil = (await r.json()).conseil;
    } catch { /* conseil de secours */ }
    if (moi !== jeton) return;
    ecran(`Voici votre conseil : ${conseil} Pour un autre conseil, tapez 1. Pour revenir au menu principal, tapez étoile.`,
      { '1': CONSEIL, '*': ACCUEIL });
  }

  const RECLAMATION = () => ecran(
    'Service des réclamations. Pour une réclamation concernant un conseil, tapez 1. Concernant le standard, tapez 2. '
    + 'Concernant cette réclamation, tapez 3.',
    { '1': RECLAMATION_ENREGISTREE, '2': RECLAMATION_ENREGISTREE, '3': RECLAMATION_ENREGISTREE, '*': ACCUEIL },
  );

  const RECLAMATION_ENREGISTREE = () => {
    const n = 'R-' + String(Math.floor(1000 + Math.random() * 9000));
    ecran(`Votre réclamation est enregistrée sous le numéro ${n}. Elle sera traitée par ordre de réclamation. `
      + 'Pour réclamer au sujet de ce numéro, tapez 1. Pour revenir au menu principal, tapez étoile.',
    { '1': RECLAMATION, '*': ACCUEIL });
  };

  const STAGIAIRE = async () => {
    ecran('Mise en relation avec le stagiaire…');
    const moi = jeton;
    await dort(2200);
    if (moi !== jeton) return;
    ecran('Allô ? Oui ? Ah. Je… Je ne sais pas trop quoi vous dire. Le conseiller n\'est pas là. Vous voulez un café ? '
      + 'Pour dire oui, tapez 1. Pour dire non, tapez 2.',
    { '1': MACHINE, '2': MACHINE });
  };

  const DELAI = () => ecran(
    `Bonjour, ici Mireille, responsable des délais. Votre délai estimé est : ${auHasard(DELAIS)}. `
    + 'Ce délai est indicatif, et il ne l\'indique pas. Pour un autre délai, tapez 4. Pour revenir au menu principal, tapez étoile.',
    { '4': DELAI, '*': ACCUEIL },
  );

  const OPERATEUR = () => ecran('Vous avez demandé un opérateur. L\'opérateur, c\'est vous. Pour revenir au menu principal, tapez étoile.', { '*': ACCUEIL });

  const PRECEDENT = () => ecran('Pour revenir au menu précédent, tapez 1. Vous êtes déjà au menu précédent.', { '1': PRECEDENT, '*': ACCUEIL });

  async function ATTENTE() {
    ecran('Tous nos conseillers sont occupés. Votre appel va être pris dans l\'ordre d\'arrivée, ou presque. Veuillez patienter.');
    const moi = jeton;
    jouerMusique();
    let rang = 4 + Math.floor(Math.random() * 6);
    for (let i = 0; i < 4; i++) {
      await dort(3500);
      if (moi !== jeton) return;
      rang += auHasard([-1, -1, -2, 1]);
      elMessage.textContent = `Vous êtes ${Math.max(1, rang)}${rang <= 1 ? 'er' : 'e'} dans la file d'attente. Temps d'attente estimé : ${auHasard(DELAIS)}.`;
    }
    await dort(3000);
    if (moi !== jeton) return;
    couperMusique();
    ecran('Votre interlocuteur est momentanément indisponible. Nous vous transférons vers le membre le plus disponible du cabinet.');
    const transfert = jeton;
    await dort(3500);
    if (transfert !== jeton) return;
    MACHINE();
  }

  function MACHINE() {
    ecran('Bzzz. Ici la machine à café. Café tiède en préparation. Pour un sucre, tapez 1. Pour deux sucres, tapez 1 deux fois.', {
      '1': async () => {
        ecran('Sucre ajouté. Bzzz… Clac.');
        const moi = jeton;
        await dort(2500);
        if (moi !== jeton) return;
        ecran('Gobelet coincé. Le cabinet vous remercie de votre appel. Fin de la communication.');
        const fin = jeton;
        await dort(3500);
        if (fin === jeton) raccrocher('Appel terminé par la machine à café');
      },
    });
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
    elEtat.textContent = 'Appel en cours';
    majDuree();
    horloge = setInterval(majDuree, 1000);
    touches.forEach((t) => { t.disabled = false; });
    decrocher.textContent = 'Raccrocher';
    tel.classList.add('en-ligne');
    ACCUEIL();
  }

  function raccrocher(raison = 'Vous avez raccroché') {
    enLigne = false;
    jeton += 1;
    menu = null;
    clearInterval(horloge);
    couperMusique();
    if (synthese) synthese.cancel();
    elEtat.textContent = 'Combiné raccroché';
    elMessage.textContent = `${raison}. Durée de l'appel : ${elDuree.textContent || '00:00'}. Merci d'avoir patienté.`;
    noter(raison + '.');
    touches.forEach((t) => { t.disabled = true; });
    decrocher.textContent = 'Rappeler';
    tel.classList.remove('en-ligne');
  }

  function appuyer(t) {
    if (!enLigne || !menu) return;
    noter(`Vous tapez ${t === '*' ? 'étoile' : t === '#' ? 'dièse' : t}.`, 'vous');
    const bouton = touches.find((b) => b.dataset.touche === t);
    bouton?.classList.remove('enfoncee');
    void bouton?.offsetWidth;
    bouton?.classList.add('enfoncee');
    const suite = menu[t];
    if (suite) suite();
    else dire('Ce choix n\'existe pas. Ce n\'est pas grave : les autres non plus. Pour revenir au menu principal, tapez étoile.');
    if (!suite) menu = { ...menu, '*': ACCUEIL };
  }

  decrocher.addEventListener('click', () => (enLigne ? raccrocher() : decrocherAppel()));
  touches.forEach((b) => b.addEventListener('click', () => appuyer(b.dataset.touche)));
  document.addEventListener('keydown', (e) => {
    if (/^[0-9*#]$/.test(e.key) && !e.target.closest('input, textarea, select')) appuyer(e.key);
  });

  tel.hidden = false;
}
