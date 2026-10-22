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

    // Délais de traitement « estimés » (accusé de réception du site, dossiers du Bureau, standard de Mireille) :
    // seule liste de délais du site. L'ordre compte : un même numéro de dossier donne toujours le même délai
    'delais' => [
        'entre 3 jours et jamais', 'sous 48 h, ouvrables, hors jours ouvrables', 'dès que possible, voire après',
        'avant la fin de l\'année (sans précision)', 'aussitôt que le stagiaire revient', 'quelques semaines, à la louche',
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
        ["Bertrand m'a fait remplir le formulaire de demande de formulaire. Sans une rature. J'en ai redemandé un.", 'Martine', 'formulaire n° 0057'],
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
            'bio' => "A fondé le cabinet un mardi, faute de mieux. Tient le diagnostic des clients. Aucun diplôme, cent pour cent d'assurance. Ne s'est jamais trompé, puisqu'il ne vérifie jamais.",
            'fiche' => ['Ancienneté' => 'depuis toujours', 'Spécialité' => 'le ton grave', 'Taux de réussite' => 'non mesuré, par principe'],
            'liens' => [['diagnostic.php', 'Passer son diagnostic']]],
        ['cle' => 'stagiaire', 'nom' => 'Le stagiaire', 'role' => 'Stagiaire (depuis 2009)',
            'bio' => "Lit les mots de travers avec un aplomb remarquable. Responsable des sketchs du Petit Moyen, de la photocopieuse et, par erreur, de la comptabilité.",
            'fiche' => ['Ancienneté' => '17 ans de stage', 'Spécialité' => 'le calembour justifié', 'Gratification' => 'un ticket de cantine'],
            'liens' => [['petit-moyen.php', 'Lire ses sketchs'], ['stagiaire.php', 'Le défier à « Questions pour un stagiaire »']]],
        ['cle' => 'delais', 'nom' => 'Mireille', 'role' => 'Standardiste, responsable des délais',
            'bio' => "Tient le standard et le calendrier du cabinet, où chaque rendez-vous tombe un mardi 31. Annonce chaque délai d'une voix parfaitement posée, sans jamais se tromper de fourchette. Auteure de la formule « entre 3 jours et jamais », qu'elle n'a jamais eu à corriger.",
            'fiche' => ['Appels perdus' => 'aucun (ils sont tous en attente)', 'Spécialité' => 'le fourchettage', 'Agenda' => 'complet jusqu\'en 2031'],
            'liens' => [['standard.php', 'Appeler son standard']]],
        ['cle' => 'accueil', 'nom' => 'Bertrand', 'role' => 'Agent d\'accueil, gardien du règlement intérieur',
            'bio' => "Connaît par cœur les 412 articles du règlement intérieur, y compris ceux qui se contredisent, et les applique tous à la fois. Remet le formulaire du jour avec une courtoisie irréprochable ; le tampon s'occupe du reste.",
            'fiche' => ['Ancienneté' => '31 ans sans une rature', 'Spécialité' => 'l\'article 12 bis (et son contraire)', 'Devise' => '« Ce n\'est pas moi, c\'est l\'alinéa. »'],
            'liens' => [['attestation.php', 'Remplir le formulaire du jour']]],
        ['cle' => 'cafe', 'nom' => 'La machine à café', 'role' => 'Membre le plus utile de l\'équipe',
            'bio' => "Seul membre du cabinet à avoir une utilité prouvée. Fait office de responsable des réclamations : c'est elle qui décroche au bout de la file d'attente du standard.",
            'fiche' => ['Ancienneté' => 'modèle 2004', 'Spécialité' => 'le café tiède', 'Pannes' => 'uniquement le lundi']],
        ['cle' => 'messmerde', 'nom' => 'Messmerde', 'role' => 'Mentaliste du cabinet',
            'bio' => "Lit dans les pensées avec une précision moyenne et une assurance totale. A prédit la création du cabinet le lendemain de sa création. Ne se trompe jamais, puisqu'il ne prédit que des évidences.",
            'fiche' => ['Ancienneté' => "il l'avait prévu", 'Spécialité' => "l'évidence", 'Signature' => 'le tampon « PRÉVU »'],
            'liens' => [['messmerde.php', 'Voir ses expériences'], ['messmerde.php#retroactives', 'Consulter ses prédictions rétroactives']]],
        ['cle' => 'tampon', 'nom' => 'Le tampon', 'role' => 'Doyen du cabinet',
            'bio' => "Plus ancien que le cabinet lui-même. Valide tout ce qu'on lui présente, sans lire. Délivre les attestations officielles du cabinet. Signe distinctif : penche légèrement à gauche.",
            'fiche' => ['Ancienneté' => 'antérieure au cabinet', 'Spécialité' => 'la validation', 'Encre' => 'rouge, de préférence'],
            'liens' => [['attestation.php', 'Faire tamponner un document']]],
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
        ['Que se passe-t-il si je suis un conseil ?', "Rien de grave, en général. Mais vous serez seul responsable de votre escabeau."],
        ['Le cabinet est-il ouvert le week-end ?', "Le site oui. Le conseiller, moyennement."],
        ['Proposez-vous des conseils sur mesure ?', "Oui, dans l'espace client : la Consultation. Vos réponses sont mélangées avant lecture, pour garantir l'impartialité."],
        ['Puis-je devenir conseiller moyen ?', "Non : le poste est occupé. Mais le stagiaire cherche quelqu'un pour le remplacer pendant sa pause, depuis 2009."],
    ],

    // Annonces du hall (reprises de l'ancien guichet) : diffusées pendant l'attente au standard
    'annonces' => [
        "Le système informatique redémarre. Veuillez ne pas bouger.",
        "Une personne demande « juste un renseignement ». La file est suspendue.",
        "Merci de patienter : la patience est un service inclus.",
        "Un ticket a été retrouvé sous la machine à café. Son propriétaire est prié de ne pas se manifester.",
        "Le tampon est en rupture d'encre. Les conseils seront validés oralement.",
        "Rappel : les formulaires ne sont ni repris, ni échangés, ni compris.",
        "La file d'attente est priée de s'aligner. Plus droit. Non, pas comme ça.",
        "Le stagiaire a appelé le numéro 0000. Personne ne s'est présenté. Il est déçu.",
        "Une imprimante fait un bruit nouveau. La direction suit la situation de près.",
        "Le chauffage est réglé sur « moyen ». C'est la règle de la maison.",
        "Les personnes arrivées avant vous sont priées de rester avant vous.",
        "Un formulaire B-12 a été rempli correctement. Une enquête est ouverte.",
        "Mireille annonce un léger retard sur le retard prévu. Il est conforme au calendrier.",
        "Bertrand rappelle que l'article 4 du règlement intérieur interdit de lire l'article 4.",
        "Une plante verte a été déplacée sans autorisation. Merci de la remettre où elle s'ennuyait.",
        "Un conseil a été délivré trop vite. Toutes nos excuses, cela ne se reproduira pas.",
        "Le numéro affiché est indicatif. Le vrai numéro est connu de la direction seule.",
    ],

    // Titre de l'onglet quand on quitte la page des yeux
    'onglet_absent' => [
        'Revenez, le conseiller s\'ennuie', 'Votre dossier vous attend', 'Le tampon est prêt', 'On vous a vu partir',
        'Le stagiaire vous cherche', 'Votre conseil refroidit', 'Mireille vous a mis en attente',
    ],

    // Conseils « premium cachés » (code Konami) : le cabinet récompense la curiosité, moyennement
    'secrets' => [
        "Vous avez trouvé le conseil caché. Conseil : ne le dites à personne. Surtout pas au stagiaire.",
        "Conseil réservé aux initiés : la flèche du haut ne mène nulle part, mais elle y mène vite.",
        "Félicitations. Vous venez de gagner le droit de recommencer.",
        "Conseil premium : quand tout va mal, tamponnez quelque chose. Ça ne change rien, mais c'est officiel.",
    ],
];
