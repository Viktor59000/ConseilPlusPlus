<?php
// Petits textes du site qui changent à chaque visite (includes/textes.php).
// Contenus originaux. Personnes inventées ; les célébrités ne viennent que de data/kamoulox.php, dans des situations
// inoffensives. Rien de politique.
return [
    // Texte du tampon posé sur la copie du conseil
    'tampons' => [
        'Validé', 'Vu', 'Conforme', 'Bof', 'Classé', 'Approuvé', 'Lu en diagonale', 'Admis', 'Retenu', 'Passable',
        'Recevable', 'Visé', 'Certifié', 'À peu près', 'Accordé', 'Homologué',
    ],

    // Mention en bas de la copie du conseil
    'mentions' => [
        'Offert. Non remboursable.', 'Valable jusqu\'à nouvel ordre.', 'Ne pas plier.', 'Relu par personne.',
        'Garanti sans garantie.', 'À conserver hors de portée des décisions.', 'Copie non contractuelle.',
        'Original perdu. Ceci est une copie.', 'Délivré sans ordonnance.', 'Usage externe uniquement.',
        'Ne pas suivre à jeun.', 'Tamponné avec soin, ou presque.', 'Document officiel (dans une certaine mesure).',
        'Imprimé sur papier recyclé, puis égaré.',
    ],

    // Délais de traitement « estimés » (accusé de réception du site, dossiers du Bureau) : l'ordre compte,
    // un même numéro de dossier donne toujours le même délai
    'delais' => [
        'entre 3 jours et jamais', 'sous 48 h, ouvrables, hors jours ouvrables', 'dès que possible, voire après',
        'avant la fin de l\'année (sans précision)', 'aussitôt que le stagiaire revient', 'quelques semaines, à la louche',
    ],

    // Note de service selon le jour de la semaine (1 = lundi … 7 = dimanche)
    'jours' => [
        1 => "Note de service : le cabinet ouvre à 14 h 12, comme tous les lundis, par solidarité.",
        2 => "Note de service : le mardi, nos conseils sont livrés avec une légère avance sur leur retard.",
        3 => "Note de service : le mercredi, le stagiaire est d'astreinte. Bon courage à tous.",
        4 => "Note de service : le jeudi est un vendredi qui se cherche. Nous l'accompagnons.",
        5 => "Note de service : vendredi après-midi, les conseils sont garantis à moitié.",
        6 => "Note de service : le samedi, le cabinet conseille en tenue décontractée. Les conseils aussi.",
        7 => "Note de service : le dimanche, le conseiller réfléchit. Il réfléchit beaucoup. Il dort, en fait.",
    ],

    // Dates particulières (« jour-mois »), prioritaires sur la note du jour
    'dates' => [
        '01-01' => "Bonne année ! Le cabinet vous souhaite une année moyenne, ce qui est déjà beaucoup.",
        '06-01' => "Épiphanie : le stagiaire a trouvé la fève. Il la conserve comme pièce à conviction.",
        '02-02' => "Chandeleur : la crêpe du jour est tombée côté conseil.",
        '14-02' => "Saint-Valentin : le conseiller offre un conseil à deux. Il est deux fois moins bon.",
        '01-04' => "Le cabinet est fermé aujourd'hui : trop sérieux, ce jour-là.",
        '01-05' => "Le cabinet ne travaille pas aujourd'hui. Les conseils sortent tout seuls, par habitude.",
        '21-06' => "Fête de la musique : le stagiaire joue du diabolo dans le hall. Ce n'est pas un instrument.",
        '14-07' => "Ce soir, le cabinet tire un feu d'artifice moyen depuis le balcon du deuxième.",
        '15-08' => "Mi-août : le cabinet tourne au ralenti. Plus que d'habitude, oui, c'est possible.",
        '01-09' => "Rentrée : le stagiaire a acheté un cartable. Nous ne savons pas pourquoi.",
        '31-10' => "Halloween : le conseiller s'est déguisé en conseiller compétent. Effrayant.",
        '01-11' => "Le cabinet fait le pont. Le pont n'a rien demandé.",
        '24-12' => "Réveillon : le stagiaire emballe les dossiers en souffrance. Il y en a beaucoup.",
        '25-12' => "Joyeux Noël. Votre conseil vous attend sous le sapin, entre deux chaussettes.",
        '31-12' => "Dernier jour de l'année : tous les dossiers en cours sont reportés à l'an prochain, comme d'habitude.",
    ],

    // Témoignages de clients inventés : [texte, nom, précision]
    'temoignages' => [
        ["Depuis mon premier conseil, mon micro-ondes me respecte.", 'Gérard, 54 ans', 'propriétaire d\'un micro-ondes'],
        ["J'ai suivi le conseil à la lettre. Je ne recommande pas. Cinq étoiles.", 'Sandrine', 'cliente Illimitée'],
        ["Ils ne m'ont pas aidée, mais avec une telle assurance.", 'Odile', 'ancienne candidate Premium (refusée)'],
        ["Mon problème est toujours là, mais il a un numéro de dossier.", 'Karim', 'dossier n° 4412-B'],
        ["Je suis venu pour un conseil, je suis reparti avec un conseil. Rien à redire.", 'Patrice', 'client satisfait, en surface'],
        ["Grâce au cabinet, j'ai vouvoyé mon grille-pain. Il m'appelle encore « monsieur ».", 'Bernard', 'retraité de la tartine'],
        ["Le délai annoncé était « entre 3 jours et jamais ». Ils ont tenu parole.", 'Aurélie', 'cliente patiente'],
        ["Mon psy m'a conseillé d'arrêter ConseilPlus+. Le cabinet m'a conseillé d'arrêter mon psy. J'hésite.", 'Thomas', 'en pleine réflexion'],
        ["J'ai offert un abonnement à ma belle-mère. Elle ne me parle plus. Merci.", 'Nadia', 'cliente reconnaissante'],
        ["Le tampon « Validé » a changé ma vie. Je le regarde tous les matins.", 'Michel', 'collectionneur de tampons'],
        ["J'ai posé une question précise. J'ai reçu une réponse. Elle n'avait aucun rapport. C'est ce qui me plaît.", 'Laurence', 'ingénieure'],
        ["Le stagiaire m'a appris un mot. Je ne l'ai jamais revu, ni le mot ni le stagiaire.", 'Yves', 'lecteur du Petit Moyen'],
        ["Ma plante verte a repris des couleurs depuis que je lui lis le conseil du jour. Elle a peur, je pense.", 'Céline', 'jardinière d\'intérieur'],
        ["Huit conseils dans mon carnet, aucun appliqué. Je me sens organisé.", 'Julien', 'client Illimité'],
        ["J'ai perdu au Duel contre le cabinet. Puis j'ai perdu au Duel contre le cabinet.", 'Rodolphe', 'adversaire régulier'],
        ["Au guichet, on m'a appelé au moment exact où il fermait. Quelle précision.", 'Martine', 'ticket n° 0057'],
        ["Je recommande à toute personne qui n'a besoin de rien.", 'Fabrice', 'consultant (le vrai)'],
        ["J'ai demandé à parler à un responsable. On m'a passé la machine à café.", 'Hélène', 'réclamation n° 0112'],
        ["Conseil reçu : repasser mes rideaux en chantant. Les voisins m'ont applaudi. Je ne sais pas pourquoi.", 'Arnaud', 'chanteur malgré lui'],
        ["Je n'ai pas compris le conseil, mais j'ai compris qu'il était officiel.", 'Josiane', 'cliente respectueuse des procédures'],
        ["On m'avait promis un conseil moyen. C'était exactement ça.", 'Pascal', 'client exigeant'],
        ["Mon chat a lu le mot du jour par-dessus mon épaule. Depuis, il me juge.", 'Inès', 'maîtresse d\'un chat cultivé'],
        ["Formule Premium refusée trois fois. J'apprécie qu'ils aient des principes.", 'Grégoire', 'persévérant'],
        ["Ça ne sert à rien, et c'est précisément pour ça que j'y retourne.", 'Valérie', 'cliente fidèle'],
    ],

    // L'équipe du cabinet (page « Le cabinet ») : portrait facultatif dans assets/equipe/<cle>.webp
    'equipe' => [
        ['cle' => 'conseiller', 'nom' => 'Le conseiller moyen', 'role' => 'Fondateur et associé unique',
            'bio' => "A fondé le cabinet un mardi, faute de mieux. Aucun diplôme, cent pour cent d'assurance. Ne s'est jamais trompé, puisqu'il ne vérifie jamais.",
            'fiche' => ['Ancienneté' => 'depuis toujours', 'Spécialité' => 'le ton grave', 'Taux de réussite' => 'non mesuré, par principe']],
        ['cle' => 'stagiaire', 'nom' => 'Le stagiaire', 'role' => 'Stagiaire (depuis 2009)',
            'bio' => "Lit les mots de travers avec un aplomb remarquable. Responsable des sketchs du Petit Moyen, de la photocopieuse et, par erreur, de la comptabilité.",
            'fiche' => ['Ancienneté' => '17 ans de stage', 'Spécialité' => 'le calembour justifié', 'Gratification' => 'un ticket de cantine']],
        ['cle' => 'delais', 'nom' => 'Mireille', 'role' => 'Responsable des délais',
            'bio' => "N'a jamais respecté un délai, par cohérence avec sa fonction. Auteure de la formule « entre 3 jours et jamais », déposée à l'INPI avec un léger retard.",
            'fiche' => ['Ancienneté' => 'arrivée en avance, une fois', 'Spécialité' => 'le fourchettage', 'Agenda' => 'complet jusqu\'en 2031']],
        ['cle' => 'guichet', 'nom' => 'Bertrand', 'role' => 'Agent d\'accueil, guichet n° 7',
            'bio' => "Appelle les numéros dans un ordre qui lui est propre. Ferme le guichet au moment exact où vient votre tour. Personne ne sait comment il fait.",
            'fiche' => ['Ancienneté' => '22 pauses café par jour', 'Spécialité' => 'la fermeture', 'Devise' => '« Suivant ! » (jamais vous)']],
        ['cle' => 'cafe', 'nom' => 'La machine à café', 'role' => 'Membre le plus utile de l\'équipe',
            'bio' => "Seul membre du cabinet à avoir une utilité prouvée. Fait office de responsable des réclamations les jours d'affluence.",
            'fiche' => ['Ancienneté' => 'modèle 2004', 'Spécialité' => 'le café tiède', 'Pannes' => 'uniquement le lundi']],
        ['cle' => 'tampon', 'nom' => 'Le tampon', 'role' => 'Doyen du cabinet',
            'bio' => "Plus ancien que le cabinet lui-même. Valide tout ce qu'on lui présente, sans lire. Signe distinctif : penche légèrement à gauche.",
            'fiche' => ['Ancienneté' => 'antérieure au cabinet', 'Spécialité' => 'la validation', 'Encre' => 'rouge, de préférence']],
    ],

    // Valeurs du cabinet (page « Le cabinet »)
    'valeurs' => [
        ['La moyenne', "Ni trop, ni trop peu. Juste assez pour qu'on ne puisse rien nous reprocher, ni nous féliciter."],
        ['L\'assurance', "Un conseil dit avec aplomb vaut deux conseils justes. Nous n'avons jamais vérifié cette règle, par assurance."],
        ['La procédure', "Tout problème mérite un numéro de dossier. Certains numéros méritent même un problème."],
        ['La sincérité', "Nos conseils ne servent à rien, et nous l'écrivons partout. Peu de cabinets peuvent en dire autant."],
    ],

    // FAQ des formules : [question, réponse]
    'faq' => [
        ['Vos conseils sont-ils fiables ?', 'Non.'],
        ['Qui rédige les conseils ?', "Personne. Trois morceaux de phrases sont tirés au hasard puis assemblés. C'est ce que nous appelons l'expertise."],
        ['Puis-je me faire rembourser ?', "Oui, intégralement, de la somme que vous avez payée, c'est-à-dire rien."],
        ['Puis-je parler à un conseiller ?', "Bien sûr. Il vous écoute. Il ne vous entend pas, mais il vous écoute."],
        ['Mes données sont-elles en sécurité ?', "Oui : nous en gardons le moins possible, et nous n'en faisons rien. Le détail est dans la page Confidentialité."],
        ['Pourquoi la formule Premium est-elle refusée ?', "Après examen de votre dossier, le cabinet estime toujours que vous méritez mieux. C'est une marque de respect."],
        ['Que se passe-t-il si je suis un conseil ?', "Rien de grave, en général. Mais vous serez seul responsable de votre trottinette."],
        ['Le cabinet est-il ouvert le week-end ?', "Le site oui. Le conseiller, moyennement."],
        ['Proposez-vous des conseils sur mesure ?', "Oui, dans l'espace client : la Consultation. Vos réponses sont mélangées avant lecture, pour garantir l'impartialité."],
        ['Puis-je devenir conseiller moyen ?', "Non : le poste est occupé. Mais le stagiaire cherche quelqu'un pour le remplacer pendant sa pause, depuis 2009."],
    ],

    // Titre de l'onglet quand on quitte la page des yeux
    'onglet_absent' => [
        'Revenez, le conseiller s\'ennuie', 'Votre dossier vous attend', 'Le tampon est prêt', 'On vous a vu partir',
        'Le stagiaire vous cherche', 'Votre conseil refroidit', 'Guichet en attente de vous',
    ],

    // Conseils « premium cachés » (code Konami) : le cabinet récompense la curiosité, moyennement
    'secrets' => [
        "Vous avez trouvé le conseil caché. Conseil : ne le dites à personne. Surtout pas au stagiaire.",
        "Conseil réservé aux initiés : la flèche du haut ne mène nulle part, mais elle y mène vite.",
        "Félicitations. Vous venez de gagner le droit de recommencer.",
        "Conseil premium : quand tout va mal, tamponnez quelque chose. Ça ne change rien, mais c'est officiel.",
    ],
];
