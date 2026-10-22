# API ConseilPlus+

Réponses en JSON (UTF-8). Base : `https://conseilplus.onrender.com/api/v1` en ligne, `http://localhost/Site/api/v1` en local
(`/api` sans version : mêmes réponses).

## Où trouver la liste des routes

Les routes, leurs paramètres, leurs codes de réponse et leurs exemples ne sont écrits qu'à **un seul endroit** :
[`data/api.php`](../data/api.php). Ce fichier alimente :

| Documentation | En ligne | En local |
|---|---|---|
| Page lisible, avec bouton « Essayer » ([`developpeurs.php`](../developpeurs.php)) | https://conseilplus.onrender.com/developpeurs.php | http://localhost/Site/developpeurs.php |
| Description OpenAPI 3.1 ([`api/openapi.php`](../api/openapi.php)), à importer dans Postman, Insomnia ou Swagger UI | https://conseilplus.onrender.com/api/openapi.php (ou `/api/v1/`) | http://localhost/Site/api/openapi.php |

Ajouter ou modifier une route = modifier `data/api.php` : les deux documentations suivent. Ce fichier-ci ne garde que ce
qu'elles ne disent pas (principes, parcours complets).

## Principes

### Clés et droits

- En-tête `X-API-Key`. La **clé maîtresse** (`API_KEY`, celle du Bureau) a tous les droits, y compris l'export de la base
  et la gestion des clés.
- Les autres applications reçoivent **leur propre clé** (page « Clés d'API » du Bureau, ou `POST /api/cles.php` avec la clé
  maîtresse), avec les droits `lecture` et/ou `ecriture`. La clé n'est affichée qu'une fois ; le site n'en garde que
  l'empreinte SHA-256. Révocation (`DELETE`), puis effacement (second `DELETE`).
- Clé absente, inconnue ou révoquée : `401`. Droit manquant (par exemple `PATCH` avec une clé en lecture seule) : `403`.
- Routes publiques (sans clé) : `POST /contacts.php`, conseils, mot du jour du jour, horoscope, widget, votes,
  abonnement aux notifications (mais pas leur envoi, sous clé). Le carnet (`/mes-conseils.php`) utilise la session du site et le jeton CSRF
  (en-tête `X-CSRF-Token`), pas de clé.

### Journal

Chaque appel est enregistré dans la table `api_logs` : date, méthode, ressource, code HTTP, IP du client et application
appelante (le nom de la clé, `gestion` pour la clé maîtresse, `site` pour les appels du site lui-même, `public` sinon).
Les adresses IP sont effacées après 90 jours. `GET /api/statistiques.php` (clé requise) en tire les totaux affichés
par Le Bureau, sans jamais renvoyer d'adresse IP.

### Limites de débit

Comptées par IP, par ressource et par méthode sur la dernière minute (`limiter()` dans [`includes/api.php`](../includes/api.php)).
Au-delà : `429 Too Many Requests` et en-tête `Retry-After`. Exemple : `POST /contacts.php` est limité à 5 demandes
par minute et par IP. Les envois de contact du site transmettent l'IP du visiteur (en-tête `X-Client-IP`), pour que
la limite s'applique au visiteur et non au serveur.

### Versions

`/api/v1/contacts.php` sert `api/contacts.php` (règle dans [`api/.htaccess`](../api/.htaccess), activée en ligne par
`docker/apache.conf`, et par [`tests/routeur.php`](../tests/routeur.php) pour le serveur de test). `/api/v1/` seul renvoie
la description OpenAPI. Une future version incompatible vivrait sous `/api/v2/` sans casser les clients de la v1.

### Pagination, tri et filtres (`GET /contacts.php`)

- Filtres : `statut` (`nouveau`, `importe`, `repondu`), `type` (type de demande), `q` (recherche dans nom, adresse, message).
- Tri : `tri=date` (par défaut), `-date`, `nom`, `-nom`.
- **Sans `page`** : la liste d'un bloc, `{ total, contacts[] }` (ce que lit Le Bureau), plafonnée à 500 demandes.
- **Avec `page`** (et `par_page`, 1 à 100, 20 par défaut) : `{ total, page, par_page, pages, contacts[] }`,
  en-têtes `Link` (`first`, `prev`, `next`, `last`, RFC 8288) et `X-Total-Count`.
- Paramètre invalide : `400` avec un message.

### Statuts d'une demande de contact

`nouveau` (déposée sur le site) → `importe` (relevée par Le Bureau) → `repondu` (réponse du cabinet, lisible par le client
dans « Mon compte » s'il était connecté en écrivant). `reponse` est obligatoire avec `repondu` (5 000 caractères au plus,
sinon `422`) ; `date_reponse` est remplie par le site ; un autre statut efface la réponse.

## Parcours importants (curl)

Remplacer `VOTRE_CLE` par la clé maîtresse ou une clé d'application ; en ligne, remplacer
`http://localhost/Site` par `https://conseilplus.onrender.com`.

**1. Une demande de contact, du site au Bureau, puis retour au client**

