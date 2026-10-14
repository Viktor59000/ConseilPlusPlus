// Touche Tab dans un champ vide : insère la suggestion (placeholder) au lieu de quitter le champ.
// Si le champ contient déjà du texte, Tab garde son comportement normal (passer au champ suivant).
const champ = document.getElementById('d-coup');

champ?.addEventListener('keydown', (evenement) => {
  if (evenement.key === 'Tab' && !evenement.shiftKey && champ.value.trim() === '' && champ.placeholder) {
    evenement.preventDefault();
    champ.value = champ.placeholder;
    champ.setSelectionRange(champ.value.length, champ.value.length);
  }
});
