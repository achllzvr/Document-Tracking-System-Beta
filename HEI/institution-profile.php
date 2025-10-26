<?php
  
// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// HEI protector
require_once __DIR__ . '/../includes/hei_protect.php';

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Institution Profile — HEI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-4xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">Edit Institution Profile</h2>
            <p class="text-sm text-slate-500">Only Head account can change institution profile (mock-only)</p>
          </header>
          <form id="profileForm" class="p-5 grid grid-cols-1 gap-3">
            <div>
              <label class="block text-xs">Institution</label>
              <select id="fHei" class="w-full px-2 py-1 rounded border"></select>
            </div>
            <div>
              <label class="block text-xs">Institution Name</label>
              <input id="fName" class="w-full px-2 py-1 rounded border" />
            </div>
            <div>
              <label class="block text-xs">Short Name</label>
              <input id="fShort" class="w-full px-2 py-1 rounded border" />
            </div>
            <div class="grid md:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs">Region</label>
                <input id="fRegion" class="w-full px-2 py-1 rounded border" />
              </div>
              <div>
                <label class="block text-xs">Municipality</label>
                <input id="fMunicipality" class="w-full px-2 py-1 rounded border" />
              </div>
            </div>
            <div>
              <label class="block text-xs">Address</label>
              <input id="fAddress" class="w-full px-2 py-1 rounded border" />
            </div>
            <div class="grid md:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs">Head Name</label>
                <input id="fHead" class="w-full px-2 py-1 rounded border" />
              </div>
              <div>
                <label class="block text-xs">Head Title</label>
                <input id="fHeadTitle" class="w-full px-2 py-1 rounded border" />
              </div>
            </div>
            <div class="grid md:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs">Email</label>
                <input id="fEmail" type="email" class="w-full px-2 py-1 rounded border" />
              </div>
              <div>
                <label class="block text-xs">Phone</label>
                <input id="fPhone" class="w-full px-2 py-1 rounded border" />
              </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
              <button type="button" id="cancelBtn" class="px-3 py-2 rounded border">Cancel</button>
              <button class="px-3 py-2 rounded bg-emerald-600 text-white">Save Profile</button>
            </div>
          </form>
        </section>
      </div>
    </main>
  </div>

  <script type="module">
  // TODO[backend]: Replace mock import with server-provided data (DB queries or API endpoints).
  // Required data: heis
    // TODO[backend]: db.getHEIById(hei_id), db.updateInstitutionProfile(heiId, fields)

    const fHei = document.getElementById('fHei');
    const fName = document.getElementById('fName');
    const fShort = document.getElementById('fShort');
    const fRegion = document.getElementById('fRegion');
    const fMunicipality = document.getElementById('fMunicipality');
    const fAddress = document.getElementById('fAddress');
    const fHead = document.getElementById('fHead');
    const fHeadTitle = document.getElementById('fHeadTitle');
    const fEmail = document.getElementById('fEmail');
    const fPhone = document.getElementById('fPhone');
    const cancelBtn = document.getElementById('cancelBtn');
    const form = document.getElementById('profileForm');

    let state = heis.slice();

    fHei.innerHTML = heis.map(h => `<option value="${h.id}">${h.name}</option>`).join('');

    function load(heiId){
      const h = state.find(x=>x.id===Number(heiId)) || state[0];
      fName.value = h.name || '';
      fShort.value = h.shortName || '';
      fRegion.value = h.region || '';
      fMunicipality.value = h.municipality || '';
      fAddress.value = h.address || '';
      fHead.value = h.headName || '';
      fHeadTitle.value = h.headTitle || '';
      fEmail.value = h.email || '';
      fPhone.value = h.phone || '';
      fHei.value = h.id;
    }
    fHei.addEventListener('change', (e) => load(e.target.value));
    cancelBtn.addEventListener('click', () => load(fHei.value));

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const id = Number(fHei.value);
      const updated = {
        id,
        name: fName.value.trim(),
        shortName: fShort.value.trim(),
        region: fRegion.value.trim(),
        municipality: fMunicipality.value.trim(),
        address: fAddress.value.trim(),
        headName: fHead.value.trim(),
        headTitle: fHeadTitle.value.trim(),
        email: fEmail.value.trim(),
        phone: fPhone.value.trim(),
      };
      state = state.map(h => h.id===id ? { ...h, ...updated } : h);
      Swal.fire({ icon: 'success', title: 'Saved', text: 'Profile saved (mock).', timer: 1200, showConfirmButton: false });
      // TODO[backend]: db.updateHEIProfile(id, updated)
    });

    // Init
    load(heis[0]?.id || 1);
    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
