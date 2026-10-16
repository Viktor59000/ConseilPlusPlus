# ConseilPlus+ — Charte (source de vérité)

Refonte du 16/10/2026, menée avec le skill `apple-design` (Human Interface Guidelines d'Apple : principes,
accessibilité, couleur, typographie, mise en page). Ancienne direction : « brutalisme administratif ».

## Concept
Un cabinet de conseil qui se prend très au sérieux. **Le sérieux d'une grande maison, au service d'un conseil absurde** :
l'emballage est irréprochable (sobre, aligné, net), le contenu est n'importe quoi. Le site ne fait jamais de clin d'œil :
c'est le décalage qui fait rire.

**Signature** : tout est droit, sauf le **tampon** rouge (« Validé », « Le plus choisi par erreur », découpage des sketchs)
et le « + » du logo. Ce sont les deux seuls éléments de travers : ils font de leur mieux.

## Nom et logo
- **ConseilPlus+** — signature : **Le conseiller moyen**. Accroche : « Des conseils. C'est déjà ça. »
- Monogramme **C++** ; le dernier « + » est posé de travers (−15°).
- Fichiers : `branding/` (sources), `assets/logo.svg` (clair) et `assets/logo-negatif.svg` (sombre, via `<picture>`),
  icônes web dans `assets/icons/`, PNG des courriels dans `assets/email/`.
- Le logo n'apparaît qu'une fois par page (en-tête). Jamais de « + » rouge seul (emblème de la Croix-Rouge).

## Voix
- Vouvoiement, phrases courtes, vocabulaire administratif (« dossier », « traitement », « conformément à »).
- Assurance totale, compétence nulle.
- Boutons : un verbe qui dit ce qui se passe (« Envoyer ma demande », « Créer mon compte »).

## Couleurs (tokens CSS, `css/style.css`)
Chaque couleur a un rôle ; le mode sombre suit le réglage du système (pas de bouton de thème, comme le demandent les HIG).

| Token | Clair | Sombre | Rôle | Contraste |
|---|---|---|---|---|
| `--fond` | `#F5F5F7` | `#000000` | Fond de page | — |
| `--surface` | `#FFFFFF` | `#1C1C1E` | Cartes, champs | — |
| `--surface-2` | `#E8E8ED` | `#2C2C2E` | Contrôles segmentés | — |
| `--texte` | `#1D1D1F` | `#F5F5F7` | Texte | 15,5:1 / 19,3:1 |
| `--texte-2` | `#6E6E73` | `#A1A1A6` | Texte secondaire | 4,7:1 / 6,6:1 |
| `--contour` | `#8E8E93` | `#8E8E93` | Bord des champs | 3,3:1 / 5,2:1 (RGAA 3.3) |
| `--accent` | `#1B2440` | `#C9D3FF` | Marine du logo : action principale, focus | 15,3:1 / 10,4:1 avec `--sur-accent` |
| `--tampon` | `#C8102E` | `#FF6B78` | Tampons, erreurs, actions destructives | 5,9:1 / 6,2:1 |
| `--surligneur` | `#FFE14D` | `#FFE14D` | Mot mis en avant (texte `#1D1D1F` dessus) | 12,9:1 |
| `--ok` | `#1E6B3A` | `#4CD07D` | Succès | 6,5:1 / 8,6:1 |

Pas de dégradé. Le marine est réservé aux actions principales (pas de décor).

## Typographie
Polices du système (San Francisco sur Apple, Segoe UI sur Windows, Roboto sur Android) : rien à télécharger.
- Titres : graisse 700, interlettrage −0,02 à −0,03 em. `h1` jusqu'à 4,75 rem (accueil).
- Texte : 17 px (1,0625 rem), interligne 1,5. Jamais en dessous de 0,75 rem.
- Données (numéros de dossier, dates, tampons) : `ui-monospace`.
- Tailles en `rem` : le texte suit le réglage du navigateur (testé à 200 %).

## Formes
- Cartes : rayon 20 px, sans bordure, ombre très légère (aucune en sombre).
- Boutons en pilule, hauteur 48 px (44 px pour `.btn-petit`) : `.btn-tampon` = action principale (plein, marine),
  `.btn-clair` = action secondaire (contour). Une seule action principale par zone.
- Champs : rayon 12 px, bord 1 px `--contour`, étiquette au-dessus, focus = contour 3 px `--accent`.
- Contrôle segmenté (sous-menu de l'espace client, votes) : fond `--surface-2`, segment actif en `--surface`.
- En-tête : barre collante translucide (seule couche « verre » du site), opaque si le système réduit la transparence.

## Mouvement
Un seul moment : le tampon se pose (« clac », 250 ms). Plus la lecture des sketchs et le guichet qui clignote deux fois.
Tout est coupé si le système demande de réduire les animations.

## Navigation (architecture)
- **En-tête** (toutes les pages) : logo (= accueil) · Le mot du jour · Nos formules · Le guichet · Contact ·
  [Espace client] / [Mon espace]. Sous 760 px, menu repliable (bouton « Menu », `aria-expanded`, Échap ;
  déplié si JavaScript est absent).
- **Sous-menu de l'espace client** (`includes/espace-nav.php`) : Mon conseil · Consultation · Le Duel · Mon carnet · Déconnexion.
- **Pied** : Plan du site · Accessibilité : partiellement conforme · L'application mobile · Nous contacter.
- Page courante signalée par `aria-current` (souligné, pas seulement une couleur).

## Pages
| Page | Fichier | Rôle |
|---|---|---|
| Accueil | `index.php` | Conseil du jour, mot du jour, méthode, contact |
| Le Petit Moyen | `petit-moyen.php` | Mot (presque) du jour, archives, feuilleter |
| Nos formules | `produit.php` | 3 formules, comparatif, FAQ |
| Le guichet | `guichet.php` | Ticket, file d'attente absurde |
| Contact | `contact.php` | Formulaire envoyé à l'API (`contacts`), accusé de réception par courriel |
| Espace client | `login.php` | Connexion / inscription |
| Mon conseil | `conseil.php` | Générateur réservé aux clients |
| Consultation | `consultation.php` | Questionnaire inutile + verdict IA |
| Le Duel | `duel.php` | Partie en 100 points (barème caché) |
| Mon carnet | `carnet.php` | Conseils classés |
| Application mobile | `application.php` | Installation (PWA), notifications, widget KWGT |
| Plan du site | `plan-du-site.php` | Second moyen de navigation (RGAA 12.1) |
| Accessibilité | `accessibilite.php` | Déclaration d'accessibilité RGAA 4.1 |

## Accessibilité (RGAA 4.1)
- Contrastes ci-dessus, en clair et en sombre ; focus visible partout ; lien d'évitement « Aller au contenu ».
- Formulaires : étiquette visible, « Tous les champs sont obligatoires » (ou mention « facultatif »), erreur en texte
  (préfixe « Erreur : »), reliée au champ (`aria-describedby`, `aria-invalid`), bord rouge épais en plus de la couleur.
- Affichage à 320 px sans défilement horizontal, texte à 200 % sans perte (vérifié par script, clair et sombre).
- Cibles tactiles de 44 px au minimum. Pas d'emoji en guise d'icône.
- Mettre à jour `accessibilite.php` (état, date, non-conformités) après chaque audit.

## Performance et qualité
- Lighthouse 100 / 100 / 100 / 100 (performance, accessibilité, bonnes pratiques, SEO) sur les 13 pages, profil mobile.
- Feuille de style intégrée à la page, polices système, scripts en `defer`. Cache des fichiers statiques : `docker/apache.conf`.
- Chaque page définit `$titre`, `$description` et `$page`.
