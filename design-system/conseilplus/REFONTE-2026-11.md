# Refonte « Le Guichet » — concept (novembre 2026)

Décidé avec le conseil (5 experts, 2 tours) et VALIDÉ par l'auteur le 20/11/2026, avec ces choix :
- concept « Le Guichet » ;
- suivi du dossier dans « Mon dossier », COMPTE OBLIGATOIRE (l'exercice porte sur l'API et les comptes) :
  pas de lien de suivi public ; le dépôt au Guichet se fait connecté (ou propose de se connecter / s'inscrire) ;
- on garde Questions pour un stagiaire et Le Duel (manches du Guichet), le standard de Mireille (entrée des
  Réclamations) et l'horoscope (encart de Messmerde) ;
- tout refaire d'un coup sur la branche « refonte », mise en ligne quand l'ensemble est fini (Lighthouse à la fin).
Méthode visuelle : skill `design-taste-frontend` (une section par écran, vraies images, deux moments forts,
mouvement discret), appliquée à notre code (pas son kit).

## Le concept en une phrase
ConseilPlus+ devient une administration où chaque visiteur ouvre un dossier qui avance vraiment,
et repart avec un document officiel absurde : un verdict tamponné, à télécharger ou partager.

Le circuit noté du cours (site → API → Bureau → réponse) devient l'histoire que vit le visiteur :
il dépose son problème (POST, statut « nouveau »), Bertrand l'importe dans Le Bureau (« importé », affiché
« en instruction »), le cabinet répond (« répondu ») et le visiteur lit le verdict.

## La démo en moins de 60 secondes
1. Au Hall, le visiteur tire un ticket et dépose son problème au Guichet.
2. Le tampon « REÇU » s'abat : il repart avec son attestation de dépôt (partageable) ; le dossier apparaît dans « Mon dossier ».
3. Dans Le Bureau, la demande arrive dans la Salle de tri ; on la fait avancer d'un coup de tampon.
4. Le visiteur rouvre « Mon dossier » : « En instruction », puis le verdict tamponné du cabinet.

## Les pages (≈ 8 publiques)
| Page | Ce qu'elle fait | Absorbe |
|---|---|---|
| Le Hall (accueil) | Conseil du jour, ticket, article du règlement, le mot du jour en bref | index |
| Le Guichet | Une seule épreuve : quelques questions, un verdict tamponné, une attestation | consultation, diagnostic, Duel, stagiaire, attestations |
| Mon dossier | Le suivi des demandes (façon colis administratif), le verdict, les conseils gardés | compte, carnet, conseil |
| Le Personnel | L'équipe, chacun relié à une étape du dossier | cabinet |
| Le Petit Moyen | Mot du jour, étymologie, sigle, sketch | (inchangé, rhabillé) |
| Messmerde | Les prédictions rétroactives, l'horoscope en encart | messmerde |
| Nos formules | Gardée (exigée par le cours : produit.php), rhabillée en tarifs de guichet | produit |
| Réclamations | Le formulaire de contact, accueilli par Mireille | contact, standard |
| Légal | Mentions, confidentialité (durées de vie), accessibilité, fiction signalée une fois | — |
La page Développeurs reste en ligne, hors menu. Toute ancienne adresse redirige (301) : l'appli Android et les liens
partagés continuent de marcher.

## Direction visuelle
- Couleurs (clair) : papier journal #E8E8E3 (fond), papier #F7F7F3 (feuilles), kraft #C9A86A (chemises, dossiers),
  laiton #BFB21A (accents, surligneur ; texte laiton #6E6608), encre #181715, rouge tampon #C8102E (tampons, logo,
  erreurs), vert administratif #2F5D50 (statuts traités). Sombre « salle des archives » : #141412 / #1C1C19 / papier
  #232320 / kraft #8C7444 / encre #EDEDE6 / laiton #D9CB3A / rouge #FF5F6D / vert #6FB3A0.
- Matière : le dossier administratif. Chemise cartonnée kraft, papier journal, laiton, encre, rouge tampon.
- Typo : Schibsted Grotesk pour tout le texte (déjà auto-hébergée) ; JetBrains Mono (déjà là) joue la machine à écrire
  pour les numéros de dossier, tickets, champs de formulaire officiels et tampons.
- Images : les gravures à l'encre du cabinet (déjà commencées : le bureau d'archives, la corbeille en laiton),
  les portraits de l'équipe.
- Moment signature : le tampon s'abat sur l'attestation, l'encre bave légèrement (un simple fondu en mouvement réduit).
- Second moment : une phrase-manifeste qui s'encre mot à mot.
- Le logo « La note » (C+ entouré au stylo rouge) est gardé.

## Le Bureau : « La Salle de tri »
Application à barre latérale. Les demandes arrivent dans des casiers (une colonne par statut : nouveau, importé,
répondu) ; on fait avancer un dossier en le glissant ou d'un coup de tampon (équivalent au clavier et sans JS) ;
le dossier s'ouvre dans un tiroir ; graphiques maison en SVG (débit du guichet, échéances) avec tableau de repli.
Il garde tout ce qui existe (affectation, échéances, notes, réponse par courriel, relecture du mot, purge…).

