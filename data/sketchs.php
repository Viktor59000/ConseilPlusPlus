<?php
// Le Petit Moyen — format « sketch » : hommage non officiel aux « Mots » d'Éric Judor et Ramzy Bédia (M6, 1997).
// Le conseiller (solennel) et le stagiaire (naïf) prétendent apprendre au public un mot que personne ne connaît,
// puis le « définissent » par une scène du quotidien qui se termine sur un calembour approximatif.
// L'ouverture et la formule finale (« Le cabinet ne voit pas d'autre explication. ») sont ajoutées à l'affichage.
return [
    'sketchs' => [
        [
            'mot' => 'Atermoiement', 'nature' => 'n. m.', 'certitude' => 41,
            'pistes' => ['Atterrissage', 'Armoire'],
            'epellation' => 'A, T, E, R, M, O, A, M, A, N.',
            'scene' => "Un petit garçon (le stagiaire) tombe de vélo. Il est allongé sur le trottoir. Un passant (le conseiller) se penche.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Mon petit, où es-tu ? Qui dois-je appeler ?'],
                ['qui' => 'stagiaire', 'texte' => 'À terre… moi… maman !'],
            ],
            'relance' => 'Et le vélo, on le laisse.',
        ],
        [
            'mot' => 'Despotique', 'nature' => 'adj.', 'certitude' => 57,
            'pistes' => ['Despacito'],
            'epellation' => "D, É, S, P, O, T, I, Q, U, E. Le é a un accent, mais on ne l'entend pas.",
            'scene' => "Fin de soirée d'anniversaire. Le stagiaire, un peu fatigué, lève son verre de jus de pomme devant ses amis.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Un discours ! Un discours !'],
                ['qui' => 'stagiaire', 'texte' => 'Je voulais juste dire… merci… à tous… des potes, hic !'],
            ],
            'relance' => '',
        ],
        [
            'mot' => 'Hyperbole', 'nature' => 'n. f.', 'certitude' => 62,
            'pistes' => ['Hypermarché'],
            'epellation' => 'H, Y, P, E, R, B, O, L, E. Avec un H, mais il est muet, donc on ne le compte pas.',
            'scene' => "Au zoo. Un soigneur (le stagiaire) vide un sac de céréales par terre devant l'hippopotame, qui s'appelle Hippo. Le directeur (le conseiller) arrive en courant.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Mais enfin ! Pas par terre ! Hippo… bol !'],
            ],
            'relance' => "Il préfère le lait d'avoine.",
        ],
        [
            'mot' => 'Ambivalence', 'nature' => 'n. f.', 'certitude' => 48,
            'pistes' => ['Ambulance', 'Amboise'],
            'epellation' => 'A, M, B, I, V, A, L, E, N, C, E. Avec un B, comme dans « bof ».',
            'scene' => "À la gare, au comptoir d'un snack. Le stagiaire hésite entre manger et attraper son train pour Valence. La serveuse (le conseiller) s'impatiente.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => "Alors, jeune homme, qu'est-ce que ce sera ?"],
                ['qui' => 'stagiaire', 'texte' => 'Euh… un hamb… hi… Valence !'],
            ],
            'relance' => 'Avec des frites, du coup.',
        ],
        [
            'mot' => 'Péninsule', 'nature' => 'n. f.', 'certitude' => 35,
            'pistes' => ['Pénélope', 'Pénible'],
            'epellation' => 'P, É, N, I, N, S, U, L, E. Le deuxième N est en option.',
            'scene' => "Au restaurant, l'addition arrive. Le stagiaire regarde son amie Nina, puis le serveur (le conseiller).",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Qui règle ?'],
                ['qui' => 'stagiaire', 'texte' => 'Ce soir ? Paie, Nina. Seule.'],
            ],
            'relance' => "Moi, j'ai pris qu'une entrée.",
        ],
        [
            'mot' => 'Tergiverser', 'nature' => 'v. intr.', 'certitude' => 53,
            'pistes' => ['Terminator', 'Tergal'],
            'epellation' => 'T, E, R, G, I, V, E, R, S, E, R. Deux R, mais un seul compte.',
            'scene' => "Dans une cuisine. Le stagiaire tient une bouilloire au-dessus d'une tasse vide. Le conseiller, pressé, attend son thé.",
            'repliques' => [
                ['qui' => 'conseiller', 'texte' => 'Bon, ce thé, ça vient ?'],
                ['qui' => 'stagiaire', 'texte' => "Le thé ? J'y… verse !"],
            ],
            'relance' => "C'est du tilleul.",
        ],
    ],

    // Mots savants proposés à l'IA pour ses sketchs (3 syllabes ou plus : plus de matière sonore à découper)
    'mots_savants' => [
        'sarcophage', 'abracadabrantesque', 'pusillanime', 'philatéliste', 'charivari', 'sarcasme', 'castagnettes',
        'acrimonie', 'anachronisme', 'baldaquin', 'calembredaine', 'capharnaüm', 'circonvolution', 'concupiscence',
        'débonnaire', 'dithyrambique', 'éphémère', 'escarmouche', 'farfelu', 'galimatias', 'hurluberlu', 'ignominie',
        'impétueux', 'incandescent', 'interstice', 'jérémiade', 'labyrinthe', 'mirobolant', 'nonchalance', 'obséquieux',
        'onomatopée', 'palimpseste', 'panégyrique', 'paparazzi', 'parcimonie', 'pérégrination', 'pernicieux',
        'prolégomènes', 'quintessence', 'rocambolesque', 'saltimbanque', 'sempiternel', 'sérendipité', 'sycophante',
        'tarabiscoté', 'tintamarre', 'turpitude', 'ubiquité', 'velléitaire', 'vicissitude', 'zénith',
    ],
];
