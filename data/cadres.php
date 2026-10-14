<?php
// Gabarits des débuts (situations) et fins (résultats) de conseils, composés au hasard.
// Ils complètent les phrases écrites à la main de data/fragments.php.
return [
    // « Si votre [chose] [problème], »
    'choses' => [
        'grille-pain', 'airfryer', 'box internet', 'plante verte', 'belle-mère', 'robot aspirateur', 'manager',
        'voisin du dessus', 'trottinette', 'Thermomix', 'frigo', 'chat', 'imprimante', 'montre connectée',
        'machine à café', 'chaudière', 'banquier', 'stagiaire', 'GPS', 'lave-vaisselle', 'dentiste', 'tondeuse',
        'colocataire', 'perceuse', 'enceinte Bluetooth', 'cousin', 'syndic', 'poisson rouge', 'réveil', 'agrafeuse',
    ],
    'problemes' => [
        'vous ignore', 'fait un bruit bizarre', 'refuse de démarrer', 'vous juge en silence', 'demande une augmentation',
        "s'est inscrit sur Vinted", 'clignote orange', 'parle pendant son sommeil', 'a changé de mot de passe',
        'vous envoie des vocaux', 'fait grève', "s'est mis au yoga", 'vous appelle par le mauvais prénom',
        'a rejoint un groupe WhatsApp sans vous', 'boude depuis mardi', 'exige un entretien annuel', 'réclame des congés',
        'se prend pour un influenceur', 'a perdu toute motivation', 'vous a bloqué', 'fait du bruit à 3h du matin',
        'ne répond plus aux mails', 'a commandé un kebab à votre nom', 'regarde des tutoriels de bricolage',
    ],
    // « Avant de [activité], »
    'activites' => [
        'repeindre votre salon', 'rappeler votre banque', 'vous inscrire au marathon', 'partir en camping',
        'adopter un furet', 'acheter un airfryer', 'rejoindre une réunion Teams', 'lancer une lessive',
        'monter un meuble suédois', 'commander un kebab', 'changer de coiffure', 'vendre votre canapé sur Leboncoin',
        'demander une augmentation', 'appeler votre mère', 'ouvrir une boulangerie', 'partir en week-end à Châteauroux',
        'tondre la pelouse', 'organiser un anniversaire surprise', 'passer le code de la route', 'publier votre première story',
        'vous mettre au running', 'rencontrer vos beaux-parents', "acheter une Safrane d'occasion", 'apprendre la flûte',
    ],
    // « En cas de [événement], »
    'evenements' => [
        'pluie de grenouilles', 'panne de Wi-Fi générale', 'grève des boulangers', 'pénurie de Kinder Bueno',
        'réunion surprise', 'invasion de pigeons', 'canicule en novembre', 'rupture de stock de raclette',
        'bug informatique', 'visite surprise de la belle-famille', 'coupure de courant pendant le match',
        'brouillard au rond-point', "fuite d'eau chez le voisin", 'contrôle fiscal imaginaire', 'mise à jour obligatoire',
        'retard de la SNCF', 'disparition de la télécommande', 'tempête dans le groupe WhatsApp', 'éclipse un mardi',
        'embouteillage au drive', 'pénurie de papier toilette', 'match de foot en prolongation',
    ],
    // « Pour [objectif], »
    'objectifs' => [
        'devenir maire de votre immeuble', 'gagner au loto du quartier', 'impressionner un notaire', 'réussir votre brunch',
        'séduire un douanier', 'obtenir une promotion imaginaire', 'battre votre record de sieste', 'devenir viral à Lunel',
        'réussir votre crémaillère', 'gagner le concours de pétanque', 'avoir enfin la paix', 'retrouver vos clés',
        "devenir l'employé du mois", 'faire bonne impression au barbecue', 'obtenir le respect des pigeons',
        'réussir votre reconversion professionnelle', 'devenir incontournable sur LinkedIn', 'arrêter de procrastiner demain',
    ],
    // Fins : « et [quelqu'un] vous [réaction]. »
    'quelquun' => [
        'votre voisin', 'le facteur', 'votre manager', 'votre belle-mère', 'le syndic', 'votre dentiste', 'le maire',
        'votre banquier', 'un inconnu', 'votre chat', 'le service client', 'votre stagiaire', 'la boulangère',
        'un pigeon', 'votre ex', 'le livreur', 'votre coach sportif', "le gardien de l'immeuble",
    ],
    'reactions' => [
        'enverra une carte postale', 'respectera davantage', 'rendra votre agrafeuse', 'invitera à son barbecue',
        'proposera un CDI', 'suivra sur Instagram', 'saluera chaque mardi', 'tutoiera à vie', 'offrira un croissant',
        'appellera « Monsieur le Directeur »', 'demandera un autographe', 'enverra un vocal de félicitations',
        'remerciera par lettre recommandée', 'ajoutera sur LinkedIn', 'prêtera sa tondeuse', 'confiera un secret sans intérêt',
        'attendra au rond-point', 'trouvera soudain fascinant', 'cédera sa place au drive', 'mentionnera dans son testament',
    ],
    // Fins : « ce qui [effet]. »
    'effets' => [
        'fera baisser le prix de la raclette', 'sera validé par la sous-préfecture', 'vous ouvrira les portes du Rotary Club',
        'déclenchera une enquête du syndic', 'sera mentionné au journal de 13h', 'vous rapportera trois points de fidélité',
        'améliorera votre score au bowling', 'fera trembler le marché du Kinder Bueno', "n'a jamais été tenté en Bretagne",
        'vous qualifiera pour la finale régionale de rien', 'sera inscrit au patrimoine de votre immeuble',
        'vous vaudra une mention spéciale du jury', 'effraiera légèrement votre assureur', 'sera débattu en conseil municipal',
        'vous rendra éligible à une carte de fidélité inutile', 'intriguera la météo', 'fera de vous une légende du TER',
    ],
    // Fins : « pour que votre [chose] [réaction d'objet]. »
    'reactions_choses' => [
        'cesse de vous juger', 'retrouve le sourire', 'accepte enfin les mises à jour', 'vous laisse dormir',
        'parte en vacances', 'reprenne confiance en lui', 'arrête de clignoter', 'trouve sa voie',
        'se sente moins seul', 'vous respecte un peu plus', "abandonne sa carrière d'influenceur", 'signe enfin la paix',
    ],
    // Fins : « et vous serez nommé [titre] [domaine]. »
    'titres' => [
        'ambassadeur', "président d'honneur", 'chef de rayon', 'délégué', 'trésorier adjoint', 'médiateur officiel',
        'conseiller spécial', 'porte-parole', 'grand chancelier', 'vice-champion', 'référent', 'parrain',
    ],
    'domaines' => [
        'de votre immeuble', 'du rond-point le plus proche', 'du club de pétanque', 'du rayon surgelés', 'des pigeons du quartier',
        'du groupe WhatsApp familial', 'de la fête des voisins', 'du parking de la mairie', 'de la laverie automatique',
        'des chaussettes orphelines', 'du marché de Noël', 'de la machine à café', 'du TER de 7h12',
    ],
];