## Ce qui ne bouge pas
- Les contrats de l'API (routes, JSON, statuts) ; tests verts à chaque étape ; auth/ intouchable.
- Le contenu (data/*.php) : c'est l'actif du site, on le met en scène sans le réécrire.
- Accessibilité : tout marche sans JS, sans image et au clavier ; contraste validé dans les deux thèmes ;
  les textes alternatifs portent l'humour ; on se moque de l'institution, jamais de l'usager.
- « Le Guichet adapté » : régime ordinaire / allégé (mouvement réduit, images légères, appli Android) /
  prioritaire (contraste renforcé, texte agrandi), par défaut selon les préférences du système.

## Chantier
Sur une branche « refonte », page par page, le site en ligne restant la version de secours.
1. Jetons, gabarit commun, composants (tampon, ticket, verdict, carte, bouton, champ).
2. Le Hall et le dépôt au Guichet avec attestation de dépôt.
3. Le suivi (Mon dossier) : le circuit noté démontrable.
4. La Salle de tri du Bureau.
5. Le Guichet complet (fusion des jeux).
6. Le Personnel, Le Petit Moyen, Messmerde, Formules, Réclamations, légal.
7. Service worker, appli Android, RGAA, CSP, puis Lighthouse à la toute fin.
Budget estimé : environ 30 h pour une première version complète du parcours (lots 1 à 4), 80 à 110 h pour tout.

## Exigences du professeur (tableau de la séance, photo fournie par l'auteur le 20/11/2026)
Elles priment sur tout le reste du concept.
- Deux applications dans `www/htdocs` : **Site vitrine** et **Gestion**, chacune avec SA base (« base de données vitrine »,
  « BDD Gestion »), reliées uniquement par l'**API** (le site vitrine expose, la Gestion consomme).
- Base **Gestion**, table **`inscris`** : `Id` (clé primaire), `remote_Id` (l'id de l'inscription sur le site vitrine,
  unique), `Nom`, `prénom`, `email`, `Téléphone`, `jour`, `horaire`, `create_at`, `Statut`.
- Le site vitrine recueille donc des **inscriptions à un rendez-vous** (nom, prénom, email, téléphone, jour, horaire).
  Dans le concept : le visiteur (connecté) **prend rendez-vous au guichet** du cabinet, un jour et un créneau.
- Logiciel de gestion : `index` → page de **connexion** ; `tableau` → **page d'arrivée** ; connexion / déconnexion
  (la déconnexion fait quitter et ramène à la connexion) ; onglets **Planning, Inscrits, Synchro, Export, Déconnexion** ;
  « affichage des inscrits ».
- **Synchro** : la Gestion lit par l'API les inscriptions nouvelles du site, les range dans `inscris` (par `remote_Id`,
  sans doublon) et repasse leur statut côté site (nouveau → importé) ; le Statut évolue ensuite dans la Gestion
  (et la réponse/confirmation revient au site pour « Mon dossier »).
- Connexion locale chez le prof : `localhost:8889` (MAMP), utilisateur `root`, mot de passe `root` → à rendre
  configurable sans toucher au code (variables / config), documenté dans le README.
- Le formulaire de contact actuel reste, pour les Réclamations ; les fonctions existantes du Bureau (dossiers, réponses,
  relecture du mot, statistiques, clés) restent, en second plan derrière les onglets du tableau.

## Le Bureau en ligne : une application à part (décision du 20/11/2026)
Solution gratuite : un second service Render, même dépôt, `docker/bureau/Dockerfile` (racine web = bureau/), adresse
du type `https://conseilplus-bureau.onrender.com` (nom choisi à la création). Il ne parle au site que par l'API
(`BUREAU_API_URL` = https://conseilplus.onrender.com/api) et garde sa base « gestion ». Seul le site vitrine reste
éveillé (UptimeRobot) ; le Bureau s'endort quand personne ne l'utilise (≈ 50 s de réveil), pour rester sous les 750 h
gratuites par mois. À la mise en ligne de la refonte : `/bureau/` du site vitrine redirige (301) vers la nouvelle adresse.
Création du service et saisie des secrets (API_KEY, DB_*, BREVO_API_KEY…) : par l'auteur, guidé par docs/DEPLOIEMENT.md.

## Planche de références (recherche du 20/11/2026 — on retient des principes, on ne copie rien)
| Référence | Ce qu'on en retient pour « Le Guichet » |
|---|---|
| Stripe Press (édition) | Fond papier uni, presque rien d'autre ; libellés d'interface en petites capitales machine entre crochets « [ Mon dossier ] » ; un très grand titre qui défile comme une enseigne ; l'objet (le livre) traité en héros. → nos numéros, tickets et libellés en mono entre crochets ; le tampon et la chemise comme objets-héros. |
| Standards Manual (manuels de normes) | Navigation en grandes lettres grasses alignées sur une grille, une barre de couleur unique (rouge) sous le logo ; l'objet photographié en très grand sur fond noir ; mosaïque de médias de tailles différentes. → la grille stricte d'un manuel administratif, un seul accent (rouge tampon) très présent, des gravures en très grand. |
| Order (archives, identités) | Libellés minuscules en mono, beaucoup de vide, des planches d'objets juxtaposés bord à bord comme dans un classeur. → les services présentés comme des fiches d'un classeur, alignées bord à bord. |
| Cooper Hewitt (musée) | Grille de collection pleine largeur, menus en colonnes sous filets fins, liens d'action en couleur d'accent en petites capitales. → le Personnel et les archives du Petit Moyen en « collection » ; filets fins, pas de cartes à ombre. |
| Teenage Engineering (objets) | Pictogrammes au trait fin à côté de chaque entrée du menu ; l'objet seul, éclairé, au centre. → pictogrammes maison au trait (guichet, chemise, tampon, plume) dans la navigation et la barre latérale du Bureau. |
| Linear (application) | Pour Le Bureau : thème sombre profond, barre latérale fine, densité maîtrisée, une seule couleur d'accent, filets 1 px, statuts en petites pastilles, panneau de détail à droite. → la Salle de tri : sombre « archives » possible, laiton comme seul accent, casiers en colonnes, tiroir de détail. |
Ce qu'on évite : les rangées de logos clients, les grands chiffres décoratifs, les dégradés, les cartes à ombre partout, le fondu au défilement.