```bash
# Le site dépose la demande (public) → 201 { id, statut: "nouveau" }
curl -X POST -H "Content-Type: application/json" \
  -d '{"nom":"Jean Moyen","email":"jean@exemple.fr","type_demande":"Réclamation","message":"Mon grille-pain me juge."}' \
  http://localhost/Site/api/contacts.php

# Le Bureau relève les nouvelles demandes → 200 { total, contacts[] } ; sans clé → 401
curl -H "X-API-Key: VOTRE_CLE" "http://localhost/Site/api/contacts.php?statut=nouveau"

# … puis marque chacune comme importée (statut par défaut) → 200
curl -X PATCH -H "X-API-Key: VOTRE_CLE" "http://localhost/Site/api/contacts.php?id=1"

# Au classement du dossier, le Bureau dépose la réponse officielle → 200
curl -X PATCH -H "X-API-Key: VOTRE_CLE" -H "Content-Type: application/json" \
  -d '{"statut":"repondu","reponse":"Madame, Monsieur, patientez."}' "http://localhost/Site/api/contacts.php?id=1"

# Purge d'une demande classée → 204 (404 si elle n'existe plus)
curl -X DELETE -H "X-API-Key: VOTRE_CLE" "http://localhost/Site/api/contacts.php?id=1"
```

`type_demande` ∈ `Demande de conseil`, `Réclamation`, `Devis Premium`, `Autre`. Champs invalides : `422` avec le détail.

**2. Liste paginée, triée et filtrée (version 1)**

```bash
curl -i -H "X-API-Key: VOTRE_CLE" "http://localhost/Site/api/v1/contacts.php?statut=importe&page=1&par_page=10&tri=-date"
```

**3. Modération du mot de demain (page « Petit Moyen » du Bureau)**

Seul le mot de **demain** se relit, se corrige ou se rejette ; sans clé, il reste invisible (`404`) ; un jour déjà
publié ne se réécrit pas (`409`).

```bash
curl -H "X-API-Key: VOTRE_CLE" "http://localhost/Site/api/mot-du-jour.php?date=AAAA-MM-JJ"     # relire → 200 | 404
curl -X PATCH -H "X-API-Key: VOTRE_CLE" -H "Content-Type: application/json" \
  -d '{"etymologie":"Du latin moyennus."}' http://localhost/Site/api/mot-du-jour.php          # corriger → 200 | 422
curl -X DELETE -H "X-API-Key: VOTRE_CLE" http://localhost/Site/api/mot-du-jour.php            # rejeter → 204
curl -X POST -H "X-API-Key: VOTRE_CLE" http://localhost/Site/api/mot-du-jour.php              # faire préparer par l'IA → 201 | 200
```

**4. Délivrer puis révoquer une clé d'application (clé maîtresse)**

```bash
curl -X POST -H "X-API-Key: VOTRE_CLE" -H "Content-Type: application/json" \
  -d '{"nom":"Tableau mural","droits":["lecture"]}' http://localhost/Site/api/cles.php   # 201 { id, nom, droits, cle }
curl -X DELETE -H "X-API-Key: VOTRE_CLE" "http://localhost/Site/api/cles.php?id=3"         # 204 : révoquée
```

**5. Sans clé**

```bash
curl http://localhost/Site/api/conseils.php?nombre=3
curl "http://localhost/Site/api/horoscope.php?signe=belier"
curl http://localhost/Site/api/widget.php
```

## Tests

```bash
php tests/test_api.php                         # contre http://localhost/Site
php tests/test_api.php http://127.0.0.1:8000   # contre un autre serveur (ce que fait GitHub Actions)
```

Le script rejoue les parcours ci-dessus (codes attendus compris) puis supprime ce qu'il a créé.

## Web Push

`api/notifications.php` est implémenté sans dépendance ([`includes/push.php`](../includes/push.php) : chiffrement RFC 8291,
VAPID RFC 8292) ; les clés VAPID sont créées au premier appel et gardées dans la table `reglages`. Seuls les services de
notification connus (FCM, Mozilla, Apple, Windows) sont acceptés.

`GET` (clé publique VAPID), `POST {action: "abonner"}` et `POST {action: "desabonner"}` sont publics. En revanche
`POST {action: "envoyer"}` exige une clé avec le droit `ecriture` (`401` sans clé, `403` avec une clé en lecture seule) :
personne d'autre que le cabinet ne peut écrire aux abonnés. L'envoi part au plus une fois par jour (`200 { deja: true }`
ensuite) ; il est déclenché chaque matin par `.github/workflows/reveil.yml` avec le secret `API_KEY`.

```bash
curl -X POST -H "X-API-Key: VOTRE_CLE" -H "Content-Type: application/json" -d '{"action":"envoyer"}' http://localhost/Site/api/notifications.php
```

Test : `php tests/test_push.php`.

## API externe utilisée : Groq (IA)

`consultation.php` et la préparation du mot du jour appellent l'API de Groq (format compatible OpenAI,
`https://api.groq.com/openai/v1/chat/completions`, modèle `GROQ_MODEL`, repli sur un modèle plus petit en cas de quota).
Sans clé ou en cas d'erreur, les générateurs locaux prennent le relais. Les certificats racine utilisés pour le HTTPS
sont dans `certs/cacert.pem`.
