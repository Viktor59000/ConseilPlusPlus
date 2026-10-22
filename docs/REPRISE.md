# Reprendre le projet sur un autre PC

Guide pour installer ConseilPlus+ sur une nouvelle machine, puis passer d'un PC à l'autre sans perdre le fil.

## 1. Prérequis (une seule fois par PC)

- **WAMP** (ou XAMPP, Laragon) avec PHP 8.1 minimum (8.3 recommandé) et MySQL.
  Extensions PHP à activer : `curl`, `openssl`, `intl`, `pdo_mysql` (actives par défaut sur WAMP).
- **Git** et un accès au dépôt https://github.com/Viktor59000/ConseilPlusPlus
- **Claude Code** (application de bureau ou terminal).

## 2. Récupérer le projet

```bash
git clone https://github.com/Viktor59000/ConseilPlusPlus.git Site
```

Le plus simple est de cloner directement dans le dossier web de WAMP (`C:\wamp64\www\Site`).
Le site sera alors sur http://localhost/Site/

Variante (cloner ailleurs, par exemple dans tes cours) : crée un lien depuis `www` vers le dossier, dans PowerShell :

```powershell
New-Item -ItemType Junction -Path C:\wamp64\www\Site -Target "C:\chemin\vers\le\clone"
```

## 3. Recréer les fichiers de configuration (ils ne sont PAS sur GitHub)

`db.php` est versionné (aucun secret : il charge `includes/bdd.php`, qui lit les identifiants MySQL dans les variables `DB_*`,
sinon `root` sans mot de passe).
Si `git pull` refuse d'écraser un ancien `db.php` local, le supprimer puis relancer `git pull`.

1. Copier `config.example.php` en `config.php`, puis renseigner :
   - `API_KEY` : n'importe quelle longue chaîne aléatoire (clé du futur logiciel de gestion) ;
   - `GROQ_API_KEY` : ta clé Groq (https://console.groq.com/keys). Laisse vide pour tester sans IA :
     le site utilise alors ses générateurs locaux.
2. Copier `bureau/config.example.php` en `bureau/config.php` et y mettre dans `BUREAU_API_KEY`
   la **même** valeur que `API_KEY` (le Bureau s'en sert pour appeler l'API).
   Facultatif : `BREVO_API_KEY` et `BUREAU_EXPEDITEUR` pour envoyer réellement les courriels en local.

> Rappel : la clé Groq est passée par une conversation le 14/10/2026. Régénère-la sur console.groq.com
> et mets la nouvelle dans `config.php` sur chaque PC.

## 4. Premier lancement

1. Démarrer WAMP (icône verte).
2. Ouvrir http://localhost/Site/ : `includes/schema.php` crée tout seul la base `site`, toutes les tables et les données de départ
   (et ne se rejoue que lorsqu'il change). Ouvrir http://localhost/Site/bureau/ crée la base `gestion` et propose de créer
   le premier agent (la clé `API_KEY` est demandée).
3. Vérifier l'API :

```bash
php tests/test_api.php
```

Résultat attendu : « Tous les tests sont passés. » (`php tests/test_pages.php` vérifie aussi toutes les pages.)
(Si `php` n'est pas reconnu, utilise le chemin complet, par exemple `C:\wamp64\bin\php\php8.3.14\php.exe`.)

> La base de données n'est pas synchronisée entre les PC : comptes, carnet et historique du mot du jour
> sont propres à chaque machine. C'est normal pour un environnement de développement.

## 5. Passer d'un PC à l'autre

**Avant de quitter un PC :**
- Demande à Claude : « Je change de PC, mets à jour le journal et pousse. »
  (Il met à jour `docs/JOURNAL.md` puis fait commit + push.)
- Ou à la main : `git add -A`, `git commit -m "…"`, `git push`.

**En arrivant sur l'autre PC :**
- `git pull` (ou demande à Claude de le faire).
- Ouvre Claude Code dans le dossier du projet et commence par :
  « Lis CLAUDE.md et docs/JOURNAL.md, puis dis-moi où on en est. »

Claude lit automatiquement `CLAUDE.md` (contexte, règles, préférences) ; `docs/JOURNAL.md` lui donne
l'état exact et la suite prévue. Rien d'autre à copier.

## 6. En cas de souci

| Problème | Solution |
|---|---|
| Page blanche ou « Erreur de connexion à la BDD » (503) | WAMP démarré ? identifiants MySQL corrects (variables `DB_*`, sinon root sans mot de passe) ? |
| L'IA ne répond jamais (« Rédigé par : le stagiaire ») | `GROQ_API_KEY` vide ou invalide dans `config.php`, ou quota Groq dépassé |
| « SSL certificate problem » dans les logs | Le fichier `certs/cacert.pem` doit être présent (il est dans le dépôt) |
| Conflit au `git pull` | Ne pas forcer : demander à Claude de résoudre le conflit |
| Dates étranges (mot du jour qui change à minuit) | Le fuseau est fixé à Europe/Paris dans `includes/bdd.php` |
| Le Bureau répond « Clé d'API refusée par le site » | `API_KEY` différente entre `config.php` et `bureau/config.php` |
