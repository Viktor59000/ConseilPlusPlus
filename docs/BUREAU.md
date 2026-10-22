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
2. **Dossiers** : liste filtrable (statut, type, « assignés à moi », « en retard »), recherche dans le nom, l'email
   **et le message**, tri par date de réception ou par échéance. **Exporter en CSV** télécharge la sélection affichée
   (`bureau/export-dossiers.php`, POST + CSRF ; BOM UTF-8, séparateur `;`, lisible directement par Excel ; une cellule
   commençant par `=`, `+`, `-` ou `@` est préfixée d'une apostrophe pour ne jamais être lue comme une formule).
3. **Fiche dossier** : prise en charge, réponse officielle proposée par le cabinet (conseil tiré de `GET /api/conseils.php`),
   modifiable, puis « Envoyer par email et classer » (voir ci-dessous ; sans configuration, la réponse est archivée).
   - **Affectation** : un agent responsable (liste des agents) et une **échéance** interne (vraie date), à ne pas confondre
     avec le « délai estimé » absurde annoncé au client. « Prendre en charge » fait de l'agent le responsable s'il n'y en a pas.
   - **Dernier intervenant** : mis à jour par les actions qui modifient le dossier (prise en charge, affectation,
     enregistrement, classement, réouverture), pas par « Rédiger une réponse », qui ne fait que proposer un texte.
   - **Notes internes** : visibles des seuls agents, jamais envoyées au demandeur ni exportées.
   - **Autres demandes de cette personne** : les autres dossiers ouverts à la même adresse email.
   - **Purger du site** (dossier classé) : `DELETE /api/contacts.php?id=…` supprime la demande dans la base du site ;
     le dossier reste au Bureau avec sa date de purge. Case de confirmation obligatoire (`bureau/purge.php`).
4. **Tableau de bord** : chiffres (dont le nombre de dossiers en retard), « Mes dossiers », « En retard » (échéance
   dépassée, dossier non classé), dossiers en attente, et **purge en lot** des dossiers classés depuis plus de 6 mois
   (200 au plus par passage, avec confirmation).
5. **Journal** (`journal.php`) : chaque relevé (nombre de dossiers créés, erreurs) et chaque purge du site.

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
| `dossiers` | Demandes importées (`id_site` = id de la demande sur le site), statut, réponse, `id_agent` (dernier intervenant), `id_responsable`, `echeance`, `date_purge` |
| `notes` | Notes internes des dossiers (`id_dossier`, `id_agent`, `texte`, `date`) |
| `releves` | Journal du Bureau : relevés de courrier et purges (colonne `nature` : `releve` ou `purge`) |

Les colonnes et tables ajoutées après coup sont créées par des migrations idempotentes (`bureau/includes/bdd.php`).
Pas de clé étrangère sur les nouvelles colonnes : la base locale (WAMP) crée des tables MyISAM.

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
