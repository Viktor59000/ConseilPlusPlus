<?php
/* Pied des pages du Bureau : ferme la zone de travail, puis les scripts (guichet.js du site pour le tampon qui s'abat,
   bureau.js pour le thème, le menu et les filtres, et les scripts propres à la page : $scripts). */
$scripts ??= [];
?>
<?php if ($agent): ?>
    <footer class="pied-b">Le Bureau — logiciel de gestion interne du cabinet ConseilPlus+. Toute ressemblance avec une administration existante est moyennement fortuite.</footer>
  </main>
</div>
<?php else: ?>
</main>
<?php endif; ?>
<script src="<?= e(fichier_versionne('../js/v2/guichet.js')) ?>" defer></script>
<script src="<?= e(script_bureau('bureau.js')) ?>" defer></script>
<?php foreach ($scripts as $s): ?>
<script src="<?= e(script_bureau($s)) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
