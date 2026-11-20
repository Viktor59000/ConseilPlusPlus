<?php
// « La Plume et le Tampon » — feuilleton de cape et d'épée joué par l'équipe du cabinet (saison 1, 12 épisodes).
// Hommage non officiel au FORMAT de « La Cape et l'Épée » (Les Robins des Bois) : seule la mécanique est reprise
// (un présentateur au sérieux de journal télévisé, une cour en costume, trois scènes courtes, des anachronismes,
// une fin en suspens). Intrigues, personnages, répliques et titres sont entièrement originaux. Aucune célébrité.
// On se moque de l'institution, jamais de l'usager : l'Usager est le seul personnage raisonnable du royaume.
//
// Lecture : includes/feuilleton.php (episode_de_la_semaine(), episode(), liste_episodes(), lignes_episode()).
//
// FORMAT
// 'personnages' : clé => [
//     'nom'    => nom de scène complet (générique, distribution),
//     'court'  => libellé court affiché devant chaque réplique (comme $noms dans includes/carte-mot.php),
//     'role'   => emploi dans la cour,
//     'equipe' => clé du membre de l'équipe joué (data/textes.php, liste « equipe ») ; null pour un invité,
//     'voix'   => réglage de synthèse vocale au format des REGLAGES de js/sketch.js ['voix', 'pitch', 'rate'],
//                 null pour un personnage muet (le Sceau : sa réplique s'affiche, elle ne se lit pas).
// ]
// 'episodes' : liste de [
//     'numero'       => 1, 2, 3… (consécutifs),
//     'titre'        => titre de l'épisode,
//     'precedemment' => « Précédemment au cabinet » (null pour le premier épisode),
//     'heraut'       => introduction du Héraut, ton de journal télévisé,
//     'scenes'       => exactement trois scènes (présentation → complication qui tourne au désastre → conclusion
//                       absurde) : ['titre' => …, 'didascalie' => décor en une ou deux phrases, 'repliques' => […]],
//     'suspense'     => la fin en suspens, dite par le Héraut, qui finit toujours par « La suite, la semaine prochaine. »
// ]
// Une réplique : ['qui' => clé de personnage, 'texte' => …, 'jeu' => indication de jeu facultative] ;
// 'qui' => 'scene' pour une didascalie en cours de scène (même convention que includes/script-sketch.php).
// La dernière réplique de la troisième scène est la chute (rôle « chute » dans lignes_episode()).
// Le Sceau est muet : ses seules « répliques » sont « Clac. » ou « … ».
// Longueur : un épisode se lit en 3 à 5 minutes (vérifié par tests/test_feuilleton.php).
return [
    'titre' => 'La Plume et le Tampon',
    'saison' => 1,

    'personnages' => [
        'heraut' => ['nom' => 'Le Héraut du cabinet', 'court' => 'Le Héraut',
            'role' => "Présentateur : annonce chaque épisode avec le sérieux d'un journal du soir",
            'equipe' => null, 'voix' => ['voix' => 0, 'pitch' => 0.9, 'rate' => 0.95]],
        'conseiller' => ['nom' => 'Sa Moyenneté Conseiller Ier', 'court' => 'Le Conseiller',
            'role' => 'Monarque candide du cabinet, ravi de tout',
            'equipe' => 'conseiller', 'voix' => ['voix' => 0, 'pitch' => 0.75, 'rate' => 0.9]],
        'mireille' => ['nom' => 'Dame Mireille, Grande Chambellane des Délais', 'court' => 'Mireille',
            'role' => 'Hautaine, glaciale, maîtresse absolue du calendrier',
            'equipe' => 'delais', 'voix' => ['voix' => 1, 'pitch' => 1.1, 'rate' => 0.85]],
        'messmerde' => ['nom' => 'Messmerde, devin de la cour', 'court' => 'Messmerde',
            'role' => "Conseiller intrigant : prédit tout, toujours après coup",
            'equipe' => 'messmerde', 'voix' => ['voix' => 2, 'pitch' => 0.95, 'rate' => 0.8]],
        'stagiaire' => ['nom' => 'Le Valet-stagiaire', 'court' => 'Le stagiaire',
            'role' => 'Valet terrorisé, envoyé partout en premier',
            'equipe' => 'stagiaire', 'voix' => ['voix' => 1, 'pitch' => 1.35, 'rate' => 1.08]],
        'bertrand' => ['nom' => 'Messire Bertrand, garde du Règlement', 'court' => 'Bertrand',
            'role' => 'Garde du règlement intérieur, un article pour chaque situation',
            'equipe' => 'accueil', 'voix' => ['voix' => 0, 'pitch' => 0.6, 'rate' => 0.8]],
        'tampon' => ['nom' => 'Le Sceau du cabinet', 'court' => 'Le Sceau',
            'role' => 'Sceau royal, muet et solennel ; penche légèrement à gauche',
            'equipe' => 'tampon', 'voix' => null],
        'cafe' => ['nom' => "L'Oracle de la salle de pause", 'court' => "L'Oracle",
            'role' => 'Machine à café, rend ses oracles entre deux bips',
            'equipe' => 'cafe', 'voix' => ['voix' => 2, 'pitch' => 0.5, 'rate' => 1.2]],
        'cour' => ['nom' => 'Toute la cour', 'court' => 'La cour',
            'role' => 'Chœur', 'equipe' => null, 'voix' => ['voix' => 0, 'pitch' => 1, 'rate' => 0.9]],
        // Invités (visiteurs extravagants, personnages inventés)
        'usager' => ['nom' => "L'Usager", 'court' => "L'Usager",
            'role' => 'Visiteur poli, seul homme raisonnable du royaume',
            'equipe' => null, 'voix' => ['voix' => 1, 'pitch' => 1, 'rate' => 1]],
        'inspecteur' => ['nom' => "L'Inspecteur Mesurat, de l'Inspection générale des Moyennes", 'court' => "L'Inspecteur",
            'role' => 'Visiteur : vérifie que le cabinet n\'est ni trop bon, ni trop mauvais',
            'equipe' => null, 'voix' => ['voix' => 2, 'pitch' => 0.85, 'rate' => 1.15]],
        'presidente' => ['nom' => 'Dame Agrafe, présidente du jury', 'court' => 'Dame Agrafe',
            'role' => 'Visiteuse : préside le Grand Concours du Meilleur Dossier',
            'equipe' => null, 'voix' => ['voix' => 1, 'pitch' => 1.2, 'rate' => 0.95]],
        'automatique' => ['nom' => 'Le Sceau automatique', 'court' => 'Le Sceau automatique',
            'role' => 'Tampon à encrage automatique, gagné au concours ; parle en majuscules',
            'equipe' => null, 'voix' => ['voix' => 2, 'pitch' => 0.6, 'rate' => 1.4]],
        'porte_plume' => ['nom' => 'Messire Porte-Plume', 'court' => 'Le Porte-Plume',
            'role' => "Visiteur : représentant de la Plume pour toutes les questions d'encre",
            'equipe' => null, 'voix' => ['voix' => 0, 'pitch' => 1.05, 'rate' => 0.9]],
    ],

    'episodes' => [
        // ───────────────────────────────────────────── 1
        [
            'numero' => 1,
            'titre' => 'Le Formulaire B-0',
            'precedemment' => null,
            'heraut' => "Mesdames, messieurs, bonsoir. Il est vingt heures au cabinet, ou à peu près : l'horloge est réglée sur « moyen ». Ce soir, premier épisode de notre feuilleton. Un usager s'est présenté au Château des Moyennes avec une demande d'une rare audace. Il souhaitait obtenir un formulaire. Voici les faits, tels qu'ils ont été consignés.",
            'scenes' => [
                [
                    'titre' => 'La salle du trône',
                    'didascalie' => "Le Conseiller Ier, couronne en carton, siège sur une chaise de bureau à roulettes. Mireille tient un sablier, Bertrand garde la porte, une agrafeuse à la ceinture. Sur un coussin de velours, le Sceau. Entre l'Usager, chapeau à la main.",
                    'repliques' => [
                        ['qui' => 'bertrand', 'texte' => "Halte. Qui va là, et muni de quel justificatif ?"],
                        ['qui' => 'usager', 'texte' => "Bonjour. Je viens chercher le formulaire de remboursement, s'il vous plaît."],
                        ['qui' => 'conseiller', 'texte' => "Un visiteur ! Mireille, avons-nous des visiteurs, d'habitude ?", 'jeu' => 'ravi'],
                        ['qui' => 'mireille', 'texte' => "Un tous les sept ans, Votre Moyenneté. Celui-ci est en avance. Je le note en retard.", 'jeu' => 'sans lever les yeux'],
                        ['qui' => 'bertrand', 'texte' => "Pour obtenir ce formulaire, monsieur, il faut d'abord remplir le formulaire B-0, dit « demande de formulaire »."],
                        ['qui' => 'usager', 'texte' => "Très bien. Où puis-je trouver le B-0 ?"],
                        ['qui' => 'bertrand', 'texte' => "Il se demande avec un B-0."],
                        ['qui' => 'usager', 'texte' => "Et le tout premier B-0, on le demande avec quoi ?", 'jeu' => 'après un temps'],
                        ['qui' => 'conseiller', 'texte' => "Excellente question. Personne ne l'avait jamais posée. Bertrand, qu'on la grave quelque part."],
                    ],
                ],
                [
                    'titre' => 'La descente aux archives',
                    'didascalie' => "Le Valet-stagiaire, appelé d'un coup de sonnette de comptoir, accourt derrière une pile de classeurs qui lui cache le visage.",
                    'repliques' => [
                        ['qui' => 'conseiller', 'texte' => "Stagiaire ! Le royaume a besoin d'un B-0."],
                        ['qui' => 'stagiaire', 'texte' => "Un B-0, Votre Moyenneté ? Le dernier a été vu en 1987, au sous-sol, à côté du minitel. Personne n'est jamais remonté pour le dire.", 'jeu' => 'tremblant'],
                        ['qui' => 'messmerde', 'texte' => "Je vois… je vois que le stagiaire va descendre.", 'jeu' => 'les doigts sur les tempes'],
                        ['qui' => 'stagiaire', 'texte' => "Vous le voyez parce qu'on vient de le dire."],
                        ['qui' => 'messmerde', 'texte' => "Je l'ai vu une seconde avant. C'est mon don."],
                        ['qui' => 'scene', 'texte' => "Le stagiaire revient, gris de poussière, brandissant une feuille jaunie."],
                        ['qui' => 'stagiaire', 'texte' => "Je l'ai ! Le dernier B-0 du royaume ! Il servait de cale sous le minitel."],
                        ['qui' => 'conseiller', 'texte' => "Merveilleux. Qu'on le tamponne immédiatement, pour fêter ça."],
                        ['qui' => 'usager', 'texte' => "Attendez, il faudrait d'abord le rempl…"],
                        ['qui' => 'tampon', 'texte' => "Clac.", 'jeu' => "s'abat sur la feuille, avec une solennité définitive"],
                        ['qui' => 'mireille', 'texte' => "Il est validé. Vierge, mais validé. On ne peut plus rien y écrire : ce serait falsifier un document officiel."],
                    ],
                ],
                [
                    'titre' => 'Le décret',
                    'didascalie' => "Un peu plus tard. Le formulaire vierge et tamponné est exposé sous une cloche à fromage. L'Usager le contemple, son manteau sur le bras.",
                    'repliques' => [
                        ['qui' => 'usager', 'texte' => "Donc, je ne peux pas demander mon formulaire."],
                        ['qui' => 'bertrand', 'texte' => "Vous pouvez, monsieur. Simplement, votre demande a déjà été acceptée sans avoir été faite."],
                        ['qui' => 'conseiller', 'texte' => "Je décrète que, désormais, tous les formulaires du royaume seront livrés vierges et déjà tamponnés. Tout le monde y gagnera un temps considérable.", 'jeu' => 'se levant'],
                        ['qui' => 'mireille', 'texte' => "Je l'inscris au calendrier. Entrée en vigueur : un mardi 31."],
                        ['qui' => 'stagiaire', 'texte' => "Et le remboursement de monsieur ?"],
                        ['qui' => 'conseiller', 'texte' => "Monsieur sera remboursé de la peine qu'il s'est donnée. C'est-à-dire de rien, puisqu'il n'a rien rempli.", 'jeu' => 'grave'],
                        ['qui' => 'usager', 'texte' => "C'est d'une logique… imparable. Je repasserai."],
                        ['qui' => 'bertrand', 'texte' => "Article 9 : on ne repasse pas, monsieur. On revient."],
                        ['qui' => 'conseiller', 'texte' => "Et maintenant, signons ce décret historique. Stagiaire, apportez la Plume royale !"],
                    ],
                ],
            ],
            'suspense' => "Mais dans l'encrier d'argent où elle dormait depuis la fondation du cabinet, la Plume royale n'était plus là. Il ne restait qu'une petite tache, en forme de point d'interrogation. Une tache bleue, dans un cabinet qui n'écrit qu'en rouge. Qui a pris la Plume ? La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 2
        [
            'numero' => 2,
            'titre' => 'Qui a pris la Plume ?',
            'precedemment' => "Un usager a demandé un formulaire. Le dernier B-0 du royaume, retrouvé sous un minitel, a été tamponné avant d'être rempli, et le Conseiller a décrété que tous les formulaires seraient désormais livrés vierges. Mais au moment de signer le décret, la Plume royale avait disparu de son encrier.",
            'heraut' => "Bonsoir. La Plume royale reste introuvable. Sans elle, aucun document ne peut être signé au cabinet, ce qui, de l'avis des spécialistes, ne change pas grand-chose, mais c'est le principe. L'enquête est ouverte.",
            'scenes' => [
                [
                    'titre' => "L'enquête",
                    'didascalie' => "Salle du trône. L'encrier vide repose sur un coussin. Bertrand a tendu un ruban de chantier entre deux chaises. Messmerde tourne autour de l'encrier en reniflant l'air.",
                    'repliques' => [
                        ['qui' => 'conseiller', 'texte' => "Qui a vu la Plume pour la dernière fois ?"],
                        ['qui' => 'mireille', 'texte' => "Moi, Votre Moyenneté. Lundi, 9 h 14. Elle signait un retard. Elle avait l'air fatiguée."],
                        ['qui' => 'messmerde', 'texte' => "Je l'avais prédit. Lisez : « Lundi, quelque chose va disparaître. »", 'jeu' => 'tendant un papier'],
                        ['qui' => 'bertrand', 'texte' => "Ce papier est daté de ce matin."],
                        ['qui' => 'messmerde', 'texte' => "La prédiction, oui. Le pressentiment, lui, est bien plus ancien."],
                        ['qui' => 'conseiller', 'texte' => "Messmerde, lisez dans les pensées du coupable."],
                        ['qui' => 'messmerde', 'texte' => "Le coupable pense… à quelque chose. Il pense fort. Il a mangé, ou il va manger.", 'jeu' => 'fermant les yeux'],
                        ['qui' => 'stagiaire', 'texte' => "J'ai mangé un croissant ! Je le jure, je l'ai mangé seul !", 'jeu' => 'livide'],
                    ],
                ],
                [
                    'titre' => 'La fouille',
                    'didascalie' => "Le stagiaire, les bras en l'air depuis quatre minutes, se tient au centre de la pièce. Bertrand le regarde fixement.",
                    'repliques' => [
                        ['qui' => 'bertrand', 'texte' => "Article 31 : la fouille est interdite le mardi. Nous sommes mardi. Je vous fouille donc sans vous toucher."],
                        ['qui' => 'stagiaire', 'texte' => "Et comment on fouille sans toucher ?"],
                        ['qui' => 'bertrand', 'texte' => "En regardant très fort. Videz vos poches de vous-même. Je ne regarde pas."],
                        ['qui' => 'scene', 'texte' => "Le stagiaire vide ses poches : quatorze stylos, un surligneur, un crayon de mini-golf."],
                        ['qui' => 'mireille', 'texte' => "Quatorze stylos. Pour un homme qui n'a jamais rien signé."],
                        ['qui' => 'stagiaire', 'texte' => "Ils me suivent ! Je les pose quelque part, et le soir ils sont dans ma poche. Personne ne m'a jamais expliqué."],
                        ['qui' => 'conseiller', 'texte' => "Mais alors tout est réglé. Signons avec l'un des quatorze.", 'jeu' => 'émerveillé'],
                        ['qui' => 'bertrand', 'texte' => "Impossible, Votre Moyenneté. Signé au stylo-bille, un décret a la valeur d'un ticket de caisse."],
                        ['qui' => 'messmerde', 'texte' => "Il ne reste qu'une solution. Consultons l'Oracle."],
                    ],
                ],
                [
                    'titre' => "L'Oracle",
                    'didascalie' => "La salle de pause, sous un néon qui tremble. Toute la cour est agenouillée devant la machine à café. Le stagiaire glisse une pièce de vingt centimes.",
                    'repliques' => [
                        ['qui' => 'cafe', 'texte' => "Bip. Bip. Sélectionnez votre boisson."],
                        ['qui' => 'conseiller', 'texte' => "Ô Oracle, où est la Plume ?"],
                        ['qui' => 'cafe', 'texte' => "Sucre : moyen. Celle que vous cherchez ne veut plus qu'on la cherche."],
                        ['qui' => 'mireille', 'texte' => "Elle a toujours parlé pour ne rien dire. Ici, c'est une qualification."],
                        ['qui' => 'cafe', 'texte' => "Gobelet non détecté. Elle reviendra quand on lira avant de signer."],
                        ['qui' => 'conseiller', 'texte' => "Lire avant de signer… Mais c'est de la science-fiction.", 'jeu' => 'après un long silence'],
                        ['qui' => 'bertrand', 'texte' => "Article 1 : personne n'a jamais lu l'article 1."],
                        ['qui' => 'conseiller', 'texte' => "Très bien. Puisque la Plume nous boude, le Sceau validera seul. Sceau, au travail !", 'jeu' => 'tendant le décret'],
                        ['qui' => 'tampon', 'texte' => "…", 'jeu' => 'reste suspendu au-dessus de la feuille, sans descendre'],
                    ],
                ],
            ],
            'suspense' => "Pour la première fois en quarante ans de carrière, le Sceau du cabinet est resté en l'air. Simple fatigue ? Crampe du manche ? Ou mouvement d'humeur ? La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 3
        [
            'numero' => 3,
            'titre' => 'Le Sceau ne tamponne plus',
            'precedemment' => "La Plume royale a disparu. La fouille du stagiaire n'a révélé que quatorze stylos sans valeur légale, et l'Oracle a rendu un oracle mystérieux : elle reviendra quand on lira avant de signer. Prié de valider seul le décret, le Sceau est resté suspendu dans les airs.",
            'heraut' => "Bonsoir. Le Sceau du cabinet ne tamponne plus. Depuis mardi, quarante-trois documents attendent sur son coussin, dont trois urgents et un très urgent, qui ne l'est plus du tout. Ce soir, la cour tente la négociation.",
            'scenes' => [
                [
                    'titre' => 'La négociation',
                    'didascalie' => "Le Sceau, sur son coussin, entouré de toute la cour. Mireille lui présente un calendrier. Bertrand porte le règlement intérieur, épais comme un annuaire.",
                    'repliques' => [
                        ['qui' => 'mireille', 'texte' => "Sceau, je vous accorde un délai pour reprendre le travail. Entre trois jours et jamais. Je vous conseille trois jours."],
                        ['qui' => 'tampon', 'texte' => "…"],
                        ['qui' => 'bertrand', 'texte' => "Article 58 : le Sceau a le droit de se taire. Article 59 : tout ce qu'il ne dira pas pourra être retenu contre lui."],
                        ['qui' => 'conseiller', 'texte' => "Sceau, mon vieux Sceau. Vous qui avez validé ma naissance, mon brevet blanc et le menu de la cantine de 2004…", 'jeu' => 'tendre'],
                        ['qui' => 'messmerde', 'texte' => "Il boude. Je le sens. Je sens aussi qu'il fait chaud. Les deux sont liés."],
                        ['qui' => 'conseiller', 'texte' => "Stagiaire, que dit le mode d'emploi ?"],
                        ['qui' => 'stagiaire', 'texte' => "Il n'y en a pas, Votre Moyenneté. Le Sceau est plus vieux que les modes d'emploi."],
                    ],
                ],
                [
                    'titre' => 'La méthode du stagiaire',
                    'didascalie' => "Faute de mieux, le stagiaire tente d'actionner le Sceau à la main. Il l'empoigne, ferme les yeux et appuie de toutes ses forces.",
                    'repliques' => [
                        ['qui' => 'stagiaire', 'texte' => "Pardon, Sceau. Pardon. C'est pour le royaume."],
                        ['qui' => 'tampon', 'texte' => "…"],
                        ['qui' => 'mireille', 'texte' => "Plus fort. Avec les épaules. Les bras, c'est pour les amateurs."],
                        ['qui' => 'scene', 'texte' => "Le Sceau glisse, pivote, et retombe d'un coup sur le front du stagiaire."],
                        ['qui' => 'tampon', 'texte' => "Clac."],
                        ['qui' => 'stagiaire', 'texte' => "Aïe ? Non, ça va. C'est même agréable. Qu'est-ce que ça dit ?"],
                        ['qui' => 'bertrand', 'texte' => "« VU ». Bien centré. Encre rouge, réglementaire.", 'jeu' => 'lisant le front du stagiaire'],
                        ['qui' => 'conseiller', 'texte' => "Il a tamponné ! Le Sceau est guéri !"],
                        ['qui' => 'messmerde', 'texte' => "Il n'a tamponné que le stagiaire. Il fait passer un message."],
                    ],
                ],
                [
                    'titre' => "L'archivage",
                    'didascalie' => "Bertrand mesure le stagiaire avec un double décimètre. Mireille remplit une fiche de classement.",
                    'repliques' => [
                        ['qui' => 'bertrand', 'texte' => "Votre Moyenneté, je suis au regret. Tout ce qui porte « VU » est un document officiel. Le stagiaire est donc un document officiel."],
                        ['qui' => 'stagiaire', 'texte' => "Mais je suis une personne !"],
                        ['qui' => 'bertrand', 'texte' => "Vous êtes une personne vue. Ce n'est plus pareil. Article 112 : tout document officiel doit être classé."],
                        ['qui' => 'mireille', 'texte' => "Format : un mètre soixante-douze. Ça rentre dans le tiroir C, si on enlève les trombones."],
                        ['qui' => 'conseiller', 'texte' => "Mon petit stagiaire. Le premier d'entre nous à être officiel. Je suis fier de toi.", 'jeu' => 'sincèrement ému'],
                        ['qui' => 'stagiaire', 'texte' => "Est-ce que je peux avoir une lampe de poche ?", 'jeu' => 'depuis le tiroir'],
                        ['qui' => 'bertrand', 'texte' => "Les documents ne lisent pas, monsieur. Ce sont eux qu'on lit."],
                        ['qui' => 'conseiller', 'texte' => "Fermez le tiroir doucement. Et qu'on lui glisse un croissant par la fente, chaque jour à dix heures. C'est le minimum, pour un document."],
                    ],
                ],
            ],
            'suspense' => "À peine le tiroir C refermé, un pigeon voyageur se posa sur le bureau de Mireille, porteur d'un courriel imprimé. L'Inspection générale des Moyennes annonçait sa visite. Demain matin. Le stagiaire est archivé, le Sceau est en grève, la Plume est introuvable. La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 4
        [
            'numero' => 4,
            'titre' => "L'Inspection générale des Moyennes",
            'precedemment' => "Le Sceau, en grève, n'a tamponné qu'une seule chose : le front du stagiaire, qui porte désormais la mention « VU » et a été classé, en tant que document officiel, dans le tiroir C. C'est alors qu'une inspection a été annoncée.",
            'heraut' => "Bonsoir. Tous les dix ans, l'Inspection générale des Moyennes vérifie que les cabinets du royaume ne sont ni trop bons, ni trop mauvais. Un cabinet trop bon perd son agrément. Un cabinet trop mauvais aussi. La marge est étroite. Ce matin, l'inspecteur est arrivé.",
            'scenes' => [
                [
                    'titre' => "L'arrivée de l'inspecteur",
                    'didascalie' => "Entre l'Inspecteur, pourpoint gris et fraise amidonnée, une règle graduée à la ceinture, un niveau à bulle en bandoulière. La cour s'aligne. Mireille a caché le Sceau sous une serviette.",
                    'repliques' => [
                        ['qui' => 'inspecteur', 'texte' => "Inspecteur Mesurat. Je ne serrerai aucune main : une poignée de main, c'est déjà un avis."],
                        ['qui' => 'conseiller', 'texte' => "Bienvenue ! Un café ? L'Oracle est d'humeur.", 'jeu' => 'radieux'],
                        ['qui' => 'inspecteur', 'texte' => "Je ne bois que du café tiède. Le chaud et le froid sont des extrêmes."],
                        ['qui' => 'cafe', 'texte' => "Bip. Tiède. Comme toujours."],
                        ['qui' => 'inspecteur', 'texte' => "Machine conforme. Premier bon point. Enfin, premier point moyen.", 'jeu' => 'notant'],
                        ['qui' => 'mireille', 'texte' => "Monsieur l'inspecteur souhaite-t-il visiter nos délais ? Ils sont tous en retard, mais dans les temps."],
                        ['qui' => 'inspecteur', 'texte' => "Plus tard. Montrez-moi d'abord votre Sceau en action."],
                        ['qui' => 'scene', 'texte' => "Tous les regards se tournent vers la serviette."],
                    ],
                ],
                [
                    'titre' => 'La démonstration',
                    'didascalie' => "La serviette ne bouge pas. Dessous, le Sceau ne bouge pas non plus. Messmerde s'avance, les mains sur les tempes.",
                    'repliques' => [
                        ['qui' => 'messmerde', 'texte' => "Monsieur l'inspecteur, je lis dans vos pensées. Vous pensez : « Ce Sceau est formidable, inutile de le voir. »"],
                        ['qui' => 'inspecteur', 'texte' => "Je pensais : « Pourquoi ce Sceau est-il sous une serviette ? »"],
                        ['qui' => 'messmerde', 'texte' => "C'est ce que j'allais dire en deuxième."],
                        ['qui' => 'bertrand', 'texte' => "Le Sceau est en période de recueillement, monsieur. Article 74 : tout objet de plus de quarante ans a droit au silence."],
                        ['qui' => 'inspecteur', 'texte' => "Alors montrez-moi un document tamponné. Un seul. Récent."],
                        ['qui' => 'conseiller', 'texte' => "Ah, ça, nous l'avons ! Bertrand, ouvrez le tiroir C.", 'jeu' => 'illuminé'],
                        ['qui' => 'scene', 'texte' => "Le stagiaire sort du tiroir, raide, un croissant à la main, le front marqué « VU »."],
                        ['qui' => 'stagiaire', 'texte' => "Document numéro 1, monsieur. Format portrait.", 'jeu' => 'au garde-à-vous'],
                        ['qui' => 'inspecteur', 'texte' => "Encre rouge. Centrage correct. Légère inclinaison à gauche…", 'jeu' => 'sortant une loupe'],
                        ['qui' => 'mireille', 'texte' => "C'est sa signature. Notre Sceau penche toujours un peu."],
                    ],
                ],
                [
                    'titre' => 'Le verdict',
                    'didascalie' => "L'inspecteur fait trois fois le tour du stagiaire, pose son niveau à bulle sur sa tête et attend que la bulle se calme.",
                    'repliques' => [
                        ['qui' => 'inspecteur', 'texte' => "Je n'avais jamais vu un document qui tient debout tout seul. Ce n'est ni bien, ni mal. C'est parfaitement moyen."],
                        ['qui' => 'conseiller', 'texte' => "Et la note, monsieur l'inspecteur ?"],
                        ['qui' => 'inspecteur', 'texte' => "Dix sur vingt. Pile. Le cabinet garde son agrément pour dix ans."],
                        ['qui' => 'scene', 'texte' => "Explosion de joie mesurée. Bertrand applaudit deux fois, conformément à l'article 40."],
                        ['qui' => 'stagiaire', 'texte' => "Est-ce que ça veut dire que je peux sortir du tiroir ?"],
                        ['qui' => 'inspecteur', 'texte' => "Surtout pas. Vous êtes la pièce maîtresse du dossier. Si l'on vous désarchive, la note tombe à neuf."],
                        ['qui' => 'conseiller', 'texte' => "Tu entends, petit ? La pièce maîtresse. Retourne vite dans ton tiroir, tu es bien trop précieux pour la lumière.", 'jeu' => 'solennel'],
                        ['qui' => 'stagiaire', 'texte' => "D'accord. Quelqu'un peut prévenir ma mère ?"],
                        ['qui' => 'inspecteur', 'texte' => "Je lui écrirai moi-même : « Madame, votre fils est agréé. »"],
                    ],
                ],
            ],
            'suspense' => "Sur le seuil, l'inspecteur laissa tomber une enveloppe épaisse, scellée de cire grise. Bertrand la ramassa et pâlit en lisant : « Réforme du règlement intérieur. Application immédiate. » Trente et un ans sans une rature. Pour combien de temps encore ? La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 5
        [
            'numero' => 5,
            'titre' => "La Réforme de l'article 412",
            'precedemment' => "Grâce au stagiaire, archivé et marqué « VU », le cabinet a obtenu dix sur vingt à l'inspection : la note parfaite. Mais en partant, l'inspecteur a laissé une enveloppe : la réforme du règlement intérieur, d'application immédiate.",
            'heraut' => "Bonsoir. Le règlement intérieur du cabinet compte quatre cent douze articles. Certains se contredisent, ce qui permet de les respecter tous en même temps. Ce soir, la réforme impose d'en supprimer un. Un seul. Au choix du cabinet.",
            'scenes' => [
                [
                    'titre' => "L'enveloppe",
                    'didascalie' => "Bertrand, assis sur les marches du trône, serre le règlement contre lui. Mireille ouvre l'enveloppe avec un coupe-papier en forme d'épée.",
                    'repliques' => [
                        ['qui' => 'mireille', 'texte' => "« Chaque cabinet supprimera un article de son règlement avant la fin de la semaine. Le choix est libre. » Libre. Quel mot désagréable.", 'jeu' => 'lisant'],
                        ['qui' => 'bertrand', 'texte' => "Je ne peux pas. Chaque article est un de mes enfants. Même le 12 bis, qui interdit le 12."],
                        ['qui' => 'conseiller', 'texte' => "Allons, Bertrand. Il y en a quatre cent douze. Supprimez le plus petit."],
                        ['qui' => 'bertrand', 'texte' => "Le plus petit, c'est l'article 4. Il interdit de lire l'article 4. Personne ne sait ce qu'il dit."],
                        ['qui' => 'messmerde', 'texte' => "Moi, je le sais. Je le lis dans vos pensées. Il dit… des choses.", 'jeu' => 'un temps'],
                        ['qui' => 'mireille', 'texte' => "Passons au vote. Le vote, au moins, ne demande pas de lire."],
                    ],
                ],
                [
                    'titre' => 'Le vote',
                    'didascalie' => "La cour vote à main levée. Le Sceau, toujours en grève, s'abstient. L'Oracle a été branché dans la salle du trône ; un bip compte pour une voix.",
                    'repliques' => [
                        ['qui' => 'conseiller', 'texte' => "Qui est pour supprimer l'article 4 ?"],
                        ['qui' => 'bertrand', 'texte' => "Objection. Pour supprimer l'article 4, il faut l'avoir lu. Or il est interdit de le lire."],
                        ['qui' => 'mireille', 'texte' => "Supprimons alors l'article qui interdit de le lire."],
                        ['qui' => 'bertrand', 'texte' => "C'est l'article 4 lui-même."],
                        ['qui' => 'conseiller', 'texte' => "Supprimons le dernier, dans ce cas. Le 412. Personne ne va jamais jusqu'au bout."],
                        ['qui' => 'bertrand', 'texte' => "L'article 412 dit : « Les articles précédents sont valables. »", 'jeu' => "d'une voix blanche"],
                        ['qui' => 'scene', 'texte' => "Le Conseiller, qui avait déjà levé le bras, le baisse très lentement."],
                        ['qui' => 'cafe', 'texte' => "Bip. Vote enregistré. Article 412 supprimé. Merci de votre visite."],
                        ['qui' => 'mireille', 'texte' => "L'Oracle a voté le premier. Et il a gagné."],
                    ],
                ],
                [
                    'titre' => 'Le royaume sans règles',
                    'didascalie' => "Le règlement n'est plus qu'une liasse de quatre cent onze articles sans valeur. Bertrand erre en tournant les pages. Le stagiaire, oublié dans son tiroir, écoute par la fente.",
                    'repliques' => [
                        ['qui' => 'bertrand', 'texte' => "Plus rien n'est valable, Votre Moyenneté. Ni les horaires, ni la file d'attente, ni l'article qui fonde le cabinet."],
                        ['qui' => 'conseiller', 'texte' => "Alors… je ne suis plus Conseiller ?"],
                        ['qui' => 'bertrand', 'texte' => "Juridiquement, vous êtes un homme assis sur une chaise à roulettes."],
                        ['qui' => 'conseiller', 'texte' => "Eh bien, je me nomme intérimaire de moi-même. En attendant mon retour.", 'jeu' => 'après réflexion, très digne'],
                        ['qui' => 'messmerde', 'texte' => "Je l'avais prévu."],
                        ['qui' => 'mireille', 'texte' => "Vous aviez prévu quoi ?"],
                        ['qui' => 'messmerde', 'texte' => "Que quelqu'un dirait « je l'avais prévu ». Et c'est arrivé."],
                        ['qui' => 'stagiaire', 'texte' => "Si plus rien n'est valable, le « VU » sur mon front, il compte encore ?", 'jeu' => 'étouffé, depuis le tiroir'],
                        ['qui' => 'bertrand', 'texte' => "Bien sûr. Le « VU » est antérieur au règlement. Comme le Sceau. Comme la poussière."],
                    ],
                ],
            ],
            'suspense' => "C'est alors que, dans un coin de la salle, l'Oracle émit un son que personne n'avait jamais entendu : un bip long, grave, presque humain. Sur son petit écran défilaient trois mots en lettres vertes : « LE DÉLAI S'ENFUIT. » Quel délai ? Et pour aller où ? La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 6
        [
            'numero' => 6,
            'titre' => 'Le Délai en cavale',
            'precedemment' => "La réforme imposait de supprimer un article du règlement ; l'Oracle a voté seul la suppression du dernier, celui qui rendait valables tous les autres, et le Conseiller s'est nommé intérimaire de lui-même. Puis l'Oracle a lancé l'alerte : « Le délai s'enfuit. »",
            'heraut' => "Bonsoir. Le règlement a été rétabli dans la nuit : Bertrand a réécrit l'article 412 de mémoire, au crayon, ce qui en fait le seul article effaçable du royaume. Le Conseiller a repris ses fonctions. Mais ce matin, une case du calendrier était vide. Le délai « entre trois jours et jamais », le plus ancien du cabinet, a pris la fuite.",
            'scenes' => [
                [
                    'titre' => 'La case vide',
                    'didascalie' => "Le bureau de Mireille. Un calendrier géant où tous les rendez-vous tombent un mardi 31. Au milieu, une case découpée aux ciseaux.",
                    'repliques' => [
                        ['qui' => 'mireille', 'texte' => "Il est parti dans la nuit. Avec ses deux bornes.", 'jeu' => 'glaciale'],
                        ['qui' => 'conseiller', 'texte' => "Un délai ne peut pas partir. Il n'a pas de jambes."],
                        ['qui' => 'mireille', 'texte' => "Il en avait deux, Votre Moyenneté. « Trois jours » et « jamais ». Il a toujours eu une jambe beaucoup plus longue que l'autre."],
                        ['qui' => 'messmerde', 'texte' => "Je le vois. Il court. Il est essoufflé. Il est… quelque part entre ici et ailleurs.", 'jeu' => 'les yeux mi-clos'],
                        ['qui' => 'bertrand', 'texte' => "C'est précis. Je fais fermer toutes les issues, sauf celles-là."],
                        ['qui' => 'conseiller', 'texte' => "Il nous faut un chasseur. Quelqu'un de rapide, de courageux, de disponible."],
                        ['qui' => 'mireille', 'texte' => "De disponible, nous n'avons que le tiroir C."],
                    ],
                ],
                [
                    'titre' => 'La chasse',
                    'didascalie' => "Le stagiaire, désarchivé pour l'occasion, le front toujours marqué « VU », avance dans le couloir avec une épuisette à papillons. La cour le suit à distance prudente.",
                    'repliques' => [
                        ['qui' => 'stagiaire', 'texte' => "Délai… petit délai… on ne te veut pas de mal… on veut juste te respecter.", 'jeu' => 'chuchotant'],
                        ['qui' => 'scene', 'texte' => "Un froissement derrière la photocopieuse. Le stagiaire plonge, épuisette en avant."],
                        ['qui' => 'stagiaire', 'texte' => "Je l'ai ! Je l'ai ! Il se débat !"],
                        ['qui' => 'mireille', 'texte' => "Montrez. Ah. Vous n'en avez attrapé que la moitié. C'est « jamais »."],
                        ['qui' => 'stagiaire', 'texte' => "Et « trois jours » ?"],
                        ['qui' => 'bertrand', 'texte' => "Il saute la haie du parking. Il est déjà loin. Trois jours, ça file vite.", 'jeu' => 'à la fenêtre'],
                        ['qui' => 'conseiller', 'texte' => "Peu importe. Un demi-délai, c'est déjà un délai."],
                    ],
                ],
                [
                    'titre' => 'Le délai unique',
                    'didascalie' => "Mireille réinscrit le demi-délai au calendrier. Dans chaque case, au feutre rouge, un seul mot : « jamais ».",
                    'repliques' => [
                        ['qui' => 'mireille', 'texte' => "À compter de ce jour, tous les délais du cabinet sont de « jamais »."],
                        ['qui' => 'conseiller', 'texte' => "Mais c'est formidable. Nous ne serons plus jamais en retard."],
                        ['qui' => 'bertrand', 'texte' => "Ni en avance, Votre Moyenneté. Article 17 : être en avance est une impolitesse envers le retard."],
                        ['qui' => 'messmerde', 'texte' => "Je prédis qu'aucun dossier ne sera traité. Et pour la première fois, je le prédis avant."],
                        ['qui' => 'mireille', 'texte' => "Cabinet ConseilPlus+, bonjour. Votre dossier sera traité jamais. Oui. Jamais. Je vous en prie. Bonne journée.", 'jeu' => 'au téléphone, voix parfaitement posée'],
                        ['qui' => 'mireille', 'texte' => "Il était ravi. Il n'a jamais été aussi sûr de quelque chose."],
                        ['qui' => 'stagiaire', 'texte' => "Et « trois jours », on le laisse dehors ?"],
                        ['qui' => 'conseiller', 'texte' => "On lui laisse la porte entrouverte. S'il revient, ce sera dans trois jours. Ou jamais. Il connaît le chemin."],
                    ],
                ],
            ],
            'suspense' => "Le soir même, un parchemin glissa sous la porte du cabinet. Il annonçait le Grand Concours du Meilleur Dossier du royaume. Date limite d'inscription : hier. Un cabinet dont le seul délai est « jamais » peut-il seulement concourir ? La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 7
        [
            'numero' => 7,
            'titre' => 'Le Concours du Meilleur Dossier',
            'precedemment' => "Le délai « entre trois jours et jamais » s'est enfui. Armé d'une épuisette, le stagiaire n'en a rattrapé que la moitié : tous les délais du cabinet sont désormais de « jamais ». Puis est arrivée l'invitation au Grand Concours du Meilleur Dossier, close depuis la veille.",
            'heraut' => "Bonsoir. Le Grand Concours du Meilleur Dossier récompense chaque année le dossier le plus complet, le mieux classé et le moins lu du royaume. Le cabinet s'est inscrit en retard, ce qui, selon Mireille, est la seule manière élégante de s'inscrire. Ce matin, la présidente du jury est arrivée.",
            'scenes' => [
                [
                    'titre' => 'Le choix du dossier',
                    'didascalie' => "Une longue table couverte de chemises cartonnées. Toute la cour cherche son meilleur dossier. Messmerde porte déjà une médaille en chocolat.",
                    'repliques' => [
                        ['qui' => 'messmerde', 'texte' => "Inutile de chercher. J'ai prédit notre victoire. La médaille est déjà là. Je l'ai juste un peu entamée."],
                        ['qui' => 'bertrand', 'texte' => "Je propose le dossier 0057 : un formulaire rempli sans une seule rature. Le seul du royaume."],
                        ['qui' => 'mireille', 'texte' => "Trop parfait. Le jury croira à une contrefaçon."],
                        ['qui' => 'conseiller', 'texte' => "Et le mien ? « Projets du cabinet pour les cent prochaines années ». Il a une très belle chemise verte."],
                        ['qui' => 'mireille', 'texte' => "Il est vide, Votre Moyenneté."],
                        ['qui' => 'conseiller', 'texte' => "Il est vide pour laisser de la place aux projets."],
                        ['qui' => 'stagiaire', 'texte' => "Moi, je suis tamponné… Techniquement, je suis un dossier…", 'jeu' => 'timide, par le tiroir entrouvert'],
                        ['qui' => 'conseiller', 'texte' => "Le stagiaire. Bien sûr. Notre dossier vivant.", 'jeu' => 'après un silence'],
                    ],
                ],
                [
                    'titre' => 'Le jury',
                    'didascalie' => "Entre Dame Agrafe, robe à paniers en papier kraft, chignon tenu par trois agrafes. Elle s'assied sans regarder la chaise, par principe.",
                    'repliques' => [
                        ['qui' => 'presidente', 'texte' => "Présentez votre dossier. Vous avez trois minutes. Ou jamais, si j'en crois votre calendrier."],
                        ['qui' => 'conseiller', 'texte' => "Madame la présidente, voici notre dossier. Il s'appelle le stagiaire."],
                        ['qui' => 'stagiaire', 'texte' => "Bonjour, madame. Document numéro 1, format portrait, agréé dix sur vingt."],
                        ['qui' => 'presidente', 'texte' => "Le dossier parle. C'est inhabituel. Les dossiers, d'ordinaire, ont la décence de se taire."],
                        ['qui' => 'mireille', 'texte' => "Il a été classé avec soin, madame. Tiroir C. Un croissant par jour."],
                        ['qui' => 'presidente', 'texte' => "Que contient-il ?"],
                        ['qui' => 'stagiaire', 'texte' => "Des… des souvenirs ? Un peu de croissant ? Ma convention de stage de 2009 ?", 'jeu' => 'paniqué'],
                        ['qui' => 'presidente', 'texte' => "Une pièce jointe d'époque. C'est bien. Mais il manque une signature."],
                        ['qui' => 'bertrand', 'texte' => "La Plume est… en déplacement, madame."],
                    ],
                ],
                [
                    'titre' => 'Le palmarès',
                    'didascalie' => "La remise des prix, dans le hall. Le cabinet est seul sur l'estrade : c'est le seul cabinet inscrit.",
                    'repliques' => [
                        ['qui' => 'presidente', 'texte' => "Le jury a délibéré. Premier prix : non attribué, faute de signature."],
                        ['qui' => 'conseiller', 'texte' => "Et le deuxième ?"],
                        ['qui' => 'presidente', 'texte' => "Deuxième prix : le cabinet ConseilPlus+. Sur un concurrent."],
                        ['qui' => 'conseiller', 'texte' => "Deuxièmes sur un ! C'est bien mieux que premiers sur un : il nous reste de la marge.", 'jeu' => 'aux anges'],
                        ['qui' => 'messmerde', 'texte' => "Prédit. Mot pour mot. À l'exception des mots.", 'jeu' => 'croquant sa médaille'],
                        ['qui' => 'stagiaire', 'texte' => "Est-ce que le dossier gagne quelque chose ?"],
                        ['qui' => 'presidente', 'texte' => "Le dossier gagne le droit de retourner dans son tiroir. Le cabinet, lui, gagne ceci."],
                        ['qui' => 'scene', 'texte' => "Elle pose un écrin de velours sur la table. Le Conseiller l'ouvre : un tampon flambant neuf, en plastique brillant, à encrage automatique."],
                        ['qui' => 'conseiller', 'texte' => "Un nouveau Sceau. Comme il brille. Il ne penche même pas."],
                    ],
                ],
            ],
            'suspense' => "Sur son coussin, l'ancien Sceau n'a rien dit. Mais Bertrand jure l'avoir vu pencher, pour la première fois, un peu plus à gauche que d'habitude. Un cabinet peut-il avoir deux Sceaux ? La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 8
        [
            'numero' => 8,
            'titre' => 'Le Sceau automatique',
            'precedemment' => "Présenté au Grand Concours du Meilleur Dossier, le stagiaire n'a obtenu, faute de signature, que la deuxième place sur un concurrent. Le prix : un tampon neuf, à encrage automatique. Sur son coussin, l'ancien Sceau, toujours en grève, a semblé pencher davantage.",
            'heraut' => "Bonsoir. Il tamponne vite, il tamponne seul, il n'a jamais besoin d'encre. Le Sceau automatique est entré au cabinet lundi. Mardi, il avait tamponné plus de documents que l'ancien Sceau en quarante ans. Mercredi, il n'y avait plus de documents.",
            'scenes' => [
                [
                    'titre' => 'Le petit nouveau',
                    'didascalie' => "Le Sceau automatique trône sur un socle à piles et clignote. L'ancien Sceau a été poussé au bout du bureau, sur un sous-verre.",
                    'repliques' => [
                        ['qui' => 'automatique', 'texte' => "BONJOUR. JE TAMPONNE. VOULEZ-VOUS QUE JE TAMPONNE ?"],
                        ['qui' => 'conseiller', 'texte' => "Quelle politesse ! L'ancien ne nous a jamais demandé notre avis.", 'jeu' => 'ravi'],
                        ['qui' => 'automatique', 'texte' => "CLAC. VALIDÉ. CLAC. VALIDÉ. CLAC. VALIDÉ."],
                        ['qui' => 'mireille', 'texte' => "Il vient de valider mon agenda, mes lunettes et la fenêtre."],
                        ['qui' => 'bertrand', 'texte' => "Article 210 : rien ne se valide sans avoir été déposé. Avez-vous déposé la fenêtre, Dame Mireille ?"],
                        ['qui' => 'mireille', 'texte' => "Elle est posée là depuis 1974. On peut dire qu'elle est déposée."],
                        ['qui' => 'tampon', 'texte' => "…", 'jeu' => 'depuis son sous-verre, regarde ailleurs avec dignité'],
                    ],
                ],
                [
                    'titre' => "L'emballement",
                    'didascalie' => "Le Sceau automatique ne s'arrête plus. Le stagiaire lui court après avec le mode d'emploi, rédigé en quatre-vingt-dix langues, aucune en français.",
                    'repliques' => [
                        ['qui' => 'stagiaire', 'texte' => "Il y a un bouton « Arrêt » ! Il est… sous le tampon. Pour l'arrêter, il faut le tamponner."],
                        ['qui' => 'automatique', 'texte' => "CLAC. URGENT. CLAC. CLASSÉ. CLAC. PAYÉ."],
                        ['qui' => 'messmerde', 'texte' => "Il a tamponné « PAYÉ » sur la machine à café. Je sens que ça va mal se passer."],
                        ['qui' => 'cafe', 'texte' => "Bip. Boisson payée. Boisson payée. Boisson payée."],
                        ['qui' => 'scene', 'texte' => "L'Oracle délivre quarante gobelets tièdes à la suite. La salle de pause est inondée de café moyen."],
                        ['qui' => 'automatique', 'texte' => "CLAC. ANNULÉ."],
                        ['qui' => 'bertrand', 'texte' => "Votre Moyenneté… il vient de tamponner « ANNULÉ » sur l'acte de fondation du cabinet.", 'jeu' => 'blême, un vieux parchemin à la main'],
                        ['qui' => 'conseiller', 'texte' => "Ah. C'est grave ?"],
                        ['qui' => 'bertrand', 'texte' => "Nous n'existons plus depuis onze secondes."],
                    ],
                ],
                [
                    'titre' => 'Le retour du vieux Sceau',
                    'didascalie' => "Silence complet. La cour n'existe plus, mais reste polie. Le stagiaire tient l'acte de fondation entre deux doigts.",
                    'repliques' => [
                        ['qui' => 'conseiller', 'texte' => "Si nous n'existons plus, qui va éponger le café ?"],
                        ['qui' => 'mireille', 'texte' => "Personne. C'est l'avantage."],
                        ['qui' => 'scene', 'texte' => "Alors, sur son sous-verre, l'ancien Sceau se soulève lentement, de toute sa hauteur de bois verni."],
                        ['qui' => 'tampon', 'texte' => "Clac.", 'jeu' => "s'abat sur l'acte de fondation, juste au-dessus de « ANNULÉ »"],
                        ['qui' => 'bertrand', 'texte' => "« ANNULÉ, l'annulation. » Encre rouge. Penché à gauche. Nous existons de nouveau.", 'jeu' => 'la voix tremblante'],
                        ['qui' => 'conseiller', 'texte' => "Il a tamponné. Pour nous. Sans la Plume.", 'jeu' => 'les larmes aux yeux'],
                        ['qui' => 'messmerde', 'texte' => "Je l'avais…"],
                        ['qui' => 'mireille', 'texte' => "Non."],
                        ['qui' => 'automatique', 'texte' => "CLAC. REM…", 'jeu' => "le stagiaire vient de lui retirer ses piles"],
                        ['qui' => 'stagiaire', 'texte' => "Il dormira dans le tiroir C. J'ai l'habitude, je lui montrerai comment on fait."],
                    ],
                ],
            ],
            'suspense' => "Le vieux Sceau a retrouvé son coussin. Mais en se reposant, il a laissé sur le buvard une empreinte que personne n'avait commandée, et qui ne figure dans aucun registre. Un mot unique, à l'encre rouge : « MESSMERDE ». Le devin de la cour a-t-il quelque chose à se reprocher ? La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 9
        [
            'numero' => 9,
            'titre' => 'Le Devin sur la sellette',
            'precedemment' => "Le Sceau automatique, gagné au concours, a tout tamponné, jusqu'à l'acte de fondation du cabinet, marqué « ANNULÉ ». L'ancien Sceau a mis fin à sa grève pour annuler l'annulation. Puis il a laissé sur le buvard un seul mot : « MESSMERDE ».",
            'heraut' => "Bonsoir. Il prédit tout, mais toujours après. Il lit dans les pensées, mais de préférence celles qu'on vient de dire à voix haute. Depuis le message du Sceau, Messmerde, devin de la cour, est le premier suspect dans l'affaire de la Plume. Ce soir, il est entendu.",
            'scenes' => [
                [
                    'titre' => "L'interrogatoire",
                    'didascalie' => "Une chaise sous une lampe de bureau. Messmerde y est assis, très calme. Bertrand interroge, Mireille chronomètre, le Conseiller mange des biscuits pour se donner une contenance.",
                    'repliques' => [
                        ['qui' => 'bertrand', 'texte' => "Où étiez-vous la nuit de la disparition ?"],
                        ['qui' => 'messmerde', 'texte' => "Ici. Je savais que la Plume allait disparaître, alors je suis resté pour voir."],
                        ['qui' => 'mireille', 'texte' => "Et vous l'avez vue disparaître ?"],
                        ['qui' => 'messmerde', 'texte' => "Non. J'ai fermé les yeux pour mieux la prédire. Quand je les ai rouverts, c'était fait. Exactement comme prévu."],
                        ['qui' => 'conseiller', 'texte' => "Il est très fort. Je ne comprends rien : c'est bien la preuve qu'il est très fort.", 'jeu' => 'la bouche pleine'],
                        ['qui' => 'bertrand', 'texte' => "Pourquoi le Sceau a-t-il tamponné votre nom ?"],
                        ['qui' => 'messmerde', 'texte' => "Parce qu'il pense à moi. Je l'ai lu dans ses pensées. Il pense énormément à moi. C'est normal, je suis très présent."],
                    ],
                ],
                [
                    'titre' => 'Le retournement',
                    'didascalie' => "Messmerde se lève, retourne la lampe vers la cour et se met à tourner lentement autour des chaises.",
                    'repliques' => [
                        ['qui' => 'messmerde', 'texte' => "Mais au fond… qui a intérêt à ce que plus rien ne soit signé ? Bertrand ! Sans signature, aucun article ne peut être modifié !"],
                        ['qui' => 'bertrand', 'texte' => "C'est exact. Je suis donc innocent, et j'ai un mobile. Les deux à la fois. Article 12 bis."],
                        ['qui' => 'messmerde', 'texte' => "Mireille ! Sans signature, aucun délai ne peut être tenu !"],
                        ['qui' => 'mireille', 'texte' => "Ils ne l'étaient pas avant. C'est tout mon métier."],
                        ['qui' => 'messmerde', 'texte' => "Le stagiaire ! Quatorze stylos dans les poches !"],
                        ['qui' => 'stagiaire', 'texte' => "Quinze, depuis ce matin. Je ne sais pas d'où vient le quinzième.", 'jeu' => 'déjà en larmes'],
                        ['qui' => 'messmerde', 'texte' => "Et vous, Votre Moyenneté… qui avez demandé la Plume le dernier…", 'jeu' => 'se tournant lentement vers le trône'],
                        ['qui' => 'conseiller', 'texte' => "Moi ? Mais je ne savais même pas qu'elle était partie avant qu'elle soit partie !"],
                        ['qui' => 'messmerde', 'texte' => "C'est exactement ce que dirait un coupable qui ne le sait pas."],
                    ],
                ],
                [
                    'titre' => 'La conclusion du devin',
                    'didascalie' => "Tout le monde est suspect. Bertrand a installé un distributeur de tickets numérotés pour organiser les aveux. L'Oracle ronronne dans un coin.",
                    'repliques' => [
                        ['qui' => 'messmerde', 'texte' => "J'ai la réponse. Le coupable est dans cette pièce."],
                        ['qui' => 'mireille', 'texte' => "Nous sommes tous dans cette pièce."],
                        ['qui' => 'messmerde', 'texte' => "Voilà pourquoi je ne me trompe jamais."],
                        ['qui' => 'bertrand', 'texte' => "Ticket numéro 1, avancez-vous pour avouer."],
                        ['qui' => 'conseiller', 'texte' => "C'est moi, le numéro 1. Bon. J'avoue… que je ne sais pas. Ça compte, comme aveu ?", 'jeu' => "s'avançant, docile"],
                        ['qui' => 'bertrand', 'texte' => "C'est un aveu d'ignorance. Article 88 : il compte pour moitié."],
                        ['qui' => 'messmerde', 'texte' => "Nous avons donc un demi-coupable. Je l'avais prévu à moitié."],
                        ['qui' => 'cafe', 'texte' => "Bip. Bip. Bip."],
                        ['qui' => 'stagiaire', 'texte' => "Il veut dire quelque chose ! Il ne bipe jamais trois fois, sauf quand il n'a plus de gobelets !"],
                        ['qui' => 'cafe', 'texte' => "Plus de gobelets. Ce que vous cherchez est descendu là où personne ne descend."],
                    ],
                ],
            ],
            'suspense' => "Là où personne ne descend. Tout le cabinet connaît cet endroit, et tout le cabinet fait semblant de ne pas le connaître : les Archives du sous-sol, fermées depuis 1987. Qui aura le courage d'y descendre ? Pas le Conseiller. Pas Mireille. Pas Bertrand. La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 10
        [
            'numero' => 10,
            'titre' => 'Les Archives du sous-sol',
            'precedemment' => "Accusé par le Sceau, Messmerde a fini par désigner toute la cour comme suspecte. Seul l'Oracle a donné une piste : la Plume serait descendue « là où personne ne descend ». Les Archives du sous-sol, fermées depuis 1987.",
            'heraut' => "Bonsoir. Selon les plans, les Archives du sous-sol occupent deux étages. Selon Bertrand, cinq. Selon le stagiaire, qui y est descendu une fois chercher un formulaire B-0 : « trop ». Ce soir, toute la cour descend. Le stagiaire passe devant.",
            'scenes' => [
                [
                    'titre' => 'La descente',
                    'didascalie' => "Un escalier en colimaçon qui n'en finit pas. Le stagiaire ouvre la marche, une lampe de poche à la main. La cour suit en file indienne, chacun tenant le pourpoint du précédent.",
                    'repliques' => [
                        ['qui' => 'stagiaire', 'texte' => "Attention à la marche. Et à celle d'après. En fait, toutes les marches sont dangereuses, ici.", 'jeu' => 'claquant des dents'],
                        ['qui' => 'conseiller', 'texte' => "Comme c'est beau, un sous-sol. On se croirait dans un sous-sol."],
                        ['qui' => 'mireille', 'texte' => "Il fait froid. J'ai demandé le chauffage pour cet escalier en 1992. Le délai court toujours."],
                        ['qui' => 'bertrand', 'texte' => "« Archives. Défense d'entrer sans y être déjà. »", 'jeu' => 'lisant une plaque émaillée'],
                        ['qui' => 'messmerde', 'texte' => "Je sens une présence. Plusieurs présences. Des présences en carton."],
                        ['qui' => 'scene', 'texte' => "La lampe éclaire des rayonnages à perte de vue, des dossiers jaunis et un minitel couvert de toiles d'araignée."],
                    ],
                ],
                [
                    'titre' => 'Les trouvailles',
                    'didascalie' => "La cour fouille les rayonnages. Chaque tiroir ouvert soulève un nuage de poussière réglementaire.",
                    'repliques' => [
                        ['qui' => 'bertrand', 'texte' => "Dossier « Projet d'installation d'une machine à café, 1986 ». Avis défavorable. Elle a été installée quand même."],
                        ['qui' => 'mireille', 'texte' => "Un agenda de 1989. Tous les rendez-vous tombent un mardi 31. J'étais donc déjà cohérente."],
                        ['qui' => 'stagiaire', 'texte' => "Ici ! Une boîte marquée « Délais retrouvés »… Quelque chose bouge dedans."],
                        ['qui' => 'scene', 'texte' => "Le stagiaire soulève le couvercle. Un petit délai, essoufflé et couvert de poussière, lève vers eux deux grands yeux de calendrier."],
                        ['qui' => 'mireille', 'texte' => "« Trois jours ». C'était donc toi. Tu n'étais pas parti, tu étais descendu.", 'jeu' => 'émue, malgré elle'],
                        ['qui' => 'conseiller', 'texte' => "Le délai prodigue ! Qu'on le réinscrive au calendrier, entre « jamais » et la pause café."],
                        ['qui' => 'scene', 'texte' => "Un grand bruit derrière eux. La porte des Archives vient de se refermer. Toute seule."],
                    ],
                ],
                [
                    'titre' => 'La porte',
                    'didascalie' => "Enfermée, la cour se serre autour de la lampe de poche, qui faiblit. Bertrand examine la serrure.",
                    'repliques' => [
                        ['qui' => 'bertrand', 'texte' => "Pour obtenir la clé, il faut une demande de clé. Elle se dépose à l'accueil."],
                        ['qui' => 'conseiller', 'texte' => "Mais c'est vous, l'accueil, Bertrand."],
                        ['qui' => 'bertrand', 'texte' => "Oui, mais l'accueil est en haut. Je ne peux pas recevoir d'en bas ma propre demande. Ce serait un conflit d'intérêts."],
                        ['qui' => 'mireille', 'texte' => "Il faudrait que quelqu'un, en haut, valide une demande glissée sous la porte. Quelqu'un qui ne parle pas, ne bouge pas, ne descend jamais…"],
                        ['qui' => 'stagiaire', 'texte' => "Le Sceau !"],
                        ['qui' => 'scene', 'texte' => "Le Conseiller rédige la demande sur le dos du stagiaire, avec l'un de ses quinze stylos, et la glisse sous la porte. Un long silence."],
                        ['qui' => 'tampon', 'texte' => "Clac.", 'jeu' => 'de l\'autre côté de la porte'],
                        ['qui' => 'scene', 'texte' => "La porte s'ouvre en grinçant."],
                        ['qui' => 'conseiller', 'texte' => "Il est descendu. Le Sceau est descendu au sous-sol. Pour nous.", 'jeu' => 'bouleversé'],
                        ['qui' => 'messmerde', 'texte' => "Ou bien il est tombé dans l'escalier. Mais je préfère votre version, elle est plus jolie."],
                    ],
                ],
            ],
            'suspense' => "Au moment de remonter, le stagiaire remarqua que le tout dernier tiroir des Archives était entrouvert. À l'intérieur, une enveloppe cachetée de cire bleue, adressée d'une écriture élégante : « À la cour, qui ne lit jamais rien. » La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 11
        [
            'numero' => 11,
            'titre' => 'La Lettre de la Plume',
            'precedemment' => "Guidée par le stagiaire, la cour est descendue aux Archives du sous-sol. Elle y a retrouvé le délai « trois jours », qui s'y cachait, et n'en est ressortie que grâce au Sceau. Dans le dernier tiroir attendait une enveloppe à la cire bleue : « À la cour, qui ne lit jamais rien. »",
            'heraut' => "Bonsoir. Au cabinet, on ouvre beaucoup d'enveloppes et l'on en lit très peu. Celle-ci fera exception : elle est signée de la seule personne capable de signer quoi que ce soit. La Plume elle-même.",
            'scenes' => [
                [
                    'titre' => 'La lecture',
                    'didascalie' => "Salle du trône. Mireille tient la lettre à bout de bras. Toute la cour écoute, debout. Le délai « trois jours » a été installé sur un coussin, à côté du Sceau.",
                    'repliques' => [
                        ['qui' => 'mireille', 'texte' => "« Chère cour. Depuis la fondation, j'ai signé quarante mille documents. Je n'en ai lu aucun. Personne ne m'en a jamais lu un seul. »", 'jeu' => 'lisant, voix parfaitement posée'],
                        ['qui' => 'conseiller', 'texte' => "C'est vrai. Nous voulions la ménager."],
                        ['qui' => 'mireille', 'texte' => "« J'ai signé un menu de cantine, un permis de pêche pour la machine à café, et trois fois la même démission, qui n'a jamais été acceptée. »"],
                        ['qui' => 'stagiaire', 'texte' => "C'était la mienne. Je voulais être stagiaire ailleurs."],
                        ['qui' => 'mireille', 'texte' => "« Je reviendrai le jour où quelqu'un, au cabinet, lira un document en entier avant de me demander de le signer. »"],
                        ['qui' => 'bertrand', 'texte' => "En entier ? Jusqu'aux annexes ?", 'jeu' => 'blême'],
                    ],
                ],
                [
                    'titre' => "L'essai",
                    'didascalie' => "Toute la cour, assise en rang, s'essaie à la lecture d'un document d'une page : le règlement de la salle de pause, six lignes. Mireille surveille, un sablier à la main.",
                    'repliques' => [
                        ['qui' => 'conseiller', 'texte' => "« Article un. La salle de pause est… » Pause. C'est écrit pause. Je fais une pause.", 'jeu' => 'lisant lentement, le doigt sur la ligne'],
                        ['qui' => 'mireille', 'texte' => "Ce n'est pas une consigne, Votre Moyenneté. C'est le nom de la salle."],
                        ['qui' => 'messmerde', 'texte' => "Je n'ai pas besoin de lire. Je sais déjà ce qui est écrit. Il est écrit : « des choses »."],
                        ['qui' => 'bertrand', 'texte' => "Je l'ai lu trois fois. Mais je le connais par cœur, ça ne compte pas : je ne l'ai pas lu, je l'ai reconnu."],
                        ['qui' => 'stagiaire', 'texte' => "J'ai fini ! J'ai tout lu, jusqu'au bout !"],
                        ['qui' => 'conseiller', 'texte' => "Bravo ! Que dit la dernière ligne ?"],
                        ['qui' => 'stagiaire', 'texte' => "« Il est interdit de lire ce règlement pendant les heures de travail. »", 'jeu' => 'penaud'],
                        ['qui' => 'mireille', 'texte' => "Il est dix heures et demie. Votre lecture est nulle et non avenue."],
                    ],
                ],
                [
                    'titre' => 'Signer sans la Plume',
                    'didascalie' => "Le Conseiller, résolu, veut répondre à la Plume par une lettre officielle. Mais une lettre officielle doit être signée.",
                    'repliques' => [
                        ['qui' => 'conseiller', 'texte' => "Alors je signerai autrement. Stagiaire, une cuillère de l'Oracle."],
                        ['qui' => 'cafe', 'texte' => "Bip. Cuillère délivrée. Attention : tiède."],
                        ['qui' => 'scene', 'texte' => "Le Conseiller trempe la cuillère dans un gobelet et trace une grande signature brune au bas de la lettre."],
                        ['qui' => 'bertrand', 'texte' => "Une signature au café a la valeur d'une tache, Votre Moyenneté. Article 301."],
                        ['qui' => 'conseiller', 'texte' => "Avec un croissant, alors ?"],
                        ['qui' => 'mireille', 'texte' => "Un croissant laisse des miettes, pas des signatures."],
                        ['qui' => 'messmerde', 'texte' => "Je signerai par la pensée."],
                        ['qui' => 'bertrand', 'texte' => "Article 302 : une signature par la pensée a la valeur d'une pensée. Les pensées ne sont pas recevables avant midi."],
                        ['qui' => 'tampon', 'texte' => "…", 'jeu' => 'regarde simplement la porte'],
                        ['qui' => 'conseiller', 'texte' => "Très bien. Nous enverrons la lettre sans signature. Elle saura que c'est nous : personne d'autre n'écrit comme ça."],
                    ],
                ],
            ],
            'suspense' => "La lettre n'eut pas besoin de partir. À l'instant où le Conseiller la pliait, on frappa trois coups à la porte du cabinet. Sur le seuil se tenait un homme en cape bleu nuit, un étui de cuir sous le bras. « Je viens de la part de la Plume, dit-il. Elle a des conditions. » La suite, la semaine prochaine.",
        ],

        // ───────────────────────────────────────────── 12
        [
            'numero' => 12,
            'titre' => 'Le Retour de la Plume',
            'precedemment' => "Dans sa lettre, la Plume a fait savoir qu'elle reviendrait le jour où quelqu'un lirait un document en entier avant de le faire signer. Les essais de lecture ont échoué, comme les signatures de remplacement, au café ou par la pensée. Puis un émissaire de la Plume a frappé à la porte.",
            'heraut' => "Bonsoir. Dernier épisode de la saison. Ce soir, la Plume pose ses conditions par la voix de son représentant. Le cabinet a revêtu ses plus beaux pourpoints et fait cirer le Sceau. L'enjeu est immense : de cette négociation dépend l'avenir de toutes les signatures du royaume. Au cabinet, environ deux par an.",
            'scenes' => [
                [
                    'titre' => 'Les conditions',
                    'didascalie' => "Entre le Porte-Plume, cape bleu nuit, chapeau à large bord. Il s'incline si bas que la plume de son chapeau balaie le parquet.",
                    'repliques' => [
                        ['qui' => 'porte_plume', 'texte' => "Messire Porte-Plume, représentant de la Plume pour toutes les questions d'encre. Ma cliente a trois conditions."],
                        ['qui' => 'conseiller', 'texte' => "Accordées !", 'jeu' => 'enthousiaste'],
                        ['qui' => 'porte_plume', 'texte' => "Je ne les ai pas encore dites."],
                        ['qui' => 'conseiller', 'texte' => "Ça m'évitera de les lire."],
                        ['qui' => 'porte_plume', 'texte' => "Un : on lit avant de signer. Deux : on lit en entier. Trois : on lit ce contrat, qui contient les deux premières."],
                        ['qui' => 'scene', 'texte' => "Il ouvre son étui. Le contrat se déroule sur toute la longueur de la salle, passe sous la porte et continue dans le couloir."],
                        ['qui' => 'bertrand', 'texte' => "Combien d'articles ?", 'jeu' => 'fasciné'],
                        ['qui' => 'porte_plume', 'texte' => "Quatre cent treize. Un de plus que votre règlement. Ma cliente a de l'humour."],
                    ],
                ],
                [
                    'titre' => 'Qui lira ?',
                    'didascalie' => "La cour se regarde. Personne ne bouge. Le délai « trois jours » s'est caché derrière le coussin du Sceau.",
                    'repliques' => [
                        ['qui' => 'mireille', 'texte' => "Je pourrais le lire. Dans un délai de… jamais."],
                        ['qui' => 'messmerde', 'texte' => "Je le lirais volontiers, si je ne savais pas déjà ce qu'il contient."],
                        ['qui' => 'porte_plume', 'texte' => "Et que contient-il ?"],
                        ['qui' => 'messmerde', 'texte' => "…des articles.", 'jeu' => 'un temps'],
                        ['qui' => 'porte_plume', 'texte' => "C'est exact. Mais cela ne suffit pas."],
                        ['qui' => 'bertrand', 'texte' => "Moi, je l'apprendrais par cœur en le lisant. Or l'article 12 bis m'interdit d'apprendre un autre texte que le règlement."],
                        ['qui' => 'stagiaire', 'texte' => "Moi, je veux bien. J'ai l'habitude des endroits longs et sombres.", 'jeu' => 'tout petit, levant la main'],
                        ['qui' => 'scene', 'texte' => "Le stagiaire part lire le contrat en le suivant jusqu'au bout du couloir. La nuit tombe. Le jour se lève. On entend au loin : « Article deux cent… »"],
                        ['qui' => 'conseiller', 'texte' => "Qu'on lui porte un croissant. Par le couloir. Le croissant suivra le contrat."],
                    ],
                ],
                [
                    'titre' => 'La signature',
                    'didascalie' => "Le matin. Le stagiaire revient, les cheveux en bataille, le front toujours marqué « VU », le contrat roulé sous le bras.",
                    'repliques' => [
                        ['qui' => 'stagiaire', 'texte' => "Lu. En entier. Annexes comprises. L'article 413 dit seulement : « Merci d'avoir lu jusqu'ici. »"],
                        ['qui' => 'porte_plume', 'texte' => "Alors ma cliente est de retour. Qui signe ?", 'jeu' => 'ému, sortant la Plume de son étui'],
                        ['qui' => 'conseiller', 'texte' => "Celui qui a lu, voyons. C'est la condition. Je viens de la comprendre ; ça m'a fait tout drôle."],
                        ['qui' => 'scene', 'texte' => "Le stagiaire prend la Plume. La cour retient son souffle. Il signe, pour la première fois de sa vie, de son propre nom."],
                        ['qui' => 'tampon', 'texte' => "Clac."],
                        ['qui' => 'bertrand', 'texte' => "Signé et tamponné. Le premier document officiel du cabinet à avoir été lu."],
                        ['qui' => 'mireille', 'texte' => "Je note l'événement au calendrier. Il tombe… un mardi 31. Tout est en ordre."],
                        ['qui' => 'messmerde', 'texte' => "Je l'avais…"],
                        ['qui' => 'cour', 'texte' => "Non."],
                        ['qui' => 'conseiller', 'texte' => "Qu'on célèbre ce jour ! Et que le stagiaire reçoive une augmentation : deux croissants !", 'jeu' => 'les bras ouverts'],
                    ],
                ],
            ],
            'suspense' => "Ce soir-là, pendant que la cour fêtait le retour de la Plume, quelqu'un sonna au guichet. Un usager, chapeau à la main, attendait poliment. « Bonjour. Je viens chercher un formulaire. » Et dans le tiroir de Bertrand, il ne restait plus un seul B-0. La suite, la semaine prochaine.",
        ],
    ],
];
