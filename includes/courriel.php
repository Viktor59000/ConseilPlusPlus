<?php
/**
 * Courriels du cabinet ConseilPlus+ : gabarit HTML (logo, cartouche du dossier, signature, tampon)
 * et envoi par l'API HTTPS de Brevo (Render bloque le SMTP sur l'offre gratuite).
 * Partagé par le site (accusé de réception du formulaire de contact) et par Le Bureau (réponses officielles).
 * Les fonctions reçoivent leurs réglages en paramètres : chaque application garde sa propre configuration.
 *
 * Gabarit compatible avec les messageries (Gmail, Outlook, mobile) : tableaux, styles en ligne,
 * images en PNG hébergées sur le site (le SVG n'est pas affiché par la plupart des messageries).
 */

/** Adresse publique du site, pour les liens et les images des courriels (jamais localhost). */
function url_publique(): string
{
    return rtrim(getenv('URL_PUBLIQUE') ?: 'https://conseilplus.onrender.com', '/');
}

/** Signature en texte brut (version sans HTML du courriel). */
function signature_texte(string $signataire, string $fonction): string
{
    return "\n\n--\n$signataire\n$fonction\nCabinet ConseilPlus+, le conseiller moyen\n"
        . "12 bis, impasse du Consensus Mou, bâtiment C, escalier D, au fond à gauche\n"
        . url_publique() . "\n\nAucun conseil délivré par ConseilPlus+ ne doit être suivi. Sérieusement.";
}

/**
 * Courriel HTML complet aux couleurs du cabinet.
 * $o : titre, corps (texte brut : échappé et mis en paragraphes ici), dossier (« 0042 » ou null),
 *      signataire, fonction, tampon (« Traité », « Reçu »…), apercu (texte d'aperçu dans la boîte de réception).
 */
