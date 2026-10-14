// Nouveau conseil sans recharger la page : appelle l'API api/conseils.php et met la fiche à jour.
// Sans JavaScript, le lien recharge simplement la page (ancre #recommandation).
const bouton = document.getElementById('autre-conseil');
const fiche = document.getElementById('recommandation');

bouton.addEventListener('click', async (evenement) => {
  evenement.preventDefault();
  bouton.setAttribute('aria-disabled', 'true');

  try {
    const reponse = await fetch('api/conseils.php', { headers: { Accept: 'application/json' } });
    if (!reponse.ok) throw new Error(`HTTP ${reponse.status}`);
    const conseil = await reponse.json();

    fiche.querySelector('[data-conseil="dossier"]').textContent = conseil.dossier;
    for (const morceau of ['situation', 'action', 'resultat']) {
      fiche.querySelector(`[data-conseil="${morceau}"]`).textContent = conseil.fragments[morceau];
    }

    // Rejoue l'animation du tampon
    const tampon = fiche.querySelector('.tampon');
    tampon.classList.remove('tampon-anime');
    void tampon.offsetWidth;
    tampon.classList.add('tampon-anime');
  } catch (erreur) {
    window.location.href = bouton.href; // en cas de problème, rechargement classique
  } finally {
    bouton.removeAttribute('aria-disabled');
  }
});
