// Les expériences de Messmerde : il « devine » des évidences avec gravité, tamponne « PRÉVU »,
// puis le visiteur juge (« Bluffant » ou « Moyennement bluffant »). L'indice de crédibilité ne fait que monter.
// Chaque séance tire 7 expériences au sort dans le répertoire (toujours un interrogatoire, toujours « Le départ » à la fin).
// Plus bas : les prédictions rétroactives (une autre prédiction, certificat horodaté après coup).
(() => {
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
    const melanger = (liste) => liste.map((v) => [Math.random(), v]).sort((a, b) => a[0] - b[0]).map(([, v]) => v);
    const auHasard = (liste) => liste[Math.floor(Math.random() * liste.length)];
    const arrivee = Date.now();
    const JOURS = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

    let score = 100;
    let etape = 0;
    let seance = [];
    let nettoyer = () => {}; // retire les écouteurs et minuteries propres à une expérience

    /** Un bouton d'action dans la scène. */
    function bouton(texte, classe = 'btn btn-tampon') {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = classe;
      b.textContent = texte;
      elScene.append(b);
      return b;
    }

    /** Un champ de saisie dans la scène (avec son libellé). */
    function champ(libelle, attributs) {
      const id = 'champ-' + Math.random().toString(36).slice(2, 8);
      const l = document.createElement('label');
      l.className = 'experience-champ';
      l.htmlFor = id;
      l.textContent = libelle;
      const i = document.createElement('input');
      i.id = id;
      Object.assign(i, attributs);
      l.append(i);
      elScene.append(l);
      return i;
    }

    /** Messmerde se concentre, puis révèle. */
    async function reveler(texte, { concentration = 'Concentration…' } = {}) {
      elScene.querySelectorAll('button, input').forEach((b) => { b.disabled = true; });
      if (concentration) {
        elRevelation.textContent = concentration;
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

    // ---------- L'interrogatoire : des questions, puis une « déduction » qui répète vos réponses ----------
    // [question, affirmation si oui, affirmation si non]
    const QUESTIONS = [
      ['Possédez-vous une plante verte ?', 'Vous possédez une plante verte.', 'Vous ne possédez aucune plante verte.'],
      ['Avez-vous déjà raté un bus ?', 'Vous avez déjà raté un bus.', "Vous n'avez jamais raté de bus."],
      ['Mettez-vous du sucre dans votre café ?', 'Vous sucrez votre café.', 'Vous ne sucrez pas votre café.'],
      ['Êtes-vous plutôt du matin ?', 'Vous êtes du matin.', "Vous n'êtes pas du matin."],
      ['Avez-vous un cousin ?', 'Vous avez un cousin.', "Vous n'avez pas de cousin."],
      ['Savez-vous faire du vélo ?', 'Vous savez faire du vélo.', 'Vous ne savez pas faire du vélo.'],
      ['Avez-vous déjà mangé une soupe en été ?', 'Vous avez déjà mangé une soupe en été.', "Vous n'avez jamais mangé de soupe en été."],
      ['Possédez-vous un tire-bouchon ?', 'Vous possédez un tire-bouchon.', "Vous n'avez pas de tire-bouchon."],
      ['Avez-vous déjà dit « à tes souhaits » à un inconnu ?', 'Vous avez déjà dit « à tes souhaits » à un inconnu.', "Vous n'avez jamais dit « à tes souhaits » à un inconnu."],
      ['Préférez-vous la croûte à la mie ?', 'Vous préférez la croûte.', 'Vous préférez la mie.'],
      ['Avez-vous déjà perdu une chaussette au lavage ?', 'Vous avez déjà perdu une chaussette au lavage.', "Vous n'avez jamais perdu de chaussette au lavage."],
      ['Êtes-vous du signe du Taureau ?', 'Vous êtes Taureau.', "Vous n'êtes pas Taureau."],
      ['Dormez-vous avec une couverture ?', 'Vous dormez avec une couverture.', 'Vous dormez sans couverture.'],
      ['Avez-vous déjà vu un escargot ?', 'Vous avez déjà vu un escargot.', "Vous n'avez jamais vu d'escargot. Inquiétant."],
      ['Possédez-vous une clé USB perdue quelque part ?', 'Vous avez une clé USB perdue quelque part.', "Vous savez où sont toutes vos clés USB."],
      ['Avez-vous déjà applaudi à l\'atterrissage d\'un avion ?', "Vous avez déjà applaudi à l'atterrissage d'un avion.", "Vous n'avez jamais applaudi à l'atterrissage d'un avion."],
      ['Faites-vous la vaisselle tout de suite après manger ?', 'Vous faites la vaisselle tout de suite.', 'Vous laissez tremper.'],
      ['Avez-vous plus de trois oreillers ?', "Vous avez plus de trois oreillers.", 'Vous avez trois oreillers ou moins.'],
      ['Savez-vous siffler ?', 'Vous savez siffler.', 'Vous ne savez pas siffler.'],
      ['Avez-vous déjà parlé à votre imprimante ?', 'Vous avez déjà parlé à votre imprimante.', "Vous n'avez jamais parlé à votre imprimante. Elle, si."],
      ['Préférez-vous les chats aux chiens ?', 'Vous préférez les chats.', 'Vous ne préférez pas les chats.'],
      ['Avez-vous un tiroir à câbles ?', 'Vous avez un tiroir à câbles.', "Vous n'avez pas de tiroir à câbles."],
      ['Avez-vous déjà fait semblant de comprendre une blague ?', 'Vous avez déjà fait semblant de comprendre une blague.', "Vous comprenez toutes les blagues. Même celle-ci."],
      ['Mangez-vous les frites avec les doigts ?', 'Vous mangez les frites avec les doigts.', 'Vous mangez les frites à la fourchette.'],
      ['Avez-vous déjà gagné à un jeu à gratter ?', 'Vous avez déjà gagné à un jeu à gratter.', "Vous n'avez jamais gagné à un jeu à gratter."],
      ['Portez-vous une montre en ce moment ?', 'Vous portez une montre.', 'Vous ne portez pas de montre.'],
      ['Avez-vous déjà monté un meuble sans la notice ?', 'Vous avez déjà monté un meuble sans la notice.', 'Vous lisez les notices.'],
      ['Êtes-vous déjà allé à Châteauroux ?', 'Vous êtes déjà allé à Châteauroux.', "Vous n'êtes jamais allé à Châteauroux."],
      ['Rangez-vous le lait dans la porte du frigo ?', 'Vous rangez le lait dans la porte du frigo.', 'Vous ne rangez pas le lait dans la porte du frigo.'],
      ['Avez-vous déjà éternué plus de trois fois de suite ?', 'Vous avez déjà éternué plus de trois fois de suite.', "Vous n'avez jamais éternué plus de trois fois de suite."],
    ];

    const interrogatoire = {
      cle: 'interrogatoire',
      titre: "L'interrogatoire",
      consigne: 'Répondez honnêtement. Messmerde va recouper vos réponses et en tirer une conclusion.',
      jouer() {
        const tirage = melanger(QUESTIONS).slice(0, 5);
        const reponses = [];
        const question = document.createElement('p');
        question.className = 'experience-question';
        elScene.append(question);
        const oui = bouton('Oui', 'btn btn-clair');
        const non = bouton('Non', 'btn btn-clair');
        const poser = () => {
          question.textContent = `${reponses.length + 1}. ${tirage[reponses.length][0]}`;
        };
        const repondre = async (r) => {
          reponses.push(r);
          if (reponses.length < tirage.length) {
            poser();
            return;
          }
          question.textContent = '';
          const nbOui = reponses.filter(Boolean).length;
          const i = Math.floor(Math.random() * tirage.length);
          const deduction = Math.random() < 0.75
            ? `Je le sens… ${tirage[i][reponses[i] ? 1 : 2]}`
            : `Vous avez répondu « oui » ${nbOui} fois et « non » ${tirage.length - nbOui} fois. Je le savais avant vous.`;
          reveler(deduction, { concentration: 'Je rassemble les indices…' });
        };
        oui.addEventListener('click', () => repondre(true));
        non.addEventListener('click', () => repondre(false));
        poser();
      },
    };

    // ---------- Le répertoire ----------
    const REPERTOIRE = [
      {
        titre: 'Le nombre',
        consigne: 'Pensez très fort à un nombre entre 1 et 10. Ne me le dites surtout pas.',
        jouer() { bouton("J'y pense").addEventListener('click', () => reveler('Votre nombre est… inférieur à 11.')); },
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
        jouer() { bouton('Dites-le').addEventListener('click', () => reveler('Vous lisez cette phrase.')); },
      },
      {
        titre: 'Le prénom',
        consigne: 'Je vais deviner la première lettre de votre prénom. Écrivez-le, je ne regarderai pas.',
        jouer() {
          const i = champ('Votre prénom', { type: 'text', maxLength: 40, autocomplete: 'given-name' });
          bouton('Devinez').addEventListener('click', () => {
            const lettre = i.value.trim().charAt(0).toUpperCase();
            reveler(lettre ? `Votre prénom commence par… « ${lettre} ».` : "Vous n'avez rien écrit. Je l'avais vu venir.");
          });
        },
      },
      {
        titre: "L'année",
        consigne: 'Écrivez votre année de naissance. Je vais en déduire votre année de naissance.',
        jouer() {
          const i = champ('Année de naissance', { type: 'number', min: 1900, max: new Date().getFullYear(), inputMode: 'numeric' });
          bouton('Déduisez').addEventListener('click', () => {
            const a = parseInt(i.value, 10);
            reveler(a ? `Vous êtes né en… ${a}.` : 'Vous êtes né. Je le sens très fort.');
          });
        },
      },
      {
        titre: 'Le jour',
        consigne: "Je vais deviner quel jour nous sommes, en me fiant uniquement à mon intuition.",
        jouer() {
          bouton('Devinez').addEventListener('click', () => reveler(`Nous sommes… ${JOURS[new Date().getDay()]}. Comme hier, mais un jour plus tard.`));
        },
      },
      {
        titre: 'Le ciel',
        consigne: 'Sans regarder par votre fenêtre, je vais dire s\'il fait jour ou nuit chez vous.',
        jouer() {
          const h = new Date().getHours();
          bouton('Regardez pour moi').addEventListener('click', () => reveler(h >= 7 && h < 20
            ? 'Il fait jour. Ou alors vous avez beaucoup de lampes.'
            : 'Il fait nuit. Je la sens, épaisse et noire.'));
        },
      },
      {
        titre: 'La durée',
        consigne: 'Je sais exactement depuis combien de temps vous êtes sur cette page.',
        jouer() {
          bouton('Prouvez-le').addEventListener('click', () => {
            const s = Math.round((Date.now() - arrivee) / 1000);
            reveler(`Depuis ${Math.floor(s / 60)} minute${s >= 120 ? 's' : ''} et ${s % 60} seconde${s % 60 > 1 ? 's' : ''}. Un peu plus, maintenant.`);
          });
        },
      },
      {
        titre: 'La pensée',
        consigne: 'Pensez à quelque chose. Peu importe quoi. Gardez-le pour vous.',
        jouer() { bouton("C'est fait").addEventListener('click', () => reveler('Vous pensez à quelque chose.')); },
      },
      {
        titre: 'Le clin d\'œil',
        consigne: 'Ne faites rien. Dans les secondes qui viennent, vous allez cligner des yeux.',
        jouer() {
          const minuterie = setTimeout(() => reveler('Vous venez de cligner des yeux. Peut-être deux fois.', { concentration: false }), 6000);
          nettoyer = () => clearTimeout(minuterie);
        },
      },
      {
        titre: 'Le dé',
        consigne: 'Je vais lancer un dé, puis prédire le résultat avec une précision absolue.',
        jouer() {
          bouton('Lancez le dé').addEventListener('click', async () => {
            const n = 1 + Math.floor(Math.random() * 6);
            elRevelation.textContent = `Le dé indique ${n}.`;
            await dort(1400);
            reveler(`Je prédis… ${n}. Exactement ce que j'avais prédit.`, { concentration: false });
          });
        },
      },
      {
        titre: 'La touche',
        consigne: "Appuyez sur n'importe quelle touche de votre clavier, ou écrivez une lettre ci-dessous.",
        jouer() {
          const i = champ('Une lettre', { type: 'text', maxLength: 1, autocomplete: 'off' });
          const deviner = (lettre) => {
            document.removeEventListener('keydown', surTouche);
            reveler(`Vous avez choisi la touche « ${lettre.toUpperCase()} ». Je l'ai senti dans mes doigts.`);
          };
          const surTouche = (e) => { if (e.key.length === 1 && e.target !== i) deviner(e.key); };
          i.addEventListener('input', () => { if (i.value) deviner(i.value); });
          document.addEventListener('keydown', surTouche);
          nettoyer = () => document.removeEventListener('keydown', surTouche);
        },
      },
      {
        titre: 'La langue',
        consigne: 'Je vais deviner la langue que vous parlez, sans vous entendre.',
        jouer() {
          bouton('Devinez').addEventListener('click', () => reveler((navigator.language || 'fr').startsWith('fr')
            ? 'Vous parlez français. Je l\'ai compris dès la première phrase.'
            : 'Vous lisez le français. C\'est déjà ça.'));
        },
      },
      {
        titre: "L'écran",
        consigne: 'Je vais mesurer votre écran à distance, par la seule force de mon regard.',
        jouer() {
          bouton('Mesurez').addEventListener('click', () => reveler(`Votre fenêtre mesure ${window.innerWidth} pixels de large. À un pixel près, comme toujours.`));
        },
      },
      {
        titre: 'Le site',
        consigne: 'Je vais deviner sur quel site vous vous trouvez.',
        jouer() { bouton('Devinez').addEventListener('click', () => reveler('Vous êtes sur ConseilPlus+. Plus précisément, chez moi.')); },
      },
      {
        titre: 'Le choix',
        consigne: 'Gauche ou droite ? Choisissez librement. Votre libre arbitre est entre vos mains.',
        jouer() {
          const g = bouton('Gauche', 'btn btn-clair');
          const d = bouton('Droite', 'btn btn-clair');
          g.addEventListener('click', () => reveler('Vous avez choisi gauche. Votre libre arbitre ne m\'a rien caché.'));
          d.addEventListener('click', () => reveler('Vous avez choisi droite. Votre libre arbitre ne m\'a rien caché.'));
        },
      },
      interrogatoire,
    ];

    const DEPART = {
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
    };

    /** Une séance : 5 tours au hasard + l'interrogatoire placé au hasard + le départ en dernier. */
    function tirerSeance() {
      const tours = melanger(REPERTOIRE.filter((e) => e !== interrogatoire)).slice(0, 5);
      tours.splice(1 + Math.floor(Math.random() * 4), 0, interrogatoire);
      return [...tours, DEPART];
    }

    // Le focus suit l'expérience (les boutons qui l'avaient disparaissent) : lu par les lecteurs d'écran
    function focaliser() {
      elTitre.tabIndex = -1;
      elTitre.focus({ preventScroll: true });
      zone.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function lancer(n, avecFocus = true) {
      nettoyer();
      nettoyer = () => {};
      etape = n;
      const e = seance[n];
      elNum.textContent = `Expérience n° ${n + 1} sur ${seance.length}`;
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
      if (avecFocus) focaliser();
    }

    function bilan() {
      nettoyer();
      elNum.textContent = 'Bilan officiel';
      elTitre.textContent = `${seance.length} expériences sur ${seance.length} réussies`;
      elConsigne.textContent = auHasard([
        "Messmerde ne s'est trompé sur rien, ce qui confirme sa méthode : ne prédire que ce qui est sûr.",
        'Aucune erreur à déplorer. Messmerde attribue ce résultat à son talent, et à la nature des questions.',
        "Messmerde rappelle que le cabinet décline toute responsabilité en cas d'évidence.",
      ]);
      elScene.replaceChildren();
      elRevelation.textContent = `Je savais que vous iriez jusqu'au bout. Crédibilité finale : ${score} %.`;
      elTampon.hidden = false;
      elAvis.hidden = true;
      bouton('Une nouvelle séance', 'btn btn-clair').addEventListener('click', () => demarrer(true));
      focaliser();
    }

    function demarrer(avecFocus = false) {
      score = 100;
      elScore.textContent = '100 %';
      elCommentaire.textContent = 'Calculé par Messmerde lui-même.';
      seance = tirerSeance();
      lancer(0, avecFocus);
    }

    elAvis.addEventListener('click', (ev) => {
      const choix = ev.target.closest('[data-verdict]');
      if (!choix) return;
      if (choix.dataset.verdict === 'bluffant') {
        score += 7;
        elCommentaire.textContent = auHasard(['Messmerde vous remercie. Il savait que vous diriez ça.', 'Messmerde accepte votre admiration. Il l\'avait réservée.']);
      } else {
        score += 3;
        elCommentaire.textContent = auHasard(['Messmerde prend « moyennement » comme un compliment. Au cabinet, c\'en est un.', 'Moyennement bluffant : la note maximale du cabinet. Messmerde est ému.']);
      }
      elScore.textContent = `${score} %`;
      panneau.classList.remove('monte');
      void panneau.offsetWidth;
      panneau.classList.add('monte');
      if (etape + 1 < seance.length) lancer(etape + 1);
      else bilan();
    });

    // Clavier et lecteur d'écran : si l'élément qui avait le focus disparaît (bouton retiré, carte cachée),
    // le focus passe à la prochaine action visible de l'expérience, sinon à la révélation (annoncée par aria-live).
    let focusDansZone = false;
    document.addEventListener('focusin', (ev) => { focusDansZone = zone.contains(ev.target); });
    zone.addEventListener('click', () => { focusDansZone = true; }, true);
    document.addEventListener('click', (ev) => { if (!zone.contains(ev.target)) focusDansZone = false; }, true);
    function recadrer() {
      const actif = document.activeElement;
      if (!focusDansZone) return;
      if (zone.contains(actif) && actif.offsetParent && !actif.disabled) return;
      if (actif !== document.body && !zone.contains(actif)) return;
      const suivant = [...zone.querySelectorAll('button, input, [tabindex="0"]')].find((e) => e.offsetParent && !e.disabled);
      const cible = suivant || elRevelation;
      if (!suivant) elRevelation.tabIndex = -1;
      cible.focus({ preventScroll: true });
    }
    new MutationObserver(() => setTimeout(recadrer, 0))
      .observe(zone, { childList: true, subtree: true, attributes: true, attributeFilter: ['hidden', 'disabled', 'class'] });

    zone.hidden = false;
    panneau.hidden = false;
    demarrer();
  }

  // ---------- Les prédictions rétroactives : ce qui vient d'arriver, certifié « PRÉVU » quelques secondes après ----------
  const autre = document.querySelector('[data-retro-autre]');
  if (autre) {
    const predictions = JSON.parse(document.getElementById('retroactives-donnees').textContent);
    const champ = (cle) => document.querySelector(`[data-retro="${cle}"]`);
    const tampon = document.querySelector('[data-retro-tampon]');
    const statut = document.querySelector('[data-retro-statut]');
    const deux = (n) => String(n).padStart(2, '0');
    const jour = (d) => `${deux(d.getDate())}/${deux(d.getMonth() + 1)}/${d.getFullYear()} à ${deux(d.getHours())} h ${deux(d.getMinutes())}`;
    const duree = (s) => {
      for (const [unite, nom] of [[86400, 'jour'], [3600, 'heure'], [60, 'minute'], [1, 'seconde']]) {
        if (s >= unite) { const n = Math.floor(s / unite); return `${n} ${nom}${n > 1 ? 's' : ''}`; }
      }
      return '0 seconde';
    };
    let actuelle = champ('texte').textContent;

    autre.addEventListener('click', () => {
      const choix = predictions.filter((p) => p[2] !== actuelle);
      const [secondes, quand, texte] = choix[Math.floor(Math.random() * choix.length)];
      actuelle = texte;
      const depot = new Date(Date.now() + 3000); // le certificat est toujours déposé après l'événement
      const evenement = new Date(depot - (secondes + 3) * 1000);
      champ('numero').textContent = `PR-${depot.getFullYear()}-${1000 + Math.floor(Math.random() * 9000)}`;
      champ('quand').textContent = quand;
      champ('texte').textContent = texte;
      champ('evenement').textContent = jour(evenement);
      champ('depot').textContent = `${jour(depot)} min ${deux(depot.getSeconds())} s`;
      champ('ecart').textContent = duree(secondes + 3);
      tampon.classList.remove('tampon-anime');
      void tampon.offsetWidth;
      tampon.classList.add('tampon-anime');
      statut.textContent = `${texte} Certificat « Prévu » déposé le ${champ('depot').textContent}.`;
    });
    autre.hidden = false;
  }
})();
