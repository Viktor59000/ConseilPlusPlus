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
   modifiable, puis « Envoyer par email et classer » (voir ci-dessous ; sans configuration, la réponse est archivée).
4. **Journal des relevés** : chaque import, avec le nombre de dossiers créés et les erreurs.

Statuts d'un dossier : `a_traiter` → `en_cours` → `classe` (réouverture possible).

## Envoi des réponses par email (Brevo)

Render bloque le SMTP (ports 25, 465, 587) sur l'offre gratuite depuis septembre 2025 : les réponses partent donc par
l'**API HTTPS de Brevo** (gratuit, 300 emails par jour, sans carte bancaire).

1. Créer un compte sur brevo.com.
2. *Senders, domains & dedicated IPs* → *Senders* → ajouter l'adresse d'expédition et la confirmer (lien reçu par email).
3. *SMTP & API* → *API keys* → générer une clé.
4. Dans Render → conseilplus → Environment : `BREVO_API_KEY` (la clé) et `BUREAU_EXPEDITEUR` (l'adresse confirmée).
   En local : les mêmes valeurs dans `bureau/config.php`.

Sans ces réglages, « Archiver et classer » garde la réponse dans le dossier sans envoyer de mail. Si l'envoi échoue
(adresse invalide, quota atteint), le dossier reste ouvert avec la réponse enregistrée, pour réessayer.
Colonne `envoi` du dossier : `envoye`, `archive` ou `echec`.

## Statistiques et tons de réponse

- **Statistiques** (`bureau/statistiques.php`) : appels à l'API par jour (7, 14 ou 30 jours), ressources, codes de réponse,
  applications appelantes. Lu par l'API elle-même (`GET /api/statistiques.php`, clé requise).
- **Clés d'API** (`bureau/cles.php`) : délivrer une clé à une autre application (droits lecture et/ou écriture, affichée une
  seule fois), la révoquer, puis l'effacer. Passe par `/api/cles.php`, réservé à la clé maîtresse du Bureau.
- **Ton de la réponse** proposée dans un dossier : standard, très administratif (articles, références) ou chaleureux (moyennement).

## Sauvegardes

Tableau de bord → carte « Sauvegardes » : un clic télécharge un fichier `.sql` complet.
- **Base du site** : demandée à l'API (`api/export.php`, clé requise) — le Bureau ne lit toujours pas la base du site.
- **Base du Bureau** : exportée directement (`bureau/export.php`).
Réimport : phpMyAdmin → base vide → *Importer*. Testé : réimport sans erreur, données identiques.

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
