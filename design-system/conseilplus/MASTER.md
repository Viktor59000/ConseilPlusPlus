# ConseilPlus+ — Charte (source de vérité)

## Concept
Un cabinet de conseil qui se prend pour une administration : formulaires, tampons, numéros de dossier.
Le ton est sérieux, le fond est absurde. Le site ne fait jamais de clin d'œil : c'est le décalage qui fait rire.

## Nom et logo
- **ConseilPlus+** — signature : **Le conseiller moyen**
- Monogramme **C++** : la contraction Conseil + Plus + « + ». Le dernier « + » est posé de travers (−15°) : il fait de son mieux.
- Fichiers sources : `branding/` (monogramme, nom, logo horizontal, icône carrée, versions négatives).
  Favicon et icônes web : `assets/icons/`. Logo du site : `assets/logo.svg`.
- Le logo est toujours en `--encre` (ou `--papier` sur fond sombre). Jamais de « + » rouge seul : il évoquerait l'emblème de la Croix-Rouge.
- Accroche : « Des conseils. C'est déjà ça. »

## Voix
- Vouvoiement, phrases courtes, vocabulaire administratif (« dossier », « traitement », « conformément à »).
- Assurance totale, compétence nulle.
- Les conseils naissent de la combinaison de 3 fragments sans rapport : situation + action + résultat.

## Couleurs (tokens CSS)
| Token | Hex | Usage |
|---|---|---|
| `--papier` | `#F4EFE4` | Fond de page |
| `--carte` | `#FFFDF7` | Cartes, champs |
| `--encre` | `#1B2440` | Texte, bordures, ombres |
| `--encre-douce` | `#4A5270` | Texte secondaire (contraste 6.9:1 sur papier) |
| `--tampon` | `#C8102E` | Tampons, CTA principal, erreurs (jamais dans le logo) |
| `--surligneur` | `#FFE14D` | Mise en avant de mots, formule recommandée |
| `--ok` | `#1E6B3A` | Messages de succès |

Pas de dégradés. Pas d'autres couleurs.

## Typographie
- Titres : **Archivo Black** (une seule graisse, majuscules réservées aux tampons)
- Texte : **IBM Plex Sans** 400/600, 17px, interligne 1.55
- Données / numéros de dossier / labels : **IBM Plex Mono** 500

## Formes et effets (style : brutalisme administratif)
- Angles vifs (0px), bordure 2px `--encre`.
- Ombre dure décalée : `4px 4px 0 var(--encre)`. Au survol, le bouton « s'enfonce » (translate 2px, ombre 2px).
- Tampons : texte mono en majuscules, cadre double rouge, rotation -6° à -12°.
- Surligneur : fond jaune sur la moitié basse du mot.
- Mouvement minimal : seul le tampon du conseil s'anime (« clac » de 250ms). Désactivé si `prefers-reduced-motion`.

## Règles UX (issues de ui-ux-pro-max)
- Labels visibles sur tous les champs, jamais de placeholder seul.
- Retour visible après chaque envoi de formulaire (succès / erreur avec la marche à suivre).
- Focus clavier visible (outline 3px `--tampon`).
- Cibles tactiles ≥ 44px, pas de défilement horizontal, testé à 375 / 768 / 1440px.
- Pas d'emoji en guise d'icône.

## Navigation
- Menu principal (toutes les pages) : Accueil · Nos formules · Contact · [Espace client] / [Mon espace] une fois connecté.
- Sous-menu de l'espace client (bandeau marine, `includes/espace-nav.php`) : Mon conseil · Consultation · Le Duel · Mon carnet · Déconnexion.
- Chaque page ne garde que ses propres actions (pas de boutons de navigation en double).

## Performance et qualité
- Lighthouse : 100 / 100 / 100 / 100 (performance, accessibilité, bonnes pratiques, SEO) sur les 8 pages, profil mobile.
- Polices hébergées sur le site (`assets/fonts`, sous-ensemble latin, préchargées), feuille de style intégrée à la page.
- Chaque page définit `$description` (meta description).

## Pages
| Page | Fichier | Rôle |
|---|---|---|
| Accueil | `index.php` | Accroche, fonctionnement, conseil gratuit, contact |
| Nos formules | `produit.php` | 3 formules, comparatif, FAQ |
| Contact | `contact.php` | Formulaire enregistré dans la table `contacts` (source de l'API) |
| Espace client | `login.php` | Connexion / inscription |
| Mon conseil | `conseil.php` | Générateur réservé aux clients connectés |
| Consultation | `consultation.php` | Questionnaire inutile + verdict IA |
| Le Duel | `duel.php` | Partie en 100 points contre le cabinet (barème caché) |
| Mon carnet | `carnet.php` | Conseils classés |
