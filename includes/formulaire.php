<?php
/**
 * Petits outils d'affichage des formulaires : échappement, alertes, champs en erreur.
 * $erreurs : tableau champ => message ; le message d'un champ porte l'id « err-<champ> »
 * (ou « err-<id> » quand l'id est donné), relié au champ par aria-describedby.
 */

/** Texte échappé pour le HTML (mêmes options que htmlspecialchars() par défaut). */
function e(?string $texte): string
{
    return htmlspecialchars((string) $texte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Message en tête de formulaire. $type : erreur (lu tout de suite) ou ok (annoncé poliment). */
function alerte(string $texte, string $type = 'erreur'): string
{
    $role = $type === 'ok' ? 'status' : 'alert';
    return '<p class="alerte alerte-' . $type . '" role="' . $role . '">' . e($texte) . "</p>\n";
}

/** Classes du bloc d'un champ : « champ », plus « champ-erreur » si le champ est en erreur. */
function classe_champ(array $erreurs, string $champ, string $classe = 'champ'): string
{
    return $classe . (isset($erreurs[$champ]) ? ' champ-erreur' : '');
}

/** Attributs ARIA d'un champ en erreur (rien sinon). */
function aria_erreur(array $erreurs, string $champ, ?string $id = null): string
{
    return isset($erreurs[$champ]) ? ' aria-invalid="true" aria-describedby="err-' . ($id ?? $champ) . '"' : '';
}

/** Message d'erreur d'un champ (rien s'il n'y en a pas). */
function erreur_champ(array $erreurs, string $champ, ?string $id = null): string
{
    return isset($erreurs[$champ]) ? '<p class="champ-msg" id="err-' . ($id ?? $champ) . '">' . e($erreurs[$champ]) . "</p>\n" : '';
}
