<?php
// Copier ce fichier en config.php (ignoré par Git) et renseigner les clés.
// En ligne (Render), les clés viennent des variables d'environnement du même nom.

// Clé que le logiciel de gestion envoie dans l'en-tête X-API-Key pour lire/modifier les contacts
define('API_KEY', getenv('API_KEY') ?: 'changez-moi');

// IA gratuite : Groq (https://console.groq.com/keys). Laisser vide pour utiliser le générateur local.
define('GROQ_API_KEY', getenv('GROQ_API_KEY') ?: '');
const GROQ_MODEL = 'openai/gpt-oss-120b';

// Courriels du site (accusé de réception du formulaire de contact) : API Brevo, mêmes variables que Le Bureau.
// Sans clé, aucun courriel n'est envoyé (le formulaire fonctionne normalement).
define('COURRIEL_BREVO_CLE', getenv('BREVO_API_KEY') ?: '');
define('COURRIEL_EXPEDITEUR', getenv('BUREAU_EXPEDITEUR') ?: '');   // adresse vérifiée dans Brevo
define('COURRIEL_EXPEDITEUR_NOM', 'ConseilPlus+, le conseiller moyen');
