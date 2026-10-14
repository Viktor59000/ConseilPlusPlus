<?php
/**
 * Règles métier des demandes de contact (partagées par l'API).
 */
const TYPES_DEMANDE = ['Demande de conseil', 'Réclamation', 'Devis Premium', 'Autre'];
const STATUTS = ['nouveau', 'importe'];

/**
 * Nettoie et valide une demande.
 * Retourne [$valeurs, $erreurs] ; $erreurs est vide si tout est correct.
 */
function valider_contact(array $source): array
{
    $valeurs = [];
    foreach (['nom', 'email', 'message', 'type_demande'] as $champ) {
        $valeurs[$champ] = trim((string) ($source[$champ] ?? ''));
    }

    $erreurs = [];
    if ($valeurs['nom'] === '' || mb_strlen($valeurs['nom']) > 100) {
        $erreurs['nom'] = 'Indiquez votre nom (100 caractères maximum).';
    }
    if (!filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($valeurs['email']) > 150) {
        $erreurs['email'] = 'Indiquez une adresse email valide.';
    }
    if (!in_array($valeurs['type_demande'], TYPES_DEMANDE, true)) {
        $erreurs['type_demande'] = 'Choisissez un type de demande.';
    }
    if (mb_strlen($valeurs['message']) < 10) {
        $erreurs['message'] = 'Votre message doit faire au moins 10 caractères.';
    }

    return [$valeurs, $erreurs];
}
