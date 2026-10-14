<?php
// Le Petit Moyen — format « sketch » : hommage non officiel aux « Mots » d'Éric Judor et Ramzy Bédia (M6, 1997).
// Le conseiller (solennel) et le stagiaire (naïf) prétendent apprendre au public un mot que personne ne connaît,
// puis le « définissent » par une scène du quotidien qui se termine sur un calembour approximatif.
// L'ouverture et la formule finale (« Le cabinet ne voit pas d'autre explication. ») sont ajoutées à l'affichage.
// Toutes les chutes sont écrites pour le site : aucune ne reprend un calembour des sketchs originaux.
return [
    'sketchs' => [
        [
            'mot' => 'Nonchalance', 'decoupage' => "non · chat · lance", 'nature' => 'n. f.', 'certitude' => 58,
            'pistes' => ['Nounours', 'Nonante'],
            'epellation' => 'N, O, N, C, H, A, L, A, N, C, E. Deux N au début, pour la symétrie.',
            'scene' => "Un jardin. Le stagiaire essaie d'apprendre à son chat à rapporter la balle. Le voisin (le conseiller) observe.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Il fait quoi, votre chat, exactement ?'],
                ['qui' => 'stagiaire', 'texte' => 'Rien. Mais il va comprendre. Allez… Non, chat… Lance !'],
            ],
            'relance' => "C'est moi qui suis allé la chercher.",
        ],
        [
            'mot' => 'Baldaquin', 'decoupage' => "bal · d'ac · un", 'nature' => 'n. m.', 'certitude' => 44,
            'pistes' => ['Balcon', 'Baladeur'],
            'epellation' => 'B, A, L, D, A, Q, U, I, N. Le U est là pour accompagner le Q, il ne travaille pas.',
            'scene' => "Fête du village. Le conseiller vend les tickets du bal. Le stagiaire arrive, seul.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "C'est deux euros l'entrée pour le bal. Je vous en mets combien ?"],
                ['qui' => 'stagiaire', 'texte' => "Le bal ? D'ac… Un."],
            ],
            'relance' => 'Je danse seul, mais je danse bien.',
        ],
        [
            'mot' => 'Vicissitude', 'decoupage' => "vis · ici · si tu", 'nature' => 'n. f.', 'certitude' => 39,
            'pistes' => ['Vitesse', 'Vichy'],
            'epellation' => 'V, I, C, I, S, S, I, T, U, D, E. Deux S au milieu, en cas de perte.',
            'scene' => "Le stagiaire, sur un escabeau, monte une étagère. Le conseiller tient la boîte à outils.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Je te passe quoi ? Le marteau ?'],
                ['qui' => 'stagiaire', 'texte' => 'Non. La vis… ici… si tu peux.'],
            ],
            'relance' => "L'étagère penche, mais elle tient.",
        ],
        [
            'mot' => 'Capharnaüm', 'decoupage' => "cafard · nan · hum", 'nature' => 'n. m.', 'certitude' => 66,
            'pistes' => ['Cafetière', 'Caravane'],
            'epellation' => 'C, A, P, H, A, R, N, A, Ü, M. Avec un tréma sur le U, pour faire joli.',
            'scene' => "Une cuisine, le soir. Le stagiaire est debout sur une chaise. Le conseiller, désinsectiseur, prend des notes.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Vous avez vu quoi, au juste ? Un cafard ou un rat ?'],
                ['qui' => 'stagiaire', 'texte' => 'Un cafard. Nan… Hum. Oui, un cafard.'],
            ],
            'relance' => "Il s'appelle Gérard. Enfin, moi, je l'appelle Gérard.",
        ],
        [
            'mot' => 'Parcimonie', 'decoupage' => "par ici · Monie", 'nature' => 'n. f.', 'certitude' => 52,
            'pistes' => ['Parking', 'Parchemin', 'Parc Astérix'],
            'epellation' => "P, A, R, C, I, M, O, N, I, E. Le C fait le bruit du S, c'est un arrangement entre eux.",
            'scene' => "Au marché, dans la foule. Le stagiaire guide sa grand-mère, Monie. Le conseiller est fromager.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Le comté, il est de ce côté !'],
                ['qui' => 'stagiaire', 'texte' => 'Attention, Mamie… Par ici, Monie !'],
            ],
            'relance' => 'Elle a pris du brie, finalement.',
        ],
        [
            'mot' => 'Saltimbanque', 'decoupage' => "salle · timbre · banque", 'nature' => 'n.', 'certitude' => 47,
            'pistes' => ['Salsifis', 'Salto'],
            'epellation' => "S, A, L, T, I, M, B, A, N, Q, U, E. Le M devant le B, c'est le règlement.",
            'scene' => "L'accueil d'une mairie. Le stagiaire, perdu, une liste à la main. Le conseiller est à l'accueil.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Bonjour. Vous cherchez quel service ?'],
                ['qui' => 'stagiaire', 'texte' => 'Alors… La salle… un timbre… et la banque.'],
            ],
            'relance' => 'Et les toilettes, si possible.',
        ],
        [
            'mot' => 'Escarmouche', 'decoupage' => "et ce car · mouche", 'nature' => 'n. f.', 'certitude' => 34,
            'pistes' => ['Escargot', 'Escalator'],
            'epellation' => 'E, S, C, A, R, M, O, U, C, H, E. Un seul R, on économise.',
            'scene' => "Un arrêt de bus, en été. Le conseiller attend. Une mouche tourne autour du stagiaire.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Vingt minutes de retard. Il arrive quand, ce car ?'],
                ['qui' => 'stagiaire', 'texte' => 'Et ce car… Mouche ! Mouche !'],
            ],
            'relance' => 'Le car est passé pendant ce temps-là.',
        ],
        [
            'mot' => 'Ambivalence', 'decoupage' => "hamb… hi · Valence", 'nature' => 'n. f.', 'certitude' => 48,
            'pistes' => ['Ambulance', 'Amboise'],
            'epellation' => 'A, M, B, I, V, A, L, E, N, C, E. Avec un B, comme dans « bof ».',
            'scene' => "Le snack de la gare. Le stagiaire hésite entre manger et son train pour Valence. Le conseiller sert.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Alors, jeune homme, qu'est-ce que ce sera ?"],
                ['qui' => 'stagiaire', 'texte' => 'Euh… un hamb… hi… Valence !'],
            ],
            'relance' => 'Avec des frites, du coup.',
        ],
        [
            'mot' => 'Péninsule', 'decoupage' => "paie · Nina · seule", 'nature' => 'n. f.', 'certitude' => 35,
            'pistes' => ['Pénélope', 'Pénible'],
            'epellation' => 'P, É, N, I, N, S, U, L, E. Le deuxième N est en option.',
            'scene' => "Au restaurant, l'addition arrive. Le stagiaire regarde son amie Nina. Le conseiller est serveur.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Qui règle ?'],
                ['qui' => 'stagiaire', 'texte' => 'Ce soir ? Paie, Nina. Seule.'],
            ],
            'relance' => "Moi, j'ai pris qu'une entrée.",
        ],
    ],

    // Mots savants proposés à l'IA pour ses sketchs (3 syllabes ou plus : plus de matière sonore à découper)
    'mots_savants' => [
        'sarcophage', 'abracadabrantesque', 'pusillanime', 'philatéliste', 'charivari', 'sarcasme', 'castagnettes',
        'acrimonie', 'anachronisme', 'calembredaine', 'circonvolution', 'concupiscence',
        'débonnaire', 'dithyrambique', 'éphémère', 'farfelu', 'galimatias', 'hurluberlu', 'ignominie',
        'impétueux', 'incandescent', 'interstice', 'jérémiade', 'labyrinthe', 'mirobolant', 'obséquieux',
        'onomatopée', 'palimpseste', 'panégyrique', 'paparazzi', 'pérégrination', 'pernicieux',
        'prolégomènes', 'quintessence', 'rocambolesque', 'sempiternel', 'sérendipité', 'sycophante',
        'tarabiscoté', 'turpitude', 'ubiquité', 'velléitaire', 'zénith', 'billevesée', 'cacophonie',
        'esbroufe', 'fanfreluche', 'mansuétude', 'pantomime', 'patatras', 'subterfuge', 'tohu-bohu',
    ],
];
