// Application installable : enregistre le service worker et gère le bouton « Installer » (page application.php).
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js').catch(() => {});
  });
}

const installer = document.getElementById('installer');
const etat = document.getElementById('installation-etat');
const dejaInstallee = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;

if (etat && dejaInstallee) {
  etat.textContent = "C'est fait : vous consultez déjà l'application installée.";
}

let invitation = null;
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  invitation = e;
  if (installer) installer.hidden = false;
});

installer?.addEventListener('click', async () => {
  if (!invitation) return;
  invitation.prompt();
  const { outcome } = await invitation.userChoice;
  invitation = null;
  installer.hidden = true;
  if (etat) {
    etat.textContent = outcome === 'accepted'
      ? "Installation acceptée. Le cabinet est désormais sur votre écran d'accueil."
      : 'Installation refusée. Le cabinet respecte votre décision, moyennement.';
  }
});

window.addEventListener('appinstalled', () => {
  if (etat) etat.textContent = "Installation terminée. Le cabinet est désormais sur votre écran d'accueil.";
});
