# CLAUDE.md — Instructions pour Claude Code (projet ConseilPlus+)

Ce fichier est lu automatiquement par Claude Code au début de chaque session, sur n'importe quel PC.
Il remplace la mémoire locale de Claude : tout ce qui doit survivre à un changement d'appareil est écrit ici
ou dans `docs/JOURNAL.md`.

## Protocole de session (OBLIGATOIRE)

**Au début de chaque session :**
1. `git pull` (le travail a pu avancer depuis l'autre PC).
2. Lire `docs/JOURNAL.md` (état du projet, dernière session, prochaines étapes, points ouverts).
3. Vérifier que `config.php` et `db.php` existent (sinon : voir `docs/REPRISE.md`, étape 3).

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
| `conseil.php`, `consultation.php`, `duel.php`, `carnet.php` | Espace client (connecté) |
| `auth/` | Connexion, inscription, déconnexion (repris de l'ancien site : sel statique + SHA-512, NE PAS changer) |
| `api/contacts.php` | REST des demandes de contact (POST public ; GET/PATCH/DELETE avec en-tête `X-API-Key`) |
| `api/conseils.php` | Conseils aléatoires (public) |
| `api/mes-conseils.php` | Carnet de l'utilisateur (session + jeton CSRF) |
| `api/mot-du-jour.php` | Mot du jour (GET) + préparation IA du lendemain (POST) |
| `includes/api.php` | Réponses JSON, clé, journal `api_logs`, limite de débit |
| `includes/ia.php` | `groq_completer()` (tentatives, filtres, repli) + verdict de la consultation |
| `includes/kamoulox.php`, `includes/tirage.php` | Générateur de conseils (gabarits Kamoulox) |
| `includes/duel.php` | Mode Duel (barème calculé par le code) |
| `includes/mot-du-jour.php`, `includes/carte-mot.php` | Le Petit Moyen (définitions + sketchs) |
| `includes/csrf.php`, `includes/http.php`, `includes/header.php`, `includes/espace-nav.php` | Commun |
| `data/*.php` | Banques de contenu (fragments, kamoulox, cadres, questions, duel, mots, sketchs) |
| `db.example.php` → `db.php` | Connexion + création/migration automatique des tables (ignoré par Git) |
| `config.example.php` → `config.php` | Clés `API_KEY` et `GROQ_API_KEY` (ignoré par Git) |
| `design-system/conseilplus/MASTER.md` | Charte graphique (source de vérité de la DA) |
| `docs/API.md`, `docs/IDEES.md`, `docs/JOURNAL.md`, `docs/REPRISE.md`, `docs/DEPLOIEMENT.md` | Documentation |
| `Dockerfile`, `docker/apache.conf`, `.github/workflows/reveil.yml` | Mise en ligne (Render + Aiven) et visite quotidienne |
| `tests/test_api.php` | Test de bout en bout de l'API (`php tests/test_api.php`) |

### Règles à respecter
- **Sécurité** : jeton CSRF sur tous les formulaires POST, `htmlspecialchars` sur toute sortie, requêtes préparées,
  `session_regenerate_id()` à la connexion, jamais de secret dans Git (`config.php`, `db.php` ignorés).
- **DA** : suivre `design-system/conseilplus/MASTER.md` (palette, typo, brutalisme administratif).
  Le logo C++ est en encre ; jamais de « + » rouge isolé (emblème de la Croix-Rouge).
- **Navigation** : menu principal à 4 entrées ; l'espace client a son propre sous-menu. Pas de boutons de navigation en double.
- **Qualité** : viser Lighthouse 100/100/100/100 sur chaque page (polices locales, CSS intégré, `$description` par page).
- **Contenu IA** : célébrités uniquement depuis la liste de `data/kamoulox.php` (actions inoffensives, pas de politiques,
  pas de personnalités en polémique) ; verbes violents filtrés ; l'IA ne doit jamais ralentir une page
  (génération du mot du jour en arrière-plan).
- **Duel** : le barème reste caché sur le site (l'utilisateur veut garder le côté illogique).
- Rien d'offensant, de sexuel, de politique ; humour bienveillant.
- Les mentions « hommage non officiel » (Kamoulox, Les Mots) sont dans le README : les garder.
- **Contenu original** : aucune chute, réplique ou calembour repris des sketchs originaux (Kamoulox, Les Mots) ;
  tout est inventé pour le site, y compris les exemples donnés à l'IA.
- **En ligne** : chaque push sur `main` redéploie https://conseilplus.onrender.com (Render). Le code doit marcher
  à la fois en local (WAMP) et en ligne (réglages lus via `getenv()`, voir `docs/DEPLOIEMENT.md`).
  Les secrets (`DB_PASS`, `API_KEY`, `GROQ_API_KEY`) se saisissent dans Render par l'utilisateur, jamais dans Git ni dans la conversation.

### Tester
- Site : http://localhost/Site/ (WAMP démarré ; voir `docs/REPRISE.md` pour le lien `www/Site`).
- API : `php tests/test_api.php` (doit afficher « Tous les tests sont passés. »).
- Supprimer les comptes / contacts de test créés pendant les vérifications.
