# Idées pour plus tard

## Conseil du jour sur téléphone (widget / notification)

Proposé le 14/10/2026. Illustre le principe « une même API peut alimenter une appli mobile » du cours.

1. **Endpoint** `GET /api/conseil-du-jour.php` : un conseil identique pour tout le monde pendant 24 h
   (générateur initialisé avec la date du jour).
2. **Widget sans appli native** (en place) (démo rapide) : KWGT (Android) ou Scriptable (iOS) lisent le JSON de l'endpoint.
3. **Notification** : transformer le site en PWA (manifest déjà présent dans `assets/icons/site.webmanifest`)
   + service worker + Web Push. Android, et iOS ≥ 16.4 une fois le site installé.
4. **Widget natif** (essayé puis retiré le 16/10 : hors PHP/JS/HTML/CSS) : Android Glance / iOS WidgetKit.

Prérequis : le téléphone doit joindre le site → hébergement (ou IP du PC sur le Wi-Fi pour tester).
Web Push exige du HTTPS, donc un hébergement.

## Le mot (presque) du jour — « Le Petit Moyen »

Proposé le 14/10/2026. Une fausse entrée de dictionnaire par jour, dans le ton administratif du site :
un jeu de mots (ou un mot qui n'en est pas un) avec une définition approximative.

Format d'une entrée :
- **Mot** : mot-valise (« procrastinette », « réunionite »), vrai mot mal compris (« dimanche : n. m., jour où l'on prépare lundi »)
  ou faux jeu de mots assumé (« Ornithorynque — n'a rien à voir avec ce qui précède »).
- **Nature** : *n. m.*, *v. tr. du 3e groupe (rarement conjugué)*, *adj. invariable sauf le mardi*…
- **Définition approximative** + **exemple d'usage** + **synonyme douteux** + **niveau de certitude** (« 12 % »).

Mise en œuvre :
1. Banque locale écrite à la main (data/mots.php), tirée selon la date : le même mot pour tout le monde dans la journée.
2. Option IA : un mot généré une fois par jour puis stocké en base (une seule requête Groq par jour, pas de quota gaspillé).
3. Endpoint `GET /api/mot-du-jour.php` → réutilisable par le widget / la notification du téléphone (voir plus haut),
   avec le conseil du jour : le téléphone afficherait « mot du jour + conseil du jour ».
4. Encadré sur l'accueil + page d'archives « Le Petit Moyen » (les mots des jours précédents).

## Logiciel de gestion « Le Bureau »

Deuxième application (base `gestion`) qui importe les demandes via `GET /api/contacts.php?statut=nouveau`
puis `PATCH`, gère les dossiers et répond automatiquement par un conseil absurde.
Améliorations API associées : journal des échanges, une clé par application, webhook, pagination, OpenAPI.
