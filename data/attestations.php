<?php
// Modèles des attestations officielles (attestation.php). {nom}, {date} et {heure} sont remplacés dans le navigateur.
// Formulations neutres : le document ne suppose ni le genre ni rien d'autre de la personne.
return [
    'conseil-non-suivi' => [
        'titre' => 'Attestation de conseil non suivi',
        'texte' => "Le cabinet ConseilPlus+ atteste que {nom} a reçu un conseil officiel et ne l'a pas suivi, conformément à nos recommandations.",
        'mention' => 'Aucun conseil délivré par ConseilPlus+ ne doit être suivi.',
    ],
    'client-moyen' => [
        'titre' => 'Certificat de client moyen',
        'texte' => "Après examen attentif, {nom} obtient le statut de client moyen, ni plus ni moins, avec effet immédiat et sans date de fin.",
        'mention' => 'Taux de moyenneté constaté : 50 %.',
    ],
    'guichet' => [
        'titre' => 'Justificatif de passage au guichet',
        'texte' => "Le cabinet certifie que {nom} a pris un ticket au guichet n° 7 le {date}, et que le guichet a fermé exactement à son tour.",
        'mention' => 'Réouverture prévue à 14 h 02, sous réserve.',
    ],
    'patience' => [
        'titre' => 'Diplôme de patience administrative',
        'texte' => "{nom} a patienté au standard du cabinet sans raccrocher, jusqu'à la machine à café incluse. Le jury salue cette endurance.",
        'mention' => 'Mention : moyennement bien.',
    ],
    'presence' => [
        'titre' => 'Attestation de présence',
        'texte' => "Le cabinet atteste que {nom} était bien sur le site de ConseilPlus+ le {date} à {heure}. Le cabinet ne voit pas d'autre explication.",
        'mention' => 'Présence constatée par le tampon lui-même.',
    ],
    'prediction' => [
        'titre' => 'Certificat de prédiction vérifiée',
        'texte' => "Messmerde, mentaliste du cabinet, avait prédit que {nom} demanderait ce certificat. La prédiction s'est réalisée le {date}.",
        'mention' => 'Prévu.',
    ],
];
