// Les expériences de Messmerde : il « devine » des évidences avec gravité, tamponne « PRÉVU »,
// puis le visiteur juge (« Bluffant » ou « Moyennement bluffant »). L'indice de crédibilité ne fait que monter.
const zone = document.querySelector('[data-experience]');
if (zone) {
  const $ = (s) => zone.querySelector(s);
  const elNum = $('[data-num]');
  const elTitre = $('[data-titre]');
  const elConsigne = $('[data-consigne]');
  const elScene = $('[data-scene]');
  const elRevelation = $('[data-revelation]');
  const elTampon = $('[data-tampon]');
  const elAvis = $('[data-avis]');
  const panneau = document.querySelector('[data-credibilite]');
  const elScore = panneau.querySelector('[data-score]');
  const elCommentaire = panneau.querySelector('[data-commentaire]');
  const dort = (ms) => new Promise((r) => setTimeout(r, ms));
  const deuxChiffres = (n) => String(n).padStart(2, '0');

  let score = 100;
  let etape = 0;
  let nettoyer = () => {}; // retire les écouteurs propres à une expérience

  /** Un bouton d'action dans la scène. */
  function bouton(texte, classe = 'btn btn-tampon') {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = classe;
    b.textContent = texte;
    elScene.append(b);
    return b;
  }

  /** Messmerde se concentre, puis révèle. */
  async function reveler(texte, { concentration = true } = {}) {
    elScene.querySelectorAll('button').forEach((b) => { b.disabled = true; });
    if (concentration) {
      elRevelation.textContent = 'Concentration…';
      elRevelation.classList.add('concentre');
      await dort(1800);
      elRevelation.classList.remove('concentre');
    }
    elRevelation.textContent = texte;
    elTampon.hidden = false;
    elTampon.classList.remove('tampon-anime');
    void elTampon.offsetWidth;
    elTampon.classList.add('tampon-anime');
    await dort(600);
    elAvis.hidden = false;
  }

  const EXPERIENCES = [
    {
      titre: 'Le nombre',
      consigne: 'Pensez très fort à un nombre entre 1 et 10. Ne me le dites surtout pas.',
      jouer() {
        bouton("J'y pense").addEventListener('click', () => reveler('Votre nombre est… inférieur à 11.'));
      },
    },
    {
      titre: "L'heure",
      consigne: "Je vais ressentir l'heure qu'il est chez vous, sans regarder la moindre montre.",
      jouer() {
        bouton('Ressentez').addEventListener('click', async () => {
          const d = new Date();
          await reveler(`Il est ${d.getHours()} h ${deuxChiffres(d.getMinutes())}… et ${d.getSeconds()} secondes.`);
          await dort(1200);
          elRevelation.textContent += ` Maintenant ${new Date().getSeconds()}. Je le sens encore.`;
        });
      },
    },
    {
      titre: 'Les cartes',
      consigne: 'Choisissez une carte, au hasard ou non. Ne me dites pas laquelle.',
      jouer() {
        const jeu = document.createElement('div');
        jeu.className = 'cartes';
        elScene.append(jeu);
        for (let i = 0; i < 3; i++) {
          const carte = document.createElement('button');
          carte.type = 'button';
          carte.className = 'carte-mystere';
          carte.setAttribute('aria-label', `Carte ${i + 1}`);
          jeu.append(carte);
          carte.addEventListener('click', () => {
            jeu.querySelectorAll('.carte-mystere').forEach((c) => c.classList.toggle('ecartee', c !== carte));
            carte.classList.add('retournee');
            carte.textContent = 'Celle-ci';
            reveler('Vous avez choisi… celle-ci.');
          });
        }
      },
    },
    {
      titre: 'Le bouton',
      consigne: 'Je prédis que vous allez appuyer sur ce bouton.',
      jouer() {
        const b = bouton('Ce bouton');
        let revele = false;
        b.addEventListener('click', () => {
          if (revele) return;
          revele = true;
          reveler('Vous avez appuyé. Je le savais.', { concentration: false });
        });
        const minuterie = setTimeout(async () => {
          if (revele) return;
          revele = true;
          await reveler("Vous n'avez pas appuyé. Je savais aussi que vous hésiteriez.", { concentration: false });
          b.disabled = false;
          b.addEventListener('click', () => {
            elRevelation.textContent = 'Et maintenant, vous appuyez quand même. Prévu.';
            b.disabled = true;
          }, { once: true });
        }, 9000);
        nettoyer = () => clearTimeout(minuterie);
      },
    },
    {
      titre: "L'appareil",
      consigne: 'Je capte les ondes de votre appareil. Ne bougez plus.',
      jouer() {
        bouton('Captez').addEventListener('click', () => reveler(window.matchMedia('(pointer: coarse)').matches
          ? 'Vous touchez un écran avec un doigt. Très probablement le vôtre.'
          : 'Une de vos mains tient une souris. Ou un pavé tactile. Ou rien. Une des trois.'));
      },
    },
    {
      titre: 'La lecture',
      consigne: 'Sans vous voir, je vais dire ce que vous êtes en train de faire, en ce moment précis.',
      jouer() {
        bouton('Dites-le').addEventListener('click', () => reveler('Vous lisez cette phrase.'));
      },
    },
    {
      titre: 'Le départ',
      consigne: "Dernière expérience. Je prédis que vous allez quitter cette page. Changez d'onglet, puis revenez.",
      jouer() {
        const titreInitial = document.title;
        let parti = false;
        const onVisibilite = () => {
          if (document.hidden) {
            parti = true;
            setTimeout(() => { document.title = 'PRÉVU. — Messmerde'; }, 0); // après le titre de site.js
          } else if (parti) {
            document.title = titreInitial;
            document.removeEventListener('visibilitychange', onVisibilite);
            clearTimeout(minuterie);
            reveler('Vous êtes parti, puis revenu. Les deux étaient prévus.', { concentration: false });
          }
        };
        document.addEventListener('visibilitychange', onVisibilite);
        const minuterie = setTimeout(() => {
          if (parti) return;
          elRevelation.textContent = 'Je sens une résistance. Elle aussi était prévue.';
          bouton('Je refuse de partir', 'btn btn-clair').addEventListener('click', () => {
            document.removeEventListener('visibilitychange', onVisibilite);
            reveler("Vous êtes resté. C'était le plan B. Prévu.", { concentration: false });
          });
        }, 15000);
        nettoyer = () => {
          clearTimeout(minuterie);
          document.removeEventListener('visibilitychange', onVisibilite);
          document.title = titreInitial;
        };
      },
    },
  ];

  function lancer(n) {
    nettoyer();
    nettoyer = () => {};
    etape = n;
    const e = EXPERIENCES[n];
    elNum.textContent = `Expérience n° ${n + 1} sur ${EXPERIENCES.length}`;
    elTitre.textContent = e.titre;
    elConsigne.textContent = e.consigne;
    elScene.replaceChildren();
    elRevelation.textContent = '';
    elTampon.hidden = true;
    elAvis.hidden = true;
    zone.classList.remove('nouvelle');
    void zone.offsetWidth;
    zone.classList.add('nouvelle');
    e.jouer();
  }

  function bilan() {
    nettoyer();
    elNum.textContent = 'Bilan officiel';
    elTitre.textContent = `${EXPERIENCES.length} expériences sur ${EXPERIENCES.length} réussies`;
    elConsigne.textContent = 'Messmerde ne s\'est trompé sur rien, ce qui confirme sa méthode : ne prédire que ce qui est sûr.';
    elScene.replaceChildren();
    elRevelation.textContent = `Je savais que vous iriez jusqu'au bout. Crédibilité finale : ${score} %.`;
    elTampon.hidden = false;
    elAvis.hidden = true;
    bouton('Recommencer les expériences', 'btn btn-clair').addEventListener('click', () => {
      score = 100;
      elScore.textContent = '100 %';
      elCommentaire.textContent = 'Calculé par Messmerde lui-même.';
      lancer(0);
    });
  }

  elAvis.addEventListener('click', (ev) => {
    const choix = ev.target.closest('[data-verdict]');
    if (!choix) return;
    if (choix.dataset.verdict === 'bluffant') {
      score += 7;
      elCommentaire.textContent = 'Messmerde vous remercie. Il savait que vous diriez ça.';
    } else {
      score += 3;
      elCommentaire.textContent = 'Messmerde prend « moyennement » comme un compliment. Au cabinet, c\'en est un.';
    }
    elScore.textContent = `${score} %`;
    panneau.classList.remove('monte');
    void panneau.offsetWidth;
    panneau.classList.add('monte');
    if (etape + 1 < EXPERIENCES.length) lancer(etape + 1);
    else bilan();
  });

  zone.hidden = false;
  panneau.hidden = false;
  lancer(0);
}
