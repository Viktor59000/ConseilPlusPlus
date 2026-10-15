<?php
/**
 * Notifications Web Push, sans dépendance : chiffrement du message (RFC 8291, aes128gcm)
 * et authentification du serveur (VAPID, RFC 8292), avec les fonctions OpenSSL de PHP.
 *
 * Les clés VAPID sont créées automatiquement au premier usage et gardées dans la table « reglages »
 * (jamais dans Git) : rien à configurer, en local comme en ligne.
 */

const PUSH_SUJET = 'https://conseilplus.onrender.com';
// Services de notification acceptés (un abonnement pointant ailleurs est refusé : pas d'envoi vers n'importe quelle URL)
const PUSH_HOTES = ['fcm.googleapis.com', 'updates.push.services.mozilla.com', 'web.push.apple.com'];
const PUSH_SUFFIXES = ['.push.services.mozilla.com', '.notify.windows.com', '.push.apple.com'];

function b64url(string $binaire): string
{
    return rtrim(strtr(base64_encode($binaire), '+/', '-_'), '=');
}

function b64url_decoder(string $texte): string
{
    return (string) base64_decode(strtr($texte, '-_', '+/') . str_repeat('=', (4 - strlen($texte) % 4) % 4));
}

/** Options OpenSSL pour une clé P-256 (sous Windows, PHP doit connaître son fichier openssl.cnf). */
function push_options_cle(): array
{
    $options = ['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC];
    foreach ([getenv('OPENSSL_CONF') ?: '', dirname((string) ini_get('extension_dir')) . '/extras/ssl/openssl.cnf'] as $cnf) {
        if ($cnf !== '' && is_file($cnf)) {
            return $options + ['config' => $cnf];
        }
    }
    return $options;
}

/** Point public brut (65 octets, 0x04 || X || Y) d'une clé P-256. */
function push_point_public($cle): string
{
    $ec = openssl_pkey_get_details($cle)['ec'];
    return "\x04" . str_pad($ec['x'], 32, "\0", STR_PAD_LEFT) . str_pad($ec['y'], 32, "\0", STR_PAD_LEFT);
}

/** Clé publique OpenSSL à partir d'un point brut P-256 (enveloppe SubjectPublicKeyInfo). */
function push_importer_point(string $point)
{
    $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $point;
    return openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64) . "-----END PUBLIC KEY-----\n");
}

/** Clés VAPID du site : ['prive' => clé OpenSSL, 'public' => point brut]. Créées une fois, puis relues. */
function push_cles_vapid(PDO $bdd): array
{
    $pem = $bdd->query("SELECT valeur FROM reglages WHERE cle = 'vapid_prive'")->fetchColumn();
    if (!$pem) {
        $cle = openssl_pkey_new(push_options_cle());
        if (!$cle) {
            throw new RuntimeException('Impossible de créer les clés VAPID : ' . openssl_error_string());
        }
        openssl_pkey_export($cle, $pem, null, push_options_cle());
        $bdd->prepare("INSERT IGNORE INTO reglages (cle, valeur) VALUES ('vapid_prive', ?)")->execute([$pem]);
        $pem = $bdd->query("SELECT valeur FROM reglages WHERE cle = 'vapid_prive'")->fetchColumn();
    }
    $prive = openssl_pkey_get_private($pem);
    return ['prive' => $prive, 'public' => push_point_public($prive)];
}

/** Vrai si l'adresse d'abonnement appartient à un service de notification connu. */
function push_endpoint_valide(string $endpoint): bool
{
    $p = parse_url($endpoint);
    if (($p['scheme'] ?? '') !== 'https' || empty($p['host'])) {
        return false;
    }
    $hote = strtolower($p['host']);
    if (in_array($hote, PUSH_HOTES, true)) {
        return true;
    }
    foreach (PUSH_SUFFIXES as $suffixe) {
        if (str_ends_with($hote, $suffixe)) {
            return true;
        }
    }
    return false;
}

/** Signature ES256 brute (r || s, 64 octets) à partir de la signature DER d'OpenSSL. */
function push_signature_brute(string $der): string
{
    $pos = 2; // 0x30, longueur (une signature P-256 tient sur une longueur courte)
    $entiers = [];
    for ($i = 0; $i < 2; $i++) {
        $longueur = ord($der[$pos + 1]);
        $entiers[] = str_pad(ltrim(substr($der, $pos + 2, $longueur), "\0"), 32, "\0", STR_PAD_LEFT);
        $pos += 2 + $longueur;
    }
    return $entiers[0] . $entiers[1];
}

/** En-tête Authorization VAPID pour un service de notification. */
function push_en_tete_vapid(array $vapid, string $endpoint): string
{
    $p = parse_url($endpoint);
    $entete = b64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
    $charge = b64url(json_encode(['aud' => $p['scheme'] . '://' . $p['host'], 'exp' => time() + 12 * 3600, 'sub' => PUSH_SUJET]));
    openssl_sign("$entete.$charge", $der, $vapid['prive'], OPENSSL_ALGO_SHA256);
    return 'vapid t=' . "$entete.$charge." . b64url(push_signature_brute($der)) . ', k=' . b64url($vapid['public']);
}

/** Chiffre un message pour un abonné (RFC 8291 : ECDH P-256 + HKDF + AES-128-GCM, un seul enregistrement). */
function push_chiffrer(string $message, string $p256dh, string $auth): string
{
    $ephemere = openssl_pkey_new(push_options_cle());
    $public_serveur = push_point_public($ephemere);
    $partage = openssl_pkey_derive(push_importer_point($p256dh), $ephemere, 32);
    if ($partage === false) {
        throw new RuntimeException('Clé de l\'abonné invalide.');
    }
    $ikm = hash_hkdf('sha256', $partage, 32, "WebPush: info\0" . $p256dh . $public_serveur, $auth);
    $sel = random_bytes(16);
    $cle = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $sel);
    $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $sel);
    $chiffre = openssl_encrypt($message . "\x02", 'aes-128-gcm', $cle, OPENSSL_RAW_DATA, $nonce, $tag);
    return $sel . pack('N', 4096) . chr(65) . $public_serveur . $chiffre . $tag;
}

/**
 * Envoie une notification à un abonné. $donnees : ['titre', 'corps', 'url'].
 * Retourne le code HTTP du service (201 = remise acceptée ; 404 / 410 = abonnement expiré).
 */
function push_envoyer(array $vapid, array $abonne, array $donnees): int
{
    $corps = push_chiffrer(json_encode($donnees, JSON_UNESCAPED_UNICODE),
        b64url_decoder($abonne['p256dh']), b64url_decoder($abonne['auth']));
    $ch = curl_init($abonne['endpoint']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $corps,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . push_en_tete_vapid($vapid, $abonne['endpoint']),
            'Content-Encoding: aes128gcm',
            'Content-Type: application/octet-stream',
            'TTL: 43200',
            'Urgency: normal',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CAINFO => __DIR__ . '/../certs/cacert.pem',
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code;
}
