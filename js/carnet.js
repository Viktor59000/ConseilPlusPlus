// Bouton « Classer au dossier » : envoie le conseil affiché à l'API api/mes-conseils.php (POST).
// Le bouton est masqué sans JavaScript ; il se réactive quand un nouveau conseil s'affiche.
const jeton = document.querySelector('meta[name="csrf"]')?.content;

for (const bouton of document.querySelectorAll('[data-classer]')) {
  const fiche = document.querySelector(bouton.dataset.classer);
  const libelle = bouton.textContent;
  bouton.hidden = false;

  fiche.addEventListener('conseil:nouveau', () => {
    bouton.disabled = false;
    bouton.textContent = libelle;
  });

  bouton.addEventListener('click', async () => {
    bouton.disabled = true;
    try {
      const reponse = await fetch('api/mes-conseils.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-Token': jeton },
        body: JSON.stringify({
          texte: fiche.querySelector('.fiche-conseil').textContent,
          dossier: fiche.querySelector('[data-dossier]').textContent.trim(),
          source: bouton.dataset.source,
        }),
      });
      if (!reponse.ok) throw new Error(`HTTP ${reponse.status}`);
      bouton.textContent = 'Classé au dossier';
    } catch (erreur) {
      bouton.disabled = false;
      bouton.textContent = 'Échec du classement, réessayer';
    }
  });
}
