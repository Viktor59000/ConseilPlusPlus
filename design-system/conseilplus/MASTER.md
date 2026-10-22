# ConseilPlus+ — Charte (source de vérité)

Identité « La note », refonte du 16/10/2026 (skills `logo-design` et `apple-design`).

## Concept
Un grand cabinet de conseil, sobre et aéré, dont le contenu est absurde. **Tout ce qui est « officiel » passe par le
stylo rouge** : la boucle autour de la note C+, les tampons, les corrections. Le conseil s'écrit sur une copie à
grands carreaux, comme une copie rendue par un professeur. Le site ne fait jamais de clin d'œil : c'est le décalage
qui fait rire.

## Logo : « La note »
- **Idée** : C+, la note moyenne, entourée au stylo rouge comme sur une copie. Nom, promesse et ancien « C++ » en un signe.
- **Construction** : « C » en Schibsted Grotesk ExtraBold vectorisé ; le « + » en exposant, de l'épaisseur du fût du C,
  remonté de 2 % (correction optique) ; une boucle de stylo elliptique, inclinée de −14°, qui fait un tour et
  dépasse (elle ne se referme pas sur elle-même : ce n'est pas un ©). Trait plein au milieu, effilé aux bouts.
- **Fichiers** (`branding/`) : `symbole` (couleur, `-negatif`, `-noir`, `-blanc`), `symbole-petit` (16–32 px : trait plus
  épais, C+ plus grand), `logo-horizontal` (avec « LE CONSEILLER MOYEN »), `logo-court` (sans signature : en-têtes),
  `logo-empile`, `icone-app` et `icone-app-maskable` (tuile marine).
- **Sur le site** : `includes/logo.php`, SVG intégré aux couleurs du thème ; la boucle se trace à la première page vue
  de la visite (masque animé), et frémit au survol.
- **Icônes web** : `assets/icons/` (favicon depuis la version petite taille ; icônes d'app sur tuile marine).
  **Courriels** : `assets/email/logo.png` et `icone.png` (PNG transparents).
- **Règles** : marge de protection = la hauteur du « + » ; jamais de « + » rouge seul (Croix-Rouge) ; jamais le symbole
  sans sa boucle ; la boucle toujours en rouge (ou une seule couleur pour les versions noir/blanc).
- Les SVG de `branding/` sont les originaux : toute retouche part d'eux, puis on refait les PNG (icônes, courriels).

## Voix
- Vouvoiement, phrases courtes, vocabulaire administratif (« dossier », « traitement », « conformément à »).
- Assurance totale, compétence nulle. Boutons : un verbe qui dit ce qui se passe.

## Couleurs (`css/base.css`)
Deux thèmes : **la copie** (clair) et **le tableau** (sombre). Par défaut, le thème suit le système ; le bouton
soleil / lune de l'en-tête permet de choisir, et le choix est gardé dans le navigateur.

| Jeton | Clair | Sombre | Rôle | Contraste |
|---|---|---|---|---|
| `--fond` | `#F5F6FA` | `#0E1220` | Fond de page | — |
| `--surface` | `#FFFFFF` | `#171C2E` | Cartes, champs | — |
| `--encre` | `#1B2440` | `#F3F1EC` | Texte (marine du logo / craie) | 14,2:1 / 16,5:1 |
| `--encre-2` | `#525A73` | `#A9AFC3` | Texte secondaire | 6,3:1 / 7,7:1 |
| `--contour` | `#7A8199` | `#7A8199` | Bord des champs | 3,6:1 / 4,4:1 (RGAA 3.3) |
| `--bouton` | `#1B2440` | `#F3F1EC` | Action principale (texte `--sur-bouton`) | 15,3:1 / 13,6:1 |
| `--rouge` | `#C8102E` | `#FF5F6D` | Stylo : boucles, tampons, focus, erreurs | 5,9:1 / 5,7:1 |
| `--jaune` | `#FFE14D` | `#FFE14D` | Surligneur (texte marine dessus) | 11,8:1 |
| `--seyes`, `--marge` | `#D9E1F7`, `#F3AEB5` | `#232A45`, `#5A2733` | Lignes et marge de la copie (décor) | — |

Le rouge est le stylo du correcteur : il annote (boucles, tampons, soulignement de la page courante, focus) et signale
les erreurs. Il ne sert jamais de fond de bouton.

## Typographie
- **Schibsted Grotesk** (variable 400–900, licence OFL) : titres en 800, interlettrage −0,03 em ; texte 18 px,
  interligne 1,6. Police de presse : sérieuse, avec du caractère.
- **JetBrains Mono** 500 (OFL) : numéros de dossier, dates, rubriques (en capitales espacées), tampons.
- Polices hébergées sur le site (`assets/fonts`, sous-ensemble latin, woff2), police de repli calibrée (pas de saut
  au chargement). Deux familles, pas une de plus.

## Mise en page
- Conteneur 1160 px ; sections très aérées (72 à 136 px) ; deux sections de suite sur le même fond ne cumulent pas
  leurs marges.
- Cartes : rayon 22 px, ombre douce teintée marine. Boutons en pilule (52 px, 44 px pour `.btn-petit`).
- Rubrique (`.etiquette`) : petit trait rouge + mono en capitales.
- **Signature** : la copie (`.fiche`) — marge rouge, lignes calées sur le texte, note « C+ » entourée en marge,
  tampon « Validé » posé de travers.

## Mouvement
- Boucle du logo tracée à la première page vue ; boucles des chiffres tracées à l'apparition.
- Surligneur qui se dessine de gauche à droite ; tampons qui s'abattent (« clac » avec rebond).
- Sections et cartes qui apparaissent au défilement (en cascade) ; transitions douces entre les pages
  (navigateurs compatibles) ; menu mobile qui se déplie ; bascule de thème en fondu.
- Micro-retours : boutons qui se soulèvent et s'enfoncent, flèches qui avancent, champ en erreur qui « refuse »,
  vote qui rebondit, nouveau conseil qui se réécrit.
- Le haut de page s'anime en CSS (rien n'attend le script) ; le reste est préparé quand la page est au repos.
- Tout est coupé si le système demande de réduire les animations.

## Navigation (architecture)
- **En-tête** : logo (= accueil) · Le cabinet · Le mot du jour · Nos formules · Messmerde · Contact · [Espace client] / [Mon espace] ·
  bouton de thème. Sous 880 px : menu repliable (« Menu », `aria-expanded`, Échap).
- **Espace client** (`includes/espace-nav.php`) : Mon conseil · Consultation · Le Duel · Mon carnet · Déconnexion.
- **Pied** : accroche, Plan du site · Accessibilité · Application mobile · Contact.
- **Le Bureau** reprend le socle (`css/base.css`), le logo et le bouton de thème ; ses composants sont dans `bureau/css/bureau.css`.

## Pages
| Page | Fichier | Rôle |
|---|---|---|
| Accueil | `index.php` | La copie du jour, mot du jour, méthode, contact |
| Le cabinet | `cabinet.php` | Équipe, valeurs, chiffres, témoignages |
| Le Petit Moyen | `petit-moyen.php` | Mot (presque) du jour, archives, feuilleter |
| Nos formules | `produit.php` | 3 formules, comparatif, FAQ |
| Messmerde | `messmerde.php` | Expériences du mentaliste, horoscope (entrée du menu) |
| Le standard | `standard.php` | Répondeur de Mireille à choix cliquables (`data/standard.php`) |
| Contact | `contact.php` | Formulaire envoyé à l'API, accusé de réception par courriel |
| Espace client | `login.php` | Connexion / inscription |
| Mon conseil · Consultation · Le Duel · Mon carnet | `conseil.php` … | Espace client |
| Application mobile | `application.php` | Installation (PWA), notifications, widget KWGT |
| Plan du site | `plan-du-site.php` | Second moyen de navigation (RGAA 12.1) |
| Accessibilité | `accessibilite.php` | Déclaration d'accessibilité RGAA 4.1 |

## Accessibilité (RGAA 4.1)
- Contrastes ci-dessus, dans les deux thèmes ; focus visible (contour rouge) ; lien d'évitement ; plan du site.
- Formulaires : étiquette visible, champs obligatoires signalés, erreur en texte (« Erreur : »), reliée au champ.
- 320 px sans défilement horizontal et texte à 200 % vérifiés par script, dans les deux thèmes.
- Bouton de thème : libellé qui dit l'action (« Passer en thème sombre »).

## Courriels (`includes/courriel.php`)
Fond `#F5F6FA`, logo au-dessus d'une carte blanche arrondie ; rubrique mono « — DOSSIER N° … » ; titre en 800 ;
signature avec le symbole, adresse absurde et tampon rouge à double filet. Tableaux et styles en ligne.

## Performance
- Lighthouse 99–100 en performance, 100 ailleurs, sur les 13 pages (profil mobile, serveur local non compressé).
- CSS intégré et minifié (`css_integre()`), police principale préchargée, scripts en `defer`.
- Éviter `text-wrap: pretty` sur tous les paragraphes (2 s de mise en page sur mobile lent).
