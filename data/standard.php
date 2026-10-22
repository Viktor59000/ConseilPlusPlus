<?php
// Le standard de Mireille (standard.php, js/standard.js) : arborescence du répondeur et textes de l'appareil.
// Un écran = un texte et, au choix : des choix cliquables [libellé, écran suivant], une suite automatique
// (« pause » en ms puis « suite »), une action du script (« conseil », « attente ») ou une fin d'appel (« fin »).
// Marques remplacées par le script : {conseil}, {numero}, {delai} (data/textes.php, « delais »), {rang}, {annonce}.
return [
    'ecrans' => [
        'accueil' => [
            'texte' => "Bienvenue au standard de ConseilPlus+. Votre appel est important pour nous : il sera donc traité avec importance. Veuillez choisir parmi les options suivantes, dans l'ordre qui vous convient.",
            'choix' => [
                ['Obtenir un conseil', 'conseil'],
                ['Faire une réclamation', 'reclamation'],
                ['Parler au stagiaire', 'stagiaire'],
                ['Connaître mon délai', 'delai'],
                ['Parler à un conseiller', 'attente'],
                ['Demander un opérateur', 'operateur'],
                ['Revenir au menu précédent', 'precedent'],
                ['Autre demande', 'inexistant'],
                ['Réécouter ce message', 'accueil'],
            ],
        ],
        'conseil' => [
            'texte' => "Un instant, le conseiller cherche un conseil…",
            'action' => 'conseil',
            'suite' => 'conseil-recu',
        ],
        'conseil-recu' => [
            'texte' => "Voici votre conseil : {conseil}",
            'choix' => [['Un autre conseil', 'conseil'], ['Revenir au menu principal', 'accueil']],
        ],
        'reclamation' => [
            'texte' => "Service des réclamations. Votre réclamation concerne-t-elle un conseil, le standard, ou cette réclamation ?",
            'choix' => [
                ['Un conseil', 'reclamation-enregistree'],
                ['Le standard', 'reclamation-enregistree'],
                ['Cette réclamation', 'reclamation-enregistree'],
                ['Revenir au menu principal', 'accueil'],
            ],
        ],
        'reclamation-enregistree' => [
            'texte' => "Votre réclamation est enregistrée sous le numéro {numero}. Elle sera traitée par ordre de réclamation.",
            'choix' => [['Réclamer au sujet de ce numéro', 'reclamation'], ['Revenir au menu principal', 'accueil']],
        ],
        'stagiaire' => [
            'texte' => "Mise en relation avec le stagiaire…",
            'pause' => 2200,
            'suite' => 'stagiaire-allo',
        ],
        'stagiaire-allo' => [
            'texte' => "Allô ? Oui ? Ah. Je… Je ne sais pas trop quoi vous dire. Le conseiller n'est pas là. Vous voulez un café ?",
            'choix' => [['Oui', 'machine'], ['Non', 'machine']],
        ],
        'delai' => [
            'texte' => "Bonjour, ici Mireille, responsable des délais. Votre délai estimé est : {delai}. Ce délai est indicatif, et il ne l'indique pas.",
            'choix' => [['Un autre délai', 'delai'], ['Revenir au menu principal', 'accueil']],
        ],
        'operateur' => [
            'texte' => "Vous avez demandé un opérateur. L'opérateur, c'est vous.",
            'choix' => [['Revenir au menu principal', 'accueil']],
        ],
        'precedent' => [
            'texte' => "Pour revenir au menu précédent, choisissez « Menu précédent ». Vous êtes déjà au menu précédent.",
            'choix' => [['Menu précédent', 'precedent'], ['Revenir au menu principal', 'accueil']],
        ],
        'inexistant' => [
            'texte' => "Ce choix n'existe pas. Ce n'est pas grave : les autres non plus.",
            'choix' => [['Revenir au menu principal', 'accueil']],
        ],
        'attente' => [
            'texte' => "Tous nos conseillers sont occupés. Votre appel va être pris dans l'ordre d'arrivée, ou presque. Veuillez patienter.",
            'action' => 'attente',
            'suite' => 'transfert',
        ],
        'transfert' => [
            'texte' => "Votre interlocuteur est momentanément indisponible. Nous vous transférons vers le membre le plus disponible du cabinet.",
            'pause' => 3500,
            'suite' => 'machine',
        ],
        'machine' => [
            'texte' => "Bzzz. Ici la machine à café. Café tiède en préparation. Combien de sucres ?",
            'choix' => [['Un sucre', 'sucre'], ['Deux sucres', 'deux-sucres']],
        ],
        'deux-sucres' => [
            'texte' => "Pour deux sucres, choisissez « Un sucre » deux fois.",
            'choix' => [['Un sucre', 'sucre']],
        ],
        'sucre' => [
            'texte' => "Sucre ajouté. Bzzz… Clac.",
            'pause' => 2500,
            'suite' => 'gobelet',
        ],
        'gobelet' => [
            'texte' => "Gobelet coincé. Le cabinet vous remercie de votre appel. Fin de la communication.",
            'pause' => 3500,
            'fin' => "Appel terminé par la machine à café",
        ],
    ],

    // Pendant l'attente : rang dans la file, entrecoupé des annonces du hall (data/textes.php, « annonces »)
    'file' => "Vous êtes {rang} dans la file d'attente. Temps d'attente estimé : {delai}.",
    'annonce' => "Annonce du hall : {annonce}",

    // Conseil de secours si l'API ne répond pas
    'conseil_secours' => "Patientez. Si cela ne suffit pas, patientez davantage.",

    // Textes de l'appareil
    'appareil' => [
        'raccroche' => "Combiné raccroché",
        'en_ligne' => "Appel en cours",
        'decrocher' => "Décrocher",
        'raccrocher' => "Raccrocher",
        'rappeler' => "Rappeler",
        'vous_raccrochez' => "Vous avez raccroché",
        'bilan' => "{raison}. Durée de l'appel : {duree}. Merci d'avoir patienté.",
        'vous' => "Vous : {choix}",
    ],
];
