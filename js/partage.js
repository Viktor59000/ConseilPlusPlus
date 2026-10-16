// Partage d'un conseil ou d'un mot : image au format story (1080 × 1920) + texte + lien.
// Téléphone : menu de partage natif (WhatsApp, Instagram, SMS…). Ordinateur : l'image est téléchargée
// et le texte copié. Les boutons [data-partager] restent cachés sans JavaScript.
//
// Attributs d'un bouton :
//   data-partager="conseil" data-source="#id"   → lit le conseil affiché dans l'élément (il peut changer)
//   data-partager="mot" data-mot data-nature data-texte data-detail data-url   → une entrée du Petit Moyen

const SITE = 'conseilplus.onrender.com';
const COULEURS = { papier: '#F5F6FA', carte: '#FFFFFF', encre: '#1B2440', douce: '#525A73', filet: '#DDE1EC', seyes: '#D9E1F7', marge: '#F3AEB5', tampon: '#C8102E', surligneur: '#FFE14D' };
// Polices du site (déjà chargées par la page)
const SANS = '"Schibsted Grotesk", Arial, sans-serif';
const MONO = '"JetBrains Mono", Consolas, monospace';

/** Le logo du cabinet, chargé une fois (le SVG reçoit une taille explicite pour tous les navigateurs). */
let logo = null;
async function chargerLogo() {
  if (logo) return logo;
  const svg = (await (await fetch('assets/logo.svg')).text()).replace('<svg ', '<svg width="984" height="256" ');
  const url = URL.createObjectURL(new Blob([svg], { type: 'image/svg+xml' }));
  logo = await new Promise((ok, ko) => { const i = new Image(); i.onload = () => ok(i); i.onerror = ko; i.src = url; });
  return logo;
}

/** Découpe un texte en lignes qui tiennent dans la largeur donnée. */
function couper(ctx, texte, largeur) {
  const lignes = [];
  let ligne = '';
  for (const mot of texte.split(/\s+/)) {
    const essai = ligne ? ligne + ' ' + mot : mot;
    if (ctx.measureText(essai).width > largeur && ligne) {
      lignes.push(ligne);
      ligne = mot;
    } else {
      ligne = essai;
    }
  }
  if (ligne) lignes.push(ligne);
  return lignes;
}

/**
 * Dessine l'image story. blocs = [{ texte, police, couleur, interligne, espaceAvant, surligne }] (contenu de la carte).
 */
async function dessiner(etiquette, blocs) {
  await Promise.all([document.fonts.load(`800 112px ${SANS}`), document.fonts.load(`600 64px ${SANS}`), document.fonts.load(`500 30px ${MONO}`)]).catch(() => {});
  const c = document.createElement('canvas');
  c.width = 1080;
  c.height = 1920;
  const ctx = c.getContext('2d');
  ctx.fillStyle = COULEURS.papier;
  ctx.fillRect(0, 0, c.width, c.height);

  try {
    const l = await chargerLogo();
    ctx.drawImage(l, 220, 150, 640, 166);
  } catch { /* sans logo, l'image reste lisible */ }

  // Mise en page du contenu pour connaître la hauteur de la carte
  const x = 150, largeur = 780;
  let hauteur = 90; // étiquette
  const prepares = blocs.map((b) => {
    ctx.font = b.police;
    const lignes = couper(ctx, b.texte, largeur);
    hauteur += (b.espaceAvant || 0) + lignes.length * b.interligne;
    return { ...b, lignes };
  });
  hauteur += 150; // place du tampon
  const yCarte = Math.max(420, Math.round((c.height - hauteur) / 2) - 40);

  // La carte : une copie, marge rouge
  ctx.save();
  ctx.shadowColor = 'rgba(27, 36, 64, .18)'; ctx.shadowBlur = 60; ctx.shadowOffsetY = 24;
  ctx.fillStyle = COULEURS.carte;
  ctx.beginPath();
  ctx.roundRect(90, yCarte, 900, hauteur + 80, 44);
  ctx.fill();
  ctx.restore();
  ctx.fillStyle = COULEURS.marge;
  ctx.fillRect(122, yCarte, 4, hauteur + 80);

  ctx.textBaseline = 'alphabetic';
  ctx.fillStyle = COULEURS.douce;
  ctx.font = `500 28px ${MONO}`;
  ctx.fillText(etiquette.toUpperCase(), x, yCarte + 80);
  ctx.fillStyle = COULEURS.filet;
  ctx.fillRect(x, yCarte + 104, largeur, 2);

  let y = yCarte + 110;
  for (const b of prepares) {
    y += b.espaceAvant || 0;
    ctx.font = b.police;
    for (const ligne of b.lignes) {
      y += b.interligne;
      if (b.surligne) {
        ctx.fillStyle = COULEURS.surligneur;
        ctx.fillRect(x - 6, y - b.interligne * 0.42, ctx.measureText(ligne).width + 12, b.interligne * 0.48);
      }
      ctx.fillStyle = b.couleur || COULEURS.encre;
      ctx.fillText(ligne, x, y);
    }
  }

  // Tampon « VALIDÉ »
  ctx.save();
  ctx.translate(820, yCarte + hauteur + 10);
  ctx.rotate(-0.17);
  ctx.strokeStyle = COULEURS.tampon;
  ctx.lineWidth = 5;
  ctx.beginPath();
  ctx.roundRect(-130, -48, 260, 84, 14);
  ctx.stroke();
  ctx.beginPath();
  ctx.roundRect(-118, -36, 236, 60, 8);
  ctx.stroke();
  ctx.fillStyle = COULEURS.tampon;
  ctx.font = `700 42px ${MONO}`;
  ctx.textAlign = 'center';
  ctx.fillText('VALIDÉ', 0, 10);
  ctx.restore();

  ctx.textAlign = 'center';
  ctx.fillStyle = COULEURS.encre;
  ctx.font = `600 40px ${SANS}`;
  ctx.fillText(SITE, 540, 1730);
  ctx.fillStyle = COULEURS.douce;
  ctx.font = `400 28px ${SANS}`;
  ctx.fillText('Aucun conseil ne doit être suivi. Sérieusement.', 540, 1790);

  return new Promise((ok) => c.toBlob(ok, 'image/png'));
}