function gabarit_courriel(array $o): string
{
    $e = fn($t) => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');
    $site = url_publique();
    $encre = '#1B2440';
    $douce = '#4A5270';
    $papier = '#F4EFE4';
    $carte = '#FFFDF7';
    $rouge = '#C8102E';
    $jaune = '#FFE14D';
    $titre_police = "'Arial Black',Arial,Helvetica,sans-serif";
    $texte_police = 'Arial,Helvetica,sans-serif';
    $mono = "'Courier New',Courier,monospace";

    // Corps : paragraphes séparés par une ligne vide, retours à la ligne conservés
    $paragraphes = '';
    foreach (preg_split('/\R{2,}/u', trim((string) $o['corps'])) as $p) {
        $paragraphes .= '<p style="margin:0 0 16px;font-family:' . $texte_police . ';font-size:15px;line-height:1.6;color:' . $encre . '">'
            . nl2br($e($p)) . '</p>';
    }

    $cartouche = $o['dossier'] ?? null
        ? '<td align="right" style="font-family:' . $mono . ';font-size:12px;color:' . $douce . ';white-space:nowrap">Dossier n° '
            . $e($o['dossier']) . '</td>'
        : '<td></td>';

    return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<meta name="color-scheme" content="light"><title>' . $e($o['titre']) . '</title></head>'
        . '<body style="margin:0;padding:0;background:' . $papier . '">'
        // Texte d'aperçu (affiché par la messagerie à côté du sujet, invisible dans le courriel)
        . '<div style="display:none;max-height:0;overflow:hidden;opacity:0">' . $e($o['apercu'] ?? '') . '</div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:' . $papier . '"><tr><td align="center" style="padding:24px 12px">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:' . $carte
            . ';border:2px solid ' . $encre . '">'

        // En-tête : logo + numéro de dossier
        . '<tr><td style="padding:20px 28px;border-bottom:2px solid ' . $encre . '">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
        . '<td><a href="' . $site . '"><img src="' . $site . '/assets/email/logo.png" width="240" height="57" alt="ConseilPlus+, le conseiller moyen"'
            . ' style="display:block;border:0;width:240px;height:auto"></a></td>'
        . $cartouche . '</tr></table></td></tr>'

        // Titre surligné
        . '<tr><td style="padding:28px 28px 8px">'
        . '<p style="margin:0 0 20px;font-family:' . $titre_police . ';font-size:22px;line-height:1.25;color:' . $encre . '">'
            . '<span style="background:' . $jaune . ';padding:0 4px">' . $e($o['titre']) . '</span></p>'
        . $paragraphes . '</td></tr>'

        // Signature
        . '<tr><td style="padding:8px 28px 28px">'
        . '<table role="presentation" cellpadding="0" cellspacing="0" style="border-top:1px dashed ' . $encre . ';padding-top:16px;width:100%"><tr>'
        . '<td width="60" valign="top" style="padding-top:16px"><img src="' . $site . '/assets/email/icone.png" width="48" height="48" alt=""'
            . ' style="display:block;border:2px solid ' . $encre . '"></td>'
        . '<td valign="top" style="padding:16px 0 0 14px;font-family:' . $texte_police . ';font-size:13px;line-height:1.5;color:' . $douce . '">'
            . '<strong style="font-family:' . $titre_police . ';font-size:15px;color:' . $encre . ';font-weight:normal">' . $e($o['signataire']) . '</strong><br>'
            . $e($o['fonction']) . ' · Cabinet ConseilPlus+<br>'
            . '12 bis, impasse du Consensus Mou, bâtiment C, escalier D<br>'
            . '<a href="' . $site . '" style="color:' . $encre . ';font-weight:bold">' . $e(preg_replace('#^https?://#', '', $site)) . '</a>'
            . ' · <a href="' . $site . '/petit-moyen.php" style="color:' . $encre . '">Le mot (presque) du jour</a></td>'
        . '<td valign="top" align="right" style="padding-top:16px">'
            . '<span style="display:inline-block;border:3px double ' . $rouge . ';color:' . $rouge . ';font-family:' . $mono
            . ';font-size:13px;font-weight:bold;letter-spacing:3px;text-transform:uppercase;padding:4px 10px">' . $e($o['tampon'] ?? 'Traité') . '</span></td>'
        . '</tr></table></td></tr>'

        // Pied
        . '<tr><td style="padding:14px 28px;background:' . $encre . ';font-family:' . $mono . ';font-size:11px;line-height:1.5;color:#D9D4C7">'
            . 'Aucun conseil délivré par ConseilPlus+ ne doit être suivi. Sérieusement.<br>'
            . 'Vous recevez ce courriel à la suite de votre demande sur ' . $e(preg_replace('#^https?://#', '', $site)) . '.</td></tr>'
        . '</table></td></tr></table></body></html>';
}

/**
 * Envoie un courriel par l'API de Brevo. Retourne [true, ''] si l'envoi est accepté, sinon [false, raison lisible].
 */
function envoyer_courriel(string $cle_api, string $expediteur, string $expediteur_nom, string $destinataire, string $destinataire_nom,
                          string $sujet, string $texte, string $html): array
{
    if ($cle_api === '' || !filter_var($expediteur, FILTER_VALIDATE_EMAIL) || !filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
        return [false, 'envoi par email non configuré'];
    }
    $corps = [
        'sender' => ['name' => $expediteur_nom, 'email' => $expediteur],
        'to' => [['email' => $destinataire, 'name' => $destinataire_nom]],
        'replyTo' => ['email' => $expediteur, 'name' => $expediteur_nom],
        'subject' => $sujet,
        'textContent' => $texte,
        'htmlContent' => $html,
    ];
    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($corps, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => ['api-key: ' . $cle_api, 'Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CAINFO => __DIR__ . '/../certs/cacert.pem',
    ]);
    $reponse = curl_exec($ch);
    $code = $reponse === false ? 0 : curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300) {
        return [true, ''];
    }
    $message = json_decode((string) $reponse, true)['message'] ?? ($code ? "erreur $code" : 'service injoignable');
    error_log("ConseilPlus+ : envoi Brevo impossible ($code) : $message");
    return [false, $message];
}
