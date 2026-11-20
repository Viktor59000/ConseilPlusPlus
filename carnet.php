<?php
/**
 * carnet.php — Ancienne page « Mon carnet », fusionnée dans Mon dossier (compte.php, section « Mes conseils gardés »).
 * Redirection permanente : les liens déjà partagés et les favoris continuent de marcher.
 */
header('Location: compte.php#carnet', true, 301);
exit;
