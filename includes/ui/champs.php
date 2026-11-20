<?php
/**
 * Champ de formulaire : libellé visible au-dessus, aide facultative, message d'erreur sous le champ.
 * Reprend la convention de includes/formulaire.php : $erreurs = [nom du champ => message], le message porte
 * l'id « err-<id> » et le champ en erreur reçoit aria-invalid="true" et aria-describedby (aide + erreur).
 * Erreurs vérifiées à l'envoi, côté serveur (jamais pendant la saisie) ; la page met le focus / l'alerte en tête.
 */

/**
 * Champ (input, textarea, select ou case à cocher).
 *   champ('courriel', 'Adresse électronique', $erreurs, ['type' => 'email', 'valeur' => $courriel, 'requis' => true,
 *         'autocomplete' => 'email'])
 *   champ('message', 'Votre problème, en une phrase', $erreurs, ['type' => 'textarea', 'aide' => 'Le cabinet lit vite.'])
 *   champ('objet', 'Objet', $erreurs, ['type' => 'select', 'options' => ['tomates' => 'Tomates', 'autre' => 'Autre']])
 * Options :
 *   type          text (défaut) | email | password | number | tel | search | url | textarea | select | case
 *   valeur        valeur actuelle (pour une case : cochée si vraie)
 *   options       pour select : valeur => libellé
 *   aide          phrase d'aide sous le libellé
 *   requis        bool (required + « (obligatoire) » dans le libellé)
 *   officiel      bool : saisie en police machine (formulaires du guichet)
 *   id            défaut : $nom ; autocomplete, inputmode, placeholder, maxlength, rows
 *   attributs     autres attributs du champ (ils remplacent ceux par défaut : ['name' => null] = jamais envoyé) ; classe : classes du bloc
 */
function champ(string $nom, string $libelle, array $erreurs = [], array $o = []): string
{
    $type = $o['type'] ?? 'text';
    $id = $o['id'] ?? $nom;
    $erreur = $erreurs[$nom] ?? null;
    $decrit = array_filter([isset($o['aide']) ? 'aide-' . $id : null, $erreur !== null ? 'err-' . $id : null]);
    // Les attributs passés remplacent ceux par défaut (ex. 'name' => null : champ jamais envoyé)
    $attributs = array_merge([
        'class' => 'champ__saisie', 'id' => $id, 'name' => $nom,
        'required' => !empty($o['requis']),
        'aria-invalid' => $erreur !== null ? 'true' : null,
        'aria-describedby' => $decrit ? implode(' ', $decrit) : null,
        'autocomplete' => $o['autocomplete'] ?? null, 'inputmode' => $o['inputmode'] ?? null,
        'placeholder' => $o['placeholder'] ?? null, 'maxlength' => $o['maxlength'] ?? null,
    ], $o['attributs'] ?? []);

    if ($type === 'textarea') {
        $saisie = '<textarea' . ui_attributs($attributs + ['rows' => $o['rows'] ?? 5]) . '>' . e((string) ($o['valeur'] ?? '')) . '</textarea>';
    } elseif ($type === 'select') {
        $saisie = '<select' . ui_attributs($attributs) . '>';
        foreach ($o['options'] ?? [] as $valeur => $texte) {
            $saisie .= '<option value="' . e((string) $valeur) . '"' . ((string) $valeur === (string) ($o['valeur'] ?? '') ? ' selected' : '') . '>' . e($texte) . '</option>';
        }
        $saisie .= '</select>';
    } elseif ($type === 'case') {
        $saisie = '<input' . ui_attributs(['type' => 'checkbox', 'value' => '1', 'checked' => !empty($o['valeur'])] + $attributs) . '>';
    } else {
        $saisie = '<input' . ui_attributs(['type' => $type, 'value' => $type === 'password' ? null : ($o['valeur'] ?? null)] + $attributs) . '>';
    }

    $libelle_html = '<label class="champ__libelle" for="' . e($id) . '">' . e($libelle)
        . (!empty($o['requis']) ? ' <span class="champ__requis">(obligatoire)</span>' : '') . '</label>';
    $aide = isset($o['aide']) ? '<p class="champ__aide" id="aide-' . e($id) . '">' . e($o['aide']) . '</p>' : '';
    $message = $erreur !== null ? '<p class="champ__erreur" id="err-' . e($id) . '">' . e($erreur) . '</p>' : '';
    $classes = ui_classes('champ', $type === 'case' ? 'champ--case' : '', !empty($o['officiel']) ? 'champ--officiel' : '',
        $erreur !== null ? 'champ--erreur' : '', $o['classe'] ?? '');

    // Case à cocher : la case avant son libellé ; sinon libellé, aide, saisie, erreur
    return '<div class="' . $classes . '">'
        . ($type === 'case' ? $saisie . $libelle_html . $aide . $message : $libelle_html . $aide . $saisie . $message)
        . '</div>';
}
