# Mise en ligne — Render + Aiven

Le site tourne en ligne sur **Render** (PHP 8.3 + Apache, dans un conteneur Docker), avec une base MySQL
hébergée par **Aiven**. Les deux offres sont gratuites et sans carte bancaire.

## Mise à jour automatique

Chaque `git push` sur `main` redéploie le site tout seul (Render est relié au dépôt GitHub).
Rien d'autre à faire : on continue à travailler en local (WAMP), on pousse, et la version en ligne suit
en 2 à 4 minutes.

## Fichiers concernés

| Fichier | Rôle |
|---|---|
| `Dockerfile` | Image PHP 8.3 + Apache, extensions `pdo_mysql` et `intl`, copie `db.example.php` et `config.example.php` |
| `docker/apache.conf` | IP réelle du visiteur derrière le proxy Render, HTTPS, dossiers internes bloqués |
| `.dockerignore` | Exclut `db.php`, `config.php`, `.git`, `tests` de l'image |
| `certs/aiven-ca.pem` | Certificat **public** de la base Aiven (connexion chiffrée vérifiée) |
| `.github/workflows/reveil.yml` | Visite quotidienne du site (garde la base active, prépare le mot du lendemain) |

## Variables d'environnement (Render → conseilplus → Environment)

| Variable | Valeur | Secret |
|---|---|---|
| `DB_HOST` | `mysql-1f33b105-lechti-2651.l.aivencloud.com` | non |
| `DB_PORT` | `17075` | non |
| `DB_NAME` | `defaultdb` | non |
| `DB_USER` | `avnadmin` | non |
| `DB_SSL_CA` | `certs/aiven-ca.pem` | non |
| `DB_PASS` | mot de passe Aiven (console Aiven → service → Password) | **oui** |
| `API_KEY` | clé du logiciel de gestion (bouton *Generate* de Render) | **oui** |
| `GROQ_API_KEY` | clé Groq (facultative : sans elle, générateurs locaux) | **oui** |

Les secrets ne vont **jamais** dans Git ni dans une conversation : ils sont saisis directement dans Render.
Modifier une variable redéploie le site automatiquement.

## Alternative à Aiven : base MySQL sur un panneau Pterodactyl (ex. Echo-Host)

Une base créée depuis l'onglet **Databases** d'un panneau Pterodactyl reste toujours allumée (pas de mise en veille).
Le site reste sur Render : seule la base change.

1. Panneau → serveur → **Databases** → **New Database** : nom `site`, *Connections from* `%` (Render n'a pas d'IP fixe).
   Recommencer pour une 2e base nommée `gestion` (Le Bureau).
2. Le panneau affiche pour chaque base : *Endpoint* (hôte:port), *Username*, *Password* (bouton *View*),
   et le nom réel de la base (souvent préfixé, par ex. `s771_site`).
3. Render → conseilplus → **Environment** :
   - `DB_HOST`, `DB_PORT` : l'endpoint de la base `site` ; `DB_NAME`, `DB_USER`, `DB_PASS` : ses identifiants ;
   - `BUREAU_DB_NAME`, `BUREAU_DB_USER`, `BUREAU_DB_PASS` : ceux de la base `gestion` ;
   - **supprimer** `DB_SSL_CA` (le certificat Aiven ne sert plus).
4. Render redéploie seul ; les tables se créent au premier chargement (le code accepte de ne pas avoir le droit
   de créer la base elle-même).

Essai du 16/10 sur Echo-Host : la création de base renvoie une erreur serveur (« An unexpected error »),
probablement aucun serveur MySQL rattaché à l'offre bot Discord. Demander au support d'Echo-Host avant de réessayer.

À savoir : la connexion n'est pas chiffrée (pas de TLS sur ce type de base) et la base suit la vie de l'abonnement
du serveur (renouvellement mensuel).

## Limites des offres gratuites

- **Render** : le site s'endort après 15 min sans visite ; la visite suivante attend environ une minute.
  Les sessions sont effacées à chaque endormissement ou déploiement (il faut parfois se reconnecter ;
  comptes et carnets restent en base).
- **Aiven** : une base gratuite inactive est éteinte automatiquement. `reveil.yml` visite le site toutes les 6 h
  (une seule visite par jour n'a pas suffi le 16/10) ; si cela arrive quand même, la rallumer depuis la console Aiven
  (*Power on*) : la reconstruction depuis la sauvegarde peut prendre longtemps (état *Rebuilding*).
- **Aiven** : 1 Go de stockage, largement suffisant.

## En cas de souci

| Problème | Piste |
|---|---|
| Déploiement en échec | Render → conseilplus → *Logs* (erreur de construction Docker) |
| Page « Guichet momentanément fermé » (erreur 503) | Base injoignable : console Aiven → service MySQL → *Power on* s'il est éteint. Si le service a disparu, en recréer un (MySQL, offre Free) et reporter `DB_HOST`, `DB_PORT`, `DB_PASS` dans Render. Le détail exact est dans Render → *Logs* (« base de données indisponible ») |
| Le workflow *Réveil quotidien* échoue (e-mail de GitHub) | Même cause probable : le site répond 503 quand la base est injoignable |
| Le site met une minute à s'afficher | Normal après 15 min d'inactivité (offre gratuite) |
