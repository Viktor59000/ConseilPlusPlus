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
// Aucun mot ni calembour de l'émission n'est repris (voir « mots_emission », interdits aussi à l'IA).
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
    ],

    // Mots traités dans l'émission : jamais repris, ni dans la banque ni par l'IA
    'mots_emission' => [
        'boogie-woogie', 'atypique', 'labrador', 'pléonasme', 'pilosité', 'spasme', 'cruciforme', 'psychisme',
        'moisissure', 'despotique', 'hypermétrope', 'juguler', 'insulaire', 'soliloque', 'diflogatul', 'philatéliste',
        'spartakisme', 'conciliabule', 'péridurale', 'caracoler', 'colocataire', 'parapente', 'liminal', 'ecchymose',
        'macchabée', 'œcuménique', 'villégiature', 'malicieux', 'parcimonieux', 'parcimonie', 'myocarde', 'péremptoire',
        'restituer', 'intérimaire', 'hyperbole', 'sécrétion', 'sommité', 'jubilé', 'palabre', 'œsophage',
        'contrepèterie', 'apanage', 'séquestration', 'globule', 'chauvinisme', 'magnanime', 'pernicieux', 'licencieux',
        'paradoxe', 'philanthrope', 'portatif', 'sarcasme', 'claustrophobe', 'castagnettes', 'libido',
        'atermoiement', 'tergiverser',
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
    ],
];
