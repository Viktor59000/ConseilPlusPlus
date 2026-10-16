// Nouveau conseil sans recharger la page : appelle l'API api/conseils.php et met la fiche à jour.
// Boutons [data-autre-conseil="#fiche"] : « Mon conseil » (lien qui recharge la page sans JavaScript)
// et l'accueil (bouton caché sans JavaScript).
document.querySelectorAll('[data-autre-conseil]').forEach((bouton) => {
  const fiche = document.querySelector(bouton.dataset.autreConseil);
  if (!fiche) return;
  bouton.hidden = false;

  bouton.addEventListener('click', async (evenement) => {
    evenement.preventDefault();
    if (bouton.getAttribute('aria-disabled') === 'true') return;
    bouton.setAttribute('aria-disabled', 'true');

    try {
      const reponse = await fetch('api/conseils.php', { headers: { Accept: 'application/json' } });
      if (!reponse.ok) throw new Error(`HTTP ${reponse.status}`);
      const conseil = await reponse.json();

      fiche.querySelector('[data-conseil="dossier"]').textContent = conseil.dossier;
      for (const morceau of ['situation', 'action', 'resultat']) {
        fiche.querySelector(`[data-conseil="${morceau}"]`).textContent = conseil.fragments[morceau];
      }

      // Rejoue les animations : le texte se réécrit, le tampon retombe
      const tampon = fiche.querySelector('.tampon');
      fiche.classList.remove('renouvele');
      tampon.classList.remove('tampon-anime');
      void tampon.offsetWidth;
      fiche.classList.add('renouvele');
      tampon.classList.add('tampon-anime');

      // Prévient les autres scripts (ex. bouton « Classer au dossier ») qu'un nouveau conseil est affiché
      fiche.dispatchEvent(new Event('conseil:nouveau'));
    } catch (erreur) {
      // En cas de problème : rechargement classique (lien) ou de la page
      if (bouton.href) window.location.href = bouton.href; else window.location.reload();
    } finally {
      bouton.removeAttribute('aria-disabled');
    }
  });
});
