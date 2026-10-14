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

## Limites des offres gratuites

- **Render** : le site s'endort après 15 min sans visite ; la visite suivante attend environ une minute.
  Les sessions sont effacées à chaque endormissement ou déploiement (il faut parfois se reconnecter ;
  comptes et carnets restent en base).
- **Aiven** : une base gratuite inactive peut être éteinte. La visite quotidienne (`reveil.yml`) l'évite ;
  si cela arrive quand même, la rallumer depuis la console Aiven (*Power on*).
- **Aiven** : 1 Go de stockage, largement suffisant.

## En cas de souci

| Problème | Piste |
|---|---|
| Déploiement en échec | Render → conseilplus → *Logs* (erreur de construction Docker) |
| « Erreur de connexion ou d'initialisation de la BDD » | Base Aiven éteinte (la rallumer) ou `DB_PASS` incorrect |
| Le site met une minute à s'afficher | Normal après 15 min d'inactivité (offre gratuite) |
