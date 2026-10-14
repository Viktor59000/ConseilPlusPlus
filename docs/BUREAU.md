# Le Bureau — logiciel de gestion

Deuxième application du projet (attendu du cours) : le back-office du cabinet. Elle **ne lit jamais la base du site** :
les demandes de contact arrivent uniquement par l'API REST (`api/contacts.php`, clé `X-API-Key`).

- En local : http://localhost/Site/bureau/
- En ligne : https://conseilplus.onrender.com/bureau/

## Fonctionnement

1. **Relever le courrier** (tableau de bord) :
   `GET /api/contacts.php?statut=nouveau` → chaque demande devient un dossier dans la base `gestion`,
   puis `PATCH /api/contacts.php?id=…` la marque `importe` sur le site.
   Sans doublon possible : `id_site` est unique, et une demande dont le marquage a échoué est re-marquée au relevé suivant.
2. **Dossiers** : liste filtrable (statut, type, nom ou email).
3. **Fiche dossier** : prise en charge, réponse officielle proposée par le cabinet (conseil tiré de `GET /api/conseils.php`),
   modifiable, puis « Envoyer et classer ». (Aucun email réel n'est envoyé : la réponse est archivée dans le dossier.)
4. **Journal des relevés** : chaque import, avec le nombre de dossiers créés et les erreurs.

Statuts d'un dossier : `a_traiter` → `en_cours` → `classe` (réouverture possible).

## Base `gestion` (créée automatiquement)

| Table | Rôle |
|---|---|
| `agents` | Comptes du Bureau (mot de passe `password_hash`) |
| `dossiers` | Demandes importées (`id_site` = id de la demande sur le site), statut, réponse, agent |
| `releves` | Journal des relevés de courrier |

## Installation

- **En local** : copier `bureau/config.example.php` en `bureau/config.php` et y mettre la même clé d'API que le site
  (`API_KEY` de `config.php`). La base `gestion` se crée au premier chargement.
- **En ligne** : rien à faire. Le Dockerfile crée `bureau/config.php`, qui lit `API_KEY` et `DB_*` dans Render ;
  le Bureau appelle l'API à l'intérieur du conteneur (`URL_INTERNE`).
- **Premier agent** : à la première ouverture, la page de connexion propose de créer le compte ; elle demande la
  **clé d'API** du site, pour que personne d'autre ne puisse s'approprier le Bureau.

## Sécurité

Session séparée de celle du site (`BUREAU`), cookie `HttpOnly` + `SameSite=Lax`, jeton CSRF sur tous les formulaires
(déconnexion comprise), `session_regenerate_id()` à la connexion, requêtes préparées, échappement des sorties,
dossier `bureau/includes` bloqué en ligne, pages non indexées (`noindex`).
