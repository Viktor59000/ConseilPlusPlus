// Abonnement à la notification quotidienne du mot (presque) du jour (Web Push, api/notifications.php).
// Android (Chrome) et ordinateur : directement ; iPhone : une fois le site ajouté à l'écran d'accueil (iOS 16.4+).
const zone = document.querySelector('[data-notifications]');
if (zone) {
  const bouton = zone.querySelector('[data-notif-bouton]');
  const etat = zone.querySelector('[data-notif-etat]');
  const dire = (texte) => { etat.textContent = texte; };
  const compatible = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

  const versOctets = (b64) => {
    const brut = atob((b64 + '='.repeat((4 - (b64.length % 4)) % 4)).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from(brut, (c) => c.charCodeAt(0));
  };
  const appeler = (corps) => fetch('api/notifications.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify(corps),
  });

  async function rafraichir() {
    const reg = await navigator.serviceWorker.ready;
    const abonnement = await reg.pushManager.getSubscription();
    bouton.dataset.abonne = abonnement ? '1' : '';
    bouton.textContent = abonnement ? 'Ne plus recevoir le mot du jour' : 'Recevoir le mot du jour chaque matin';
    if (Notification.permission === 'denied') {
      dire('Les notifications sont bloquées pour ce site : autorisez-les dans les réglages du navigateur.');
    } else {
      dire(abonnement ? 'Abonnement actif : le mot du jour arrive chaque matin, vers 7 h.' : '');
    }
  }

  if (!compatible) {
    dire(/iPhone|iPad/.test(navigator.userAgent)
      ? 'Sur iPhone, ajoutez d\'abord le site à l\'écran d\'accueil, puis ouvrez-le depuis son icône.'
      : 'Ce navigateur ne gère pas les notifications.');
  } else {
    bouton.hidden = false;
    rafraichir();
    bouton.addEventListener('click', async () => {
      bouton.disabled = true;
      try {
        const reg = await navigator.serviceWorker.ready;
        if (bouton.dataset.abonne) {
          const abonnement = await reg.pushManager.getSubscription();
          if (abonnement) {
            await appeler({ action: 'desabonner', endpoint: abonnement.endpoint });
            await abonnement.unsubscribe();
          }
        } else {
          if (await Notification.requestPermission() !== 'granted') {
            dire('Notifications refusées. Le cabinet respecte votre décision, moyennement.');
            return;
          }
          const { cle_publique: cle } = await (await fetch('api/notifications.php')).json();
          const abonnement = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: versOctets(cle) });
          const r = await appeler({ action: 'abonner', abonnement: abonnement.toJSON() });
          if (!r.ok) throw new Error(r.status);
        }
        await rafraichir();
      } catch {
        dire('L\'abonnement n\'a pas pu être enregistré. Réessayez plus tard.');
      } finally {
        bouton.disabled = false;
      }
    });
  }
}
