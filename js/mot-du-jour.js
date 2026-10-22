// Fait préparer par l'IA le mot de demain, en arrière-plan, une fois la page affichée.
// Sans effet si le mot existe déjà ; en cas d'échec, la banque du Petit Moyen prendra le relais demain.
(() => {
  window.addEventListener('load', () => {
    setTimeout(() => {
      fetch('api/mot-du-jour.php', { method: 'POST', keepalive: true }).catch(() => {});
    }, 3000);
  });
})();
