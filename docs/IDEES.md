# Idées pour plus tard

## Conseil du jour sur téléphone (widget / notification)

Proposé le 14/10/2026. Illustre le principe « une même API peut alimenter une appli mobile » du cours.

1. **Endpoint** `GET /api/conseil-du-jour.php` : un conseil identique pour tout le monde pendant 24 h
   (générateur initialisé avec la date du jour).
2. **Widget sans appli native** (démo rapide) : KWGT (Android) ou Scriptable (iOS) lisent le JSON de l'endpoint.
3. **Notification** : transformer le site en PWA (manifest déjà présent dans `assets/icons/site.webmanifest`)
   + service worker + Web Push. Android, et iOS ≥ 16.4 une fois le site installé.
4. **Widget natif** (plus lourd) : Android Glance / iOS WidgetKit.

Prérequis : le téléphone doit joindre le site → hébergement (ou IP du PC sur le Wi-Fi pour tester).
Web Push exige du HTTPS, donc un hébergement.

## Logiciel de gestion « Le Bureau »

Deuxième application (base `gestion`) qui importe les demandes via `GET /api/contacts.php?statut=nouveau`
puis `PATCH`, gère les dossiers et répond automatiquement par un conseil absurde.
Améliorations API associées : journal des échanges, une clé par application, webhook, pagination, OpenAPI.
