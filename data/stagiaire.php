<?php
// « Questions pour un stagiaire » (stagiaire.php, js/stagiaire.js) : le stagiaire buzze toujours le premier et répond
// faux, avec aplomb et argument ; le visiteur, membre du jury, retient la bonne réponse ou celle du stagiaire.
// Parodie non officielle : questions, réponses et répliques inventées pour le site. Rien de politique.
return [
    // [question, bonne réponse, réponse du stagiaire, argument du stagiaire]
    'questions' => [
        ['Combien de pattes a une araignée ?', 'Huit', 'Six', "Deux d'entre elles sont des bras. Une araignée qui marcherait sur ses bras, cela se saurait."],
        ['De quelle couleur est le ciel par temps clair ?', 'Bleu', 'Transparent', "On voit à travers. Le bleu, c'est ce qu'il y a derrière."],
        ['Combien y a-t-il de jours dans une semaine ?', 'Sept', 'Cinq', "Le samedi et le dimanche ne sont pas dans la semaine. Ils sont dans le week-end : c'est écrit dessus."],
        ['Qui pond des œufs : la poule ou la vache ?', 'La poule', 'La vache', "Elle les pond en dehors des heures d'ouverture. C'est pour cela que personne ne l'a jamais vue faire."],
        ['De quelle couleur est la neige ?', 'Blanche', 'Grise', "Je parle de la neige d'il y a trois jours. C'est de loin la plus répandue."],
        ['Combien y a-t-il de minutes dans une heure ?', 'Soixante', 'Cent', "Une heure, c'est une centaine de minutes. Soixante, c'est l'heure du déjeuner, quand on se dépêche."],
        ["Quelle saison suit l'été ?", "L'automne", "L'été suivant", "Il se passe des choses entre les deux, mais c'est un détail de calendrier."],
        ['Avec quoi se brosse-t-on les dents ?', 'Une brosse à dents', 'Une brosse à cheveux', "Une brosse à dents serait une brosse faite de dents. Ce n'est pas hygiénique."],
        ['Quel est le contraire de « froid » ?', 'Chaud', 'Tiède', "Le contraire de froid, c'est « pas froid ». Le premier « pas froid » qu'on rencontre, c'est tiède. Je suis précis."],
        ['Combien de roues a un vélo ?', 'Deux', 'Trois', "En comptant le plateau. Il tourne, il est rond : je ne vois pas ce qu'on peut lui reprocher."],
        ['Où vit le poisson rouge ?', "Dans l'eau", 'Dans un bocal', "L'eau est dans le bocal. Le bocal, c'est l'adresse. Je donne l'adresse."],
        ['Combien font deux plus deux ?', 'Quatre', 'Vingt-deux', "« Plus » veut dire qu'on rajoute. J'ai rajouté un deux à côté du deux."],
        ['Quel est le fruit du pommier ?', 'La pomme', 'La poire', "Le pommier, c'est déjà l'arbre. Le fruit porte forcément un autre nom, sinon on confondrait."],
        ['Combien de côtés a un triangle ?', 'Trois', 'Deux', "L'intérieur et l'extérieur. Les trois autres sont des bords."],
        ['Que mange principalement la vache ?', "De l'herbe", 'Du fromage', "Elle en produit énormément. Elle doit bien en goûter un peu, par conscience professionnelle."],
        ['Quel instrument mesure la température ?', 'Le thermomètre', 'La main sur le front', "Cette méthode est employée depuis des siècles. On ne change pas une méthode qui donne un résultat."],
        ["Qu'est-ce qui est le plus long : un kilomètre ou un mètre ?", 'Un kilomètre', 'Un mètre', "Un mètre, on le voit en entier. Un kilomètre, on n'en voit jamais que le début."],
        ["Combien de lettres compte l'alphabet français ?", 'Vingt-six', 'Vingt-cinq', "J'ai compté deux fois et trouvé vingt-cinq les deux fois. C'est donc vérifié."],
        ['À quoi sert une échelle ?', 'À monter', 'À être rangée', "Une échelle passe l'essentiel de sa vie au garage. Je réponds statistiquement."],
        ['Comment appelle-t-on le petit de la vache ?', 'Le veau', 'Le petit-lait', "Il vient de la vache et il est petit. La filiation est établie."],
        ["À quelle température l'eau gèle-t-elle ?", 'Zéro degré', 'En hiver', "On me demande à quelle température : je donne la plus fiable."],
        ['Combien de doigts y a-t-il sur une main ?', 'Cinq', 'Quatre', "Le pouce n'est pas un doigt. C'est un pouce. Sinon, on l'appellerait le doigt."],
        ['Quelle est la capitale de la France ?', 'Paris', 'Le P', "C'est la seule majuscule de Paris. Je n'ai pas rédigé la question."],
        ['Quel est le contraire de « dedans » ?', 'Dehors', 'Dedans, vu de dehors', "C'est le même endroit, mais de l'autre côté. Je suis sorti vérifier."],
        ['Sur quelle planète vivons-nous ?', 'La Terre', 'Au rez-de-chaussée', "Pour ma part, en tout cas. Je n'ai pas d'information sur les étages."],
        ['Avec quoi ouvre-t-on une porte fermée à clé ?', 'Avec la clé', 'Avec la porte', "Sans porte, il n'y a rien à ouvrir. La porte est indispensable ; la clé n'est qu'un accessoire."],
        ['Combien de pieds a une chaise, en général ?', 'Quatre', 'Un', "Un pied général, et trois pieds particuliers."],
        ['Quelle est la forme d\'une roue ?', 'Ronde', 'Plate', "Vue de face, elle est ronde. Vue de côté, elle est plate. Et on la voit plus souvent de côté."],
        ["Qu'est-ce qui tombe quand il pleut ?", 'La pluie', 'Le parapluie', "Toujours au moment où on le referme. J'ai des témoins."],
        ['Combien d\'heures compte une journée ?', 'Vingt-quatre', 'Huit', "Une journée de travail. Le reste, c'est la soirée et la nuit, qui font l'objet d'autres questions."],
        ['De quelle couleur est une tomate mûre ?', 'Rouge', 'Verte', "Elle a commencé verte. On ne renie pas ses origines."],
        ["Que produisent les abeilles ?", 'Du miel', 'Un bourdonnement', "C'est la seule chose qu'on les entende produire. Le miel vient en plus."],
        ['Combien de mois compte une année ?', 'Douze', 'Onze', "Le mois d'août ne compte pas : le cabinet est fermé."],
        ['Quel organe sert à respirer ?', 'Les poumons', 'Le nez', "On n'a jamais vu ses poumons respirer. Son nez, si, tous les matins, devant le miroir."],
        ['Quelle est la moitié de dix ?', 'Cinq', '« Di »', "Dix coupé en deux donne « di » et « x ». Je garde la première moitié, c'est la plus prononçable."],
        ['De quel côté le soleil se lève-t-il ?', "À l'est", 'Le matin', "Le côté dépend de votre fenêtre. Le matin, c'est valable pour tout le monde."],
        ['Quel animal miaule ?', 'Le chat', 'Le chaton', "Le chat a passé l'âge de miauler. Il réclame."],
        ['Combien de faces a un dé à jouer ?', 'Six', 'Une', "On n'en voit jamais qu'une à la fois. Les autres relèvent de la rumeur."],
        ["Que colle-t-on sur une lettre pour l'envoyer ?", 'Un timbre', 'Une autre lettre', "Pour lui tenir compagnie pendant le trajet. Je procède ainsi et je n'ai jamais reçu de réclamation. Ni de réponse."],
        ['Quel est le féminin de « boulanger » ?', 'Boulangère', 'Baguette', "On dit « une » baguette. C'est donc le féminin du boulanger. J'applique la grammaire."],
        ['Où range-t-on les glaces ?', 'Au congélateur', 'Dans la salle de bains', "C'est là qu'est accrochée la plus grande. On se regarde dedans tous les matins."],
        ['Combien de secondes compte une minute ?', 'Soixante', 'Une, puis une autre', "Il faut les prendre une par une, sinon on s'y perd. Je ne suis jamais allé au bout."],
        ["Qu'est-ce qui pèse le plus : un kilo de plumes ou un kilo de briques ?", 'Le même poids', 'Le kilo de plumes', "Il en faut beaucoup plus. Et il faut toutes les porter."],
        ['Quel cri pousse le mouton ?', 'Il bêle', 'Il ronfle', "On les compte pour s'endormir. Passé le cinquantième, je n'en ai jamais entendu un seul bêler."],
        ['Combien de couleurs compte un arc-en-ciel ?', 'Sept', 'Une', "La couleur arc-en-ciel. Elle est un peu chargée, mais c'est une seule couleur."],
        ['Que met-on dans une valise ?', 'Des affaires', "L'autre valise", "Pour gagner de la place dans le placard. Il faut penser au retour."],
    ],

    // Le présentateur annonce le buzzer du stagiaire (toujours le premier)
    'buzz' => [
        'Le stagiaire a buzzé avant la fin de la question.',
        'Buzzer du stagiaire, au troisième mot.',
        'Le stagiaire a buzzé. Il avait la main posée sur le buzzer depuis le début de la partie.',
        'Le stagiaire a buzzé pendant la lecture du numéro de la question.',
        'Le stagiaire a buzzé. Il précise qu\'il connaît la réponse depuis 2009.',
        'Buzzer du stagiaire. Temps de réaction : négatif.',
    ],

    // Le jury retient la bonne réponse
    'bonne' => [
        'La bonne réponse est retenue. Le point revient au bon sens.',
        'Le jury retient la bonne réponse. Le stagiaire prend note et ne change rien.',
        'Bonne réponse retenue. Un point pour le bon sens, qui ne buzze jamais mais gagne parfois.',
        'Le jury tranche pour la bonne réponse. Le stagiaire a demandé que son désaccord figure au procès-verbal.',
        'Point accordé au bon sens. Le stagiaire salue la décision sans la comprendre.',
    ],

    // Le jury retient la réponse du stagiaire
    'stagiaire' => [
        "Le jury retient la réponse du stagiaire. Elle est consignée au procès-verbal, l'argument en annexe.",
        'Réponse du stagiaire retenue. Un point pour le stagiaire, qui le range dans son dossier de stage.',
        'Le jury valide la réponse du stagiaire. La question sera modifiée en conséquence.',
        "Le stagiaire marque le point. Le règlement de la partie ne s'y oppose pas, après relecture rapide.",
        'Réponse retenue. Le stagiaire remercie le jury et sa propre assurance.',
    ],

    // Fin de partie : [stagiaire en tête, bon sens en tête, égalité]
    'bilans' => [
        'stagiaire' => [
            'Le stagiaire remporte la partie. Il demande que le résultat soit pris en compte dans son évaluation de stage, qui n\'a pas eu lieu depuis 2009.',
            'Victoire du stagiaire. Il gagne le droit de rejouer, et une gratification d\'un ticket de cantine.',
        ],
        'bon_sens' => [
            'Le bon sens remporte la partie. Le stagiaire demande un recomptage, puis un second, puis un recomptage du second.',
            'Victoire du bon sens. Le stagiaire conteste le résultat par écrit, sur le formulaire prévu à cet effet.',
        ],
        'egalite' => [
            'Égalité parfaite. Le cabinet considère que c\'est le résultat le plus moyen possible, et le valide.',
            'Égalité. Le règlement prévoit une question subsidiaire ; elle a été égarée. Le résultat est donc définitif.',
        ],
    ],
];
