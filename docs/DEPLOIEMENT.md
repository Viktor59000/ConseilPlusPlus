# Mise en ligne — Render + TiDB Cloud

Le site tourne en ligne sur **Render** (PHP 8.3 + Apache, dans un conteneur Docker), avec une base MySQL
hébergée par **TiDB Cloud Serverless** (compatible MySQL, région Francfort). Les deux offres sont gratuites et
sans carte bancaire. (Jusqu'au 16/10, la base était chez Aiven : éteinte faute d'activité, voir plus bas.)

## Mise à jour automatique

Chaque `git push` sur `main` redéploie le site tout seul (Render est relié au dépôt GitHub).
Rien d'autre à faire : on continue à travailler en local (WAMP), on pousse, et la version en ligne suit
en 2 à 4 minutes.

## Fichiers concernés

| Fichier | Rôle |
|---|---|
| `Dockerfile` | Image PHP 8.3 + Apache, extensions `pdo_mysql` et `intl`, copie `config.example.php` |
| `docker/apache.conf` | IP réelle du visiteur derrière le proxy Render, HTTPS, dossiers internes bloqués |
| `.dockerignore` | Exclut `config.php`, `.git`, `tests` de l'image |
| `certs/aiven-ca.pem` | Ancien certificat Aiven (inutile avec TiDB, qui utilise les autorités du système) |
| `includes/courriel.php` | Gabarit HTML des courriels (logo, signature, tampon) et envoi par l'API Brevo |
| `assets/email/` | Logo et icône PNG des courriels (servis par le site en ligne) |
| `.github/workflows/reveil.yml` | Passes toutes les 6 h : vérifie site et base, prépare le mot du lendemain, notification du matin |
| `.github/workflows/garde-eveille.yml` | Un passage de près de 6 h relancé chaque heure, qui visite le site toutes les 9 min : le site gratuit ne s'endort plus (750 h/mois offertes par Render, assez pour un service) |

## Variables d'environnement (Render → conseilplus → Environment)

| Variable | Valeur | Secret |
|---|---|---|
| `DB_HOST` | `gateway01.eu-central-1.prod.aws.tidbcloud.com` | non |
| `DB_PORT` | `4000` | non |
| `DB_NAME` | `site` (Le Bureau crée et utilise `gestion` sur le même serveur) | non |
| `DB_USER` | `4UjHJ89hDwKrPSe.root` | non |
| `DB_SSL_CA` | `/etc/ssl/certs/ca-certificates.crt` (TLS obligatoire chez TiDB) | non |
| `DB_PASS` | mot de passe TiDB (console TiDB → cluster → *Connect* → *Generate Password*) | **oui** |
| `API_KEY` | clé du logiciel de gestion (bouton *Generate* de Render) | **oui** |
| `GROQ_API_KEY` | clé Groq (facultative : sans elle, générateurs locaux) | **oui** |
| `BREVO_API_KEY` | clé API Brevo (Brevo → SMTP et API → Clés API) : accusés de réception + réponses du Bureau | **oui** |
| `BUREAU_EXPEDITEUR` | `conseilplusplus@gmail.com` (expéditeur vérifié dans Brevo) | non |

Les secrets ne vont **jamais** dans Git ni dans une conversation : ils sont saisis directement dans Render.
Modifier une variable redéploie le site automatiquement.

## Secrets du dépôt GitHub (Settings → Secrets and variables → Actions)

| Secret | Utilisé par | Valeur |
|---|---|---|
| `API_KEY` | `reveil.yml` : préparer le mot du jour et envoyer la notification (POST protégés, sinon 401) | la même que `API_KEY` sur Render |
| `ANDROID_CLE_P12` | `android.yml` : signature de l'APK | le fichier `conseilplus.p12` encodé en base64 |
| `ANDROID_CLE_MDP` | `android.yml` | le mot de passe de cette clé |

La clé de signature Android est gardée **hors du dépôt** (l'ancienne, publiée par erreur dans Git jusqu'au 22/10/2026,
est abandonnée : ne jamais la réutiliser). Changer de clé oblige à désinstaller puis réinstaller l'appli une fois.

## Autre alternative : base MySQL sur un panneau Pterodactyl (ex. Echo-Host)

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

