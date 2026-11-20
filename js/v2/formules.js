// Nos formules (produit.php) : le bouton « Souscrire à Premium » ouvre la fenêtre du paiement refusé au lieu d'aller
// au guichet, et le tampon « Refusé » s'abat (window.Guichet). Sans JavaScript, le bouton mène au guichet.
(() => {
  document.querySelectorAll('[data-ouvre-dialogue]').forEach((lien) => {
    const dialogue = document.getElementById(lien.dataset.ouvreDialogue);
    if (!dialogue || typeof dialogue.showModal !== 'function') return;
    lien.addEventListener('click', (e) => {
      e.preventDefault();
      dialogue.showModal();
      const tampon = dialogue.querySelector('.tampon');
      if (tampon && window.Guichet) setTimeout(() => window.Guichet.tamponner(tampon), 120);
    });
    // Un clic sur le fond assombri ferme la fenêtre (Échap aussi, nativement)
    dialogue.addEventListener('click', (e) => {
      const r = dialogue.getBoundingClientRect();
      const dehors = e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
      if (e.target === dialogue && dehors) dialogue.close();
    });
  });
})();
