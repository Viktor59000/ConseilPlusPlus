<?php
/**
 * Règles métier des demandes de contact (partagées par l'API, le formulaire de contact et l'espace client).
 */
const TYPES_DEMANDE = ['Demande de conseil', 'Réclamation', 'Devis Premium', 'Autre'];
// nouveau : déposée sur le site · importe : relevée par Le Bureau · repondu : le cabinet a répondu (réponse lisible par le client)
const STATUTS = ['nouveau', 'importe', 'repondu'];
const LIBELLES_STATUT = ['nouveau' => 'Déposée au guichet', 'importe' => 'En cours d\'examen', 'repondu' => 'Réponse du cabinet'];
const REPONSE_MAX = 5000;

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

/**
 * Valide un changement de statut (PATCH de l'API). « repondu » exige la réponse du cabinet.
 * Retourne [$statut, $reponse (null hors « repondu »), $erreurs].
 */
function valider_statut(array $corps): array
{
    $statut = $corps['statut'] ?? 'importe';
    $reponse = is_string($corps['reponse'] ?? null) ? trim($corps['reponse']) : '';
    $erreurs = [];
    if (!in_array($statut, STATUTS, true)) {
        $erreurs['statut'] = 'Statut invalide (' . implode(', ', STATUTS) . ').';
    } elseif ($statut === 'repondu' && ($reponse === '' || mb_strlen($reponse) > REPONSE_MAX)) {
        $erreurs['reponse'] = 'Réponse du cabinet obligatoire avec le statut « repondu » (' . REPONSE_MAX . ' caractères au plus).';
    }
    return [$statut, $statut === 'repondu' ? $reponse : null, $erreurs];
}

/** Rattache une demande au client connecté qui vient de l'envoyer (jamais d'après l'adresse email). */
function rattacher_contact(PDO $bdd, int $id, int $utilisateur): void
{
    $bdd->prepare('UPDATE contacts SET id_utilisateur = ? WHERE id = ? AND id_utilisateur IS NULL')->execute([$utilisateur, $id]);
}

/** Demandes d'un client (« Mes demandes »), les plus récentes d'abord. */
function demandes_du_client(PDO $bdd, int $utilisateur): array
{
    $stmt = $bdd->prepare('SELECT id, type_demande, message, statut, date_creation, reponse, date_reponse FROM contacts
                           WHERE id_utilisateur = ? ORDER BY date_creation DESC, id DESC');
    $stmt->execute([$utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** Supprime une demande du client (seulement si elle est à lui). Retourne vrai si elle a été supprimée. */
function supprimer_demande_client(PDO $bdd, int $id, int $utilisateur): bool
{
    $stmt = $bdd->prepare('DELETE FROM contacts WHERE id = ? AND id_utilisateur = ?');
    $stmt->execute([$id, $utilisateur]);
    return $stmt->rowCount() > 0;
}
