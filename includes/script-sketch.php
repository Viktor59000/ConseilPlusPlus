<?php
// Liste de répliques d'un sketch. Attend $lignes ([qui, texte, rôle]) et $noms (qui → libellé), voir carte-mot.php.
?>
      <ol class="sketch-script">
<?php foreach ($lignes as [$qui, $texte, $role]): ?>
<?php if ($qui === 'scene'): ?>
        <li class="sketch-scene" data-qui="scene"><?= htmlspecialchars($texte) ?></li>
<?php else: ?>
        <li class="replique replique-<?= $qui ?><?= $role ? " replique-$role" : '' ?>" data-qui="<?= $qui ?>">
          <span class="replique-qui mono"><?= $noms[$qui] ?></span>
          <span class="replique-texte"><?= $role === 'chute' ? '<mark>' . htmlspecialchars($texte) . '</mark>' : htmlspecialchars($texte) ?></span>
        </li>
<?php endif; ?>
<?php endforeach; ?>
      </ol>
