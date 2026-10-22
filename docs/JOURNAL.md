# Journal de bord — ConseilPlus+

Mis à jour par Claude à la fin de chaque session (et avant chaque changement de PC).
L'entrée la plus récente est en haut de l'historique.

## État actuel (22/10/2026)

Les trois attendus du cours sont faits, en ligne et testés ; le dossier est prêt pour le correcteur (`README.md` : parcours
noté en schéma de séquence, tableau « exigence → code → preuve », captures à jour dans `docs/captures/`).
**En ligne** sur https://conseilplus.onrender.com (Render gratuit + TiDB Cloud Serverless gratuit), redéployé à chaque push
(`docs/DEPLOIEMENT.md`). Lighthouse mesuré en ligne le 22/10 (profil mobile) : performance 93–100, accessibilité,
bonnes pratiques et SEO 100. RGAA 4.1 : partiellement conforme (audit interne). Identité « La note » (C+ entouré au stylo rouge).

- **Site vitrine** : accueil (conseil du jour, article du règlement intérieur du jour, mot du jour), Le cabinet, Le Petit Moyen
  (175 mots avec étymologies, 41 sketchs, « Le sigle du jour » du mouvement abréviationniste), formules, contact (passe par l'API).
- **Services de l'équipe** : Questions pour un stagiaire, Messmerde (expériences, prédictions rétroactives, horoscope),
  standard de Mireille (choix cliquables, sans clavier), attestations précédées du formulaire du jour, diagnostic.
  L'ancien guichet redirige (301) vers Le cabinet.
- **Espace client** : Mon conseil, Consultation, Le Duel, Mon carnet, **Mon compte** (mes demandes, leur statut, la réponse
  du cabinet ; suppression des demandes et du compte).
- **API REST** : une seule liste des routes, `data/api.php`, d'où sortent `developpeurs.php` et `api/openapi.php` (OpenAPI 3.1) ;
  `docs/API.md` ne garde que les principes et les parcours curl. Version `/api/v1/`, pagination/tri/filtres, clés par
  application (lecture/écriture), journal `api_logs`, limites de débit. POST mot du jour et « envoyer » des notifications sous clé.
- **Le Bureau** (`bureau/`, base `gestion`) : relevé `nouveau` → `importe`, affectation et échéances, notes internes,
  historique de la personne, CSV, purge du site, réponse par courriel (Brevo) **renvoyée au site** (`repondu`),
  relecture du mot de demain, journal, statistiques, clés, sauvegardes.
- **Mobile** : PWA, notification quotidienne (Web Push maison), appli Android + widget (`android/`, APK publié par
  `android.yml`, signé avec la nouvelle clé gardée dans les secrets GitHub).
- **Exploitation** : tests GitHub à chaque push, *Réveil quotidien* (exige le secret `API_KEY`), *Garde éveillé*, UptimeRobot.
- Confidentialité alignée sur ce qui est fait réellement (chantier du 22/10 : `confidentialite.php`, doc des notifications,
  bug de minuit, migrations sûres).

## Prochaines étapes

1. **Démo de secours** : tout le parcours prêt sur WAMP (agent, une demande, un compte client) au cas où Render ou le réseau
   tomberait, et une **vidéo** du parcours site → API → Bureau → Mon compte.
2. **Répétitions** de la démo en 5 minutes (`README.md`, section Soutenance), chronométrées, avec les questions à préparer.
3. **Diapositives** : « Données personnelles » (ce qui est stocké, où, combien de temps, comment l'effacer) et
   « Promis / constaté / corrigé » (audits du conseil et ce qui a été corrigé).
4. Tester la notification sur un vrai téléphone et les nouvelles pages avec un lecteur d'écran (NVDA, TalkBack),
   puis mettre à jour `accessibilite.php`.
5. Perspectives notées dans `docs/IDEES.md` (attestations reliées aux services, anonymisation et purge automatique au Bureau…).

## Points ouverts

- **Anciennes données sur Aiven** (comptes, carnets, historique des mots, dossiers du Bureau) : base bloquée en
  *Rebuilding* depuis le 16/10. Le site tourne sur une base TiDB neuve ; migrer les données si Aiven revient.
- L'ancienne clé de signature Android (`conseilplus.p12`, mot de passe en clair) reste dans l'historique Git : abandonnée
  le 22/10/2026, ne jamais la réutiliser. La nouvelle est hors du dépôt (secrets GitHub, `docs/DEPLOIEMENT.md`).
- **Régénérer la clé Groq** (elle a transité par une conversation) et la mettre dans `config.php` sur chaque PC
  et dans Render (`GROQ_API_KEY`).
- **Régénérer `API_KEY` dans Render** (la valeur a transité par une conversation), puis reporter la même valeur dans le
  secret GitHub `API_KEY` (sinon *Réveil quotidien* échoue en 401).
- RGAA : audit interne seulement ; lecteur d'écran à passer sur Le Duel, le standard, Questions pour un stagiaire et les sketchs.
- La qualité des sketchs générés par l'IA est variable : relus dans Le Bureau (page Petit Moyen) avant publication ;
  ajuster `CONSIGNE_SKETCH` dans `includes/mot-du-jour.php` si besoin.
- `config.php` et `bureau/config.php` ne sont pas dans Git (secrets) : à recréer sur chaque PC (`docs/REPRISE.md`).
  `db.php` est versionné (aucun secret). Pour l'envoi de courriels en local : `BREVO_API_KEY` / `BUREAU_EXPEDITEUR`.
- **Brevo** : compte dédié au cabinet (`conseilplusplus@gmail.com`, expéditeur vérifié) ; une adresse Gmail peut finir en
  indésirables (DMARC), un nom de domaine à soi réglerait ça.
- Transcriptions des « Mots » : 49/54 analysées ; calembours non relevés pour Séquestration, Chauvinisme, Portatif,
  Sarcasme, Castagnettes (`calembours_emission` vide pour ces mots). Transcriptions dans les conversations Gemini de
  l'utilisateur, jamais dans le dépôt.

## Historique des sessions

### 22/10/2026 — Refactor, front revu, audit du conseil
- **Refactor** : `db.php` versionné → `includes/bdd.php` (une connexion par requête) + `includes/schema.php` (rejoué seulement
  quand il change) ; client HTTP unique (`includes/http.php`) et adresses (`includes/url.php`) ; API : `router()`,
  `exiger_methode()`, `lire_entier()`… ; pages : `includes/init.php`, `includes/formulaire.php`, `includes/consultation.php` ;
  Bureau : `amorce.php`, `rediriger()`, `vues.php` ; chaque script JS dans sa propre portée.
- **Front** (skill frontend-design) : étiquettes en capitales retirées, police machine réservée aux tampons/écrans,
  panneaux à plat et papiers à coins, plus de fondu au défilement. Lighthouse en ligne : 93–100 / 100 / 100 / 100.
- **Audit du conseil** (skill debate : 5 experts, 3 rounds) → corrigé : export sans secrets, accusé de réception sans texte
  saisi, POST mot-du-jour et notifications « envoyer » sous clé, « chiffré » → empreinte SHA-512, lien de réinitialisation
  journalisé en local seulement, contacts plafonnés à 500, sitemap complet, **nouvelle clé Android hors dépôt**.
- **Conseil n° 2 (contenu et Bureau)**, appliqué : générateurs sans répétitions ni clins d'œil, Le Petit Moyen enrichi
  (étymologies, 175 mots, 41 sketchs, « Le sigle du jour » du mouvement abréviationniste), guichet retiré (301), standard
  sans clavier, Questions pour un stagiaire, prédictions rétroactives, règlement intérieur, formulaire du jour ;
  Bureau : affectation, échéances, notes, historique, CSV, purge, relecture du mot de demain ; le client lit la réponse
  du Bureau dans « Mon compte ». Correctif TiDB : une colonne par ALTER (le Bureau était en 503).
- Secrets GitHub et UptimeRobot faits par l'utilisateur ; APK signé avec la nouvelle clé (vérifié).
- **À faire par l'utilisateur** (fait) : secrets GitHub `API_KEY`, `ANDROID_CLE_P12`, `ANDROID_CLE_MDP` (voir `docs/DEPLOIEMENT.md`),
  puis relancer *Réveil quotidien* et *Application Android* ; réinstaller l'APK (nouvelle clé) ; UptimeRobot.

### 16/10/2026 — Second PC (le lore du cabinet, API pro)
- **Standard téléphonique** de Mireille (`standard.php`) : clavier, voix de synthèse et musique d'attente facultatives,
  file d'attente qui finit à la machine à café. **Horoscope de Messmerde** (section de `messmerde.php` + `api/horoscope.php`).
  **Diagnostic** « Quel client êtes-vous ? » (`diagnostic.php`, sans JS). **Attestations** du tampon (`attestation.php`,
  image dessinée dans le navigateur, rien n'est envoyé). Lien de service sur chaque carte de l'équipe.
- **API** : pagination/tri/filtres (`GET /contacts.php?page=…`, réponse sans `page` inchangée pour Le Bureau), alias `/api/v1/`
  (`api/.htaccess`, `a2enmod rewrite` dans le Dockerfile, `tests/routeur.php` pour la CI), `api/openapi.php`,
  clés par application (`api_cles`, `api/cles.php`, `bureau/cles.php`), export réservé à la clé maîtresse. Tests complétés.
- **Vérifications** : Lighthouse en ligne (profil mobile) : diagnostic, attestations, standard 100/100/100/100 ;
  Messmerde 99/100/100/100 (portrait servi en 3 tailles) ; accueil 98 à 100 selon la latence de Render (sur `/` : le test sur
  `/index.php` donne SEO 92 à cause de la balise canonique vers `/`, normal). Focus clavier corrigé chez Messmerde et dans les
  attestations, `accessibilite.php` mis à jour, barre du Bureau sur une ligne. Tests GitHub verts.

### 16/10/2026 — Second PC (Messmerde)
- **Les expériences de Messmerde** (`messmerde.php`, `js/messmerde.js`) : le mentaliste du cabinet « devine » des évidences
  en 7 expériences (nombre < 11, heure lue sur l'appareil, « celle-ci », le bouton, l'appareil, la lecture, le départ :
  titre d'onglet « PRÉVU. »), tampon « PRÉVU », verdict « Bluffant / Moyennement bluffant », crédibilité qui ne fait que monter.
  Concept repris d'une vidéo (faux mentaliste), gags et répliques inventés pour le site. Nom choisi par l'utilisateur.
- Carte « Messmerde » dans Le cabinet (lien vers ses expériences) ; prompt du portrait dans `docs/IMAGES.md`
  (`assets/equipe/messmerde.webp`, fourni par l'utilisateur, aussi affiché en tête de sa page). Liens : pied de page, plan du site.
- Répertoire de 20 expériences (prénom, année, jour, ciel, durée, pensée, clin d'œil, dé, touche, langue, écran, choix…) :
  7 tirées au sort par séance, toujours un **interrogatoire** (5 questions oui/non parmi 30, puis une « déduction » qui
  répète une réponse ou compte les oui) et toujours « Le départ » en dernier.

### 16/10/2026 — PC principal (ménage de code)
- Doublons supprimés : CSRF du Bureau (réutilise `includes/csrf.php`), délais estimés (`delai_estime()` dans
  `includes/textes.php`, liste dans `data/textes.php`), texte de l'accusé de réception (`accuse_reception()` dans
  `includes/courriel.php`, utilisé aussi par l'aperçu), style des rubriques (`css/base.css`), règle CSS morte.

### 16/10/2026 — PC principal (README, documentation de l'API)
- Page publique **L'API du cabinet** (`developpeurs.php`) : 17 endpoints décrits, « Essayer » en direct sur les GET publics.
- README réécrit : captures (`docs/captures/`), schéma d'architecture (Mermaid), tableau de l'API, technique, installation,
  **déroulé de soutenance en 5 minutes** et questions à préparer.
- Rappel : `GROQ_API_KEY` absente dans Render, donc l'IA tourne en secours en ligne (à ajouter avec la nouvelle clé).

### 16/10/2026 — PC principal (sécurité, tests, Bureau)
- Sécurité sans toucher à `auth/` : cookies de session protégés, HSTS, CSP (empreinte du script du thème), X-Frame-Options,
  Permissions-Policy, limite des essais de connexion / inscription (vérifiée : 11e essai refusé). Contrôlé en ligne.
- Tests GitHub à chaque push (`tests.yml`, MySQL réel) : verts. Courriel « mot de passe oublié » vérifié en ligne (délivré).
- Le Bureau : page Statistiques (nouvel endpoint `api/statistiques.php`), trois tons de réponse. La recherche existait déjà.
- Duel : tableau d'honneur (table `duels`, prénom seulement, effacé avec le compte). Le Petit Moyen : mots préférés du public.
- À faire par l'utilisateur : régénérer les clés Groq, Brevo et `API_KEY` (passées par la conversation), supprimer le compte
  Aiven, tester widget et notification sur un vrai téléphone, passage au lecteur d'écran (RGAA « totalement conforme »).

### 16/10/2026 — PC principal (contenu)
- Textes tournants (`data/textes.php`) : 16 tampons, 14 mentions, note de service selon le jour et 15 dates
  particulières, 24 témoignages de clients inventés (accueil, formules, cabinet), FAQ passée de 3 à 10 questions.
- Nouvelle page **Le cabinet** (dans le menu) : 6 membres de l'équipe, 4 valeurs, chiffres, témoignages.
  Portraits attendus dans `assets/equipe/<cle>.webp` (sinon monogramme entouré) : prompts donnés à l'utilisateur.
- Banques agrandies : mots 59 → 120, sketchs 15 → 30 (calembours originaux, contrôlés contre l'émission),
  questions 40 → 80, coups du Duel 16 → 50, annonces du guichet 6 → 25 incidents et 8 → 20 attentes.
- Surprises : titre d'onglet quand on part, 5 clics sur un tampon (tampon géant), code Konami (conseil caché).
- Menu replié sous 1080 px (5 liens). Lighthouse 98–100, 320 px / 200 % vérifiés.

### 16/10/2026 — PC principal (compte, légal, widget)
- **Appli Android rétablie** à la demande de l'utilisateur (seul code hors PHP/JS/HTML/CSS) : widget aux couleurs de
  « La note » (copie arrondie, marge rouge, mode sombre via `values-night`), nouvelle icône ; `api/widget.php?conseils=`
  remis (le tri l'avait retiré alors que le widget s'en sert). APK reconstruit par GitHub (réussi).
- **`reveil.yml` était invalide** depuis l'ajout des notifications (« : » dans une commande non protégée) : ni réveil
  ni notification ne tournaient. Corrigé (bloc `run: |`). Nouveau `garde-eveille.yml` : visite toutes les 10 min.
- Code du prof (`auth/`) **intact**. Ajouts à côté : mot de passe oublié (Brevo, jeton haché, 1 h, usage unique,
  réponse identique que le compte existe ou non), « Mon compte » avec suppression (carnet et jetons effacés
  explicitement : MyISAM sous WAMP ignore les clés étrangères), 8 caractères minimum (formulaire + réinitialisation).
- Mentions légales, confidentialité (journal de l'API purgé après 90 jours), page 404 dans le ton, robots.txt,
  sitemap.xml, Open Graph (image 1200 × 630), « Un autre conseil » sans compte sur l'accueil, faux paiement Premium,
  sous-menu mobile sur une ligne, guichet sans tirets vides. Tout testé (parcours complet, Lighthouse, 320 px / 200 %).

### 16/10/2026 — PC principal (sauvegardes)
- Boutons « Télécharger la base du site / du Bureau » dans le tableau de bord du Bureau (`bureau/export.php`,
  `api/export.php` avec clé, `includes/export-sql.php` : export en PHP pur, sans mysqldump). Réimport testé.

### 16/10/2026 — PC principal (refonte « La note »)
- L'utilisateur a jugé la refonte « Apple » trop fade : nouvelle identité complète avec le skill `logo-design`
  (3 pistes, choix A « La note » : C+ entouré au stylo rouge). Kit dans `branding/`, icônes web, PNG des courriels.
- Polices Schibsted Grotesk + JetBrains Mono (OFL, woff2 dans `assets/fonts`). Socle commun `css/base.css`
  (jetons, thèmes, animations), composants `css/style.css` (site) et `bureau/css/bureau.css`.
- Bouton clair / sombre (`includes/theme.php`, `js/theme.js`), choix gardé dans le navigateur, pas de flash.
- Animations (`js/site.js`) : boucle du logo, surligneur, tampons, apparitions au défilement, transitions de page.
- Courriels refaits (carte blanche, rubrique mono, signature + tampon) ; image de partage aux nouvelles couleurs.
- Lighthouse 99–100 / 100 / 100 / 100 sur 13 pages ; 320 px et texte 200 % vérifiés dans les deux thèmes.

### 16/10/2026 — PC principal (suite)
- **Refonte de la DA** avec le skill `apple-design` (installé dans `~/.claude/skills/apple-design`, HIG d'Apple) :
  polices système (plus de polices à télécharger), cartes arrondies, boutons en pilule, mode sombre automatique,
  en-tête translucide, contrôles segmentés ; seul le tampon rouge reste de travers (signature). Charte : `MASTER.md`.
- **Architecture** : le mot du jour et le guichet passent dans le menu principal ; menu repliable sous 760 px (`js/menu.js`) ;
  pied de page avec plan du site, accessibilité, application, contact.
- **RGAA 4.1** : `plan-du-site.php`, `accessibilite.php` (déclaration, partiellement conforme), champs obligatoires signalés,
  erreurs préfixées « Erreur : », contrastes vérifiés en clair et sombre, 320 px et texte à 200 % vérifiés par script.
- Lighthouse 100/100/100/100 sur les 13 pages. Cache des fichiers statiques sur Render (`docker/apache.conf`).
- Correctif : la liste KWGT de `application.php` partageait la classe `.formules` avec la page des formules.

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
