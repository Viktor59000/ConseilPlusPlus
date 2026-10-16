# Journal de bord — ConseilPlus+

Mis à jour par Claude à la fin de chaque session (et avant chaque changement de PC).
L'entrée la plus récente est en haut de l'historique.

## État actuel (16/10/2026)

Site vitrine **complet et fonctionnel**, Lighthouse 100/100/100/100 sur les 9 pages (profil mobile).
**En ligne** sur https://conseilplus.onrender.com (Render gratuit + TiDB Cloud Serverless gratuit), redéployé à chaque push
(`docs/DEPLOIEMENT.md`).

- **Pages publiques** : accueil (conseil du jour + mot du jour), formules, contact (passe par l'API), espace client, Le Petit Moyen.
- **Espace client** : Mon conseil (sans rechargement), Consultation (questionnaire tournant + verdict IA),
  Le Duel (partie en 100 points, barème caché), Mon carnet (conseils classés).
- **API REST** (`docs/API.md`) : contacts (site → logiciel de gestion, clé `X-API-Key`), conseils, carnet, mot du jour.
  Journal de chaque échange dans `api_logs`, limite de débit, tests dans `tests/test_api.php`.
- **Sécurité** : CSRF, `session_regenerate_id`, champ piège anti-robots, requêtes préparées, échappement des sorties.
- **Le Petit Moyen** : mot du jour (fausse définition ou sketch du conseiller et du stagiaire),
  préparé par l'IA la veille, banque locale en secours. Sketchs sur la mécanique des « Mots » (15 en banque).
- **Le Bureau** (`bureau/`, `docs/BUREAU.md`) : logiciel de gestion séparé (base `gestion`), en ligne sur `/bureau/`.
  Premier agent créé en ligne par l'utilisateur. Envoi réel des réponses par email via Brevo (code poussé).
- **Mobile** : site installable (PWA) + widget KWGT (Android) via `api/widget.php`. L'appli Android native a été retirée le 16/10
  (tri : uniquement PHP, JS, HTML, CSS, plus Dockerfile et workflow pour la mise en ligne).
- **Partage** (image story), **Le guichet** (`guichet.php`), **votes** « Moyen / Pas moyen » (palmarès dans Le Bureau),
  **notification quotidienne** du mot du jour (Web Push maison, envoi par `reveil.yml` vers 7 h).

## Prochaines étapes

0. **Tester la notification sur un vrai téléphone** : `application.php` → « Recevoir le mot du jour chaque matin »,
   puis lancer le workflow *Réveil quotidien* à la main (onglet Actions de GitHub) ou attendre le lendemain 7 h.
1. **Le Bureau** : à confronter aux consignes du prof (séance du 16/10). Envoi des réponses par email :
   **vérifié en ligne le 15/10** (via Brevo, expéditeur = boîte perso de l'utilisateur ; une adresse dédiée au
   cabinet pourra la remplacer : la vérifier dans Brevo puis changer `BUREAU_EXPEDITEUR` dans Render).
3. Améliorations possibles de l'API : une clé par application, webhook, pagination, documentation OpenAPI.

## Points ouverts

- **Anciennes données sur Aiven** (comptes, carnets, historique des mots, dossiers du Bureau) : base bloquée en
  *Rebuilding* depuis le 16/10. Le site tourne sur une base TiDB neuve ; migrer les données si Aiven revient.
- **Courriels** : tester en ligne l'accusé de réception (formulaire de contact) et une réponse du Bureau une fois la
  nouvelle clé Brevo en place.
- `data/cadres.php` : accords au masculin avec des objets féminins (« votre trottinette se sente moins seul ») à neutraliser.
- La clé de signature Android `conseilplus.p12` reste dans l'historique Git (appli retirée) : ne jamais la réutiliser.

- **Régénérer la clé Groq** (elle a transité par une conversation) et la mettre dans `config.php` sur chaque PC
  et dans Render (variable `GROQ_API_KEY`, absente pour l'instant : le site en ligne utilise les générateurs locaux).
- **Régénérer `API_KEY` dans Render** (bouton *Generate*) : la valeur actuelle a transité par la conversation.
- Formulaire de contact en ligne (appel interne de l'API via `URL_INTERNE`) pas encore testé de bout en bout.
- Affichage mobile du sous-menu de l'espace client non vérifié visuellement (audit Lighthouse mobile OK).
- La qualité des sketchs générés par l'IA est variable (calembours parfois faibles) : surveiller, ajuster
  `CONSIGNE_SKETCH` dans `includes/mot-du-jour.php` si besoin.
- `db.php`, `config.php` et `bureau/config.php` ne sont pas dans Git : à recréer sur chaque PC (`docs/REPRISE.md`,
  `docs/BUREAU.md`). Pour l'envoi d'emails en local, mettre `BREVO_API_KEY` / `BUREAU_EXPEDITEUR` dans `bureau/config.php`.
- **Brevo** : nouveau compte dédié au cabinet (`conseilplusplus@gmail.com`, expéditeur vérifié). Clé API créée et collée
  dans Render par l'utilisateur (`BREVO_API_KEY`), `BUREAU_EXPEDITEUR` = `conseilplusplus@gmail.com`.
- Transcriptions des « Mots » : 49/54 analysées ; calembours non relevés pour Séquestration, Chauvinisme, Portatif,
  Sarcasme, Castagnettes (`calembours_emission` vide pour ces mots). Transcriptions dans les conversations Gemini de
  l'utilisateur, jamais dans le dépôt.

## Historique des sessions

### 16/10/2026 — PC principal
- **Nouvelle base : TiDB Cloud Serverless** (Francfort, port 4000, TLS) à la place d'Aiven ; variables Render mises à
  jour (`docs/DEPLOIEMENT.md`). Site et Bureau de nouveau en ligne, tables recréées vides.
- **Courriels du cabinet** : `includes/courriel.php` (gabarit HTML compatible messageries : logo PNG, cartouche
  « Dossier n° », signature avec adresse absurde et tampon rouge), partagé par le site et Le Bureau.
  Accusé de réception automatique du formulaire de contact (2 max par adresse et par jour).
  Aperçu local : `tests/apercu-courriel.php?type=accuse` / `?type=reponse`. **Testé en ligne : accusé n° 0001 délivré.**
- **Tri du dépôt** : appli Android (Kotlin, Gradle, workflow `android.yml`) retirée, section correspondante de
  `application.php` et paramètre `?conseils` de `api/widget.php` supprimés ; icônes et certificat Aiven inutilisés supprimés ;
  `includes/footer.php` et `bureau/includes/bas.php` commencent par une balise PHP (GitHub les classait en « Hack »).
- Nouvelle adresse du cabinet `conseilplusplus@gmail.com` et compte Brevo dédié (créés par l'utilisateur).
- Pull des 22 commits du second PC (Bureau, mise en ligne, appli Android, partage, guichet, votes, notifications).
- Mise à niveau locale : `db.php` recopié depuis `db.example.php` (nouvelles tables), `bureau/config.php` créé.
- **Panne en ligne** : le nom d'hôte Aiven (`DB_HOST`) n'existe plus dans le DNS → service MySQL Aiven éteint ou supprimé.
  À faire par l'utilisateur dans la console Aiven (voir `docs/DEPLOIEMENT.md`, « En cas de souci »).
- Page d'erreur de base de données sécurisée : plus d'adresse ni de message MySQL affichés, code 503
  (le workflow de réveil détecte désormais la panne), réponse JSON pour l'API.
- Aiven rallumé par l'utilisateur : service en *Rebuilding* (reconstruction depuis la sauvegarde) pendant plus d'une heure.
- Alternative étudiée : base MySQL sur le panneau Pterodactyl d'Echo-Host (serveur du bot « Oni bot », compte d'un tiers).
  Code adapté (pas besoin du droit CREATE DATABASE, identifiants propres au Bureau), mais la création de base
  échoue côté Echo-Host (erreur serveur, puis limite 429) : aucune base créée, à voir avec leur support.
- `reveil.yml` : passage toutes les 6 h (garde la base Aiven active), notification seulement à la passe du matin.

### 15/10/2026 — Second PC
- Envoi d'emails du Bureau vérifié en ligne (Brevo).
- Partage d'un conseil / d'un mot : image story 1080 × 1920 générée dans le navigateur, menu de partage natif.
- Le guichet : ticket, file absurde (guichet 7, pause café, erreur de saisie), fermeture pile à votre tour.
- Votes « Moyen » / « Pas moyen » (`api/votes.php`, empreinte anonyme), palmarès dans Le Bureau via l'API.
- Notifications Web Push sans dépendance (`includes/push.php`, `tests/test_push.php`), clés VAPID en base.

### 14/10/2026 (soir) — Second PC
- **Le Bureau** : logiciel de gestion séparé (base `gestion`) : relevé du courrier via l'API (GET puis PATCH `importe`),
  dossiers, réponse officielle générée avec un conseil de l'API, classement, journal des relevés. Testé de bout en bout.
- Sketchs refaits sur la mécanique des « Mots » (étude de 44 sketchs) ; mots de l'émission libres, calembours interdits.
- Widget Android épuré ; gabarit Kamoulox « déclarez la guerre » remplacé par « offrez ça à [pays] ».
- Le Bureau : envoi réel des réponses par email via l'API Brevo (Render bloque le SMTP en gratuit) ; compte Brevo
  créé, expéditeur vérifié, variables ajoutées dans Render ; déploiement retardé par un incident Render.

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
