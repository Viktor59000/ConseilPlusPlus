// « Questions pour un stagiaire » : 10 questions tirées au sort ; le stagiaire buzze, répond faux et argumente ;
// le jury (le visiteur) retient la bonne réponse ou celle du stagiaire. Le score oppose le bon sens au stagiaire.
(() => {
  const jeu = document.querySelector('[data-jeu]');
  if (!jeu) return;
  const banque = JSON.parse(document.getElementById('stagiaire-donnees').textContent);
  const $ = (s) => jeu.querySelector(s);
  const elNum = $('[data-num]');
  const elQuestion = $('[data-question]');
  const elBuzz = $('[data-buzz]');
  const elReponse = $('[data-reponse]');
  const elStagiaire = $('[data-stagiaire]');
  const elArgument = $('[data-argument]');
  const elJury = $('[data-jury]');
  const elVerdict = $('[data-verdict]');
  const suivante = $('[data-suivante]');
  const annonce = $('[data-annonce]');
  const tableau = document.querySelector('[data-score]');
  const NB = 10;
  const lent = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const dort = (ms) => new Promise((r) => setTimeout(r, lent ? ms : 0));
  const auHasard = (liste) => liste[Math.floor(Math.random() * liste.length)];
  const melanger = (liste) => liste.map((v) => [Math.random(), v]).sort((a, b) => a[0] - b[0]).map(([, v]) => v);

  let partie = [];
  let n = 0;
  let points = { bon_sens: 0, stagiaire: 0 };
  let jeton = 0; // invalide les minuteries d'une question abandonnée

  function afficherScore() {
    for (const [cle, valeur] of Object.entries(points)) {
      const dd = tableau.querySelector(`[data-points="${cle}"]`);
      if (dd.textContent === String(valeur)) continue;
      dd.textContent = valeur;
      dd.classList.remove('marque');
      void dd.offsetWidth;
      dd.classList.add('marque');
    }
  }

  async function poser(avecFocus) {
    const ce = ++jeton;
    const [question, , reponse, argument] = partie[n];
    elNum.textContent = `Question n° ${n + 1} sur ${NB}`;
    elQuestion.textContent = question;
    elBuzz.textContent = '';
    elReponse.hidden = true;
    elJury.hidden = true;
    elVerdict.textContent = '';
    suivante.hidden = true;
    jeu.classList.remove('nouvelle');
    void jeu.offsetWidth;
    jeu.classList.add('nouvelle');
    if (avecFocus) elQuestion.focus({ preventScroll: true });
    annonce.textContent = `Question ${n + 1} : ${question}`;

    await dort(700);
    if (ce !== jeton) return;
    elBuzz.textContent = auHasard(banque.buzz);
    elBuzz.classList.remove('buzz-anime');
    void elBuzz.offsetWidth;
    elBuzz.classList.add('buzz-anime');

    await dort(1100);
    if (ce !== jeton) return;
    elStagiaire.textContent = reponse;
    elArgument.textContent = `« ${argument} »`;
    elReponse.hidden = false;
    elJury.hidden = false;
    annonce.textContent = `${elBuzz.textContent} Réponse du stagiaire : ${reponse}. ${argument} Le jury retient la bonne réponse, ou celle du stagiaire.`;
  }

  function trancher(choix) {
    const bonne = partie[n][1];
    if (choix === 'bonne') {
      points.bon_sens++;
      elVerdict.textContent = `La bonne réponse était : ${bonne}. ${auHasard(banque.bonne)}`;
    } else {
      points.stagiaire++;
      elVerdict.textContent = `${auHasard(banque.stagiaire)} Pour mémoire, la bonne réponse était : ${bonne}.`;
    }
    afficherScore();
    suivante.textContent = n + 1 < NB ? 'Question suivante' : 'Résultat de la partie';
    suivante.hidden = false;
    elJury.hidden = true;
    suivante.focus({ preventScroll: true });
    annonce.textContent = `${elVerdict.textContent} Score : bon sens ${points.bon_sens}, stagiaire ${points.stagiaire}.`;
  }

  function bilan() {
    jeton++;
    const cle = points.stagiaire > points.bon_sens ? 'stagiaire' : points.bon_sens > points.stagiaire ? 'bon_sens' : 'egalite';
    elNum.textContent = 'Fin de la partie';
    elQuestion.textContent = `Le bon sens ${points.bon_sens}, le stagiaire ${points.stagiaire}.`;
    elBuzz.textContent = '';
    elReponse.hidden = true;
    elVerdict.textContent = auHasard(banque.bilans[cle]);
    suivante.textContent = 'Nouvelle partie';
    suivante.hidden = false;
    elQuestion.focus({ preventScroll: true });
    annonce.textContent = `Fin de la partie. ${elQuestion.textContent} ${elVerdict.textContent}`;
    n = NB;
  }

  function demarrer(avecFocus) {
    partie = melanger(banque.questions).slice(0, NB);
    n = 0;
    points = { bon_sens: 0, stagiaire: 0 };
    afficherScore();
    poser(avecFocus);
  }

  elJury.addEventListener('click', (e) => {
    const b = e.target.closest('[data-choix]');
    if (b && !elJury.hidden) trancher(b.dataset.choix);
  });
  suivante.addEventListener('click', () => {
    if (n >= NB) { demarrer(true); return; }
    n++;
    if (n < NB) poser(true);
    else bilan();
  });

  jeu.hidden = false;
  tableau.hidden = false;
  demarrer(false);
})();
