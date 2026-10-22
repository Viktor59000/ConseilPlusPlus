// Le guichet : un ticket, une file qui avance au gré de l'administration, et une fermeture pile à votre tour.
(() => {
  const machine = document.querySelector('[data-machine]');
  if (machine) {
    const $ = (s) => document.querySelector(s);
    const ecranNumero = $('[data-appele]');
    const ecranPoste = $('[data-poste]');
    const elTicket = $('[data-ticket]');
    const elDevant = $('[data-devant]');
    const elAttente = $('[data-attente]');
    const bouton = $('[data-prendre]');
    const tampon = $('[data-ferme]');
    const journal = $('[data-journal]');

    const hasard = (min, max) => min + Math.floor(Math.random() * (max - min + 1));
    const auHasard = (liste) => liste[Math.floor(Math.random() * liste.length)];
    const numero = (n) => String(n).padStart(4, '0');
    const dort = (ms) => new Promise((r) => setTimeout(r, ms));

    const ATTENTES = [
      'entre 4 minutes et jamais', 'moins longtemps que la dernière fois, sans doute', 'environ un café et demi',
      'le temps de remplir le formulaire B-12', 'quelques instants administratifs', 'à peu près une saison',
      'ça dépend du guichet', 'non communiquée pour des raisons de sérénité',
      'deux chansons de Florent Pagny, version longue', 'le temps de faire un tour de rond-point, ou deux',
      'trois pauses café, réglementaires', 'jusqu\'à la prochaine réforme du guichet', 'une demi-sieste',
      'un épisode de série, sans le générique', 'calculée en jours ouvrés, à partir de demain',
      'le temps que le stagiaire retrouve le tampon', 'courte, à l\'échelle de l\'univers', 'raisonnable, pour un mardi',
      'confidentielle', 'en cours d\'estimation (prévoir un délai pour l\'estimation)',
    ];
    const INCIDENTS = [
      (p) => `Le guichet ${p} part en pause café. Il revient. Il ne revient pas.`,
      () => 'Le système informatique redémarre. Veuillez ne pas bouger.',
      () => 'Le stagiaire cherche un stylo qui fonctionne.',
      () => 'Une personne demande « juste un renseignement ». La file est suspendue.',
      () => 'Le conseiller vérifie que vous avez bien pris un ticket.',
      () => 'Merci de patienter : la patience est un service inclus.',
      (p) => `Le guichet ${p} est réservé aux personnes munies du ticket du guichet ${p}.`,
      () => 'Un ticket a été retrouvé sous la machine à café. Son propriétaire est prié de ne pas se manifester.',
      () => 'Le tampon est en rupture d\'encre. Les conseils seront validés oralement.',
      () => 'Rappel : les tickets ne sont ni repris, ni échangés, ni compris.',
      (p) => `Le guichet ${p} vient d'ouvrir. Il vient de fermer.`,
      () => 'La file d\'attente est priée de s\'aligner. Plus droit. Non, pas comme ça.',
      () => 'Le stagiaire a appelé le numéro 0000. Personne ne s\'est présenté. Il est déçu.',
      () => 'Une imprimante fait un bruit nouveau. La direction suit la situation de près.',
      () => 'Le chauffage est réglé sur « moyen ». C\'est notre politique.',
      () => 'Les personnes arrivées avant vous sont priées de rester avant vous.',
      (p) => `Le guichet ${p} passe en mode « réflexion ». Durée indéterminée.`,
      () => 'Un formulaire B-12 a été rempli correctement. Une enquête est ouverte.',
      () => 'La musique d\'attente a été remplacée par du silence d\'attente.',
      () => 'Mireille, responsable des délais, annonce un léger retard sur le retard prévu.',
      () => 'Bertrand, guichet 7, rappelle qu\'il ne s\'appelle pas « monsieur le guichet ».',
      () => 'Le distributeur de tickets s\'est mis en grève. Il réclame du papier.',
      () => 'Une plante verte a été déplacée sans autorisation. Merci de la remettre où elle s\'ennuyait.',
      () => 'Un conseil a été délivré trop vite. Toutes nos excuses, cela ne se reproduira pas.',
      () => 'Le numéro affiché est indicatif. Le vrai numéro est connu de la direction seule.',
    ];

    let appele = hasard(31, 58);
    let ticket = null;
    let seance = 0;

    function annoncer(texte) {
      const li = document.createElement('li');
      const heure = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
      li.innerHTML = `<span class="mono"></span> `;
      li.firstChild.textContent = heure;
      li.append(texte);
      journal.prepend(li);
      while (journal.children.length > 8) journal.lastChild.remove();
    }

    function afficher(poste) {
      ecranNumero.textContent = numero(appele);
      ecranNumero.classList.remove('clignote');
      void ecranNumero.offsetWidth;
      ecranNumero.classList.add('clignote');
      ecranPoste.textContent = poste ? `Guichet ${poste}` : 'Guichet —';
      if (ticket !== null) {
        const devant = Math.max(0, ticket - appele - 1);
        elDevant.textContent = devant === 0 ? 'personne, a priori' : devant;
        elAttente.textContent = auHasard(ATTENTES);
      }
    }

    async function file(ma) {
      while (ma === seance) {
        await dort(hasard(2600, 6200));
        if (ma !== seance) return;

        // C'est bientôt votre tour : le guichet ferme
        if (ticket !== null && appele === ticket - 1) {
          await dort(hasard(2500, 4000));
          if (ma !== seance) return;
          tampon.hidden = false;
          tampon.classList.add('tampon-anime');
          ecranNumero.textContent = 'FERMÉ';
          ecranPoste.textContent = 'Réouverture : 14 h 02';
          elDevant.textContent = 'sans objet';
          elAttente.textContent = 'jusqu\'à la réouverture, puis on verra';
          annoncer(`Le guichet ferme. Le numéro ${numero(ticket)} est invité à revenir à la réouverture, avec un nouveau ticket.`);
          bouton.textContent = 'Reprendre un ticket';
          bouton.hidden = false;
          return;
        }

        const tirage = Math.random();
        const poste = hasard(1, 6);
        if (tirage < 0.6) {
          appele += 1;
          afficher(poste);
          annoncer(`Numéro ${numero(appele)}, guichet ${poste}.`);
        } else if (tirage < 0.72) {
          appele = Math.max(1, appele - 1);
          afficher(poste);
          annoncer(`Erreur de saisie : retour au numéro ${numero(appele)}.`);
        } else if (tirage < 0.82) {
          annoncer(`Le numéro ${numero(appele + 1)} est attendu au guichet 7. Il n'y a pas de guichet 7.`);
        } else {
          annoncer(auHasard(INCIDENTS)(poste));
        }
      }
    }

    bouton.addEventListener('click', () => {
      seance += 1;
      tampon.hidden = true;
      tampon.classList.remove('tampon-anime');
      if (ecranNumero.textContent === 'FERMÉ') appele = hasard(31, 58);
      ticket = appele + hasard(4, 7);
      elTicket.textContent = numero(ticket);
      bouton.hidden = true;
      afficher(null);
      annoncer(`Ticket ${numero(ticket)} délivré. Merci de votre patience, elle sera mise à contribution.`);
      file(seance);
    });

    machine.hidden = false;
    afficher(null);
    annoncer('Le guichet est ouvert. Prenez un ticket.');
  }
})();
