// Service worker de ConseilPlus+ (application installée).
// - Pages publiques et mot du jour : réseau d'abord, copie de secours si le téléphone est hors ligne
//   (ou si le site gratuit met trop longtemps à se réveiller).
// - Polices, images, scripts : cache d'abord, mis à jour en arrière-plan.
// - Espace client, formulaires, API d'écriture : jamais mis en cache.
const VERSION = 'conseilplus-v2';
const HORS_LIGNE = 'hors-ligne.html';
const A_PRECHARGER = [
  HORS_LIGNE,
  'assets/logo.svg',
  'assets/fonts/archivo-black.woff2',
  'assets/fonts/ibm-plex-sans.woff2',
  'assets/fonts/ibm-plex-mono-500.woff2',
  'assets/icons/icon-192.png',
];
const PAGES_PUBLIQUES = ['', 'index.php', 'petit-moyen.php', 'produit.php'];
const DELAI_RESEAU = 6000; // au-delà, on montre la copie de secours (le réseau continue de la rafraîchir)

const base = new URL('./', self.location).pathname;
const chemin = (url) => url.pathname.slice(base.length);

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(VERSION).then((c) => c.addAll(A_PRECHARGER)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((cles) => Promise.all(cles.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

/** Réseau d'abord ; met la réponse en cache ; repli sur le cache si le réseau échoue ou traîne. */
async function reseauDabord(requete, cle) {
  const cache = await caches.open(VERSION);
  const reseau = fetch(requete).then((r) => {
    if (r.ok) cache.put(cle, r.clone());
    return r;
  });
  reseau.catch(() => {}); // l'échec est géré plus bas, ou sans importance si la copie a déjà été servie
  const copie = await cache.match(cle);
  if (!copie) return reseau;
  const delai = new Promise((r) => setTimeout(() => r(copie), DELAI_RESEAU));
  return Promise.race([reseau.catch(() => copie), delai]);
}

/** Cache d'abord, rafraîchi en arrière-plan. */
async function cacheDabord(requete) {
  const cache = await caches.open(VERSION);
  const copie = await cache.match(requete);
  const reseau = fetch(requete).then((r) => {
    if (r.ok) cache.put(requete, r.clone());
    return r;
  });
  return copie || reseau;
}

self.addEventListener('fetch', (e) => {
  const requete = e.request;
  const url = new URL(requete.url);
  if (requete.method !== 'GET' || url.origin !== self.location.origin || !url.pathname.startsWith(base)) return;
  const p = chemin(url);

  if (requete.mode === 'navigate') {
    if (PAGES_PUBLIQUES.includes(p)) {
      // Clé sans paramètres (?source=appli, ?page=…) : une seule copie de secours par page
      e.respondWith(reseauDabord(requete, url.origin + url.pathname).catch(() => caches.match(HORS_LIGNE)));
    } else {
      e.respondWith(fetch(requete).catch(() => caches.match(HORS_LIGNE)));
    }
    return;
  }
  if (p === 'api/mot-du-jour.php' && !url.search) {
    e.respondWith(reseauDabord(requete, requete.url));
    return;
  }
  if (p.startsWith('assets/') || p.startsWith('js/')) {
    e.respondWith(cacheDabord(requete));
  }
});

// Notification du mot (presque) du jour (envoyée par api/notifications.php)
self.addEventListener('push', (e) => {
  let d = {};
  try { d = e.data ? e.data.json() : {}; } catch { d = { corps: e.data ? e.data.text() : '' }; }
  e.waitUntil(self.registration.showNotification(d.titre || 'ConseilPlus+', {
    body: d.corps || 'Le mot (presque) du jour est arrivé.',
    icon: 'assets/icons/icon-192.png',
    badge: 'assets/icons/favicon-48.png',
    lang: 'fr',
    tag: 'mot-du-jour',
    data: { url: d.url || 'petit-moyen.php' },
  }));
});

self.addEventListener('notificationclick', (e) => {
  e.notification.close();
  const cible = new URL(e.notification.data?.url || './', self.registration.scope).href;
  e.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((fenetres) => {
    const ouverte = fenetres.find((f) => f.url.startsWith(self.registration.scope));
    return ouverte ? ouverte.navigate(cible).then((f) => f && f.focus()) : self.clients.openWindow(cible);
  }));
});
