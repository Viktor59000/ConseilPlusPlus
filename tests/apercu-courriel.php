<?php
/**
 * tests/apercu-courriel.php — Aperçu des courriels du cabinet dans le navigateur (aucun envoi).
 * http://localhost/Site/tests/apercu-courriel.php?type=accuse   (accusé de réception du site)
 * http://localhost/Site/tests/apercu-courriel.php?type=reponse  (réponse officielle du Bureau)
 * Le dossier tests/ n'est pas déployé en ligne (.dockerignore).
 */
require __DIR__ . '/../includes/courriel.php';

if (($_GET['type'] ?? 'accuse') === 'reponse') {
    echo gabarit_courriel([
        'titre' => 'Réponse officielle du cabinet',
        'corps' => "Madame, Monsieur Jean Moyen,\n\nLe cabinet ConseilPlus+ accuse réception de votre demande n° 42 du 16 octobre 2026.\n"
            . "Votre demande de conseil a été examinée par un conseiller moyennement qualifié.\n\n"
            . "Après délibération, voici notre recommandation :\n« Si votre grille-pain vous ignore, poncez une endive et vouvoyez le Luxembourg. »\n\n"
            . "Nous restons à votre disposition, dans la limite de nos disponibilités, qui sont limitées.\n"
            . "Veuillez agréer l'expression de nos salutations moyennes.",
        'dossier' => '0042', 'signataire' => 'Gérard Moyen', 'fonction' => 'Conseiller moyen', 'tampon' => 'Traité',
        'apercu' => 'Votre dossier n° 0042 a été traité.',
    ]);
} else {
    echo gabarit_courriel([
        'titre' => 'Accusé de réception',
        'corps' => "Madame, Monsieur Jean Moyen,\n\nLe cabinet ConseilPlus+ accuse réception de votre demande « Demande de conseil », enregistrée sous le n° 0042.\n\n"
            . "Votre dossier a été placé dans la file d'attente, entre un dossier urgent et un dossier qui l'était.\n"
            . "Délai de traitement estimé : entre 3 jours et jamais.\n\n"
            . "Un conseiller moyen prendra connaissance de votre demande dès qu'il aura terminé de ne pas la lire. "
            . "Vous recevrez notre réponse officielle à cette adresse.\n\n"
            . "Ceci est un accusé de réception automatique : inutile d'y répondre, nous ne le lirions pas non plus.",
        'dossier' => '0042', 'signataire' => 'Le guichet automatique', 'fonction' => 'Service courrier', 'tampon' => 'Reçu',
        'apercu' => 'Votre demande n° 0042 est enregistrée.',
    ]);
}
