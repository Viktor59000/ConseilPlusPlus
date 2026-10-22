# Idées pour plus tard

## Conseil du jour sur téléphone (widget / notification) — réalisé

Proposé le 14/10/2026. Illustre le principe « une même API peut alimenter une appli mobile » du cours.

1. **Endpoint** : devenu `GET /api/widget.php` (mot du jour + un conseil, champs prêts à afficher).
2. **Widget sans appli native** (en place) (démo rapide) : KWGT (Android) ou Scriptable (iOS) lisent le JSON de l'endpoint.
3. **Notification** (en place) : PWA (`manifest.webmanifest`, `sw.js`) + Web Push sans dépendance (`includes/push.php`).
   Android, et iOS ≥ 16.4 une fois le site installé.
4. **Widget natif** (en place pour Android) : appli `android/` et son widget « Le Petit Moyen », APK publié par
   `.github/workflows/android.yml`. iOS (WidgetKit) non prévu.

Prérequis : le téléphone doit joindre le site → hébergement (ou IP du PC sur le Wi-Fi pour tester).
Web Push exige du HTTPS, donc un hébergement.

## Le mot (presque) du jour — « Le Petit Moyen » — réalisé

Proposé le 14/10/2026. Une fausse entrée de dictionnaire par jour, dans le ton administratif du site :
un jeu de mots (ou un mot qui n'en est pas un) avec une définition approximative.

Format d'une entrée :
- **Mot** : mot-valise (« procrastinette », « réunionite »), vrai mot mal compris (« dimanche : n. m., jour où l'on prépare lundi »)
  ou faux jeu de mots assumé (« Ornithorynque — n'a rien à voir avec ce qui précède »).
- **Nature** : *n. m.*, *v. tr. du 3e groupe (rarement conjugué)*, *adj. invariable sauf le mardi*…
- **Définition approximative** + **exemple d'usage** + **synonyme douteux** + **niveau de certitude** (« 12 % »).

Mise en œuvre :
1. Banque locale écrite à la main (data/mots.php), tirée selon la date : le même mot pour tout le monde dans la journée.
2. Option IA : un mot généré une fois par jour puis stocké en base (une seule requête Groq par jour, pas de quota gaspillé).
3. Endpoint `GET /api/mot-du-jour.php` → réutilisable par le widget / la notification du téléphone (voir plus haut),
   avec le conseil du jour : le téléphone afficherait « mot du jour + conseil du jour ».
4. Encadré sur l'accueil + page d'archives « Le Petit Moyen » (les mots des jours précédents).

## Logiciel de gestion « Le Bureau » — réalisé

Deuxième application (base `gestion`) qui importe les demandes via `GET /api/contacts.php?statut=nouveau`
puis `PATCH`, gère les dossiers et répond automatiquement par un conseil absurde.
Améliorations API associées : journal des échanges, une clé par application, pagination, OpenAPI (faits) ; webhook (à faire :
prévenir Le Bureau à chaque nouvelle demande au lieu du relevé).

## Perspectives retenues par le conseil (22/10/2026)

Issues de l'audit du conseil n° 3 (cinq points de vue, deux tours) : utiles, mais reportées après la soutenance.

1. **Attestations reliées aux services** : chaque service mène à « son » attestation (client moyen depuis le diagnostic,
   patience depuis le standard, prédiction chez Messmerde) par un lien `attestation.php?document=…` qui présélectionne
   le document. Les documents existent déjà dans `data/attestations.php` ; aujourd'hui `attestation.php` ne lit aucun paramètre.
2. **Copie du Bureau : anonymisation et purge automatique.** Quand un client supprime sa demande ou son compte sur le site,
   le dossier du Bureau garde son nom, son adresse et son message. Idée : propager la suppression (anonymiser le dossier :
   nom, adresse et message remplacés, réponse et dates gardées pour les statistiques) et planifier la purge des dossiers
   classés depuis plus de 6 mois (aujourd'hui en lot, à la main, depuis le tableau de bord).
3. **CSS en cache** : le CSS est intégré dans chaque page (bon pour Lighthouse à la première visite) ; un fichier
   versionné, mis en cache par le navigateur, allégerait les pages suivantes. À mesurer avant de choisir.
4. **Empreinte du schéma du Bureau** : comme `includes/schema.php` côté site, ne rejouer les migrations de
   `bureau/includes/bdd.php` que lorsqu'elles changent (empreinte gardée en base), au lieu de les vérifier à chaque page.

**Refusé : le « dossier visiteur »** (suivre sa demande sans compte, par un numéro gardé dans le navigateur). Doublon de
« Mon compte », qui affiche déjà le statut et la réponse aux clients connectés ; et suivre un visiteur non connecté
demanderait de conserver un identifiant de plus, donc un consentement et une ligne de plus dans la politique de
confidentialité, pour un gain faible : la réponse lui parvient déjà par courriel.
