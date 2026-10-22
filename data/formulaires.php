<?php
// Le formulaire du jour, remis par Bertrand avant toute attestation (attestation.php) : un par jour, tiré par date
// (formulaire_du_jour(), includes/textes.php). Rien n'est envoyé au serveur : les cases restent dans le navigateur.
// Types de case : texte, nombre, date, choix (cases à cocher), reserve (cadre à ne pas remplir).
// {bilan} et {code} sont remplacés dans le navigateur (js/attestation.js). Contenu original.
return [
    'formulaires' => [
        ['code' => 'B-12', 'titre' => "Demande préalable d'attestation", 'cases' => [
            ['n' => '1', 'type' => 'texte', 'libelle' => "Nom, tel qu'il figurera sur l'attestation, sans le nom."],
            ['n' => '4', 'type' => 'choix', 'libelle' => 'Vous remplissez ce formulaire :', 'options' => ['De votre plein gré', 'À la demande de Bertrand', 'Les deux, mais pas en même temps']],
            ['n' => '7 bis', 'type' => 'texte', 'libelle' => 'À remplir uniquement si la case 7 bis est déjà remplie.'],
            ['n' => '9', 'type' => 'reserve', 'libelle' => "Cadre réservé à l'administration. L'administration ne le remplit pas non plus."],
        ]],
        ['code' => 'C-7', 'titre' => 'Déclaration sur l\'honneur de non-déclaration', 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => "Je déclare sur l'honneur :", 'options' => ["N'avoir rien à déclarer", 'Avoir quelque chose à ne pas déclarer']],
            ['n' => '2', 'type' => 'texte', 'libelle' => 'Ce que vous ne déclarez pas (facultatif, mais obligatoire).'],
            ['n' => '3', 'type' => 'date', 'libelle' => "Date de la présente déclaration, si elle diffère d'aujourd'hui."],
            ['n' => '5', 'type' => 'reserve', 'libelle' => 'Signature du déclarant. Ne pas signer ici : la case est trop petite.'],
        ]],
        ['code' => 'A-0', 'titre' => "Formulaire d'attente", 'cases' => [
            ['n' => 'A', 'type' => 'nombre', 'libelle' => "Nombre de minutes déjà attendues (arrondir à l'attente supérieure)."],
            ['n' => 'B', 'type' => 'choix', 'libelle' => 'Vous avez attendu :', 'options' => ['Assis', 'Debout', "Dans l'ordre inverse"]],
            ['n' => 'C', 'type' => 'texte', 'libelle' => 'Ce que vous attendiez, si vous vous en souvenez.'],
        ]],
        ['code' => 'D-3 ter', 'titre' => 'Fiche de renseignements non renseignés', 'cases' => [
            ['n' => '1', 'type' => 'texte', 'libelle' => 'Renseignement que vous ne souhaitez pas fournir.'],
            ['n' => '2', 'type' => 'choix', 'libelle' => 'Ce renseignement est :', 'options' => ['Exact', 'Inexact', 'Exactement inexact']],
            ['n' => '3', 'type' => 'texte', 'libelle' => 'Écrire ici en capitales minuscules.'],
            ['n' => '4', 'type' => 'reserve', 'libelle' => 'Case barrée par erreur. Ne pas remplir, ne pas débarrer.'],
        ]],
        ['code' => 'E-21', 'titre' => 'Demande de dérogation à la demande', 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => 'Vous sollicitez :', 'options' => ['Une dérogation', 'Une dérogation à la dérogation', 'Rien, à titre dérogatoire']],
            ['n' => '2', 'type' => 'texte', 'libelle' => 'Motif de la dérogation (deux mots au plus, trois si nécessaire).'],
            ['n' => '2 bis', 'type' => 'texte', 'libelle' => 'Reprendre ici le motif de la case 2, sans le recopier.'],
        ]],
        ['code' => 'F-9', 'titre' => "Attestation préalable d'aptitude à recevoir une attestation", 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => 'Avez-vous déjà reçu une attestation ?', 'options' => ['Oui', 'Non', 'Pas encore, mais depuis longtemps']],
            ['n' => '2', 'type' => 'choix', 'libelle' => 'Si vous avez coché « Non » en case 1, cochez « Oui » ici.', 'options' => ['Oui']],
            ['n' => '3', 'type' => 'nombre', 'libelle' => "Nombre d'attestations que vous pensez mériter (entre 1 et 1)."],
            ['n' => '6', 'type' => 'reserve', 'libelle' => "Les cases 4 et 5 ont été abrogées par l'article 247. Elles restent applicables."],
        ]],
        ['code' => 'G-14', 'titre' => 'Questionnaire de satisfaction anticipée', 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => 'Vous serez satisfait du service :', 'options' => ['Très', 'Moyennement', "Plutôt avant qu'après"]],
            ['n' => '2', 'type' => 'texte', 'libelle' => "Ce que vous auriez aimé qu'on vous dise, si vous l'aviez demandé."],
            ['n' => '3', 'type' => 'choix', 'libelle' => 'Recommanderiez-vous ce formulaire à un proche ?', 'options' => ['Oui', 'Non', 'À un proche éloigné']],
        ]],
        ['code' => 'H-2', 'titre' => 'Formulaire de rectification du formulaire', 'cases' => [
            ['n' => '1', 'type' => 'texte', 'libelle' => 'Erreur relevée dans le formulaire H-1 (qui ne vous a pas été remis).'],
            ['n' => '2', 'type' => 'texte', 'libelle' => 'Correction proposée, sans rien corriger.'],
            ['n' => '3', 'type' => 'choix', 'libelle' => 'Vous reconnaissez avoir été informé :', 'options' => ['Oui', 'Oui, mais tardivement', 'Par le présent formulaire']],
            ['n' => '4', 'type' => 'reserve', 'libelle' => 'Visa du gardien du règlement intérieur (apposé par avance).'],
        ]],
        ['code' => 'K-5', 'titre' => "Déclaration de présence à l'accueil", 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => 'Vous êtes actuellement :', 'options' => ["À l'accueil", 'Ailleurs', "En route vers l'accueil, depuis l'accueil"]],
            ['n' => '2', 'type' => 'texte', 'libelle' => "Heure d'arrivée prévue (pour une personne déjà arrivée)."],
            ['n' => '3', 'type' => 'choix', 'libelle' => 'Vous êtes venu :', 'options' => ['Seul', 'Accompagné', 'Accompagné de vous-même']],
            ['n' => '3 bis', 'type' => 'texte', 'libelle' => "À remplir uniquement par les personnes qui n'ont pas lu la case 3 bis."],
        ]],
        ['code' => 'L-44', 'titre' => "Demande d'accès à la case suivante", 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => "Je sollicite l'accès à la case 2.", 'options' => ['Je sollicite']],
            ['n' => '2', 'type' => 'texte', 'libelle' => "Accès accordé. Inscrire ici votre demande d'accès à la case 3."],
            ['n' => '3', 'type' => 'reserve', 'libelle' => "Accès en cours d'instruction. Délai estimé : entre 3 jours et jamais."],
        ]],
        ['code' => 'M-8', 'titre' => 'Inventaire des poches', 'cases' => [
            ['n' => '1', 'type' => 'nombre', 'libelle' => 'Nombre de poches de votre tenue actuelle (poches intérieures en option).'],
            ['n' => '2', 'type' => 'texte', 'libelle' => "Contenu de la poche la plus à gauche, vue de l'intérieur."],
            ['n' => '3', 'type' => 'choix', 'libelle' => 'Vos poches sont :', 'options' => ['Pleines', 'Vides', 'Pleines de vide']],
        ]],
        ['code' => 'N-1', 'titre' => 'Formulaire relatif au formulaire du jour', 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => 'Avez-vous reçu le formulaire du jour ?', 'options' => ["Oui, c'est celui-ci", "Non, c'est celui-ci"]],
            ['n' => '2', 'type' => 'texte', 'libelle' => "Code du présent formulaire (il figure en tête, mais Bertrand préfère le lire de votre main)."],
            ['n' => '3', 'type' => 'choix', 'libelle' => 'Cochez cette case uniquement si vous ne cochez aucune case.', 'options' => ['Je ne coche aucune case']],
        ]],
        ['code' => 'P-17', 'titre' => "Déclaration d'intention de remplir", 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => "J'ai l'intention de remplir le présent formulaire.", 'options' => ['Oui', 'Non']],
            ['n' => '2', 'type' => 'texte', 'libelle' => 'Si vous avez coché « Non », expliquez pourquoi vous remplissez cette case.'],
            ['n' => '3', 'type' => 'date', 'libelle' => 'Date à laquelle cette intention vous est venue (approximative, mais précise).'],
            ['n' => '4', 'type' => 'reserve', 'libelle' => 'Espace laissé intentionnellement vide. Merci de respecter son intention.'],
        ]],
        ['code' => 'R-6', 'titre' => 'Attestation de lecture du règlement intérieur', 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => "J'ai lu le règlement intérieur :", 'options' => ['En entier', 'En partie', "Sauf l'article 4"]],
            ['n' => '2', 'type' => 'nombre', 'libelle' => "Numéro de l'article que vous préférez (412 au plus)."],
            ['n' => '3', 'type' => 'texte', 'libelle' => "Citez l'article 4 de mémoire (il est interdit de le lire)."],
        ]],
        ['code' => 'S-30', 'titre' => 'Contestation du tampon', 'cases' => [
            ['n' => '1', 'type' => 'texte', 'libelle' => 'Objet de la contestation.'],
            ['n' => '2', 'type' => 'choix', 'libelle' => 'Vous contestez :', 'options' => ['Le tampon', "L'encre du tampon", "L'inclinaison du tampon"]],
            ['n' => '3', 'type' => 'reserve', 'libelle' => 'Réponse du tampon : « Validé ». Elle est jointe d\'office à votre contestation.'],
        ]],
        ['code' => 'T-11', 'titre' => "Fiche d'identification de l'instrument d'écriture", 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => 'Vous remplissez ce formulaire avec :', 'options' => ['Un stylo', 'Un crayon', 'Un clavier, assimilé à un crayon']],
            ['n' => '2', 'type' => 'choix', 'libelle' => "Couleur de l'encre :", 'options' => ['Bleue', 'Noire', 'Celle du formulaire']],
            ['n' => '3', 'type' => 'texte', 'libelle' => 'Indiquer ici la couleur de la présente case.'],
            ['n' => '4', 'type' => 'reserve', 'libelle' => "L'encre rouge est réservée au tampon."],
        ]],
        ['code' => 'V-0', 'titre' => 'Renonciation au droit de renoncer', 'cases' => [
            ['n' => '1', 'type' => 'choix', 'libelle' => 'Je renonce :', 'options' => ['À renoncer', 'À ne pas renoncer', 'À choisir']],
            ['n' => '2', 'type' => 'texte', 'libelle' => 'Lieu de la renonciation (le cabinet accepte « ici »).'],
            ['n' => '3', 'type' => 'choix', 'libelle' => "Je certifie exacts les renseignements portés dans les cases que je n'ai pas remplies.", 'options' => ['Je certifie']],
        ]],
    ],

    // Constat de Bertrand, affiché avec l'attestation : le tampon valide quoi qu'il arrive
    'verdicts' => [
        'Bertrand a examiné le formulaire {code} : {bilan}. Conforme.',
        'Formulaire {code} vérifié : {bilan}. Les cases vides ont été jugées remplies de bonne foi. Conforme.',
        "Bertrand a relu le formulaire {code} deux fois, dont une à l'envers : {bilan}. Rien à signaler.",
        "Formulaire {code} : {bilan}, soit exactement le nombre attendu aujourd'hui. Conforme.",
        "Formulaire {code} : {bilan}. L'article 96 s'applique : le tampon a validé.",
    ],
];
