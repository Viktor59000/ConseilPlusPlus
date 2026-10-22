# API ConseilPlus+

Base : `http://localhost/Site/api/v1` (ou `/api`, alias sans version) — réponses en JSON (UTF-8).

- **Version** : `/api/v1/contacts.php` sert `api/contacts.php` (règle dans `api/.htaccess`, activée en ligne par `docker/apache.conf`, et par `tests/routeur.php` pour le serveur de test).
- **OpenAPI 3.1** : `GET /api/v1/` ou `/api/openapi.php`, généré à partir de `data/api.php` (même source que `developpeurs.php`).
- **Clés** : en-tête `X-API-Key`. La clé maîtresse (`API_KEY`, celle du Bureau) a tous les droits. Les autres applications
  reçoivent leur propre clé (`api/cles.php`, page « Clés d'API » du Bureau) avec les droits `lecture` et/ou `ecriture` ;
  le site n'en garde que l'empreinte SHA-256. Clé absente ou révoquée : `401`. Droit manquant : `403`.

## Contacts — `api/contacts.php`

Échange des demandes de contact entre le site vitrine et le logiciel de gestion.

| Méthode | URL | Clé | Rôle | Réponses |
|---|---|---|---|---|
| `POST` | `/contacts.php` | non | Le site vitrine envoie une demande | `201` créée · `422` champs invalides |
| `GET` | `/contacts.php?statut=nouveau` | lecture | Lister les demandes (filtres, tri et pagination ci-dessous) | `200` · `400` · `401` |
| `GET` | `/contacts.php?id=42` | lecture | Détail d'une demande | `200` · `404` |
| `PATCH` | `/contacts.php?id=42` | écriture | Changer le statut (par défaut `importe`) | `200` · `404` · `422` |
| `DELETE` | `/contacts.php?id=42` | écriture | Supprimer une demande traitée | `204` · `404` |

**Liste** : filtres `statut` (`nouveau` / `importe`), `type` (type de demande), `q` (recherche dans nom, adresse, message) ;
tri `tri=date` (défaut), `-date`, `nom`, `-nom`. Sans `page`, la liste est renvoyée d'un bloc `{ total, contacts[] }` (ce que lit Le Bureau), plafonnée à 500 demandes.
Avec `page` (et `par_page`, 1 à 100, 20 par défaut) : `{ total, page, par_page, pages, contacts[] }`,
en-têtes `Link` (`first`, `prev`, `next`, `last`, RFC 8288) et `X-Total-Count`.

**Limite** : `POST` est limité à 5 demandes par minute et par IP (`429 Too Many Requests` au-delà, en-tête `Retry-After`).

## Journal des échanges

Chaque appel à l'API est enregistré dans la table `api_logs` : date, méthode, ressource, code HTTP, IP du client
et application appelante (`gestion` si la clé est valide, `site` pour les appels du site lui-même, `public` sinon).

Corps du `POST` (JSON ou formulaire) :

```json
{ "nom": "Jean Moyen", "email": "jean@exemple.fr", "type_demande": "Demande de conseil", "message": "Mon grille-pain me juge." }
```

`type_demande` ∈ `Demande de conseil`, `Réclamation`, `Devis Premium`, `Autre`.

Corps du `PATCH` (facultatif) : `{ "statut": "importe" }`.

## Carnet — `api/mes-conseils.php`

Conseils classés par l'utilisateur connecté. Authentification par la session du site (cookie) ;
`POST` et `DELETE` exigent le jeton CSRF de la page dans l'en-tête `X-CSRF-Token`.

| Méthode | URL | Rôle | Réponses |
|---|---|---|---|
| `GET` | `/mes-conseils.php` | Liste des conseils classés | `200` · `401` |
| `POST` | `/mes-conseils.php` | Classe un conseil `{texte, dossier, source}` | `201` · `200` (déjà classé) · `403` · `409` (carnet plein) · `422` |
| `DELETE` | `/mes-conseils.php?id=42` | Retire un conseil du carnet | `204` · `404` |

`source` ∈ `generateur`, `ia`, `secours`. `dossier` au format `4821-K`.

## Conseils — `api/conseils.php`

| Méthode | URL | Clé | Rôle |
|---|---|---|---|
| `GET` | `/conseils.php` | non | Un conseil aléatoire |
| `GET` | `/conseils.php?nombre=3` | non | Plusieurs conseils (5 max) |

## Widget — `api/widget.php`

| Méthode | URL | Clé | Rôle |
|---|---|---|---|
| `GET` | `/widget.php` | non | Mot du jour + un conseil, en champs prêts à afficher (widget KWGT / Scriptable) |

Réponse : `date`, `mot`, `nature`, `ligne1` (définition, ou chute du sketch), `ligne2` (exemple, ou découpage officiel),
`conseil`, `dossier`. En-tête `Cache-Control: max-age=600`.

Formule KWGT (Android) : `$wg("https://conseilplus.onrender.com/api/widget.php", json, ".mot")$` (procédure sur `application.php`).

## Votes — `api/votes.php`

| Méthode | URL | Clé | Rôle |
|---|---|---|---|
| `GET` | `/votes.php?mots=Mirobolant,Labrador` | non | Compteurs « moyen » / « pas_moyen » de chaque mot |
| `GET` | `/votes.php?palmares=5` | non | Les mots les mieux notés (utilisé par Le Bureau) |
| `POST` | `/votes.php` | non | Vote `{mot, vote}` (`moyen` ou `pas_moyen`) : `200` · `404` mot inconnu · `422` · `429` |

Un vote par visiteur et par mot (modifiable). Seule une empreinte anonyme est stockée (hachage IP + mot + secret).

## Notifications — `api/notifications.php`

| Méthode | URL | Clé | Rôle |
|---|---|---|---|
| `GET` | `/notifications.php` | non | Clé publique VAPID (pour s'abonner dans le navigateur) |
| `POST` | `/notifications.php` | non | `{action: "abonner", abonnement}` · `{action: "desabonner", endpoint}` · `{action: "envoyer"}` |

Web Push implémenté sans dépendance (`includes/push.php` : chiffrement RFC 8291, VAPID RFC 8292) ; clés VAPID créées
au premier appel et gardées dans la table `reglages`. Seuls les services de notification connus (FCM, Mozilla, Apple,
Windows) sont acceptés. L'envoi part au plus une fois par jour ; il est déclenché chaque matin par `reveil.yml`.
Test : `php tests/test_push.php`.

## Statistiques — `api/statistiques.php`

| Méthode | Paramètres | Réponse |
|---|---|---|
| `GET` (clé requise) | `?jours=14` (1 à 90) | `200 { jours, total, erreurs, par_jour[], par_ressource[], par_code[], par_application[] }` |

Totaux tirés du journal `api_logs` (aucune adresse IP renvoyée). Affichés par Le Bureau, page « Statistiques ».

## Sauvegarde — `api/export.php`

| Méthode | Paramètres | Réponse |
|---|---|---|
| `GET` (clé maîtresse) | — | `200 { base, fichier, sql }` : toute la base du site en SQL (structure + données) |

Utilisé par le bouton « Télécharger la base du site » du Bureau. Contient des données personnelles : clé maîtresse obligatoire (`403` pour une clé d'application).

## Clés d'application — `api/cles.php` (clé maîtresse)

| Méthode | URL | Rôle | Réponses |
|---|---|---|---|
| `GET` | `/cles.php` | Lister les clés (nom, début de la clé, droits, dernier appel, révoquée) | `200 { total, cles[] }` |
| `POST` | `/cles.php` | Délivrer une clé `{ nom, droits: ["lecture", "ecriture"] }` ; la clé n'est renvoyée qu'une fois | `201 { id, nom, droits, cle }` · `422` |
| `DELETE` | `/cles.php?id=3` | Révoquer (1re fois), puis effacer (2e fois) | `204` · `404` |

Le nom de l'application apparaît dans le journal `api_logs` (colonne `application`) et donc dans les statistiques.

## Exemples (curl)

```bash
curl -X POST -H "Content-Type: application/json" -d '{"nom":"Jean","email":"jean@exemple.fr","type_demande":"Autre","message":"Bonjour le conseiller"}' http://localhost/Site/api/contacts.php
curl -H "X-API-Key: VOTRE_CLE" "http://localhost/Site/api/v1/contacts.php?statut=nouveau&page=1&par_page=10&tri=-date"
curl -X PATCH -H "X-API-Key: VOTRE_CLE" "http://localhost/Site/api/contacts.php?id=1"
curl http://localhost/Site/api/conseils.php
```

## Tests

```bash
php tests/test_api.php
```

## API externe utilisée : Groq (IA)

`consultation.php` envoie les réponses du questionnaire à l'API de Groq (format compatible OpenAI,
`https://api.groq.com/openai/v1/chat/completions`, modèle `GROQ_MODEL`). Sans clé ou en cas d'erreur,
le générateur local prend le relais. Les certificats racine utilisés pour le HTTPS sont dans `certs/cacert.pem`.