- **Render** : le site s'endort après 15 min sans visite (la visite suivante attend environ une minute) ;
  `garde-eveille.yml` le visite toutes les 9 min pour l'éviter (passage de près de 6 h, relayé par la passe horaire suivante : GitHub sautait les passes de 10 min).
  En plus, **UptimeRobot** (offre gratuite, compte de l'utilisateur, en place depuis le 22/10/2026) interroge le site à intervalle
  régulier (toutes les 5 min au mieux sur l'offre gratuite) : il le garde éveillé si GitHub saute des passes, et prévient par courriel quand le site ne répond plus.
  Les sessions sont effacées à chaque endormissement ou déploiement (il faut parfois se reconnecter ;
  comptes et carnets restent en base).
- **TiDB Cloud Serverless** : 5 Go de stockage et un quota mensuel de « Request Units », très au-dessus des besoins.
  Pas d'extinction pour inactivité. `reveil.yml` visite quand même le site toutes les 6 h (mot du jour, notification).
- **Brevo** : 300 courriels par jour. L'expéditeur est une adresse Gmail : Brevo prévient qu'un domaine « freemail »
  peut finir en indésirables (DMARC). Un nom de domaine à soi réglerait ça.
- **Ancienne base Aiven** : éteinte automatiquement faute d'activité le 16/10, bloquée en *Rebuilding*. Les données
  d'avant (comptes, carnets, dossiers du Bureau) y sont restées ; à migrer vers TiDB si elle revient.

## En cas de souci

| Problème | Piste |
|---|---|
| Déploiement en échec | Render → conseilplus → *Logs* (erreur de construction Docker) |
| Page « Archives momentanément égarées » (erreur 503) | Base injoignable : console TiDB Cloud → vérifier que le cluster est actif ; si le mot de passe a été régénéré, le reporter dans `DB_PASS` (Render). Le détail exact est dans Render → *Logs* (« base de données indisponible ») |
| Le workflow *Réveil quotidien* échoue (e-mail de GitHub) | Étape en `503` : base injoignable (ligne précédente). Étape « Préparer le mot de demain » ou « notification » en `401` : le secret GitHub `API_KEY` manque ou ne correspond plus à `API_KEY` sur Render (à reporter après chaque régénération) |
| Le site met une minute à s'afficher | Le site s'est endormi : vérifier que le workflow *Garde éveillé* tourne (onglet Actions de GitHub) |
| Aucun courriel reçu après le formulaire de contact | `BREVO_API_KEY` / `BUREAU_EXPEDITEUR` dans Render ; Brevo → *Statistiques* → *Transactionnel* ; dossier Indésirables. Au plus 2 accusés par adresse et par jour |

## Sécurité en ligne

- `docker/securite.ini` : cookies de session `HttpOnly`, `Secure`, `SameSite=Lax`, mode strict ; version de PHP masquée.
- `docker/apache.conf` : HSTS (HTTPS obligatoire), `X-Frame-Options: DENY`, `Permissions-Policy`, version d'Apache masquée.
- Politique de contenu (CSP) envoyée par PHP (`envoyer_csp()` dans `includes/theme.php`) : seuls les fichiers du site ; le seul
  script intégré (thème) est autorisé par son empreinte. **Ajouter un script dans une page = le mettre dans un fichier `js/`.**
- Limite des essais (`includes/limite-connexion.php`) : 10 connexions par quart d'heure et 5 inscriptions par heure et par
  adresse IP, exécutée juste avant `auth/` (code du cours non modifié) par `auto_prepend_file` (Apache en ligne,
  `auth/.htaccess` sous WAMP).
- Tests automatiques à chaque push (`.github/workflows/tests.yml`) : syntaxe PHP, pages (`tests/test_pages.php`), API, Web Push.

## Exporter (sauvegarder) les bases

**Le plus simple** : Le Bureau (en local ou en ligne) → tableau de bord → carte « Sauvegardes » →
« Télécharger la base du site » ou « Télécharger la base du Bureau ». Le fichier `.sql` se réimporte avec
phpMyAdmin (base vide → *Importer*). Les méthodes ci-dessous restent utiles sans accès au Bureau.

Deux bases : `site` (comptes, contacts, carnets, mots du jour…) et `gestion` (Le Bureau). Le mot de passe est toujours
demandé par `-p` (jamais écrit dans la commande, il resterait dans l'historique).

**En local (WAMP)** — dans un terminal ouvert dans le dossier du projet :

```bash
C:/wamp64/bin/mysql/mysql9.1.0/bin/mysqldump.exe -uroot --single-transaction --no-tablespaces --set-gtid-purged=OFF --databases site gestion > sauvegarde-locale.sql
```

Ou sans terminal : phpMyAdmin (icône WAMP → phpMyAdmin) → base `site` → onglet *Exporter* → format SQL → *Exécuter*.

**En ligne (TiDB Cloud)** — même outil, avec l'adresse de TiDB et la connexion chiffrée (certificats `certs/cacert.pem`) :

```bash
C:/wamp64/bin/mysql/mysql9.1.0/bin/mysqldump.exe -h gateway01.eu-central-1.prod.aws.tidbcloud.com -P 4000 -u 4UjHJ89hDwKrPSe.root -p --ssl-mode=VERIFY_IDENTITY --ssl-ca=certs/cacert.pem --single-transaction --no-tablespaces --set-gtid-purged=OFF --databases site gestion > sauvegarde-tidb.sql
```

Autre voie : console TiDB Cloud → cluster → *Data* → *Export* (fichier SQL ou CSV à télécharger).

**Réimporter** un fichier (par exemple la base en ligne vers WAMP) :

```bash
C:/wamp64/bin/mysql/mysql9.1.0/bin/mysql.exe -uroot < sauvegarde-tidb.sql
```

Les fichiers `.sql` contiennent des données personnelles (adresses email, empreintes des mots de passe) : ne jamais les
mettre dans Git (`*.sql` est ignoré).