/** Contenu à partager selon le bouton. */
function contenu(bouton) {
  if (bouton.dataset.partager === 'conseil') {
    const source = document.querySelector(bouton.dataset.source);
    const texte = source.querySelector('.fiche-conseil').textContent.replace(/\s+/g, ' ').trim();
    const dossier = ((source.querySelector('[data-dossier]') || source.querySelector('.fiche-entete .mono'))?.textContent.match(/\d{4}-[A-Z]/) || [''])[0];
    return {
      nom: 'conseilplus-conseil.png',
      titre: 'Un conseil de ConseilPlus+',
      texte: `« ${texte} » — conseil officiel du cabinet ConseilPlus+, le conseiller moyen.`,
      url: new URL('./', location.href).href,
      etiquette: 'Conseil officiel' + (dossier ? ' — dossier n° ' + dossier : ''),
      blocs: [{ texte, police: `600 64px ${SANS}`, interligne: 88, espaceAvant: 40 }],
    };
  }
  const d = bouton.dataset;
  const blocs = [
    { texte: d.mot, police: `800 112px ${SANS}`, interligne: 130, espaceAvant: 30, surligne: true },
    { texte: d.nature, police: `italic 400 44px ${SANS}`, interligne: 64, couleur: COULEURS.douce },
    { texte: d.texte, police: `600 58px ${SANS}`, interligne: 80, espaceAvant: 40 },
  ];
  if (d.detail) blocs.push({ texte: d.detail, police: `italic 400 48px ${SANS}`, interligne: 66, espaceAvant: 34, couleur: COULEURS.douce });
  return {
    nom: 'conseilplus-' + d.mot.toLowerCase().normalize('NFD').replace(/[^a-z]+/g, '-') + '.png',
    titre: `${d.mot} — Le Petit Moyen`,
    texte: `${d.mot} (${d.nature}) : ${d.texte} — Le Petit Moyen, dictionnaire officieux de ConseilPlus+.`,
    url: new URL(d.url || 'petit-moyen.php', location.href).href,
    etiquette: 'Le Petit Moyen — dictionnaire officieux',
    blocs,
  };
}

function annoncer(bouton, message) {
  const ancien = bouton.textContent;
  bouton.textContent = message;
  setTimeout(() => { bouton.textContent = ancien; }, 2500);
}

async function partager(bouton) {
  const c = contenu(bouton);
  bouton.setAttribute('aria-busy', 'true');
  try {
    const image = await dessiner(c.etiquette, c.blocs);
    const fichier = new File([image], c.nom, { type: 'image/png' });
    if (navigator.canShare && navigator.canShare({ files: [fichier] })) {
      await navigator.share({ files: [fichier], title: c.titre, text: `${c.texte} ${c.url}` });
    } else if (navigator.share) {
      await navigator.share({ title: c.titre, text: c.texte, url: c.url });
    } else {
      // Ordinateur : téléchargement de l'image + texte copié
      const lien = document.createElement('a');
      lien.href = URL.createObjectURL(image);
      lien.download = c.nom;
      lien.click();
      setTimeout(() => URL.revokeObjectURL(lien.href), 10000);
      await navigator.clipboard?.writeText(`${c.texte} ${c.url}`).catch(() => {});
      annoncer(bouton, 'Image téléchargée, texte copié');
    }
  } catch (e) {
    if (e?.name !== 'AbortError') annoncer(bouton, 'Partage impossible');
  } finally {
    bouton.removeAttribute('aria-busy');
  }
}

for (const bouton of document.querySelectorAll('[data-partager]')) {
  bouton.hidden = false;
  bouton.addEventListener('click', () => partager(bouton));
}
