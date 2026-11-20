// Les documents officiels du cabinet, dessinés dans le navigateur (1600 × 1131, paysage), jamais envoyés au serveur :
// l'image se télécharge ou se partage (menu de partage du téléphone). Couleurs du Guichet : papier, encre, laiton, rouge tampon.
//
// Deux usages :
//  1. attestation.php (Service des documents officiels) : le formulaire du jour de Bertrand est « vérifié » (cases
//     comptées), puis l'attestation choisie est dessinée au nom saisi, et le tampon s'abat sur l'aperçu.
//  2. Tout conteneur [data-document='{…}'] (attestation de dépôt du Guichet) : ses boutons [data-telecharger] et
//     [data-partager-attestation], cachés sans JavaScript, dessinent le document décrit en JSON :
//     { fichier, titre, corps, mention, references: [...], champs: [[libellé, valeur], …], tampon, signataire, fonction }
(() => {
  const C = { papier: '#F7F7F3', encre: '#181715', douce: '#57544C', filet: '#C9C7BE', laiton: '#BFB21A', rouge: '#C8102E', surligneur: '#E6DE7A' };
  const SANS = '"Schibsted Grotesk", Arial, sans-serif';
  const MONO = '"JetBrains Mono", Consolas, monospace';

  let logo = null;
  async function chargerLogo() {
    if (logo) return logo;
    const svg = (await (await fetch('assets/logo.svg')).text())
      .replace('<svg ', '<svg width="984" height="256" ').replaceAll('#1B2440', C.encre); // encre du Guichet
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

  /** Dessine un document officiel et renvoie l'image PNG (Blob). */
  async function dessiner(doc) {
    await Promise.all([document.fonts.load(`500 64px ${SANS}`), document.fonts.load(`500 28px ${MONO}`)]).catch(() => {});
    const maintenant = new Date();
    const date = maintenant.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });

    const c = document.createElement('canvas');
    c.width = 1600;
    c.height = 1131;
    const ctx = c.getContext('2d');
    ctx.fillStyle = C.papier;
    ctx.fillRect(0, 0, c.width, c.height);
    // Cadre officiel : un filet laiton épais, un filet d'encre fin en retrait
    ctx.strokeStyle = C.laiton;
    ctx.lineWidth = 10;
    ctx.strokeRect(36, 36, 1528, 1059);
    ctx.strokeStyle = C.encre;
    ctx.lineWidth = 2;
    ctx.strokeRect(60, 60, 1480, 1011);

    // En-tête : le cabinet et son service à gauche, les références à droite
    try { ctx.drawImage(await chargerLogo(), 110, 100, 300, 78); } catch { /* sans logo */ }
    ctx.fillStyle = C.encre;
    ctx.font = `500 24px ${SANS}`;
    ctx.textAlign = 'left';
    ctx.fillText('Cabinet ConseilPlus+', 430, 132);
    ctx.fillStyle = C.douce;
    ctx.fillText(doc.service || 'Service des documents officiels', 430, 164);
    ctx.font = `500 22px ${MONO}`;
    ctx.textAlign = 'right';
    (doc.references || []).forEach((r, i) => ctx.fillText(r, 1490, 128 + i * 34));
    ctx.strokeStyle = C.filet;
    ctx.lineWidth = 2;
    ctx.beginPath(); ctx.moveTo(110, 230); ctx.lineTo(1490, 230); ctx.stroke();

    // Titre, surligné au laiton
    ctx.textAlign = 'center';
    ctx.font = `500 62px ${SANS}`;
    const largeurTitre = ctx.measureText(doc.titre).width;
    ctx.fillStyle = C.surligneur;
    ctx.fillRect(800 - largeurTitre / 2 - 10, 318, largeurTitre + 20, 24);
    ctx.fillStyle = C.encre;
    ctx.fillText(doc.titre, 800, 336);

    // Corps, puis les champs remplis à la machine, puis la mention
    const champs = doc.champs || [];
    ctx.font = `400 ${champs.length ? 34 : 40}px ${SANS}`;
    let y = champs.length ? 420 : 450;
    for (const ligne of couper(ctx, doc.corps, 1180)) {
      ctx.fillText(ligne, 800, y);
      y += champs.length ? 50 : 60;
    }
    if (champs.length) {
      y += 16;
      for (const [libelle, valeur] of champs) {
        ctx.textAlign = 'right';
        ctx.fillStyle = C.douce;
        ctx.font = `400 26px ${SANS}`;
        ctx.fillText(libelle, 640, y);
        ctx.textAlign = 'left';
        ctx.fillStyle = C.encre;
        ctx.font = `500 26px ${MONO}`;
        const v = String(valeur);
        ctx.fillText(v.length > 48 ? `${v.slice(0, 47)}…` : v, 672, y);
        ctx.strokeStyle = C.filet;
        ctx.setLineDash([3, 6]);
        ctx.beginPath(); ctx.moveTo(380, y + 14); ctx.lineTo(1220, y + 14); ctx.stroke();
        ctx.setLineDash([]);
        y += 48;
      }
      ctx.textAlign = 'center';
    }
    if (doc.mention) {
      ctx.fillStyle = C.douce;
      ctx.font = `italic 400 28px ${SANS}`;
      ctx.fillText(doc.mention, 800, Math.min(y + 30, 860));
    }

    // Pied : la date, la signature, le tampon
    ctx.textAlign = 'left';
    ctx.fillStyle = C.encre;
    ctx.font = `400 26px ${SANS}`;
    ctx.fillText(`Fait le ${date}, pour valoir ce que de droit,`, 120, 960);
    ctx.fillText("c'est-à-dire pas grand-chose.", 120, 998);
    ctx.textAlign = 'center';
    ctx.font = `italic 500 46px ${SANS}`;
    ctx.fillText(doc.signataire || 'Le tampon', 1180, 960);
    ctx.font = `400 22px ${SANS}`;
    ctx.fillStyle = C.douce;
    ctx.fillText(doc.fonction || 'Doyen du cabinet ConseilPlus+', 1180, 998);

    ctx.save();
    ctx.translate(1290, 850);
    ctx.rotate(-0.16);
    ctx.globalAlpha = 0.9;
    ctx.strokeStyle = C.rouge;
    ctx.lineWidth = 6;
    ctx.strokeRect(-160, -52, 320, 104);
    ctx.lineWidth = 2;
    ctx.strokeRect(-146, -40, 292, 80);
    ctx.fillStyle = C.rouge;
    ctx.font = `500 48px ${MONO}`;
    ctx.fillText((doc.tampon || 'Validé').toUpperCase(), 0, 16);
    ctx.restore();

    return new Promise((ok) => c.toBlob(ok, 'image/png'));
  }

  /** Télécharge un fichier (lien temporaire). */
  function telecharger(fichier) {
    const url = URL.createObjectURL(fichier);
    const lien = document.createElement('a');
    lien.href = url;
    lien.download = fichier.name;
    lien.click();
    setTimeout(() => URL.revokeObjectURL(url), 2000);
  }

  /** Partage par le menu du téléphone (image si possible, sinon texte + lien). */
  async function partager(fichier, texte) {
    try {
      if (navigator.canShare?.({ files: [fichier] })) await navigator.share({ files: [fichier], text: texte });
      else await navigator.share({ text: `${texte} ${location.href}` });
    } catch { /* partage annulé */ }
  }

  // ---------- 2. Documents décrits en JSON (attestation de dépôt du Guichet) ----------
  document.querySelectorAll('[data-document]').forEach((conteneur) => {
    let doc;
    try { doc = JSON.parse(conteneur.dataset.document); } catch { return; }
    let fichier = null;
    const obtenir = async () => {
      fichier ??= new File([await dessiner(doc)], `${doc.fichier || 'attestation'}.png`, { type: 'image/png' });
      return fichier;
    };
    const bTelecharger = conteneur.querySelector('[data-telecharger]');
    const bPartager = conteneur.querySelector('[data-partager-attestation]');
    if (bTelecharger) {
      bTelecharger.hidden = false;
      bTelecharger.addEventListener('click', async () => telecharger(await obtenir()));
    }
    if (bPartager && navigator.share) {
      bPartager.hidden = false;
      bPartager.addEventListener('click', async () => partager(await obtenir(), `${doc.titre}, délivrée par le cabinet ConseilPlus+.`));
    }
  });

  // ---------- 1. Service des documents officiels (attestation.php) ----------
  const demande = document.querySelector('[data-demande]');
  if (!demande) return;
  const { modeles, formulaire, verdicts } = JSON.parse(document.getElementById('donnees-attestation').textContent);
  const apercu = document.querySelector('[data-apercu]');
  const image = apercu.querySelector('[data-image]');
  let fichier = null;
  let partageTexte = '';
  demande.hidden = false;

  /** Constat de Bertrand sur le formulaire du jour : les cases sont comptées, le verdict est toujours favorable. */
  function constat() {
    const cases = [...demande.querySelectorAll('[data-case]')];
    const remplies = cases.filter((c) => c.querySelector('input:checked')
      || [...c.querySelectorAll('input:not([type="checkbox"])')].some((i) => i.value.trim())).length;
    const total = cases.length;
    const bilan = remplies === 0 ? `aucune case remplie sur ${total}`
      : `${remplies} case${remplies > 1 ? 's' : ''} remplie${remplies > 1 ? 's' : ''} sur ${total}`;
    return verdicts[Math.floor(Math.random() * verdicts.length)].replaceAll('{code}', formulaire).replaceAll('{bilan}', bilan);
  }

  demande.addEventListener('submit', async (e) => {
    e.preventDefault();
    const champNom = demande.querySelector('[data-nom]');
    const nom = champNom.value.trim().replace(/\s+/g, ' ').slice(0, 40);
    if (!nom) { champNom.focus(); return; }
    const type = demande.querySelector('[data-type]').value;
    const modele = modeles[type];
    const verdict = constat();
    const maintenant = new Date();
    const date = maintenant.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
    const heure = maintenant.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }).replace(':', ' h ');
    const numero = `ATT-${maintenant.getFullYear()}-${String(Math.floor(1000 + Math.random() * 9000))}`;
    const remplir = (t) => t.replaceAll('{nom}', nom.replaceAll(' ', ' ')).replaceAll('{date}', date).replaceAll('{heure}', heure);
    const blob = await dessiner({
      titre: modele.titre, corps: remplir(modele.texte), mention: remplir(modele.mention), tampon: 'Validé',
      references: [`N° ${numero}`, 'Original : ne pas photocopier (ou alors en couleur)', `Formulaire préalable ${formulaire} : conforme`],
      signataire: 'Le tampon', fonction: 'Doyen du cabinet ConseilPlus+',
    });
    partageTexte = `${modele.titre} au nom de ${nom}, délivrée par le cabinet ConseilPlus+.`;
    fichier = new File([blob], `attestation-${type}.png`, { type: 'image/png' });
    if (image.src) URL.revokeObjectURL(image.src);
    image.src = URL.createObjectURL(blob);
    image.alt = `${modele.titre} au nom de ${nom}, tamponnée « Validé » par le doyen du cabinet.`;
    apercu.hidden = false;
    apercu.querySelector('[data-verdict]').textContent = verdict;
    apercu.querySelector('[data-statut]').textContent = `${verdict} Document prêt : ${modele.titre}, tamponné « Validé ».`;
    apercu.scrollIntoView({ behavior: window.Guichet?.mouvementPermis() ? 'smooth' : 'auto', block: 'start' });
    apercu.querySelector('[data-telecharger]').focus({ preventScroll: true });
    // Le tampon s'abat sur l'aperçu (js/v2/guichet.js), une fois l'image posée
    const tampon = apercu.querySelector('[data-tampon], .tampon');
    if (tampon && window.Guichet) Promise.resolve(image.decode?.()).catch(() => {}).finally(() => window.Guichet.tamponner(tampon));
  });

  apercu.querySelector('[data-telecharger]').addEventListener('click', () => { if (fichier) telecharger(fichier); });
  const boutonPartage = apercu.querySelector('[data-partager-attestation]');
  if (!navigator.share) boutonPartage.hidden = true;
  boutonPartage.addEventListener('click', () => { if (fichier) partager(fichier, partageTexte); });
})();
