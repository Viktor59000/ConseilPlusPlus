# CLAUDE.md — Instructions pour Claude Code (projet ConseilPlus+)

Ce fichier est lu automatiquement par Claude Code au début de chaque session, sur n'importe quel PC.
Il remplace la mémoire locale de Claude : tout ce qui doit survivre à un changement d'appareil est écrit ici
ou dans `docs/JOURNAL.md`.

## Protocole de session (OBLIGATOIRE)

**Au début de chaque session :**
1. `git pull` (le travail a pu avancer depuis l'autre PC).
2. Lire `docs/JOURNAL.md` (état du projet, dernière session, prochaines étapes, points ouverts).
3. Vérifier que `config.php` et `bureau/config.php` existent (sinon : voir `docs/REPRISE.md`, étape 3).

**Après chaque modification terminée et vérifiée :**
- Commit + push immédiatement, sans demander (préférence explicite de l'utilisateur).
- Avant chaque commit : vérifier qu'aucun secret n'est indexé (`git grep --cached -n "gsk_"`).
- Message de commit en français, titre court + liste des changements.

**À la fin de chaque session (ou quand l'utilisateur dit qu'il change d'appareil) :**
- Mettre à jour `docs/JOURNAL.md` : ajouter une entrée datée en haut de « Historique des sessions »,
  mettre à jour « État actuel », « Prochaines étapes » et « Points ouverts ». Commit + push.

## L'utilisateur

- Viktor Venet, étudiant (Bachelor CGDD), dépôt GitHub : https://github.com/Viktor59000/ConseilPlusPlus
- Langue : **français** (code, commentaires, commits, interface, réponses).
- Il préfère qu'on avance et qu'on décide (« je te fais confiance »), mais il donne souvent des retours précis
  en cours de route : les intégrer sans perdre ce qui était en cours.
- Il fournit parfois des documents « act-like » (Kamoulox, Les Mots d'Éric et Ramzy) : s'en servir comme base,
  les adapter à l'univers du site, ne jamais recopier de répliques originales des sketchs (droits d'auteur).

## Le projet

Cours « API PHP — Du site vitrine au logiciel de gestion » (6 séances, du 14/10/2026 au 11/12/2026).
Attendus du cours : site vitrine 3 pages (index, produit, contact.php) avec BDD, une API REST en PHP,
puis un logiciel de gestion séparé qui consomme l'API (statut des demandes `nouveau` → `importe`).

**ConseilPlus+, « le conseiller moyen »** : faux cabinet de conseil qui se prend pour une administration
et donne des conseils absurdes (esprit Kamoulox). Ton sérieux, fond absurde, jamais de clin d'œil.

### Stack
- PHP 8.3 (WAMP), MySQL 9, PDO + requêtes préparées partout, aucun framework, aucune dépendance Composer.
- Vanilla JS (amélioration progressive : tout fonctionne sans JS).
- IA gratuite : API Groq (format OpenAI), modèle `openai/gpt-oss-120b`, repli `openai/gpt-oss-20b` (quota 429).
  Les modèles Llama ne sont plus gratuits sur Groq.
- WAMP n'a pas de certificats racine : cURL utilise `certs/cacert.pem`. Ne JAMAIS désactiver la vérification SSL.

### Carte du code
| Chemin | Rôle |
|---|---|
| `index.php`, `produit.php`, `contact.php`, `login.php` | Pages publiques |
| `petit-moyen.php` | Archives du mot (presque) du jour |
| `conseil.php`, `consultation.php`, `duel.php`, `carnet.php`, `compte.php` | Espace client (connecté) ; `compte.php` = suppression du compte |
| `mot-de-passe-oublie.php`, `reinitialiser.php`, `includes/compte.php` | Mot de passe oublié (lien par courriel, 1 h, usage unique) : **à côté** du code `auth/`, même méthode de hachage |
| `mentions-legales.php`, `confidentialite.php`, `accessibilite.php`, `plan-du-site.php`, `erreur-404.php` | Pages légales, RGAA, plan, 404 (`ErrorDocument` dans `docker/apache.conf`) |
| `robots.txt`, `sitemap.xml`, `assets/og-image.png` (modèle `tests/og-image.html`) | Référencement et aperçu des liens partagés |
| `auth/` | Connexion, inscription, déconnexion (repris de l'ancien site : sel statique + SHA-512, NE PAS changer) |
| `api/contacts.php` | REST des demandes de contact (POST public ; GET paginé/filtré/trié, PATCH, DELETE avec en-tête `X-API-Key`) |
| `api/cles.php`, `bureau/cles.php` | Clés d'API par application (droits lecture/ecriture, stockées hachées) ; la clé maîtresse `API_KEY` garde tous les droits |
| `api/openapi.php`, `api/.htaccess` | Description OpenAPI 3.1 générée depuis `data/api.php` ; alias versionné `/api/v1/…` (`tests/routeur.php` pour `php -S`) |
| `api/conseils.php` | Conseils aléatoires (public) |
| `api/mes-conseils.php` | Carnet de l'utilisateur (session + jeton CSRF) |
| `api/mot-du-jour.php` | Mot du jour (GET) + préparation IA du lendemain (POST) |
| `includes/api.php` | Squelette des points d'accès : `router()`, `exiger_methode()`, `lire_entier()`, `refuser_champs()`, réponses JSON, clé, journal `api_logs`, limite de débit |
| `includes/init.php`, `includes/formulaire.php` | Début de chaque page (session, CSRF, `exiger_connexion()`) ; `e()`, `alerte()` et champs en erreur |
| `includes/http.php`, `includes/url.php` | Seul client cURL (`requete_http()`, `appeler_api()`, aussi utilisé par Le Bureau) ; toutes les adresses du site |
| `includes/ia.php` | `groq_completer()` (tentatives, filtres, repli) + verdict de la consultation |
| `includes/kamoulox.php`, `includes/tirage.php` | Générateur de conseils (gabarits Kamoulox) |
| `includes/duel.php` | Mode Duel (barème calculé par le code) |
| `includes/mot-du-jour.php`, `includes/carte-mot.php` | Le Petit Moyen (définitions + sketchs) |
| `includes/csrf.php`, `includes/header.php`, `includes/espace-nav.php`, `includes/consultation.php`, `includes/votes.php` | Commun |
| `bureau/includes/amorce.php`, `bureau/includes/vues.php` | Début des pages du Bureau (`$agent`), `rediriger()`, `exiger_post()`, `barres()` |
| `data/*.php` | Banques de contenu (fragments, kamoulox, cadres, questions, duel, mots, sketchs, textes) |
| `data/textes.php`, `includes/textes.php` | Petits textes tournants : tampons, mentions, note de service (jour / dates), témoignages, équipe, valeurs, FAQ, titres d'onglet, conseils secrets |
| `includes/limite-connexion.php`, `auth/.htaccess`, `docker/securite.ini` | Sécurité autour du code du cours : limite des essais (avant `auth/`), cookies protégés |
| `api/statistiques.php`, `bureau/statistiques.php` | Statistiques d'usage de l'API, affichées dans Le Bureau |
| `.github/workflows/tests.yml`, `tests/test_pages.php` | Tests automatiques à chaque push |
| `developpeurs.php`, `data/api.php`, `js/api-doc.js` | Documentation publique de l'API (bouton « Essayer ») : tenir `data/api.php` à jour avec `docs/API.md` |
| `cabinet.php` | Le cabinet : équipe (portraits facultatifs `assets/equipe/<cle>.webp`), valeurs, chiffres, témoignages |
| `db.php` → `includes/bdd.php`, `includes/schema.php` | Connexion unique (`bdd()`), tables créées/migrées automatiquement quand `schema.php` change (empreinte dans `reglages`) |
| `config.example.php` → `config.php` | Clés `API_KEY` et `GROQ_API_KEY` (ignoré par Git) |
| `design-system/conseilplus/MASTER.md` | Charte graphique (source de vérité de la DA) |
| `docs/API.md`, `docs/IDEES.md`, `docs/JOURNAL.md`, `docs/REPRISE.md`, `docs/DEPLOIEMENT.md` | Documentation |
| `Dockerfile`, `docker/apache.conf`, `.github/workflows/reveil.yml`, `.github/workflows/garde-eveille.yml` | Mise en ligne (Render + TiDB Cloud), passes de contrôle toutes les 6 h, garde de 6 h relancée chaque heure, visite toutes les 9 min (site jamais endormi) |
| `android/`, `.github/workflows/android.yml` | Appli Android (WebView + widget « Le Petit Moyen » via `api/widget.php?conseils=12`), APK construit par GitHub à chaque push touchant `android/` (seul code hors PHP/JS/HTML/CSS, voulu) |
| `bureau/` | **Le Bureau**, logiciel de gestion séparé (base `gestion`), consomme l'API (`docs/BUREAU.md`) |
| `guichet.php`, `js/guichet.js` | Le guichet (ticket, file absurde, fermeture à votre tour) |
| `messmerde.php`, `js/messmerde.js` | Les expériences de Messmerde, mentaliste du cabinet : 20 « prédictions » évidentes (7 tirées par séance) dont un interrogatoire, tampon « PRÉVU » |
| `standard.php`, `js/standard.js` | Le standard téléphonique de Mireille (clavier, musique d'attente WebAudio, file qui finit à la machine à café) |
| `includes/horoscope.php`, `data/horoscope.php`, `api/horoscope.php` | Horoscope de Messmerde (déterministe par jour, 12 signes, API GET) |
| `diagnostic.php`, `data/diagnostic.php` | « Quel client êtes-vous ? » du conseiller (formulaire GET, toujours moyen) |
| `attestation.php`, `js/attestation.js`, `data/attestations.php` | Attestations officielles du tampon, dessinées dans le navigateur (rien envoyé au serveur) |
| Lore | Chaque membre de l'équipe (`data/textes.php`, clé `lien`) tient un service du site |
| `js/partage.js` | Partage d'un conseil / d'un mot (image story générée dans le navigateur) |
| `api/votes.php`, `js/votes.js` | Votes « Moyen » / « Pas moyen » (palmarès lu par Le Bureau) |
| `api/notifications.php`, `includes/push.php`, `js/notifications.js` | Notification quotidienne du mot du jour (Web Push sans dépendance, clés VAPID en base) |
| `tests/test_api.php`, `tests/test_push.php` | Tests de l'API et du chiffrement Web Push |

### Règles à respecter
- **Architecture** : une page = `require_once __DIR__ . '/includes/init.php'` + ses bibliothèques en `require_once __DIR__`, gabarits en `include`, données en `require __DIR__ . '/data/…'`.
  Aucune fonction définie dans une page ; logique dans `includes/`. Pas de second client cURL ni de nouvelle source d'URL. Chaque script JS dans sa propre portée `(() => { … })();`.
- **Sécurité** : jeton CSRF sur tous les formulaires POST, `htmlspecialchars` sur toute sortie, requêtes préparées,
  `session_regenerate_id()` à la connexion, jamais de secret dans Git (`config.php`, `bureau/config.php` ignorés ; `db.php` ne contient aucun secret).
- **DA** : suivre `design-system/conseilplus/MASTER.md` (identité « La note » : C+ entouré au stylo rouge, Schibsted Grotesk, thèmes clair/sombre avec bouton, animations ;
  socle commun `css/base.css`, skills `logo-design` et `apple-design`).
  Le logo C++ est en encre ; jamais de « + » rouge isolé (emblème de la Croix-Rouge).
- **Navigation** : menu principal à 5 entrées (Le cabinet, Le mot du jour, Nos formules, Le guichet, Contact) ; l'espace client a son propre sous-menu.
  Les autres services (Messmerde, standard, diagnostic, attestations) passent par les cartes de l'équipe (Le cabinet) et le plan du site, pas par le menu. Pas de boutons de navigation en double.
- **Qualité** : viser Lighthouse 100/100/100/100 sur chaque page (CSS intégré et minifié, police préchargée, `$description` par page)
  et la conformité RGAA 4.1 (`accessibilite.php` à tenir à jour).
- **Contenu IA** : célébrités uniquement depuis la liste de `data/kamoulox.php` (actions inoffensives, pas de politiques,
  pas de personnalités en polémique) ; verbes violents filtrés ; l'IA ne doit jamais ralentir une page
  (génération du mot du jour en arrière-plan).
- **Duel** : le barème reste caché sur le site (l'utilisateur veut garder le côté illogique).
- Rien d'offensant, de sexuel, de politique ; humour bienveillant.
- Les mentions « hommage non officiel » (Kamoulox, Les Mots) sont dans le README : les garder.
- **Contenu original** : aucune chute, réplique ou calembour repris des sketchs originaux (Kamoulox, Les Mots) ;
  tout est inventé pour le site, y compris les exemples donnés à l'IA. Les **mots** traités dans « Les Mots » restent
  libres (choix de l'utilisateur) ; leurs **calembours** sont listés dans `data/sketchs.php` (`calembours_emission`)
  et refusés automatiquement. Format d'un sketch : fausse lecture → scène (1 ligne) → « C'est donc un … »
  (conseiller) → « C'est vrai. » + calembour assumé (stagiaire) → relance.
- **En ligne** : chaque push sur `main` redéploie https://conseilplus.onrender.com (Render). Le code doit marcher
  à la fois en local (WAMP) et en ligne (réglages lus via `getenv()`, voir `docs/DEPLOIEMENT.md`).
  Les secrets (`DB_PASS`, `API_KEY`, `GROQ_API_KEY`) se saisissent dans Render par l'utilisateur, jamais dans Git ni dans la conversation.

### Tester
- Site : http://localhost/Site/ (WAMP démarré ; voir `docs/REPRISE.md` pour le lien `www/Site`).
- API : `php tests/test_api.php` (doit afficher « Tous les tests sont passés. »).
- Supprimer les comptes / contacts de test créés pendant les vérifications.
