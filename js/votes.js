// Votes « Moyen » / « Pas moyen » sur les entrées du Petit Moyen (API api/votes.php).
// Le vote de chaque visiteur est rappelé dans le navigateur (localStorage) pour surligner son choix.
(() => {
  const blocs = [...document.querySelectorAll('[data-votes]')];

  function mesVotes() {
    try { return JSON.parse(localStorage.getItem('petit-moyen-votes') || '{}'); } catch { return {}; }
  }

  function memoriser(mot, vote) {
    try {
      const v = mesVotes();
      v[mot] = vote;
      localStorage.setItem('petit-moyen-votes', JSON.stringify(v));
    } catch { /* stockage indisponible : le vote est tout de même enregistré par le cabinet */ }
  }

  function afficher(bloc, comptes, monVote) {
    for (const choix of ['moyen', 'pas_moyen']) {
      bloc.querySelector(`[data-compte="${choix}"]`).textContent = comptes?.[choix] ?? 0;
      bloc.querySelector(`[data-vote="${choix}"]`).setAttribute('aria-pressed', String(monVote === choix));
    }
  }

  if (blocs.length) {
    const mots = [...new Set(blocs.map((b) => b.dataset.mot))];
    fetch('api/votes.php?mots=' + encodeURIComponent(mots.join(',')), { headers: { Accept: 'application/json' } })
      .then((r) => (r.ok ? r.json() : {}))
      .catch(() => ({}))
      .then((comptes) => {
        const miens = mesVotes();
        for (const bloc of blocs) {
          afficher(bloc, comptes[bloc.dataset.mot], miens[bloc.dataset.mot]);
          bloc.hidden = false;
        }
      });

    for (const bloc of blocs) {
      bloc.addEventListener('click', async (e) => {
        const bouton = e.target.closest('[data-vote]');
        if (!bouton) return;
        const mot = bloc.dataset.mot;
        const vote = bouton.dataset.vote;
        bloc.querySelectorAll('button').forEach((b) => { b.disabled = true; });
        try {
          const r = await fetch('api/votes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ mot, vote }),
          });
          if (!r.ok) throw new Error(r.status);
          const comptes = await r.json();
          memoriser(mot, vote);
          // Le même mot peut apparaître deux fois sur la page (mot du jour + feuilleter) : tout est mis à jour
          blocs.filter((b) => b.dataset.mot === mot).forEach((b) => afficher(b, comptes, vote));
        } catch {
          bouton.title = 'Vote non enregistré, réessayez plus tard.';
        } finally {
          bloc.querySelectorAll('button').forEach((b) => { b.disabled = false; });
        }
      });
    }
  }
})();
