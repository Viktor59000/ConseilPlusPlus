// Documentation de l'API (developpeurs.php) : le bouton « Essayer » appelle un endpoint public en GET
// et affiche le code HTTP, la durée et la réponse JSON. Sans JavaScript, les commandes curl restent affichées.
(() => {
  document.querySelectorAll('[data-essayer]').forEach((bouton) => {
    const resultat = document.getElementById(bouton.getAttribute('aria-controls'));
    bouton.hidden = false;
    bouton.addEventListener('click', async () => {
      bouton.disabled = true;
      bouton.textContent = 'Appel en cours…';
      resultat.hidden = false;
      resultat.textContent = '';
      const debut = performance.now();
      try {
        const reponse = await fetch(bouton.dataset.essayer, { headers: { Accept: 'application/json' } });
        const texte = await reponse.text();
        let joli = texte;
        try { joli = JSON.stringify(JSON.parse(texte), null, 2); } catch { /* réponse non JSON : affichée telle quelle */ }
        resultat.textContent = `HTTP ${reponse.status} · ${Math.round(performance.now() - debut)} ms\n\n${joli}`;
      } catch {
        resultat.textContent = 'Le cabinet ne répond pas. Réessayez dans une minute (il se réveille peut-être).';
      } finally {
        bouton.disabled = false;
        bouton.textContent = 'Réessayer';
      }
    });
  });
})();
