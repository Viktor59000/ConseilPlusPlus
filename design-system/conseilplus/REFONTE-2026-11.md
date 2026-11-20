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

## Système (lot 1 : fondations)
Tout le nouveau site s'appuie sur ces pièces. Revue visuelle : `tests/galerie.php` (sans base de données :
`php -S 127.0.0.1:8150 -t .` puis `/tests/galerie.php` ; `?theme=clair|sombre&regime=ordinaire|allege|prioritaire` force le rendu).
Transition : les pages pas encore migrées gardent `includes/header.php`, `includes/footer.php`, `css/base.css`, `css/style.css`
(à supprimer à la fin de la refonte).

### Fichiers
| Chemin | Rôle |
|---|---|
| `css/v2/jetons.css` | Seules valeurs brutes : couleurs clair / sombre (contrastes AA notés en commentaire), `--accent`, échelle typographique, espacements (4 → 128), rayons, ombres des papiers, durées et courbes, régimes. Déclare `@layer jetons, socle, composants, site, bureau, page` |
| `css/v2/socle.css` | Polices, remise à zéro, titres, liens (trait qui se trace), focus, évitement, libellés « machine » entre crochets, utilitaires (`.visuellement-cache`, `.mono`, `.muet`, `.reference`, `.chapeau`, `.texte`, `.surligne`, `.mesure`) |
| `css/v2/composants.css` | Styles des composants PHP |
| `css/v2/site.css` | Conteneur, grille, en-tête (barre rouge sous le logo), sections « un écran », héros split, entrée du héros, phrase qui s'encre, pied |
| `css/v2/bureau.css` | Lot Bureau : thème « archives » (`data-espace="bureau"` : laiton seul accent, sombre plus profond), casiers |
| `includes/gabarit.php` | `page_debut(array $meta)` / `page_fin()`, `css_v2()`, `filtre_encre()`, `MENU_SITE` |
| `includes/ui/ui.php` | Point d'entrée des composants + `machine()`, `ui_racine()`, `ui_id()`, `ui_attributs()`, `ui_classes()`, `ui_fleche()`, `ui_logo()` |
| `includes/ui/{actions,papiers,blocs,champs,scene,graphiques,regime,pictos}.php` | Les composants (une famille par fichier, documentée en tête) |
| `js/v2/guichet.js` | Menu, régime, entrée du héros, tampon, phrase qui s'encre ; expose `window.Guichet` |
| `assets/scenes/<nom>-<largeur>.webp` + `<nom>-800.jpg` | Gravures : cabinet, methode-1, guichet, dossier, salle-de-tri, standard (480 / 800 / 1200, 1600 pour salle-de-tri ; qualité 78). PNG sources hors dépôt : `Documents/Cours/Semestre5/images-sources/` |

### Gabarit
```php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/gabarit.php';
page_debut(['titre' => 'Le Hall', 'description' => '…', 'page' => 'hall']);
// <section class="heros" data-enter>…</section> <section class="ecran">…</section>
page_fin();
```
`$meta` : `titre` (ou `titre_complet`), `description`, `page` (hall | guichet | petit-moyen | personnel | reclamations | dossier),
`racine` ('' ou '../'), `base_href`, `canonical` (false : aucune), `image`, `image_alt`, `robots`, `css` (feuilles en plus, couche
`page`), `scripts` (en plus, defer, versionnés), `bureau` (true : bureau.css), `html_attributs` (ex. `['data-espace' => 'bureau']`),
`classe_corps`. Fournit : CSP (`envoyer_csp()`), CSS intégré minifié (`css_integre()` via `css_v2()`), polices préchargées, script
thème + régime avant affichage, évitement, filtre `#encre-tampon`, en-tête (logo « La note », menu à pictogrammes, `[ Mon dossier ]`
→ compte.php ou login.php, thème, régime), `<main id="contenu">`, pied (Services, Le cabinet, Légal, Développeurs, CERFA 0-A,
mention unique de la fiction), scripts.

