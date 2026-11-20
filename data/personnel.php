<?php
// Le Personnel (cabinet.php) : ce que la page ajoute à l'équipe de data/textes.php (clé « equipe »), par clé de membre.
//   'poste'    n° de poste à l'annuaire interne
//   'etape'    l'étape du dossier que le membre tient (voir « circuit » ci-dessous)
//   'alt'      texte alternatif du portrait (assets/equipe/<cle>.webp) ; sans portrait, un monogramme le remplace
// 'circuit' : le trajet d'un dossier dans le cabinet, étape par étape, avec qui la tient et où elle se passe.
return [
    'membres' => [
        'conseiller' => ['poste' => '01', 'etape' => 'verdict',
            'alt' => 'Le conseiller moyen, mains jointes sous le menton, contemple avec satisfaction une feuille parfaitement blanche.'],
        'accueil' => ['poste' => '02', 'etape' => 'accueil',
            'alt' => 'Portrait de Bertrand, en attente de son formulaire d\'autorisation de portrait.'],
        'tampon' => ['poste' => '03', 'etape' => 'depot',
            'alt' => 'Le tampon du cabinet, médaillé, posé sur son encreur rouge à côté d\'un cadre vide qu\'il vient de valider.'],
        'delais' => ['poste' => '04', 'etape' => 'instruction',
            'alt' => 'Mireille, stylo rouge à la main, regarde par-dessus ses lunettes une horloge qui avance dans le mauvais sens.'],
        'stagiaire' => ['poste' => '05', 'etape' => 'instruction',
            'alt' => 'Le stagiaire marche à grands pas en lisant à voix haute un dictionnaire rouge, un badge vierge autour du cou.'],
        'messmerde' => ['poste' => '06', 'etape' => 'verdict',
            'alt' => 'Messmerde, deux doigts sur la tempe et les yeux fermés, consulte une boule à neige qui contient un château.'],
        'cafe' => ['poste' => '07', 'etape' => 'reclamations',
            'alt' => 'La machine à café du cabinet, décorée d\'une cocarde rouge, sert un gobelet tiède sans qu\'on le lui demande.'],
    ],

    'circuit' => [
        'accueil' => ['titre' => 'Accueil', 'texte' => 'Bertrand remet un ticket et rappelle l\'article du jour. Le dossier n\'existe pas encore, mais il est déjà en retard.',
            'lien' => ['index.php', 'Le Hall']],
        'depot' => ['titre' => 'Dépôt', 'texte' => 'Le visiteur dépose son problème au guichet. Le tampon s\'abat : « Reçu ». Il n\'a pas lu, il ne lit jamais.',
            'lien' => ['guichet.php', 'Le Guichet']],
        'instruction' => ['titre' => 'Instruction', 'texte' => 'Mireille fixe le délai, le stagiaire lit le dossier à voix haute, de travers. Le statut passe à « En instruction ».',
            'lien' => ['compte.php', 'Mon dossier']],
        'verdict' => ['titre' => 'Verdict', 'texte' => 'Le conseiller tranche. Messmerde confirme qu\'il l\'avait prévu. Le tampon valide le tout, à l\'encre rouge.',
            'lien' => ['compte.php', 'Mon dossier']],
        'reclamations' => ['titre' => 'Réclamations', 'texte' => 'Tout recours passe par le standard de Mireille et finit, au bout de la file, à la machine à café.',
            'lien' => ['contact.php', 'Réclamations']],
    ],
];
