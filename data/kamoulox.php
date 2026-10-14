<?php
// Banques lexicales du générateur Kamoulox (adaptées au format « conseil », à l'impératif).
// Principe : un verbe précis et concret appliqué à un complément qui ne le supporte pas,
// du vocabulaire du quotidien français (d'hier et d'aujourd'hui), et deux actions sans aucun lien entre elles.
// Verbes : [impératif, infinitif].
return [
    'verbes' => [
        // artisanaux
        ['rabotez', 'raboter'], ['poncez', 'poncer'], ['badigeonnez', 'badigeonner'], ['savonnez', 'savonner'],
        ['marouflez', 'maroufler'], ['décapez', 'décaper'], ['tartinez', 'tartiner'], ['repassez', 'repasser'],
        ['empaillez', 'empailler'], ['vernissez', 'vernir'], ['cirez', 'cirer'], ['tapissez', 'tapisser'],
        ['ramonez', 'ramoner'], ['calfeutrez', 'calfeutrer'], ['dégivrez', 'dégivrer'], ['lustrez', 'lustrer'],
        ['enduisez', 'enduire'], ['jointoyez', 'jointoyer'], ['tronçonnez', 'tronçonner'], ['vissez', 'visser'],
        ['emballez sous vide', 'emballer sous vide'],
        // bureau et administration
        ['photocopiez', 'photocopier'], ['agrafez', 'agrafer'], ['faxez', 'faxer'], ['surlignez', 'surligner'],
        ['tamponnez', 'tamponner'], ['archivez', 'archiver'], ['facturez', 'facturer'], ['plastifiez', 'plastifier'],
        ['scannez', 'scanner'], ['numérotez', 'numéroter'], ['classez', 'classer'], ['contresignez', 'contresigner'],
        ['notifiez', 'notifier'], ['relancez par mail', 'relancer par mail'], ['mettez en copie cachée', 'mettre en copie cachée'],
        ['inscrivez à l\'ordre du jour', 'inscrire à l\'ordre du jour'],
        // cuisine
        ['épluchez', 'éplucher'], ['panez', 'paner'], ['flambez', 'flamber'], ['déglacez', 'déglacer'],
        ['farcissez', 'farcir'], ['dénoyautez', 'dénoyauter'], ['émincez', 'émincer'], ['gratinez', 'gratiner'],
        ['marinez', 'mariner'], ['blanchissez', 'blanchir'], ['caramélisez', 'caraméliser'], ['passez au airfryer', 'passer au airfryer'],
        ['fumez au bois de hêtre', 'fumer au bois de hêtre'], ['congelez', 'congeler'], ['mixez au Thermomix', 'mixer au Thermomix'],
        // numérique
        ['likez', 'liker'], ['géolocalisez', 'géolocaliser'], ['mettez en story', 'mettre en story'],
        ['mettez en vente sur Vinted', 'mettre en vente sur Vinted'], ['épinglez dans le groupe WhatsApp', 'épingler dans le groupe WhatsApp'],
        ['abonnez-vous à', 's\'abonner à'], ['bloquez', 'bloquer'], ['mettez à jour', 'mettre à jour'],
        ['filmez en slow motion', 'filmer en slow motion'], ['notez cinq étoiles', 'noter cinq étoiles'],
        ['réservez sur Doctolib', 'réserver sur Doctolib'], ['partagez l\'écran avec', 'partager l\'écran avec'],
        // divers
        ['louez', 'louer'], ['vaccinez', 'vacciner'], ['repeignez', 'repeindre'], ['assurez', 'assurer'],
        ['déclarez aux impôts', 'déclarer aux impôts'], ['promenez', 'promener'], ['dédicacez', 'dédicacer'],
        ['parrainez', 'parrainer'], ['coachez', 'coacher'], ['homologuez', 'homologuer'], ['sponsorisez', 'sponsoriser'],
        ['naturalisez', 'naturaliser'], ['scolarisez', 'scolariser'], ['décorez', 'décorer'], ['recyclez', 'recycler'],
    ],
    // Verbes inoffensifs, les seuls autorisés avec une célébrité
    'verbes_sociaux' => [
        ['tutoyez', 'tutoyer'], ['vouvoyez', 'vouvoyer'], ['félicitez', 'féliciter'], ['amadouez', 'amadouer'],
        ['saluez', 'saluer'], ['envoyez un fax à', 'envoyer un fax à'], ['souhaitez bon courage à', 'souhaiter bon courage à'],
        ['offrez une raclette à', 'offrir une raclette à'],
        ['demandez l\'heure à', 'demander l\'heure à'], ['envoyez un vocal de quatre minutes à', 'envoyer un vocal de quatre minutes à'],
        ['prêtez votre trottinette à', 'prêter votre trottinette à'], ['tenez la porte à', 'tenir la porte à'],
        ['proposez un covoiturage à', 'proposer un covoiturage à'], ['demandez une dédicace à', 'demander une dédicace à'],
        ['offrez un Kinder Bueno à', 'offrir un Kinder Bueno à'], ['remerciez', 'remercier'],
        ['souhaitez un bon week-end à', 'souhaiter un bon week-end à'], ['applaudissez', 'applaudir'],
    ],
    'objets' => [
        // classiques du quotidien
        'une brouette', 'un pluviomètre', 'une endive', 'un grille-pain', 'un tire-fesse', 'un dentier', 'un rond-point',
        'un abattant de WC', 'une gomme', 'un château fort', 'un paillasson', 'une bétonnière', 'un abat-jour',
        'un pot de rillettes', 'une soupière', 'un scooter des neiges', 'une passoire', 'un nain de jardin',
        'un sèche-cheveux', 'un radiateur', 'une crêpière', 'un camping-car', 'un bidet', 'une tondeuse',
        'un horodateur', 'une calculatrice', 'une perruque', 'un parcmètre', 'une serpillière', 'un bigoudi',
        'un presse-ail', 'une caravane', 'un cendrier', 'une raquette de ping-pong', 'un porte-manteau',
        'une boîte de cassoulet', 'un rouleau de scotch', 'un vélo d\'appartement', 'une tringle à rideaux',
        'un appareil à raclette', 'une yaourtière', 'un chausse-pied', 'une clé à molette', 'un tabouret de bar',
        // produits et marques d'hier
        'un minitel', 'une Safrane', 'une Fuego', 'un Kinder Bueno', 'la Carte Vitale', 'un Paris Match de 1994',
        'un pot de Mamie Nova', 'un Tamagotchi', 'un Walkman', 'un Bescherelle', 'un Carambar', 'une 2CV',
        // produits et objets d'aujourd'hui
        'une trottinette électrique', 'un airfryer', 'un Thermomix', 'une gourde isotherme', 'des AirPods',
        'un ring light', 'une enceinte Bluetooth', 'un pass Navigo', 'une box internet', 'un robot aspirateur',
        'un colis Vinted', 'une Tesla', 'un QR code', 'une manette de PS5', 'un bob Cochonou',
        'un pot de Nutella', 'une perche à selfie', 'un vélo en libre-service', 'une liseuse', 'un bubble tea',
        'un tote bag', 'une montre connectée', 'un Fanta citron', 'un kebab', 'une galette-saucisse',
    ],
    // pour « inventez [objet impossible] »
    'objets_nus' => [
        'le lampadaire', 'la brouette', 'le pluviomètre', 'le grille-pain', 'la tanche', 'le rond-point', 'la serpillière',
        'le dentier', 'la soupière', 'l\'horodateur', 'le paillasson', 'la bétonnière', 'le bigoudi', 'la passoire',
        'la trottinette', 'l\'airfryer', 'le Thermomix', 'le robot aspirateur', 'la box internet', 'le pass Navigo',
        'la galette-saucisse', 'le chausse-pied', 'la yaourtière', 'le QR code', 'la perche à selfie', 'le Tamagotchi',
    ],
    'qualificatifs' => [
        // invariables en genre (s'accordent avec « le » comme avec « la »)
        'à voile', 'gonflable', 'à pédales', 'sans fil', 'en kit', 'à réaction', 'de compétition', 'à manivelle',
        'à remontoir', 'en viager', 'à double vitrage', 'à pile', 'pliable', 'diplomatique', 'connecté au Wi-Fi',
        'éco-responsable', 'en abonnement mensuel', 'à reconnaissance faciale', 'compatible Bluetooth', 'à charbon',
        'sous licence officielle', 'garanti deux ans', 'à pédalier', 'en édition limitée',
    ],
    'lieux' => [
        'au pressing', 'chez Flunch', 'à la déchetterie', 'au rayon fromage', 'à la mairie', 'au PMU',
        'sur une aire d\'autoroute', 'à la sous-préfecture', 'au karting', 'dans un Buffalo Grill', 'à la piscine municipale',
        'au salon de l\'agriculture', 'chez le contrôleur technique', 'au vide-grenier', 'à la laverie automatique',
        'en visio', 'dans un open space', 'au drive', 'dans un escape game', 'au point relais', 'à la salle de sport',
        'dans un Airbnb', 'à la caisse automatique', 'en télétravail', 'au rayon bricolage', 'dans le TER',
        'à la fête des voisins', 'au barbecue de la mairie', 'pendant un webinaire', 'au marché de Noël',
    ],
    'animaux' => [
        'une tanche', 'un lama', 'un furet', 'un pélican', 'une loutre', 'un poulpe', 'une chèvre', 'un ragondin',
        'un hamster', 'une limace', 'un dindon', 'un hérisson', 'un mérou', 'une autruche', 'un blaireau',
        'un capybara', 'un alpaga', 'un axolotl', 'un panda roux', 'une poule pondeuse', 'un bouledogue français',
        'un cochon d\'Inde', 'une sardine', 'un goéland', 'une coccinelle',
    ],
    'animaux_nus' => [
        'poulpe', 'loutre', 'tanche', 'lama', 'furet', 'pélican', 'ragondin', 'chèvre', 'hérisson', 'mérou', 'dindon',
        'capybara', 'alpaga', 'axolotl', 'goéland', 'sardine', 'hamster', 'blaireau',
    ],
    'nationalites' => [
        'moldave', 'finlandais', 'andorran', 'luxembourgeois', 'monégasque', 'liechtensteinois', 'estonien', 'belge',
        'suisse', 'islandais', 'maltais', 'slovène', 'lituanien', 'chypriote', 'québécois',
    ],
    'personnes' => [
        'un paysan', 'un pompier', 'un scout', 'un notaire', 'un douanier', 'votre belle-mère', 'Mamie', 'le facteur',
        'un contrôleur SNCF', 'un maître-nageur', 'votre dentiste', 'un garde-champêtre', 'le maire adjoint',
        'un agent immobilier', 'votre voisin de palier', 'un moniteur d\'auto-école', 'un livreur Deliveroo',
        'votre manager', 'un coach sportif', 'un influenceur culinaire', 'votre stagiaire', 'un chauffeur VTC',
        'le syndic de copropriété', 'votre conseiller France Travail', 'un vendeur Vinted', 'le service client',
        'un arbitre de Ligue 1', 'votre prof de yoga', 'un youtubeur bricolage', 'le gardien de l\'immeuble',
    ],
    // Célébrités grand public, d'hier et d'aujourd'hui : uniquement des actions inoffensives
    // (tutoyer, saluer, faxer, ressembler à…). Pas de politiques, pas de personnalités au cœur d'une polémique.
    'celebrites' => [
        // un peu datées
        'Philippe Lavil', 'Philippe Bouvard', 'David Charvet', 'Francis Lalanne', 'Tex', 'Patrick Sabatier',
        'Jean-Pierre Foucault', 'Pierre Bellemare', 'Évelyne Leclercq', 'Roger Pierre', 'Julien Lepers', 'Michel Drucker',
        'Mimie Mathy', 'Évelyne Dhéliat', 'Christophe Dechavanne',
        // d'aujourd'hui, grand public
        'Thomas Pesquet', 'Teddy Riner', 'Léon Marchand', 'Antoine Dupont', 'Philippe Etchebest', 'Cyril Lignac',
        'Squeezie', 'Nagui', 'Jean-Luc Reichmann', 'Karine Le Marchand', 'Norman', 'Amixem', 'Michou',
        'Laure Manaudou', 'Tony Parker', 'Omar Sy', 'Florent Pagny', 'Vianney',
    ],
    // avec la préposition déjà accordée (« au Luxembourg », pas « à le Luxembourg »)
    // « offrez ça à [pays] » (gabarit E du coup de grandeur)
    'pays' => [
        'à la Suisse', 'au Luxembourg', 'à la Moldavie', 'à la République tchèque', 'à la Belgique', 'à Andorre',
        'au Liechtenstein', 'à Monaco', 'à l\'Estonie', 'à Malte', 'à la Slovénie', 'à l\'Islande', 'au Portugal',
        'au Québec', 'à Saint-Marin', 'à la Lituanie',
    ],
    'concepts' => [
        'Le sulfate de charbon', 'La sédentarité métabolique', 'Le coefficient de friture', 'La jurisprudence du flan',
        'Le théorème du parcmètre', 'La loi de conservation des biscottes', 'Le principe de précaution capillaire',
        'L\'indice de rugosité du mardi', 'La convention de Genève sur les nouilles', 'L\'algorithme du pot-au-feu',
        'Le bilan carbone de la mayonnaise', 'La neuroscience du Kinder Bueno', 'Le RGPD des escargots',
        'La transition énergétique du gratin', 'Le règlement européen sur les bigoudis', 'La blockchain du camembert',
        'Le protocole sanitaire du flan', 'L\'intelligence artificielle de la raclette',
    ],
    'aliments' => [
        'raclette', 'cassoulet', 'pâté en croûte', 'flan', 'tartiflette', 'couscous', 'endive au jambon', 'rillettes',
        'avocado toast', 'poke bowl', 'kebab', 'galette-saucisse', 'bubble tea', 'croque-monsieur', 'quiche lorraine',
    ],
    // Jargon de jeu fantôme, ajouté à la fin d'une action
    'jargon' => [
        'en case {animal} {nationalite}', 'puis passez votre tour', 'puis posez votre joker',
        'puis échangez trois points contre {objet}', 'avec bonus {aliment}', 'en double case {animal}',
        'puis activez votre carte chance {nationalite}', 'en mode {animal} {nationalite}', 'puis demandez la vidéo',
        'avec malus {aliment}', 'puis jouez les prolongations', 'puis validez votre combo {animal}',
    ],
];
