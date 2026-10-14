# Journal de bord — ConseilPlus+

Mis à jour par Claude à la fin de chaque session (et avant chaque changement de PC).
L'entrée la plus récente est en haut de l'historique.

## État actuel (14/10/2026)

Site vitrine **complet et fonctionnel**, Lighthouse 100/100/100/100 sur les 9 pages (profil mobile).

- **Pages publiques** : accueil (conseil du jour + mot du jour), formules, contact (passe par l'API), espace client, Le Petit Moyen.
- **Espace client** : Mon conseil (sans rechargement), Consultation (questionnaire tournant + verdict IA),
  Le Duel (partie en 100 points, barème caché), Mon carnet (conseils classés).
- **API REST** (`docs/API.md`) : contacts (site → logiciel de gestion, clé `X-API-Key`), conseils, carnet, mot du jour.
  Journal de chaque échange dans `api_logs`, limite de débit, tests dans `tests/test_api.php`.
- **Sécurité** : CSRF, `session_regenerate_id`, champ piège anti-robots, requêtes préparées, échappement des sorties.
- **Le Petit Moyen** : mot du jour (fausse définition ou sketch du conseiller et du stagiaire),
  préparé par l'IA la veille, banque locale en secours.

## Prochaines étapes

1. **Le Bureau** (logiciel de gestion, attendu par le cours) : 2e application avec sa propre base `gestion`,
   qui importe les demandes via `GET /api/contacts.php?statut=nouveau` puis `PATCH ?id=` (statut `importe`),
   gère les dossiers et y répond automatiquement par un conseil absurde. Attendre les consignes du prof (séance du 16/10).
2. **Conseil du jour sur téléphone** (widget KWGT/Scriptable puis PWA + notifications) : voir `docs/IDEES.md`.
   Nécessite un hébergement HTTPS. L'API `mot-du-jour.php` est déjà prête.
3. Améliorations possibles de l'API : une clé par application, webhook, pagination, documentation OpenAPI.

## Points ouverts

- **Régénérer la clé Groq** (elle a transité par une conversation) et la mettre dans `config.php` sur chaque PC.
- Affichage mobile du sous-menu de l'espace client non vérifié visuellement (audit Lighthouse mobile OK).
- La qualité des sketchs générés par l'IA est variable (calembours parfois faibles) : surveiller, ajuster
  `CONSIGNE_SKETCH` dans `includes/mot-du-jour.php` si besoin.
- `db.php` et `config.php` ne sont pas dans Git : à recréer sur chaque PC (`docs/REPRISE.md`).

## Historique des sessions

### 14/10/2026 — PC principal (session fondatrice)
- Import du dépôt BaraQuiz, tri, puis transformation en **ConseilPlus+** (nouveau dépôt ConseilPlusPlus).
- Branding : logo C++ (skill logo-design), charte `design-system/conseilplus/MASTER.md`, favicon et icônes web.
- Front avec le skill ui-ux-pro-max, puis refonte de l'architecture (menu court + sous-menu espace client).
- API REST + journal + limite de débit + tests ; formulaire de contact branché sur l'API.
- IA Groq : consultation (verdict Kamoulox), Duel (arbitre), mot du jour (sketchs « Les Mots »).
- Générateur Kamoulox composé (banques lexicales + gabarits), vocabulaire actualisé.
- Sécurité (CSRF, anti-spam), carnet de conseils, Lighthouse 100 partout.
- Documents fournis par l'utilisateur et intégrés : « Kamoulox — Act-like », « Les Mots d'Éric et Ramzy — Act-like ».