### Composants (fonctions qui RENVOIENT du HTML ; texte échappé, sauf paramètres `…_html`)
| Signature | Options principales |
|---|---|
| `bouton(string $libelle, ?string $href = null, array $o = [])` | `variante` principal (défaut) / secondaire, `fleche`, `taille` compacte, `large`, `type`, `nom`, `valeur`, `icone_html`, `classe`, `attributs` |
| `lien_fleche(string $libelle, string $href, array $o = [])` | action tertiaire fléchée |
| `machine(string $texte, array $o = [])` | libellé « [ TEXTE ] » en mono capitales (crochets non lus) |
| `picto(string $nom, array $o = [])` | guichet, chemise, tampon, plume, telephone, casier, sonnette ; `titre` (sinon décoratif) |
| `tampon(string $texte, array $o = [])` | `couleur` rouge / vert / encre / laiton, `angle`, `taille` petit / grand / geant, `mention`, `mouvement` aucun / au-chargement / en-attente, `decoratif`, `id` |
| `statut(string $statut, array $o = [])`, `statut_libelle()` | nouveau « Déposé », importe « En instruction », repondu « Répondu », refuse ; libellés : `STATUTS_DOSSIER` (seule source) |
| `ticket(int\|string $numero, array $o = [])` | `guichet`, `libelle`, `date`, `attente`, `tampon_html` |
| `dossier(string $numero, string $statut, string $contenu_html, array $o = [])` | chemise kraft : `titre`, `niveau`, `meta`, `tampon_html`, `id` |
| `attestation(array $o)` | `type` attestation / verdict, `titre`, `niveau`, `reference`, `service`, `corps` / `corps_html`, `champs`, `signataire`, `fonction`, `paraphe`, `tampon` (options de `tampon()` + `texte`) |
| `carte(array $o)` | `titre`, `texte` / `texte_html`, `href` (carte entière cliquable), `etiquette`, `scene` ['nom', 'alt'], `pied_html`, `niveau` ; dans `.rangee.rangee--classeur` : fiches bord à bord |
| `bloc_alerte(string $texte, string $type = 'info', array $o = [])` | info / succes / attention / erreur (role alert) ; `titre`, `reference`, `texte_html` (`alerte()` de formulaire.php reste aux anciennes pages) |
| `etapes_statut(string $statut, array $o = [])` | suivi façon colis ; `dates`, `descriptions`, `libelle` |
| `champ(string $nom, string $libelle, array $erreurs = [], array $o = [])` | text / email / password / number / textarea / select / case ; `valeur`, `options`, `aide`, `requis`, `officiel` (police machine), `autocomplete`, `inputmode`… ; erreur `err-<id>` + `aria-invalid` + `aria-describedby` |
| `scene(string $nom, string $alt, array $o = [])` | `<picture>` WebP + JPEG, width/height lus sur le disque ; `tailles`, `chargement`, `prioritaire` (héros), `classe` (`scene--libre`) |
| `graphique_barres(array $donnees, array $o)` | SVG + libellés HTML + tableau caché ; `titre`, `sous_titre`, `unite`, `entete`, `max`, `accent`, `hauteur`, `resume` |
| `graphique_anneau(array $donnees, array $o)` | `titre`, `total`, `unite`, `entete`, `resume` ; séries 1 à 5 : encre, rouge, vert, laiton, kraft |
| `regime_formulaire(string $id)`, `regime_volet()` | CERFA 0-A (trois radios) ; volet de l'en-tête |

### Mouvement (`js/v2/guichet.js`)
- Entrée du héros : `data-enter` ; le h1 monte ligne par ligne (masques, texte rendu ensuite), le reste de `.heros__texte` et
  `.heros__pose` suit de 12 px, l'image se pose de 1,04 à 1 ; une fois, environ 1,1 s.
- Le tampon s'abat : `Guichet.tamponner(el)` ; ou `tampon(…, ['mouvement' => 'au-chargement'])`, ou un bouton `data-tamponner="#id"`.
  La feuille porteuse (`.attestation`, `.dossier__feuille`, `.ticket`, `[data-papier]`) encaisse le coup.
- La phrase qui s'encre : `<p class="phrase-encre" data-encre>` (mots en `<em>` au rouge) ; une fois, à l'écran ;
  `data-encre="manuel"` + `Guichet.encrer(el)`.
- Survols : bouton +1 px / enfoncé, flèche +3 px, carte liée +2 px, image ×1,02, soulignés tracés. Rien d'autre au défilement.
- Allégé (ou mouvement réduit sans choix) : durées nulles, tampon en fondu, phrase encrée d'emblée, héros immobile, images légères.

### Règles d'usage (dont la planche de références)
- Section : `<section class="ecran">` (`style="--h: 2"` pour deux écrans ; un écran = fenêtre − en-tête, borné 560–1000 px), un seul
  `.conteneur`, tête `.ecran__tete` (filet rouge, `.reference` en crochets, h2, `.chapeau`) ; fonds : défaut, `ecran--papier`, `ecran--fond-2`.
- Grille stricte de manuel : `.grille` 12 colonnes, `.c-3` … `.c-8`, `.d-7` / `.d-8` / `.d-9` (de la colonne N à la ligne de droite) ;
  `.rangee` (`--par`) ; `.rangee--classeur` pour les fiches bord à bord. Filets fins plutôt que cartes ; ombres réservées aux papiers.
- Héros : `.heros > .conteneur.grille > .heros__texte` (colonnes 1-5) + `.heros__visuel` (7-12 : `scene(…, ['prioritaire' => true])`,
  `.heros__pose` pour un papier posé). Les objets-héros (tampon `geant`, chemise, gravures) en très grand.
- Un seul accent très présent : `--accent` (rouge tampon sur le site, laiton dans Le Bureau) ; le laiton reste décor sur le site.
  Pas de texte rouge sur kraft ; le laiton #BFB21A ne porte jamais de texte en clair.
- Police machine en capitales entre crochets (`machine()`, `.reference`, onglets, étiquettes, tickets, titres du pied) ; texte courant en
  casse de phrase. Pictogrammes maison au trait dans la navigation et la barre latérale du Bureau.
- Un seul bouton principal par vue. Pas de 3D, pas de dégradés, pas de fondu au défilement, pas de chiffres décoratifs.
- Thème et régime posés avant affichage par `theme_script()` ; l'empreinte CSP est recalculée seule par `envoyer_csp()`.
