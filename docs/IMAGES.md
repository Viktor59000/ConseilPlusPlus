# Images du site

## Portraits de l'équipe (`assets/equipe/<cle>.webp`, page « Le cabinet »)

Générés avec ChatGPT (images), le 16/10/2026, puis convertis en WebP 640 × 640 (qualité 80).
Sans image, `cabinet.php` affiche un monogramme entouré au stylo.

**Leçon** : la première série se ressemblait (même visage, même pose) car le générateur réutilisait une image de
référence de la conversation. Chaque prompt impose donc un âge, un genre, une morphologie, une coiffure et un cadrage
différents, et précise « personnage inventé, ne ressemblant à aucune image précédente ».

Début commun à chaque prompt :

> New image, same style as the previous ones (flat editorial illustration, fine ink hatching, paper grain, palette
> navy #1B2440, off-white #F5F6FA background, red #C8102E accent, tiny yellow #FFE14D touches, no text, no letters,
> square 1:1, subject centered with empty margin). Completely invented character, nothing like the previous ones.

| Fichier | Sujet (suite du prompt) |
|---|---|
| `conseiller.webp` | A heavy-set man around 62, completely bald on top with a thin ring of grey hair, thick bushy grey eyebrows, clean-shaven round face, no glasses. Sits behind a small wooden desk, seen slightly from below, fingertips steepled under his chin, eyes half-closed with smug self-satisfaction. Navy three-piece suit, red tie tied noticeably crooked. On the desk, a single blank sheet of paper placed with great ceremony. |
| `stagiaire.webp` | A very tall, skinny young man of about 20, ginger buzz cut, big ears, freckles, clean-shaven. Full body, mid-stride from left to right. Proudly holds a thick red dictionary upside down and open, reading aloud, mouth wide open, eyebrows raised with absolute certainty. Oversized navy cardigan, red lanyard with a blank badge flying sideways, yellow sticky note on his back. |
| `delais.webp` | Tight close-up of a Black woman in her sixties with a short natural grey afro, large square red-framed glasses pushed down her nose, small gold earrings. Looks over her glasses, one eyebrow raised, sceptical unhurried half-smile. Navy turtleneck, holds a red pen like a cigarette holder. Behind her, a wall clock with no numbers, hands pointing in opposite directions. |
| `accueil.webp` (à générer ; remplace `guichet.webp`, Bertrand endormi, retiré car il moquait la personne et non la procédure) | A slim, upright man in his early fifties with olive skin, a neatly trimmed short black beard and a navy uniform cap worn perfectly straight. Stands very straight behind a reception desk, seen straight on, holding up with both hands, with total courtesy, an enormous thick binder of rules with dozens of coloured tabs. Impeccable navy uniform, red tie, small yellow badge. On the desk, a perfectly aligned stack of blank forms and a pen placed at an exact right angle. Calm, serious, flawless. |
| `cafe.webp` | Still life, no human body, no face: an old, battered office coffee vending machine in three-quarter view, standing proudly alone. Blank red rosette award pinned on its side, one paper cup in the slot with spiral steam, a yellow sticky note, one brighter red button, a small dent. Museum-like composition. |
| `messmerde.webp` | A lanky man around 40 with long straight black hair parted in the middle and a thin pencil moustache. Waist-up, facing us, eyes closed in exaggerated concentration, two fingertips pressed to his temple. Cheap navy suit under a too-short dark velvet cape with a red lining. In his other hand, a small snow globe held up like a crystal ball. Slightly theatrical, completely sure of himself. |
| `tampon.webp` | Still life, no face: a very old wooden rubber stamp with a worn round handle, upright on a navy ink pad like a podium, leaning slightly left, tiny gold medal on a red ribbon. Next to it, a fresh crooked red imprint of an empty double-bordered rectangle, a few red ink drops. Venerable, official, a little smug. |

## Autres images

- `assets/og-image.png` (aperçu des liens partagés, 1200 × 630) : modèle `tests/og-image.html`, capturé dans un navigateur.
- Logo, icônes et PNG des courriels : `design-system/conseilplus/MASTER.md` (section Logo).
