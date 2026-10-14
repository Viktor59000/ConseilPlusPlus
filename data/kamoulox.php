<?php
// Banques lexicales du générateur Kamoulox (adaptées au format « conseil », à l'impératif).
// Principe : un verbe précis et concret appliqué à un complément qui ne le supporte pas,
// du vocabulaire du quotidien français, et deux actions sans aucun lien entre elles.
// Verbes : [impératif, infinitif].
return [
    'verbes' => [
        // artisanaux
        ['rabotez', 'raboter'], ['poncez', 'poncer'], ['badigeonnez', 'badigeonner'], ['savonnez', 'savonner'],
        ['marouflez', 'maroufler'], ['décapez', 'décaper'], ['tartinez', 'tartiner'], ['repassez', 'repasser'],
        ['empaillez', 'empailler'], ['vernissez', 'vernir'], ['cirez', 'cirer'], ['tapissez', 'tapisser'],
        ['ramonez', 'ramoner'], ['calfeutrez', 'calfeutrer'], ['dégivrez', 'dégivrer'],
        // bureau
        ['photocopiez', 'photocopier'], ['agrafez', 'agrafer'], ['faxez', 'faxer'], ['surlignez', 'surligner'],
        ['tamponnez', 'tamponner'], ['archivez', 'archiver'], ['facturez', 'facturer'], ['plastifiez', 'plastifier'],
        ['scannez', 'scanner'], ['numérotez', 'numéroter'], ['classez', 'classer'],
        // cuisine
        ['épluchez', 'éplucher'], ['panez', 'paner'], ['flambez', 'flamber'], ['déglacez', 'déglacer'],
        ['farcissez', 'farcir'], ['dénoyautez', 'dénoyauter'], ['émincez', 'émincer'], ['gratinez', 'gratiner'],
        ['marinez', 'mariner'], ['blanchissez', 'blanchir'],
        // divers
        ['louez', 'louer'], ['vaccinez', 'vacciner'], ['repeignez', 'repeindre'],
        ['assurez', 'assurer'], ['déclarez aux impôts', 'déclarer aux impôts'], ['promenez', 'promener'],
    ],
    'verbes_sociaux' => [
        ['tutoyez', 'tutoyer'], ['vouvoyez', 'vouvoyer'], ['félicitez', 'féliciter'], ['amadouez', 'amadouer'],
        ['recadrez', 'recadrer'], ['adoptez', 'adopter'], ['saluez', 'saluer'], ['envoyez un fax à', 'envoyer un fax à'],
        ['souhaitez bon courage à', 'souhaiter bon courage à'], ['invitez au PMU', 'inviter au PMU'],
        ['offrez une raclette à', 'offrir une raclette à'], ['demandez l\'heure à', 'demander l\'heure à'],
    ],
    'objets' => [
        'une brouette', 'un pluviomètre', 'une endive', 'un grille-pain', 'un tire-fesse', 'un dentier', 'un rond-point',
        'un abattant de WC', 'une gomme', 'un château fort', 'un paillasson', 'une bétonnière', 'un abat-jour',
        'un pot de rillettes', 'une soupière', 'un scooter des neiges', 'une passoire', 'un nain de jardin',
        'un sèche-cheveux', 'un radiateur', 'une crêpière', 'un camping-car', 'un bidet', 'une tondeuse',
        'un horodateur', 'une calculatrice', 'une perruque', 'un parcmètre', 'une serpillière', 'un bigoudi',
        'un presse-ail', 'une caravane', 'un cendrier', 'une raquette de ping-pong', 'un porte-manteau',
        'un minitel', 'une Safrane', 'une Fuego', 'un Kinder Bueno', 'la Carte Vitale', 'un Paris Match de 1994',
        'un pot de Mamie Nova', 'une boîte de cassoulet', 'un rouleau de scotch', 'un vélo d\'appartement',
    ],
    // pour « inventez [objet impossible] »
    'objets_nus' => [
        'le lampadaire', 'la brouette', 'le pluviomètre', 'le grille-pain', 'la tanche', 'le rond-point', 'la serpillière',
        'le dentier', 'la soupière', 'l\'horodateur', 'le paillasson', 'la bétonnière', 'le bigoudi', 'la passoire',
    ],
    'qualificatifs' => [
        // invariables en genre (s'accordent avec « le » comme avec « la »)
        'à voile', 'gonflable', 'à pédales', 'sans fil', 'en kit', 'à réaction', 'de compétition', 'à manivelle',
        'à remontoir', 'en viager', 'à double vitrage', 'à pile', 'pliable', 'diplomatique',
    ],
    'lieux' => [
        'au pressing', 'chez Flunch', 'à la déchetterie', 'au rayon fromage', 'à la mairie', 'au PMU',
        'sur une aire d\'autoroute', 'à la sous-préfecture', 'au karting', 'dans un Buffalo Grill', 'à la piscine municipale',
        'au salon de l\'agriculture', 'chez le contrôleur technique', 'au vide-grenier', 'à la laverie automatique',
    ],
    'animaux' => [
        'une tanche', 'un lama', 'un furet', 'un pélican', 'une loutre', 'un poulpe', 'une chèvre', 'un ragondin',
        'un hamster', 'une limace', 'un dindon', 'un hérisson', 'un mérou', 'une autruche', 'un blaireau',
    ],
    'animaux_nus' => ['poulpe', 'loutre', 'tanche', 'lama', 'furet', 'pélican', 'ragondin', 'chèvre', 'hérisson', 'mérou', 'dindon'],
    'nationalites' => ['moldave', 'finlandais', 'andorran', 'luxembourgeois', 'monégasque', 'liechtensteinois', 'estonien', 'belge', 'suisse', 'islandais'],
    'personnes' => [
        'un paysan', 'un pompier', 'un scout', 'un notaire', 'un douanier', 'votre belle-mère', 'Mamie', 'le facteur',
        'un contrôleur SNCF', 'un maître-nageur', 'votre dentiste', 'un garde-champêtre', 'le maire adjoint',
        'un agent immobilier', 'votre voisin de palier', 'un moniteur d\'auto-école',
    ],
    // Célébrités un peu datées : uniquement des actions inoffensives (tutoyer, saluer, faxer, ressembler à…)
    'celebrites' => [
        'Philippe Lavil', 'Philippe Bouvard', 'David Charvet', 'Francis Lalanne', 'Tex', 'Patrick Sabatier',
        'Jean-Pierre Foucault', 'Pierre Bellemare', 'Jacques Martin', 'Évelyne Leclercq', 'Roger Pierre',
    ],
    // avec la préposition déjà accordée (« au Luxembourg », pas « à le Luxembourg »)
    'pays' => ['à la Suisse', 'au Luxembourg', 'à la Moldavie', 'à la République tchèque', 'à la Belgique', 'à Andorre', 'au Liechtenstein', 'à Monaco', 'à l\'Estonie'],
    'concepts' => [
        'Le sulfate de charbon', 'La sédentarité métabolique', 'Le coefficient de friture', 'La jurisprudence du flan',
        'Le théorème du parcmètre', 'La loi de conservation des biscottes', 'Le principe de précaution capillaire',
        'L\'indice de rugosité du mardi', 'La convention de Genève sur les nouilles',
    ],
    'aliments' => ['raclette', 'cassoulet', 'pâté en croûte', 'flan', 'tartiflette', 'couscous', 'endive au jambon', 'rillettes'],
    // Jargon de jeu fantôme, ajouté à la fin d'une action
    'jargon' => [
        'en case {animal} {nationalite}', 'puis passez votre tour', 'puis posez votre joker',
        'puis échangez trois points contre {objet}', 'avec bonus {aliment}', 'en double case {animal}',
    ],
];
