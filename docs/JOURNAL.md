# Journal de bord — ConseilPlus+

Mis à jour par Claude à la fin de chaque session (et avant chaque changement de PC).
L'entrée la plus récente est en haut de l'historique.

## État actuel (14/10/2026)

Site vitrine **complet et fonctionnel**, Lighthouse 100/100/100/100 sur les 9 pages (profil mobile).
**En ligne** sur https://conseilplus.onrender.com (Render gratuit + MySQL Aiven gratuit), redéployé à chaque push
(`docs/DEPLOIEMENT.md`).

- **Pages publiques** : accueil (conseil du jour + mot du jour), formules, contact (passe par l'API), espace client, Le Petit Moyen.
- **Espace client** : Mon conseil (sans rechargement), Consultation (questionnaire tournant + verdict IA),
  Le Duel (partie en 100 points, barème caché), Mon carnet (conseils classés).
- **API REST** (`docs/API.md`) : contacts (site → logiciel de gestion, clé `X-API-Key`), conseils, carnet, mot du jour.
  Journal de chaque échange dans `api_logs`, limite de débit, tests dans `tests/test_api.php`.
- **Sécurité** : CSRF, `session_regenerate_id`, champ piège anti-robots, requêtes préparées, échappement des sorties.
- **Le Petit Moyen** : mot du jour (fausse définition ou sketch du conseiller et du stagiaire),
  préparé par l'IA la veille, banque locale en secours.

## Prochaines étapes

1. **Le Bureau** : fait (`bureau/`, `docs/BUREAU.md`). À confronter aux consignes du prof (séance du 16/10).
   Créer le premier agent en ligne (clé d'API demandée).
2. **Appli Android** : tester l'APK et le widget sur un vrai téléphone ; Play Store plus tard (25 $, clé de signature secrète).
3. Améliorations possibles de l'API : une clé par application, webhook, pagination, documentation OpenAPI.

## Points ouverts

- **Régénérer la clé Groq** (elle a transité par une conversation) et la mettre dans `config.php` sur chaque PC
  et dans Render (variable `GROQ_API_KEY`, absente pour l'instant : le site en ligne utilise les générateurs locaux).
- **Régénérer `API_KEY` dans Render** (bouton *Generate*) : la valeur actuelle a transité par la conversation.
- Formulaire de contact en ligne (appel interne de l'API via `URL_INTERNE`) pas encore testé de bout en bout.
- Affichage mobile du sous-menu de l'espace client non vérifié visuellement (audit Lighthouse mobile OK).
- La qualité des sketchs générés par l'IA est variable (calembours parfois faibles) : surveiller, ajuster
  `CONSIGNE_SKETCH` dans `includes/mot-du-jour.php` si besoin.
- `db.php` et `config.php` ne sont pas dans Git : à recréer sur chaque PC (`docs/REPRISE.md`).

## Historique des sessions

### 14/10/2026 (soir) — Second PC
- **Le Bureau** : logiciel de gestion séparé (base `gestion`) : relevé du courrier via l'API (GET puis PATCH `importe`),
  dossiers, réponse officielle générée avec un conseil de l'API, classement, journal des relevés. Testé de bout en bout.
- Sketchs refaits sur la mécanique des « Mots » (étude de 44 sketchs) ; mots de l'émission libres, calembours interdits.
- Widget Android épuré ; gabarit Kamoulox « déclarez la guerre » remplacé par « offrez ça à [pays] ».

### 14/10/2026 — Second PC (D:\Conseil++)
- Installation sur ce PC : clone dans `D:\Conseil++`, jonction `C:\wamp64\www\Site`, `db.php` / `config.php` recréés, tests OK.
- Le Petit Moyen : sketchs repris des « Mots » originaux remplacés par des chutes inventées (9 sketchs),
  18 nouvelles définitions (68 mots), section « Feuilleter le dictionnaire » (page au hasard + sommaire).
- Mise en ligne : Dockerfile, réglages par variables d'environnement, Render (Frankfurt, Free) + Aiven MySQL (Free),
  visite quotidienne `reveil.yml`, `docs/DEPLOIEMENT.md`.
- Sketchs : format court par défaut (scène + chute + tampon du découpage), lecture animée et voix de synthèse,
  rituel complet dans un volet.
- Site installable (manifest + service worker + page `application.php`), API `api/widget.php`.
- Appli Android (`android/`) : site en WebView + widget mot du jour / conseil ; APK publié par GitHub Actions :
  https://github.com/Viktor59000/ConseilPlusPlus/releases/download/android/conseilplus.apk (pas encore testé sur un vrai téléphone).

### 14/10/2026 — PC principal (session fondatrice)
- Import du dépôt BaraQuiz, tri, puis transformation en **ConseilPlus+** (nouveau dépôt ConseilPlusPlus).
- Branding : logo C++ (skill logo-design), charte `design-system/conseilplus/MASTER.md`, favicon et icônes web.
- Front avec le skill ui-ux-pro-max, puis refonte de l'architecture (menu court + sous-menu espace client).
- API REST + journal + limite de débit + tests ; formulaire de contact branché sur l'API.
- IA Groq : consultation (verdict Kamoulox), Duel (arbitre), mot du jour (sketchs « Les Mots »).
- Générateur Kamoulox composé (banques lexicales + gabarits), vocabulaire actualisé.
- Sécurité (CSRF, anti-spam), carnet de conseils, Lighthouse 100 partout.
- Documents fournis par l'utilisateur et intégrés : « Kamoulox — Act-like », « Les Mots d'Éric et Ramzy — Act-like ».
