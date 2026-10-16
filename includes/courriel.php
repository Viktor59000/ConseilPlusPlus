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
 * Courriel HTML complet aux couleurs du cabinet (charte : design-system/conseilplus/MASTER.md).
 * $o : titre, corps (texte brut : échappé et mis en paragraphes ici), dossier (« 0042 » ou null),
 *      signataire, fonction, tampon (« Traité », « Reçu »…), apercu (texte d'aperçu dans la boîte de réception).
 * Mise en page en tableaux et styles en ligne : seule façon d'être affiché pareil dans Gmail, Outlook et sur mobile.
 */
function gabarit_courriel(array $o): string
{
    $e = fn($t) => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');
    $site = url_publique();
    $domaine = $e(preg_replace('#^https?://#', '', $site));
    $encre = '#1B2440';
    $encre2 = '#525A73';
    $fond = '#F5F6FA';
    $filet = '#DDE1EC';
    $rouge = '#C8102E';
    $police = "'Schibsted Grotesk',Arial,Helvetica,sans-serif";
    $mono = "'JetBrains Mono',Consolas,'Courier New',monospace";

    // Corps : paragraphes séparés par une ligne vide, retours à la ligne conservés
    $paragraphes = '';
    foreach (preg_split('/\R{2,}/u', trim((string) $o['corps'])) as $p) {
        $paragraphes .= '<p style="margin:0 0 18px;font-family:' . $police . ';font-size:16px;line-height:1.65;color:' . $encre . '">'
            . nl2br($e($p)) . '</p>';
    }
    $dossier = ($o['dossier'] ?? null) ? 'Dossier n° ' . $e($o['dossier']) : 'Cabinet ConseilPlus+';

    return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<meta name="color-scheme" content="light"><meta name="supported-color-schemes" content="light"><title>' . $e($o['titre']) . '</title></head>'
        . '<body style="margin:0;padding:0;background:' . $fond . '">'
        // Texte d'aperçu (affiché par la messagerie à côté du sujet, invisible dans le courriel)
        . '<div style="display:none;max-height:0;overflow:hidden;opacity:0">' . $e($o['apercu'] ?? '') . '</div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:' . $fond . '"><tr><td align="center" style="padding:32px 12px">'

        // Logo, au-dessus de la carte
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px"><tr><td style="padding:0 8px 20px">'
        . '<a href="' . $site . '"><img src="' . $site . '/assets/email/logo.png" width="190" height="49" alt="ConseilPlus+, le conseiller moyen"'
            . ' style="display:block;border:0;width:190px;height:auto"></a></td></tr></table>'

        // La carte
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#FFFFFF;border-radius:20px;border:1px solid ' . $filet . '">'
        . '<tr><td style="padding:36px 40px 8px">'
        . '<p style="margin:0 0 14px;font-family:' . $mono . ';font-size:12px;letter-spacing:2px;text-transform:uppercase;color:' . $encre2 . '">'
            . '<span style="color:' . $rouge . '">&#8212;&nbsp;</span>' . $dossier . '</p>'
        . '<h1 style="margin:0 0 24px;font-family:' . $police . ';font-size:28px;line-height:1.15;font-weight:800;letter-spacing:-0.5px;color:' . $encre . '">'
            . $e($o['titre']) . '</h1>'
        . $paragraphes . '</td></tr>'

        // Signature : symbole C+, nom, fonction, adresse ; tampon à droite
        . '<tr><td style="padding:8px 40px 36px">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid ' . $filet . '"><tr>'
        . '<td width="56" valign="top" style="padding-top:20px"><img src="' . $site . '/assets/email/icone.png" width="44" height="44" alt=""'
            . ' style="display:block;border:0"></td>'
        . '<td valign="top" style="padding:20px 0 0 12px;font-family:' . $police . ';font-size:13px;line-height:1.55;color:' . $encre2 . '">'
            . '<strong style="font-size:15px;color:' . $encre . '">' . $e($o['signataire']) . '</strong><br>'
            . $e($o['fonction']) . ' · Cabinet ConseilPlus+<br>'
            . '12 bis, impasse du Consensus Mou, bâtiment C, escalier D<br>'
            . '<a href="' . $site . '" style="color:' . $encre . ';font-weight:bold;text-decoration:underline">' . $domaine . '</a>'
            . ' · <a href="' . $site . '/petit-moyen.php" style="color:' . $encre . ';text-decoration:underline">Le mot (presque) du jour</a></td>'
        . '<td valign="top" align="right" style="padding-top:22px">'
            . '<span style="display:inline-block;border:3px double ' . $rouge . ';border-radius:6px;color:' . $rouge . ';font-family:' . $mono
            . ';font-size:13px;font-weight:bold;letter-spacing:3px;text-transform:uppercase;padding:5px 12px;white-space:nowrap">' . $e($o['tampon'] ?? 'Traité') . '</span></td>'
        . '</tr></table></td></tr></table>'

        // Pied, sous la carte
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px"><tr>'
        . '<td style="padding:20px 8px 0;font-family:' . $police . ';font-size:12px;line-height:1.6;color:' . $encre2 . '">'
            . 'Aucun conseil délivré par ConseilPlus+ ne doit être suivi. Sérieusement.<br>'
            . 'Vous recevez ce courriel à la suite de votre demande sur ' . $domaine . '.</td></tr></table>'
        . '</td></tr></table></body></html>';
}

/**
 * Accusé de réception d'une demande de contact (envoyé par contact.php, prévisualisé par tests/apercu-courriel.php).
 * Retourne ['sujet', 'texte' (version sans HTML, signature comprise), 'html'].
 */
function accuse_reception(string $nom, string $type_demande, int $id): array
{
    require_once __DIR__ . '/textes.php'; // delai_estime()
    $numero = sprintf('%04d', $id);
    $corps = "Madame, Monsieur $nom,\n\n"
        . "Le cabinet ConseilPlus+ accuse réception de votre demande « $type_demande », enregistrée sous le n° $numero.\n\n"
        . "Votre dossier a été placé dans la file d'attente, entre un dossier urgent et un dossier qui l'était.\n"
        . 'Délai de traitement estimé : ' . delai_estime($id) . ".\n\n"
        . "Un conseiller moyen prendra connaissance de votre demande dès qu'il aura terminé de ne pas la lire. "
        . "Vous recevrez notre réponse officielle à cette adresse.\n\n"
        . "Ceci est un accusé de réception automatique : inutile d'y répondre, nous ne le lirions pas non plus.";
    return [
        'sujet' => "Votre demande n° $numero — accusé de réception du cabinet ConseilPlus+",
        'texte' => $corps . signature_texte('Le guichet automatique', 'Service courrier'),
        'html' => gabarit_courriel([
            'titre' => 'Accusé de réception',
            'corps' => $corps,
            'dossier' => $numero,
            'signataire' => 'Le guichet automatique',
            'fonction' => 'Service courrier',
            'tampon' => 'Reçu',
            'apercu' => "Votre demande n° $numero est enregistrée. Elle sera traitée, un jour.",
        ]),
    ];
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
