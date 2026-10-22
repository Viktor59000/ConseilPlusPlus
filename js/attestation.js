// Les attestations officielles : dessinées dans le navigateur (1600 × 1131, paysage), jamais envoyées au serveur.
(() => {
  const demande = document.querySelector('[data-demande]');
  if (demande) {
    const modeles = JSON.parse(document.getElementById('modeles-attestation').textContent);
    const apercu = document.querySelector('[data-apercu]');
    const image = apercu.querySelector('[data-image]');
    const C = { fond: '#FFFFFF', encre: '#1B2440', douce: '#525A73', filet: '#DDE1EC', rouge: '#C8102E', jaune: '#FFE14D' };
    const SANS = '"Schibsted Grotesk", Arial, sans-serif';
    const MONO = '"JetBrains Mono", Consolas, monospace';
    let fichier = null;
    let partageTexte = '';

    let logo = null;
    async function chargerLogo() {
      if (logo) return logo;
      const svg = (await (await fetch('assets/logo.svg')).text()).replace('<svg ', '<svg width="984" height="256" ');
      const url = URL.createObjectURL(new Blob([svg], { type: 'image/svg+xml' }));
      logo = await new Promise((ok, ko) => { const i = new Image(); i.onload = () => ok(i); i.onerror = ko; i.src = url; });
      return logo;
    }

    function couper(ctx, texte, largeur) {
      const lignes = [];
      let ligne = '';
      for (const mot of texte.split(' ')) {
        const essai = ligne ? `${ligne} ${mot}` : mot;
        if (ctx.measureText(essai).width > largeur && ligne) { lignes.push(ligne); ligne = mot; } else { ligne = essai; }
      }
      if (ligne) lignes.push(ligne);
      return lignes;
    }

    async function dessiner(modele, nom) {
      await Promise.all([document.fonts.load(`800 64px ${SANS}`), document.fonts.load(`500 28px ${MONO}`)]).catch(() => {});
      const maintenant = new Date();
      const date = maintenant.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
      const heure = maintenant.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }).replace(':', ' h ');
      const numero = `ATT-${maintenant.getFullYear()}-${String(Math.floor(1000 + Math.random() * 9000))}`;
      const remplir = (t) => t.replaceAll('{nom}', nom.replaceAll(' ', '\u00A0')).replaceAll('{date}', date).replaceAll('{heure}', heure);

      const c = document.createElement('canvas');
      c.width = 1600;
      c.height = 1131;
      const ctx = c.getContext('2d');
      ctx.fillStyle = C.fond;
      ctx.fillRect(0, 0, c.width, c.height);
      // Double cadre officiel
      ctx.strokeStyle = C.encre;
      ctx.lineWidth = 8;
      ctx.strokeRect(40, 40, 1520, 1051);
      ctx.lineWidth = 2;
      ctx.strokeRect(62, 62, 1476, 1007);

      try { ctx.drawImage(await chargerLogo(), 110, 100, 360, 94); } catch { /* sans logo */ }
      ctx.fillStyle = C.douce;
      ctx.font = `500 26px ${MONO}`;
      ctx.textAlign = 'right';
      ctx.fillText(`N° ${numero}`, 1490, 130);
      ctx.fillText('Original — ne pas photocopier (ou alors en couleur)', 1490, 170);

      // Titre
      ctx.textAlign = 'center';
      ctx.font = `800 64px ${SANS}`;
      const titre = modele.titre;
      const largeurTitre = ctx.measureText(titre).width;
      ctx.fillStyle = C.jaune;
      ctx.fillRect(800 - largeurTitre / 2 - 10, 336, largeurTitre + 20, 26);
      ctx.fillStyle = C.encre;
      ctx.fillText(titre, 800, 356);

      // Corps
      ctx.font = `500 40px ${SANS}`;
      let y = 470;
      for (const ligne of couper(ctx, remplir(modele.texte), 1180)) {
        ctx.fillText(ligne, 800, y);
        y += 60;
      }
      ctx.fillStyle = C.douce;
      ctx.font = `italic 400 30px ${SANS}`;
      ctx.fillText(remplir(modele.mention), 800, y + 30);

      // Pied : date, signature, tampon
      ctx.textAlign = 'left';
      ctx.fillStyle = C.encre;
      ctx.font = `500 26px ${SANS}`;
      ctx.fillText(`Fait le ${date}, pour valoir ce que de droit,`, 120, 930);
      ctx.fillText("c'est-à-dire pas grand-chose.", 120, 968);

      ctx.textAlign = 'center';
      ctx.font = `italic 600 46px ${SANS}`;
      ctx.fillText('Le tampon', 1180, 930);
      ctx.font = `400 24px ${SANS}`;
      ctx.fillStyle = C.douce;
      ctx.fillText('Doyen du cabinet ConseilPlus+', 1180, 968);

      ctx.save();
      ctx.translate(1300, 820);
      ctx.rotate(-0.16);
      ctx.strokeStyle = C.rouge;
      ctx.lineWidth = 6;
      ctx.strokeRect(-150, -52, 300, 104);
      ctx.lineWidth = 2;
      ctx.strokeRect(-138, -40, 276, 80);
      ctx.fillStyle = C.rouge;
      ctx.font = `700 48px ${MONO}`;
      ctx.fillText('VALIDÉ', 0, 16);
      ctx.restore();

      partageTexte = `${titre} au nom de ${nom}, délivrée par le cabinet ConseilPlus+.`;
      return new Promise((ok) => c.toBlob(ok, 'image/png'));
    }

    demande.addEventListener('submit', async (e) => {
      e.preventDefault();
      const nom = demande.querySelector('[data-nom]').value.trim().replace(/\s+/g, ' ').slice(0, 40);
      if (!nom) return;
      const type = demande.querySelector('[data-type]').value;
      const blob = await dessiner(modeles[type], nom);
      fichier = new File([blob], `attestation-${type}.png`, { type: 'image/png' });
      if (image.src) URL.revokeObjectURL(image.src);
      image.src = URL.createObjectURL(blob);
      image.alt = `${modeles[type].titre} au nom de ${nom}, tamponnée « VALIDÉ »`;
      apercu.hidden = false;
      apercu.classList.remove('tamponne');
      void apercu.offsetWidth;
      apercu.classList.add('tamponne');
      apercu.querySelector('[data-statut]').textContent = `Document prêt : ${modeles[type].titre}, tamponné « VALIDÉ ».`;
      apercu.querySelector('[data-telecharger]').focus({ preventScroll: true });
      apercu.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    apercu.querySelector('[data-telecharger]').addEventListener('click', () => {
      if (!fichier) return;
      const lien = document.createElement('a');
      lien.href = image.src;
      lien.download = fichier.name;
      lien.click();
    });

    const boutonPartage = apercu.querySelector('[data-partager-attestation]');
    if (!navigator.share) boutonPartage.hidden = true;
    boutonPartage.addEventListener('click', async () => {
      if (!fichier) return;
      try {
        if (navigator.canShare?.({ files: [fichier] })) await navigator.share({ files: [fichier], text: partageTexte });
        else await navigator.share({ text: `${partageTexte} ${location.href}` });
      } catch { /* partage annulé */ }
    });
  }
})();
