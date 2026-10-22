<?php
/**
 * api/notifications.php — Notification quotidienne du mot (presque) du jour (Web Push).
 *
 * GET                                        → 200 { cle_publique } (clé VAPID pour s'abonner dans le navigateur)
 * POST { action: "abonner", abonnement }      → 201 abonné | 422 abonnement invalide
 * POST { action: "desabonner", endpoint }     → 200
 * POST { action: "envoyer" } (clé « ecriture »)                → 200 { envoyes, expires } | 200 { deja: true } (une fois par jour au plus)
 *
 * Public, sans clé : l'envoi est déclenché chaque matin par la visite quotidienne (.github/workflows/reveil.yml)
 * et ne peut partir qu'une fois par jour, quel que soit le nombre d'appels. Limite : 10 appels par minute et par IP.
 */
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/push.php';
require_once __DIR__ . '/../includes/mot-du-jour.php';

/** Clés VAPID du site, ou 503 si elles sont illisibles ou impossibles à créer. */
function vapid_ou_503(PDO $bdd): array
{
    try {
        return push_cles_vapid($bdd);
    } catch (RuntimeException $e) {
        error_log('ConseilPlus+ : ' . $e->getMessage());
        repondre(503, ['erreur' => 'Notifications indisponibles pour le moment.']);
    }
}

router([
    'GET' => fn() => repondre(200, ['cle_publique' => b64url(vapid_ou_503($bdd)['public'])]),

    'POST' => function () use ($bdd) {
        limiter(10);
        $corps = lire_corps();
        switch ($corps['action'] ?? '') {

            case 'abonner':
                $a = $corps['abonnement'] ?? [];
                $endpoint = (string) ($a['endpoint'] ?? '');
                $p256dh = (string) ($a['keys']['p256dh'] ?? '');
                $auth = (string) ($a['keys']['auth'] ?? '');
                if (strlen($endpoint) > 500 || !push_endpoint_valide($endpoint)
                    || strlen(b64url_decoder($p256dh)) !== 65 || strlen(b64url_decoder($auth)) !== 16) {
                    repondre(422, ['erreur' => 'Abonnement invalide.']);
                }
                $bdd->prepare('INSERT INTO abonnements_push (endpoint, p256dh, auth) VALUES (?, ?, ?)
                               ON DUPLICATE KEY UPDATE p256dh = VALUES(p256dh), auth = VALUES(auth)')
                    ->execute([$endpoint, $p256dh, $auth]);
                repondre(201, ['abonne' => true]);

            case 'desabonner':
                $bdd->prepare('DELETE FROM abonnements_push WHERE endpoint = ?')->execute([(string) ($corps['endpoint'] ?? '')]);
                repondre(200, ['abonne' => false]);

            case 'envoyer':
                exiger_cle('ecriture'); // déclenché par GitHub Actions : personne d'autre ne peut écrire aux abonnés
                $vapid = vapid_ou_503($bdd);
                // Une seule notification par jour : la date du dernier envoi est réservée avant d'envoyer
                $jour = date('Y-m-d');
                $reserve = $bdd->prepare("INSERT INTO reglages (cle, valeur) VALUES ('push_dernier_envoi', :j)
                                          ON DUPLICATE KEY UPDATE valeur = IF(valeur = :j2, valeur, :j3)");
                $reserve->execute(['j' => $jour, 'j2' => $jour, 'j3' => $jour]);
                if ($reserve->rowCount() === 0) {
                    repondre(200, ['deja' => true]);
                }
                $mot = mot_du_jour($bdd);
                $donnees = ['titre' => 'Le mot (presque) du jour : ' . $mot['mot'], 'corps' => chute_sketch($mot) ?? $mot['definition'], 'url' => 'petit-moyen.php'];
                $envoyes = 0;
                $expires = 0;
                foreach ($bdd->query('SELECT * FROM abonnements_push')->fetchAll(PDO::FETCH_ASSOC) as $abonne) {
                    try {
                        $code = push_envoyer($vapid, $abonne, $donnees);
                    } catch (RuntimeException $e) {
                        $code = 410; // clé illisible : abonnement inutilisable
                    }
                    if (in_array($code, [404, 410], true)) {
                        $bdd->prepare('DELETE FROM abonnements_push WHERE id = ?')->execute([$abonne['id']]);
                        $expires++;
                    } elseif ($code >= 200 && $code < 300) {
                        $envoyes++;
                    }
                }
                repondre(200, ['envoyes' => $envoyes, 'expires' => $expires]);

            default:
                repondre(422, ['erreur' => 'Action inconnue (abonner, desabonner ou envoyer).']);
        }
    },
]);
