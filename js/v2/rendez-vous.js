// Le Guichet (guichet.php) : le formulaire de rendez-vous ne propose que les horaires encore libres du jour choisi.
// Données : <script type="application/json" id="creneaux-libres"> ({ "AAAA-MM-JJ": ["09:07", …], … }), lues par le serveur
// dans creneaux_libres(). Sans JavaScript, tous les horaires restent proposés : l'API refuse un créneau pris (409).
(() => {
  const donnees = document.getElementById('creneaux-libres');
  const jour = document.querySelector('[data-rdv-jour]');
  const horaire = document.querySelector('[data-rdv-horaire]');
  if (!donnees || !jour || !horaire) return;
  let libres = {};
  try { libres = JSON.parse(donnees.textContent); } catch { return; }

  // Un jour complet ne se choisit pas
  [...jour.options].forEach((o) => { if (o.value && !(libres[o.value] || []).length) o.disabled = true; });

  const filtrer = () => {
    const ouverts = libres[jour.value] || null;
    [...horaire.options].forEach((o) => {
      if (!o.value) return;
      const pris = ouverts !== null && !ouverts.includes(o.value);
      o.disabled = pris;
      o.textContent = o.textContent.replace(/ — pris$/, '') + (pris ? ' — pris' : '');
    });
    if (horaire.selectedOptions[0]?.disabled) horaire.value = '';
  };
  jour.addEventListener('change', filtrer);
  filtrer();
})();
