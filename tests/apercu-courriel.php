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
    // Le vrai courriel envoyé par contact.php, pour une demande fictive n° 42
    echo accuse_reception('Jean Moyen', 'Demande de conseil', 42)['html'];
}
