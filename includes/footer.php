<?php /* Pied de page commun à toutes les pages du site. */ ?>
</main>
<footer class="pied">
  <div class="conteneur pied-in">
    <div>
      <p class="pied-accroche">Des conseils. C'est déjà ça.</p>
      <p>Aucun conseil délivré par ConseilPlus+ ne doit être suivi. Sérieusement.</p>
    </div>
    <nav aria-label="Liens utiles">
      <ul>
        <li><a href="plan-du-site.php">Plan du site</a></li>
        <li><a href="accessibilite.php">Accessibilité : partiellement conforme</a></li>
        <li><a href="application.php">L'application mobile</a></li>
        <li><a href="developpeurs.php">L'API (développeurs)</a></li>
        <li><a href="contact.php">Nous contacter</a></li>
        <li><a href="mentions-legales.php">Mentions légales</a></li>
        <li><a href="confidentialite.php">Confidentialité</a></li>
      </ul>
    </nav>
    <p class="pied-mentions">© <?= date('Y') ?> ConseilPlus+, le conseiller moyen · Hommage non officiel à l'humour absurde · Polices Schibsted Grotesk et JetBrains Mono (licence OFL)</p>
  </div>
</footer>
<script type="application/json" id="textes-site"><?= json_encode(['onglet' => textes()['onglet_absent'], 'secrets' => textes()['secrets']], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="js/theme.js" defer></script>
<script src="js/site.js" defer></script>
<script src="js/pwa.js" defer></script>
</body>
</html>
