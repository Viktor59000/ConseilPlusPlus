<?php
// Copier ce fichier en config.php (ignoré par Git) et renseigner les clés.
// En ligne (Render), les clés viennent des variables d'environnement du même nom.

// Clé que le logiciel de gestion envoie dans l'en-tête X-API-Key pour lire/modifier les contacts
define('API_KEY', getenv('API_KEY') ?: 'changez-moi');

// IA gratuite : Groq (https://console.groq.com/keys). Laisser vide pour utiliser le générateur local.
define('GROQ_API_KEY', getenv('GROQ_API_KEY') ?: '');
const GROQ_MODEL = 'openai/gpt-oss-120b';
