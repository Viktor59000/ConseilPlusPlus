# API ConseilPlus+

Base : `http://localhost/Site/api` — réponses en JSON (UTF-8).

## Contacts — `api/contacts.php`

Échange des demandes de contact entre le site vitrine et le logiciel de gestion.

| Méthode | URL | Clé | Rôle | Réponses |
|---|---|---|---|---|
| `POST` | `/contacts.php` | non | Le site vitrine envoie une demande | `201` créée · `422` champs invalides |
| `GET` | `/contacts.php?statut=nouveau` | oui | Lister les demandes (filtre `nouveau` / `importe` facultatif) | `200` · `401` |
| `GET` | `/contacts.php?id=42` | oui | Détail d'une demande | `200` · `404` |
| `PATCH` | `/contacts.php?id=42` | oui | Changer le statut (par défaut `importe`) | `200` · `404` · `422` |
| `DELETE` | `/contacts.php?id=42` | oui | Supprimer une demande traitée | `204` · `404` |

**Clé** : en-tête `X-API-Key`, valeur `API_KEY` de `config.php`.

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

## Conseils — `api/conseils.php`

| Méthode | URL | Clé | Rôle |
|---|---|---|---|
| `GET` | `/conseils.php` | non | Un conseil aléatoire |
| `GET` | `/conseils.php?nombre=3` | non | Plusieurs conseils (5 max) |

## Exemples (curl)

```bash
curl -X POST -H "Content-Type: application/json" -d '{"nom":"Jean","email":"jean@exemple.fr","type_demande":"Autre","message":"Bonjour le conseiller"}' http://localhost/Site/api/contacts.php
curl -H "X-API-Key: VOTRE_CLE" "http://localhost/Site/api/contacts.php?statut=nouveau"
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
