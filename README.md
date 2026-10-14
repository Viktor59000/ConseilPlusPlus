# ConseilPlus+ — Le conseiller moyen

Site vitrine PHP/MySQL (PDO) d'un faux cabinet de conseil qui délivre des conseils absurdes.

**En ligne : https://conseilplus.onrender.com** (mis à jour automatiquement à chaque push, voir [docs/DEPLOIEMENT.md](docs/DEPLOIEMENT.md)).

> **Reprendre le projet sur un autre PC** : [docs/REPRISE.md](docs/REPRISE.md) (installation + passage d'un PC à l'autre).
> **Où en est-on ?** : [docs/JOURNAL.md](docs/JOURNAL.md). Instructions pour Claude Code : [CLAUDE.md](CLAUDE.md).

## Installation

1. Placer le dossier dans le `www` de WAMP (ou équivalent).
2. Copier `db.example.php` en `db.php` (identifiants MySQL) et `config.example.php` en `config.php` (clé de l'API, clé Groq facultative).
3. Ouvrir le site : `db.php` crée la base `site`, les tables et remplit les fragments de conseils au premier chargement.

## Pages

| Page | Fichier |
|---|---|
| Accueil | `index.php` |
| Produit / service | `produit.php` |
| Contact | `contact.php` (enregistré dans `contacts`, statut `nouveau`) |
| Le Petit Moyen | `petit-moyen.php` (mot du jour : fausse définition ou sketch) |
| Le Bureau (logiciel de gestion) | `bureau/` : relève les demandes via l'API, dossiers, réponses (voir [docs/BUREAU.md](docs/BUREAU.md)) |
| Espace client | `login.php` → `conseil.php` → `consultation.php` (questionnaire + IA) → `carnet.php` (conseils classés) → `duel.php` (mode Duel contre l'arbitre IA), réservés aux connectés |

## API

Voir [docs/API.md](docs/API.md) : `api/contacts.php` (REST, site → logiciel de gestion), `api/mes-conseils.php` (carnet), `api/conseils.php` et `api/mot-du-jour.php`. Chaque échange est journalisé dans `api_logs`.

## Base de données

| Table | Rôle |
|---|---|
| `contacts` | Demandes du formulaire de contact (`nom`, `email`, `message`, `type_demande`, `statut` nouveau/importe, `date_creation`) — source de la future API |
| `utilisateurs` | Comptes clients (mot de passe salé + SHA-512) |
| `carnet` | Conseils classés par chaque utilisateur |
| `api_logs` | Journal des échanges de l'API (méthode, ressource, code, IP, application) |
| `fragments` | Morceaux de conseils (situation / action / résultat), synchronisés automatiquement avec `data/fragments.php` |

Banques de contenu à enrichir librement :
- `data/fragments.php` : 110 situations, 120 actions, 95 conclusions écrites à la main, combinées aux actions composées par les gabarits Kamoulox.
- `data/questions.php` : 40 questions absurdes, 4 tirées au hasard à chaque consultation.
- `data/kamoulox.php` : banques lexicales (verbes, objets, lieux, animaux, célébrités, pays, jargon de jeu) utilisées par les gabarits Kamoulox de `includes/kamoulox.php`.

## Crédits

Le générateur de conseils est un **hommage non officiel** au Kamoulox de Kad Merad et Olivier Baroux. Aucune réplique ni aucun visuel original n'est repris.

## Arborescence

```
index.php, produit.php, contact.php, login.php, conseil.php
includes/      header.php, footer.php, tirage.php
api/           contacts.php, conseils.php (API REST JSON)
auth/          connexion.php, inscription.php, logout.php
includes/      + api.php, http.php, contacts.php, ia.php
tests/         test_api.php
docs/          API.md
certs/         cacert.pem (certificats HTTPS)
css/style.css
assets/        logo.svg, icons/ (favicon, icônes web)
branding/      logo source (SVG), concepts
design-system/ charte graphique (MASTER.md)
data/          fragments.php
db.example.php, config.example.php
```
