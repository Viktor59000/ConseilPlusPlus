<?php
/**
 * guichet.php — Ancienne page « Le guichet », supprimée. Les liens déjà partagés mènent désormais au cabinet
 * (redirection permanente ; pas de session ni de gabarit : ce n'est plus une page).
 */
header('Location: cabinet.php', true, 301);
exit;
