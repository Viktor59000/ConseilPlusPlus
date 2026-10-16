<?php
// Le Petit Moyen — format « sketch » : hommage non officiel aux « Mots » d'Éric Judor et Ramzy Bédia (M6, 1997).
// Le conseiller (solennel) et le stagiaire (sûr de lui) prétendent apprendre au public un mot que personne ne connaît.
//
// Mécanique (étudiée sur l'émission, réécrite avec nos propres mots et calembours) :
//   1. le stagiaire lit le mot de travers, avec aplomb (« pistes ») ;
//   2. une scène du quotidien, en une ligne ;
//   3. le conseiller conclut gravement en employant le mot comme un mot ordinaire (« C'est donc un… ») ;
//   4. le stagiaire JUSTIFIE avec le calembour : « C'est vrai. » + le découpage sonore, répété, assumé ;
//   5. une relance qui file la blague. Le rire vient de l'assurance face à un jeu de mots laborieux.
// L'ouverture, l'épellation et la formule finale sont dans le « rituel intégral » (volet replié sur le site).
// Les mots de l'émission sont libres, jamais leurs calembours (voir « calembours_emission », vérifié aussi pour l'IA).
return [
    'sketchs' => [
        [
            'mot' => 'Mirobolant', 'decoupage' => "miro · bol · an", 'nature' => 'adj.', 'certitude' => 61,
            'pistes' => ['Mirabelle'],
            'epellation' => "M, I, R, O, B, O, L, A, N, T. Le T est muet, il a été prévenu.",
            'scene' => "Le matin du 1er janvier. Le stagiaire, sans ses lunettes, cherche son bol de café.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un réveil mirobolant."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Je suis miro, j'ai pas vu le bol, et c'était le premier de l'an. Miro, bol, an. Le café est sur le chat."],
            ],
            'relance' => "Bonne année quand même. Surtout au chat.",
        ],
        [
            'mot' => 'Labrador', 'decoupage' => "là · bras · dort", 'nature' => 'n. m.', 'certitude' => 52,
            'pistes' => ['Labyrinthe'],
            'epellation' => "L, A, B, R, A, D, O, R. Le premier A est là pour l'équilibre.",
            'scene' => "Trois heures du matin. Le stagiaire, jeune papa, berce le bébé debout dans le salon.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un labrador."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Le petit, il dort pas dans son lit. Il dort là, sur le bras. Là, bras, dort. Toujours le même bras, comme par hasard."],
            ],
            'relance' => "Maintenant mon bras dort aussi. On est deux à dormir, sauf moi.",
        ],
        [
            'mot' => 'Insulaire', 'decoupage' => "un sou · l'air", 'nature' => 'adj.', 'certitude' => 46,
            'pistes' => ['Insuline', 'Insulte'],
            'epellation' => "I, N, S, U, L, A, I, R, E. Le E final, on ne l'entend pas, mais il paie quand même.",
            'scene' => "Une station-service. Le stagiaire veut regonfler ses pneus ; la machine réclame une pièce.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Il est donc devenu insulaire."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Avant, l'air c'était gratuit. Maintenant, un sou, l'air. Un sou, l'air ! Bientôt ils vont faire payer le vent."],
            ],
            'relance' => "Je suis reparti à plat. Mais j'ai gardé mon sou.",
        ],
        [
            'mot' => 'Colocataire', 'decoupage' => "Coco · la cata · terre", 'nature' => 'n.', 'certitude' => 63,
            'pistes' => ['Chocolat'],
            'epellation' => "C, O, L, O, C, A, T, A, I, R, E. Deux O, un pour chaque colocataire.",
            'scene' => "Un appartement. Le perroquet du stagiaire, Coco, vient de renverser le vase.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Il vit donc avec un colocataire."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. J'ai crié « Coco ! La cata ! Par terre ! » Et maintenant il le répète toute la journée. Coco, la cata, terre."],
            ],
            'relance' => "Il dit aussi « Bonjour » au facteur. Le facteur répond. Ça me fait de la compagnie.",
        ],
        [
            'mot' => 'Moisissure', 'decoupage' => "mois · six · sœurs", 'nature' => 'n. f.', 'certitude' => 41,
            'pistes' => ['Moissonneuse'],
            'epellation' => "M, O, I, S, I, S, S, U, R, E. Deux S au milieu, ils se tiennent compagnie.",
            'scene' => "Juin. Le stagiaire repasse sa chemise pour la sixième fois du mois.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Il traverse donc une période de moisissure."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Ce mois-ci, six sœurs se marient. Mois, six sœurs. Six mariages, une seule chemise. Et je suis fils unique."],
            ],
            'relance' => "Ce sont les sœurs de ma femme. Elle, elle a trois robes.",
        ],
        [
            'mot' => 'Mélancolie', 'decoupage' => "Mélanie · colle · y", 'nature' => 'n. f.', 'certitude' => 57,
            'pistes' => ['Mélange', 'Mélodie'],
            'epellation' => "M, É, L, A, N, C, O, L, I, E. L'accent est d'origine.",
            'scene' => "Une classe de maternelle, atelier gommettes. Le stagiaire est l'instituteur, épuisé.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Il traverse donc une grande mélancolie."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Mélanie colle. Mélanie colle les gommettes, Mélanie colle les ciseaux, Mélanie colle Kevin. Mélanie colle, y'a plus que ça."],
            ],
            'relance' => "Kevin va bien. Il est juste un peu collé au radiateur.",
        ],
        [
            'mot' => 'Hurluberlu', 'decoupage' => "hurle · Hubert · lu", 'nature' => 'n. m.', 'certitude' => 48,
            'pistes' => ['Hurricane', 'Hublot'],
            'epellation' => "H, U, R, L, U, B, E, R, L, U. Trois U, on ne s'en lasse pas.",
            'scene' => "Une bibliothèque, silence absolu. Hubert (le stagiaire) ouvre son relevé de banque.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un hurluberlu."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Quand Hubert a lu, Hubert hurle. Hurle, Hubert, lu. Toujours dans cet ordre-là, comme par hasard."],
            ],
            'relance' => "On lui a prêté un livre de recettes pour le calmer. Il hurle moins, mais il a faim.",
        ],
        [
            'mot' => 'Nonchalance', 'decoupage' => "non · chat · lance", 'nature' => 'n. f.', 'certitude' => 58,
            'pistes' => ['Nounours'],
            'epellation' => "N, O, N, C, H, A, L, A, N, C, E. Le deuxième N ne sert à rien, mais il est là.",
            'scene' => "Un jardin. Le stagiaire essaie d'apprendre à son chat à rapporter une balle.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Ce chat fait donc preuve d'une grande nonchalance."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Je lui dis « Non, chat, lance ! », il me regarde. « Non, chat, lance ! », il me regarde. Non, chat, lance. Il a compris, il fait exprès."],
            ],
            'relance' => "Maintenant c'est lui qui me lance la balle. Et c'est moi qui cours.",
        ],
        [
            'mot' => 'Baldaquin', 'decoupage' => "bal · d'ac · un", 'nature' => 'n. m.', 'certitude' => 44,
            'pistes' => ['Balthazar'],
            'epellation' => "B, A, L, D, A, Q, U, I, N. Le U accompagne le Q, il ne travaille pas.",
            'scene' => "Fête du village. Le conseiller vend les tickets du bal ; le stagiaire arrive, seul.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Il lui faudrait donc un baldaquin."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. On m'a demandé « Le bal, combien de places ? » J'ai dit « D'ac… un. » Bal, d'ac, un. Tous les ans, la même chose."],
            ],
            'relance' => "L'année prochaine je viens avec ma mère. Bal, d'ac, deux.",
        ],
        [
            'mot' => 'Vicissitude', 'decoupage' => "la vis · ici · si tu", 'nature' => 'n. f.', 'certitude' => 39,
            'pistes' => ['Vichyssoise'],
            'epellation' => "V, I, C, I, S, S, I, T, U, D, E. Deux S au milieu, en cas de perte.",
            'scene' => "Un salon. Le stagiaire, sur un escabeau, monte une étagère ; le conseiller tient la boîte à outils.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Il traverse donc une vicissitude."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Il me dit « La vis, ici, si tu peux. » « La vis, ici, si tu veux. » La vis, ici, si tu. Je sais plus où donner de la vis."],
            ],
            'relance' => "L'étagère penche, mais c'est voulu. C'est pour les livres penchés.",
        ],
        [
            'mot' => 'Capharnaüm', 'decoupage' => "cafard · nan · hum", 'nature' => 'n. m.', 'certitude' => 66,
            'pistes' => ['Camembert'],
            'epellation' => "C, A, P, H, A, R, N, A, Ü, M. Avec un tréma sur le U, pour faire joli.",
            'scene' => "Une cuisine, le soir. Le stagiaire est debout sur une chaise ; le conseiller, désinsectiseur, prend des notes.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un capharnaüm."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Il m'a demandé « Vous avez un cafard ? » J'ai dit « Nan… hum… » Cafard, nan, hum. J'étais pas sûr, il était derrière le frigo."],
            ],
            'relance' => "Il s'appelle Gérard. Il paie pas de loyer, mais il est discret.",
        ],
        [
            'mot' => 'Saltimbanque', 'decoupage' => "salle · timbre · banque", 'nature' => 'n.', 'certitude' => 47,
            'pistes' => ['Saltimbocca'],
            'epellation' => "S, A, L, T, I, M, B, A, N, Q, U, E. Le M devant le B, c'est le règlement.",
            'scene' => "L'accueil d'une mairie. Le stagiaire, perdu, une liste à la main.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un saltimbanque."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Je cherche la salle, un timbre et la banque. Salle, timbre, banque. Trois guichets, une seule matinée, comme par hasard."],
            ],
            'relance' => "Et la photocopieuse est en panne. Ça, c'est pas un mot, c'est une habitude.",
        ],
        [
            'mot' => 'Escarmouche', 'decoupage' => "et ce car · mouche", 'nature' => 'n. f.', 'certitude' => 34,
            'pistes' => ['Escargot', 'Escalator'],
            'epellation' => "E, S, C, A, R, M, O, U, C, H, E. Un seul R, on économise.",
            'scene' => "Un arrêt de bus en plein été. Une mouche tourne autour du stagiaire.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Il est donc pris dans une escarmouche."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Je demande « Et ce car ? » : mouche. « Et ce car ? » : mouche. Et ce car, mouche. Elle répond à la place du chauffeur."],
            ],
            'relance' => "Le car est passé pendant ce temps-là. La mouche est montée.",
        ],
        [
            'mot' => 'Ambivalence', 'decoupage' => "hamb… · i · Valence", 'nature' => 'n. f.', 'certitude' => 48,
            'pistes' => ['Ambulance'],
            'epellation' => "A, M, B, I, V, A, L, E, N, C, E. Avec un B, comme dans « bof ».",
            'scene' => "Le snack de la gare. Le stagiaire hésite entre un burger et son train pour Valence.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Il fait donc preuve d'ambivalence."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Je dis « un hamb… », et hop, le train ; « …i… Valence ! », et hop, la faim. Hamb, i, Valence. Je suis resté sur le quai avec une serviette."],
            ],
            'relance' => "Les frites, ça se mange dans tous les trains. Sauf le mien, il est parti.",
        ],
        [
            'mot' => 'Péninsule', 'decoupage' => "paie · Nina · seule", 'nature' => 'n. f.', 'certitude' => 35,
            'pistes' => ['Pénicilline'],
            'epellation' => "P, É, N, I, N, S, U, L, E. Le deuxième N est en option.",
            'scene' => "Au restaurant, l'addition arrive. Le stagiaire regarde son amie Nina.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc une péninsule."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. À chaque fois c'est « Paie, Nina. » Et elle paie, Nina. Seule. Paie, Nina, seule. Moi j'ai jamais de monnaie, comme par malheur."],
            ],
            'relance' => "J'avais pris qu'une entrée. Et un dessert. Et le vin.",
        ],
        [
            'mot' => 'Pamplemousse', 'decoupage' => "pan · plein · mousse", 'nature' => 'n. m.', 'certitude' => 58,
            'pistes' => ['Pamphlet'],
            'epellation' => "P, A, M, P, L, E, M, O, U, S, S, E. Deux S, comme dans « mousse ».",
            'scene' => "Au bar, le stagiaire porte trois demis en même temps. Il fait le fier.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un pamplemousse."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Pan ! Le plateau par terre, et moi, plein de mousse. Pan, plein, mousse. Après, j'ai pris un jus de fruit."],
            ],
            'relance' => "Le serveur m'a dit que j'étais pressé. Je n'ai pas compris.",
        ],
        [
            'mot' => 'Lampadaire', 'decoupage' => "l'an · pas · d'air", 'nature' => 'n. m.', 'certitude' => 62,
            'pistes' => ['Lampion'],
            'epellation' => "L, A, M, P, A, D, A, I, R, E. Comme « lampe », mais en plus haut.",
            'scene' => "Dans le métro, au mois d'août, sans climatisation. Le stagiaire tient la barre depuis un moment.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un lampadaire."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. J'y suis depuis un an, et toujours pas d'air. L'an, pas d'air. Je respire par roulement avec mon voisin."],
            ],
            'relance' => "Lui le lundi, moi le mardi. Le mercredi, on improvise.",
        ],
        [
            'mot' => 'Pantomime', 'decoupage' => "pan · tôt · Mimi", 'nature' => 'n. f.', 'certitude' => 49,
            'pistes' => ['Pantoufle'],
            'epellation' => "P, A, N, T, O, M, I, M, E. Sans un bruit, évidemment.",
            'scene' => "Dimanche, 6 heures du matin. La voisine, Mimi, accroche un tableau.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc une pantomime."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Pan ! Pan ! Si tôt, Mimi ? Pan, tôt, Mimi. Elle m'a répondu par gestes, pour ne pas réveiller le chat."],
            ],
            'relance' => "Le chat était réveillé. C'est lui qui tenait le marteau.",
        ],
        [
            'mot' => 'Calamité', 'decoupage' => "Calais · mi · thé", 'nature' => 'n. f.', 'certitude' => 55,
            'pistes' => ['Calamar'],
            'epellation' => "C, A, L, A, M, I, T, É. L'accent est sur le thé.",
            'scene' => "Sur le ferry pour Calais, le stagiaire commande un thé bien chaud. La mer est agitée.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc une calamité."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Arrivé à Calais, il me restait la moitié du thé. Calais, mi-thé. L'autre moitié est sur le capitaine."],
            ],
            'relance' => "Il l'a bien pris. Il préfère le café, de toute façon.",
        ],
        [
            'mot' => 'Quiproquo', 'decoupage' => "qui · pro · quoi", 'nature' => 'n. m.', 'certitude' => 66,
            'pistes' => ['Quinquina'],
            'epellation' => "Q, U, I, P, R, O, Q, U, O. Deux Q : le cabinet en commande toujours deux.",
            'scene' => "Le stagiaire appelle le service informatique. Ça sonne longtemps, puis quelqu'un décroche.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un quiproquo."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Il dit : « Qui ? » Je dis : « Le pro. » Il dit : « Quoi ? » Qui, pro, quoi. On a raccroché tous les deux, très satisfaits."],
            ],
            'relance' => "L'imprimante ne marche toujours pas, mais on s'est bien compris.",
        ],
        [
            'mot' => 'Tarabiscoté', 'decoupage' => "Tara · bisque · côté", 'nature' => 'adj.', 'certitude' => 52,
            'pistes' => ['Tarabiscuit'],
            'epellation' => "T, A, R, A, B, I, S, C, O, T, É. Avec un C, pour changer.",
            'scene' => "Au restaurant, Tara commande une bisque de homard, puis se retourne pour saluer quelqu'un.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un repas tarabiscoté."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Tara s'est tournée, et la bisque est partie sur le côté. Tara, bisque, côté. Sur ma chemise, plus précisément."],
            ],
            'relance' => "Elle m'a dit que ça m'allait bien. Le homard aussi, il a dit ça.",
        ],
        [
            'mot' => 'Tintouin', 'decoupage' => "Tintin · ouin", 'nature' => 'n. m.', 'certitude' => 60,
            'pistes' => ['Tintamarre'],
            'epellation' => "T, I, N, T, O, U, I, N. Le « ouin » est d'origine.",
            'scene' => "Le neveu du stagiaire, trois ans, arrive à la dernière page de son album de Tintin.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un tintouin."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. C'était fini, Tintin. Alors : « Ouin ! » Tintin, ouin. Il a pleuré jusqu'à l'album suivant."],
            ],
            'relance' => "L'album suivant, c'était le même. Il n'a pas vu la différence. Moi non plus.",
        ],
        [
            'mot' => 'Ritournelle', 'decoupage' => "rit · tour · Nelle", 'nature' => 'n. f.', 'certitude' => 57,
            'pistes' => ['Rillettes'],
            'epellation' => "R, I, T, O, U, R, N, E, L, L, E. Deux L, pour tourner plus vite.",
            'scene' => "À la fête foraine, Nelle, la cousine du stagiaire, monte sur le manège.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc une ritournelle."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. À chaque tour, Nelle rit. Rit, tour, Nelle. Au vingtième tour, elle riait moins."],
            ],
            'relance' => "Moi, je tenais la barbe à papa. Elle aussi a fait vingt tours.",
        ],
        [
            'mot' => 'Bâtonnet', 'decoupage' => "bas · ton · nez", 'nature' => 'n. m.', 'certitude' => 63,
            'pistes' => ['Bateau-mouche'],
            'epellation' => "B, Â, T, O, N, N, E, T. Avec un chapeau sur le A, pour le soleil.",
            'scene' => "Photo de classe. Le photographe s'impatiente : le stagiaire lève le menton bien trop haut.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un bâtonnet."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Il a crié : « Baisse ton nez ! » Bas, ton, nez. Sur la photo, on ne voit que mes cheveux."],
            ],
            'relance' => "Ma mère l'a encadrée quand même. Elle dit que je n'ai jamais été aussi bien coiffé.",
        ],
        [
            'mot' => 'Hospitalité', 'decoupage' => "hôpital · l'été", 'nature' => 'n. f.', 'certitude' => 54,
            'pistes' => ['Hospice'],
            'epellation' => "H, O, S, P, I, T, A, L, I, T, É. Le H est muet, il se repose.",
            'scene' => "Début juillet. Le stagiaire tente un saut de l'ange depuis un transat.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc l'hospitalité."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. J'ai passé tout l'été à l'hôpital. Hôpital, l'été. Ils ont été très accueillants."],
            ],
            'relance' => "Ils m'ont déjà réservé une chambre pour l'été prochain.",
        ],
        [
            'mot' => 'Formalité', 'decoupage' => "fort · mal · lit · thé", 'nature' => 'n. f.', 'certitude' => 59,
            'pistes' => ['Fromage'],
            'epellation' => "F, O, R, M, A, L, I, T, É. Une formalité.",
            'scene' => "Un lundi de novembre, le stagiaire appelle le cabinet avec une voix de crapaud.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc une simple formalité."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. J'ai fort mal, je reste au lit, avec un thé. Fort, mal, lit, thé. Le médecin a dit que ce n'était rien."],
            ],
            'relance' => "Il me l'a dit au téléphone. Il était au lit, lui aussi.",
        ],
        [
            'mot' => 'Paparazzi', 'decoupage' => "papa · rat · zzz", 'nature' => 'n. m.', 'certitude' => 61,
            'pistes' => ['Pepperoni'],
            'epellation' => "P, A, P, A, R, A, Z, Z, I. Deux Z, pour le ronflement.",
            'scene' => "La nuit, dans la maison de campagne. Le père du stagiaire s'est endormi dans le fauteuil.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un paparazzi."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Papa dormait, un rat lui est passé dessus, et lui : « Zzz. » Papa, rat, zzz. Le rat a pris une photo, je crois."],
            ],
            'relance' => "Elle est dans le journal local. Le rat a l'air très content.",
        ],
        [
            'mot' => 'Débonnaire', 'decoupage' => "des bons · nerfs", 'nature' => 'adj.', 'certitude' => 53,
            'pistes' => ['Des bonnets'],
            'epellation' => "D, É, B, O, N, N, A, I, R, E. Deux N, pour les deux nerfs.",
            'scene' => "Vendredi soir, sur le périphérique, tout est bloqué. Le stagiaire est au volant de la Safrane.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un conducteur débonnaire."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Pour rester calme là-dedans, il faut des bons nerfs. Des bons, nerfs. J'en avais, au départ."],
            ],
            'relance' => "Au bout d'une heure, il m'en restait un. Je l'ai gardé pour le retour.",
        ],
        [
            'mot' => 'Ubiquité', 'decoupage' => "Hubi · quitté", 'nature' => 'n. f.', 'certitude' => 50,
            'pistes' => ['Biquette'],
            'epellation' => "U, B, I, Q, U, I, T, É. Le U est partout à la fois.",
            'scene' => "Fin de soirée chez le stagiaire. Hubert, dit Hubi, cherche son manteau depuis vingt minutes.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc le don d'ubiquité."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Hubi avait quitté la soirée, mais il était encore là. Hubi, quitté. Le manteau, il le portait."],
            ],
            'relance' => "Il est parti deux fois. On l'a raccompagné deux fois. Il habite à côté.",
        ],
        [
            'mot' => 'Kaléidoscope', 'decoupage' => "calé · dos · scope", 'nature' => 'n. m.', 'certitude' => 47,
            'pistes' => ['Calendrier'],
            'epellation' => "K, A, L, É, I, D, O, S, C, O, P, E. Avec un K, pour faire savant.",
            'scene' => "Le soir, au jardin, le stagiaire observe le ciel avec un télescope, adossé à un pommier.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est donc un kaléidoscope."],
                ['qui' => 'stagiaire', 'texte' => "C'est vrai. Bien calé, le dos contre l'arbre, l'œil dans le télescope. Calé, dos, scope. J'ai vu plein de couleurs."],
            ],
            'relance' => "C'était le feu d'artifice du village. Le télescope était dans le mauvais sens.",
        ],
    ],

    // Mots traités dans l'émission : le MOT est libre (il n'appartient à personne), mais le CALEMBOUR du duo ne
    // doit jamais revenir. Pour chaque mot, les morceaux sonores déjà utilisés (sans accents ni ponctuation à la
    // comparaison) : un sketch, de la banque ou de l'IA, qui les contient est refusé. Liste vide = calembour inconnu.
    'calembours_emission' => [
        'boogie-woogie' => ['bouddh', 'yogisme'],
        'atypique' => ['tu piques', 'tu pique'],
        'labrador' => ["la d'abord", 'la dabord'],
        'pléonasme' => ['aux nasmes', 'nasme'],
        'pilosité' => ['pile au', 'pile poil'],
        'spasme' => ['espace me', 'espaceme'],
        'cruciforme' => ['cru si fort'],
        'psychisme' => ['cheese'],
        'moisissure' => ['moi si sur', 'si sur'],
        'despotique' => ['post it', 'postit'],
        'hypermétrope' => ["m'est trompe", 'hyper trop'],
        'juguler' => ['je gueul'],
        'insulaire' => ['deux r'],
        'soliloque' => ['il laque'],
        'diflogatul' => ['dif foule', 'diffoule'],
        'philatéliste' => ['la tenniste'],
        'spartakisme' => ['sport a ski'],
        'conciliabule' => ['con s il y a bulle', 'bulle a zero'],
        'péridurale' => ['pere ideal'],
        'caracoler' => [],
        'colocataire' => ['cratere'],
        'parapente' => ['pas tres bon'],
        'liminal' => ['animal'],
        'ecchymose' => ['esquimo'],
        'macchabée' => ['mccartney'],
        'œcuménique' => ['economique'],
        'villégiature' => ['tueur'],
        'malicieux' => ['mal aux yeux', "mal l'yeux"],
        'parcimonieux' => ['par ici mon', 'par ci mon'],
        'parcimonie' => ['par ici mon', 'par ci mon'],
        'myocarde' => [],
        'péremptoire' => ['paix en moi'],
        'restituer' => ['re situer', 'ou es tu'],
        'intérimaire' => ['inter maire', 'terre mer'],
        'hyperbole' => ['bol'],
        'sécrétion' => ['secretaire'],
        'sommité' => [],
        'jubilé' => ["j'ai oublie"],
        'palabre' => ["pas l'arbre", 'jamais bre'],
        'œsophage' => ["l'eau sauvage", 'eau sauvage'],
        'contrepèterie' => ['contre petrie'],
        'apanage' => ['lapins nagent', 'lapin'],
        'séquestration' => [],
        'globule' => ['pull'],
        'chauvinisme' => [],
        'magnanime' => ['miami'],
        'pernicieux' => ['permis'],
        'licencieux' => [],
        'paradoxe' => ['pas de rock'],
        'philanthrope' => ['fille en trop', 'en trop'],
        'portatif' => [],
        'sarcasme' => [],
        'claustrophobe' => ['cause trop'],
        'castagnettes' => [],
        'libido' => [],
    ],

    // Mots savants proposés à l'IA pour ses sketchs (3 syllabes ou plus : plus de matière sonore à découper)
    'mots_savants' => [
        'sarcophage', 'abracadabrantesque', 'pusillanime', 'charivari', 'acrimonie', 'anachronisme', 'calembredaine',
        'circonvolution', 'débonnaire', 'dithyrambique', 'éphémère', 'farfelu', 'galimatias', 'ignominie',
        'impétueux', 'incandescent', 'interstice', 'jérémiade', 'labyrinthe', 'obséquieux', 'onomatopée',
        'palimpseste', 'panégyrique', 'paparazzi', 'pérégrination', 'prolégomènes', 'quintessence', 'rocambolesque',
        'sempiternel', 'sérendipité', 'sycophante', 'tarabiscoté', 'ubiquité', 'velléitaire', 'zénith', 'billevesée',
        'cacophonie', 'esbroufe', 'fanfreluche', 'mansuétude', 'pantomime', 'patatras', 'subterfuge', 'tohu-bohu',
        'acrobatie', 'bâtonnet', 'calamité', 'diabolo', 'entourloupe', 'formalité', 'gargouille', 'hospitalité',
        'kaléidoscope', 'lampadaire', 'marmelade', 'nostalgie', 'opportunité', 'pamplemousse', 'quiproquo', 'ritournelle',
        'saperlipopette', 'tintouin', 'vaudeville', 'zigouigoui',
        // + les mots de l'émission (calembour original interdit) : ajoutés par preparer_mot()
    ],
];
