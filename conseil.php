<?php
/**
 * conseil.php — Ancienne page « Mon conseil », fusionnée dans Mon dossier (compte.php, section « Le conseil du jour »).
 * Redirection permanente. auth/connexion.php (code du cours, non modifiable) envoie ici après la connexion :
 * compte.php prend ensuite le relais (y compris le retour au Guichet retenu avant la connexion).
 */
header('Location: compte.php', true, 301);
exit;
