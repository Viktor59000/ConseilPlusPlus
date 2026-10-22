<?php
// Gabarits des débuts (situations) et fins (résultats) de conseils, composés au hasard.
// Ils complètent les phrases écrites à la main de data/fragments.php.
return [
    // « Si votre [chose] [problème], » (masculin ou féminin : les problèmes et réactions restent invariables)
    'choses' => [
        'friteuse', 'bouilloire', 'cafetière', 'sèche-linge', 'hotte aspirante', 'store électrique', 'manager',
        'voisin du dessus', 'interphone', 'radiateur', 'micro-ondes', 'chat', 'scanner', 'thermostat',
        'machine à café', 'chaudière', 'portail automatique', 'tablette', 'vélo électrique', 'lave-vaisselle', 'compteur Linky', 'tondeuse',
        'colocataire', 'perceuse', 'enceinte Bluetooth', 'banque', 'syndic', 'poisson rouge', 'réveil', 'agrafeuse',
    ],
    'problemes' => [
        'vous ignore', 'fait un bruit bizarre', 'refuse de démarrer', 'émet un bip toutes les quatre minutes', 'demande une augmentation',
        "s'est inscrit sur Vinted", 'sent légèrement le caramel', 'parle pendant son sommeil', 'a changé de mot de passe',
        'vous envoie des vocaux', 'fait grève', "s'est mis au yoga", 'vous appelle par le mauvais prénom',
        'a rejoint une chorale sans vous', 'boude depuis mardi', 'exige un entretien annuel', 'réclame des congés',
        'se prend pour un influenceur', 'a perdu toute motivation', 'vous a bloqué', 'fait du bruit à 3h du matin',
        'ne répond plus aux mails', 'a commandé un kebab à votre nom', 'regarde des tutoriels de bricolage',
    ],
    // « Avant de [activité], »
    'activites' => [
        'repeindre votre salon', 'rappeler votre banque', 'vous inscrire au marathon', 'partir en camping',
        'adopter un furet', 'acheter un fer à repasser vapeur', "prendre l'avion pour Brest", 'lancer une lessive',
        'poser du papier peint', 'commander une pizza quatre fromages', 'changer de coiffure', 'vendre votre canapé sur Leboncoin',
        'changer de fournisseur d\'électricité', 'appeler votre mère', 'ouvrir une boulangerie', 'partir en week-end à Châteauroux',
        'tondre la pelouse', 'organiser un anniversaire surprise', 'passer le code de la route', 'faire réviser votre chaudière',
        'vous mettre au running', "refaire votre carte d'identité", "acheter une Safrane d'occasion", 'apprendre la flûte',
    ],
    // « En cas de [événement], »
    'evenements' => [
        'pluie de grenouilles', "panne d'ascenseur générale", 'grève des boulangers', 'pénurie de coquillettes',
        'réunion surprise', 'invasion de coccinelles', 'neige en juillet', 'rupture de stock de piles AA',
        'bug informatique', 'visite surprise du contrôleur du gaz', 'coupure de courant pendant le gratin',
        'brouillard sur le parking', "fuite d'eau chez le voisin", 'contrôle fiscal imaginaire', 'mise à jour obligatoire',
        'retard de la SNCF', 'disparition de la télécommande', 'tempête de grêle sur la véranda', 'éclipse un mardi',
        'embouteillage au drive', 'pénurie de trombones', 'match de foot en prolongation',
    ],
    // « Pour [objectif], »
    'objectifs' => [
        'devenir maire de votre immeuble', 'gagner au loto du quartier', 'impressionner un huissier', 'réussir votre brunch',
        'faire sourire la douane', 'obtenir une promotion imaginaire', 'battre votre record de sieste', 'devenir viral à Lunel',
        'réussir votre crémaillère', 'gagner le concours de pétanque', 'avoir enfin la paix', 'retrouver le bouchon du dentifrice',
        'décrocher le prix du meilleur cake', 'faire bonne impression au barbecue', 'obtenir le respect des mouettes',
        'réussir votre reconversion professionnelle', 'devenir incontournable sur LinkedIn', 'ranger enfin le garage',
    ],
    // Fins : « et [quelqu'un] vous [réaction]. »
    'quelquun' => [
        'le voisinage', 'le facteur', 'votre manager', 'votre tante', 'votre comptable', 'le cabinet dentaire', 'la médiathèque',
        'votre banque', 'une personne inconnue', 'votre chat', 'le service client', 'votre kinésithérapeute', 'la boulangerie',
        'un pigeon', 'votre ex', 'le point relais', 'la salle de sport', "la loge de l'immeuble",
    ],
    'reactions' => [
        'enverra un faire-part', 'respectera davantage', 'rendra votre agrafeuse', "invitera à un vin d'honneur",
        'proposera un CDI', 'suivra sur Instagram', 'saluera chaque mardi', 'tutoiera à vie', "offrira un calendrier de l'an dernier",
        'appellera « Maître »', 'demandera un autographe', 'enverra un bouquet de félicitations',
        'remerciera par lettre recommandée', 'consultera avant chaque décision', 'prêtera sa tondeuse', 'confiera un secret sans intérêt',
        'attendra sur le quai', 'écoutera soudain avec passion', 'cédera sa place au drive', 'citera dans un discours de mariage',
    ],
    // Fins : « ce qui [effet]. »
    'effets' => [
        'fera baisser le prix du gruyère râpé', 'sera validé par le service des archives', 'vous ouvrira les portes du Rotary Club',
        'déclenchera une enquête de voisinage', 'sera mentionné au journal de 13h', 'vous rapportera trois points de fidélité',
        'améliorera votre score au bowling', 'fera trembler le marché du radis', "n'a jamais été tenté en Bretagne",
        'vous qualifiera pour la finale régionale de lancer de tongs', 'sera inscrit au patrimoine de votre immeuble',
        'vous vaudra une mention spéciale du jury', 'effraiera légèrement votre assureur', 'sera débattu en conseil municipal',
        'vous rendra prioritaire à la file des colis', "intriguera l'Institut géographique national", "sera relevé par le compteur d'eau",
    ],
    // Fins : « pour que votre [chose] [réaction d'objet]. »
    'reactions_choses' => [
        'retrouve sa notice', 'retrouve le sourire', 'accepte enfin les mises à jour', 'vous laisse dormir',
        'parte en vacances', 'reprenne confiance', 'arrête de clignoter', 'trouve sa voie',
        'garde le moral jusqu\'au printemps', 'vous respecte un peu plus', 'consente à fonctionner le dimanche', 'signe enfin la paix',
    ],
    // Fins : « et vous serez nommé [titre] [domaine]. » (titres épicènes)
    'titres' => [
        'membre du bureau', "membre d'honneur", 'chef de projet', 'responsable', 'secrétaire de séance', 'arbitre officiel',
        'capitaine', 'porte-parole', 'commissaire aux comptes', 'mascotte', 'archiviste', 'témoin officiel',
    ],
    'domaines' => [
        'de votre immeuble', 'du carrefour le plus proche', 'du club de pétanque', 'du rayon surgelés', 'des boîtes aux lettres du quartier',
        'du repas de famille', 'du loto des écoles', 'du parking du supermarché', 'du local à poubelles',
        'des couvercles orphelins', 'de la brocante du dimanche', 'du distributeur de friandises', 'du TER de 7h12',
    ],
];
