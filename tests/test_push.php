<?php
/**
 * tests/test_push.php — Vérifie le Web Push maison (includes/push.php) sans service extérieur :
 * un « navigateur » simulé déchiffre le message selon la RFC 8291, la signature VAPID est vérifiée
 * avec la clé publique, et seuls les services de notification connus sont acceptés.
 *
 * Usage : php tests/test_push.php
 */
require __DIR__ . '/../includes/push.php';

$echecs = 0;
function verifier(string $libelle, bool $ok): void
{
    global $echecs;
    echo ($ok ? '[OK] ' : '[ÉCHEC] ') . $libelle . "\n";
    $echecs += $ok ? 0 : 1;
}

// 1. Chiffrement : le navigateur simulé retrouve le message
$navigateur = openssl_pkey_new(push_options_cle());
$p256dh = push_point_public($navigateur);
$auth = random_bytes(16);
$message = json_encode(['titre' => 'Le mot (presque) du jour : Mirobolant', 'corps' => 'Miro, bol, an.'], JSON_UNESCAPED_UNICODE);
$corps = push_chiffrer($message, $p256dh, $auth);

$sel = substr($corps, 0, 16);
$taille = unpack('N', substr($corps, 16, 4))[1];
$longueur_id = ord($corps[20]);
$public_serveur = substr($corps, 21, $longueur_id);
$chiffre = substr($corps, 21 + $longueur_id, -16);
$tag = substr($corps, -16);
$partage = openssl_pkey_derive(push_importer_point($public_serveur), $navigateur, 32);
$ikm = hash_hkdf('sha256', $partage, 32, "WebPush: info\0" . $p256dh . $public_serveur, $auth);
$cle = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $sel);
$nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $sel);
$clair = openssl_decrypt($chiffre, 'aes-128-gcm', $cle, OPENSSL_RAW_DATA, $nonce, $tag);

verifier('en-tête aes128gcm (taille 4096, clé de 65 octets)', $taille === 4096 && $longueur_id === 65 && $public_serveur[0] === "\x04");
verifier('le navigateur déchiffre le message', $clair === $message . "\x02");

// 2. VAPID : le jeton est signé par la clé du site
$vapid = ['prive' => openssl_pkey_new(push_options_cle())];
$vapid['public'] = push_point_public($vapid['prive']);
$entete = push_en_tete_vapid($vapid, 'https://fcm.googleapis.com/fcm/send/abc123');
preg_match('/^vapid t=([^.]+)\.([^.]+)\.([^,]+), k=(.+)$/', $entete, $m);
$sig = b64url_decoder($m[3]);
$der_entier = fn(string $n) => (ord(ltrim($n, "\0")[0] ?? "\0") > 127 ? "\0" : '') . ltrim($n, "\0");
[$r, $s] = [$der_entier(substr($sig, 0, 32)), $der_entier(substr($sig, 32))];
$der = "\x30" . chr(4 + strlen($r) + strlen($s)) . "\x02" . chr(strlen($r)) . $r . "\x02" . chr(strlen($s)) . $s;
$charge = json_decode(b64url_decoder($m[2]), true);
verifier('signature VAPID valide (ES256)', openssl_verify("$m[1].$m[2]", $der, push_importer_point($vapid['public']), OPENSSL_ALGO_SHA256) === 1);
verifier('audience = origine du service de notification', $charge['aud'] === 'https://fcm.googleapis.com');
verifier('clé publique annoncée = clé du site', b64url_decoder($m[4]) === $vapid['public']);

// 3. Seuls les services de notification connus sont acceptés
verifier('FCM (Chrome, Android) accepté', push_endpoint_valide('https://fcm.googleapis.com/fcm/send/xyz'));
verifier('Mozilla accepté', push_endpoint_valide('https://updates.push.services.mozilla.com/wpush/v2/xyz'));
verifier('Apple accepté', push_endpoint_valide('https://web.push.apple.com/QGx'));
verifier('adresse quelconque refusée', !push_endpoint_valide('https://exemple.test/piege'));
verifier('HTTP non chiffré refusé', !push_endpoint_valide('http://fcm.googleapis.com/fcm/send/xyz'));
verifier('faux sous-domaine refusé', !push_endpoint_valide('https://fcm.googleapis.com.exemple.test/x'));

echo $echecs ? "\n$echecs test(s) en échec.\n" : "\nTous les tests sont passés.\n";
exit($echecs ? 1 : 0);
