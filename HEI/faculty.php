<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Faculty Data — HEI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <div>
              <h2 class="font-semibold">Records</h2>
              <p class="text-sm text-slate-500">Add or edit faculty rows (mock-only)</p>
            </div>
            <button id="addBtn" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-emerald-600 text-white"><i data-lucide="plus" class="h-4 w-4"></i>Add Row</button>
          </header>
          <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="text-slate-600 border-b">
                <tr>
                  <th class="py-2">Name</th>
                  <th>Employment</th>
                  <th>Gender</th>
                  <th>Primary Teaching</th>
                  <th>Highest Degree</th>
                  <th>Discipline</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="rows"></tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Edit modal -->
  <div id="editModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="editTitle">
    <div class="bg-white w-full max-w-xl rounded-xl shadow-xl overflow-hidden">
      <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
        <h3 id="editTitle" class="font-semibold">Edit Row</h3>
        <button id="editClose" class="p-2 rounded hover:bg-slate-50" aria-label="Close edit modal"><i data-lucide="x" class="h-5 w-5"></i></button>
      </div>
      <form id="editForm" class="p-5 grid grid-cols-2 gap-3">
        <div class="col-span-2"><label class="block text-xs">Name</label><input id="fName" class="w-full px-2 py-1 rounded border" required /></div>
        <div><label class="block text-xs">Employment</label><select id="fEmployment" class="w-full px-2 py-1 rounded border"></select></div>
        <div><label class="block text-xs">Gender</label><select id="fGender" class="w-full px-2 py-1 rounded border"></select></div>
        <div><label class="block text-xs">Primary Teaching</label><select id="fPrimary" class="w-full px-2 py-1 rounded border"></select></div>
        <div><label class="block text-xs">Highest Degree</label><select id="fHighest" class="w-full px-2 py-1 rounded border"></select></div>
        <div class="col-span-2"><label class="block text-xs">Discipline</label><select id="fDiscipline" class="w-full px-2 py-1 rounded border"></select></div>
        <div class="col-span-2 flex items-center justify-end gap-2 pt-2">
          <button type="button" id="editCancel" class="px-3 py-2 rounded border">Cancel</button>
          <button class="px-3 py-2 rounded bg-emerald-600 text-white">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script type="module">
    import { facultyData, employmentCodes, degrees, disciplines, genders, heis } from '../../assets/mock/mock-data.js';

    const params = new URLSearchParams(location.search);
    const heiId = Number(params.get('hei_id')) || 1;
    let data = facultyData.filter(r => r.heiId === heiId);

    const rows = document.getElementById('rows');
    const addBtn = document.getElementById('addBtn');

    function tr(r){
      return `<tr class="border-b">
        <td class="py-2">${r.name}</td>
        <td>${r.employmentType}</td>
        <td>${r.gender}</td>
        <td>${r.primaryTeaching}</td>
        <td>${r.highestDegree}</td>
        <td>${r.discipline}</td>
        <td>
          <button class="text-blue-600 hover:underline text-sm" data-action="edit" data-id="${r.id}">Edit</button>
          <button class="text-rose-600 hover:underline text-sm ml-3" data-action="del" data-id="${r.id}">Delete</button>
        </td>
      </tr>`;
    }
    function render(){ rows.innerHTML = data.map(tr).join('') || '<tr><td colspan="7" class="py-6 text-center text-slate-500">No rows yet.</td></tr>'; }
    render();

    // Modal logic
    const modal = document.getElementById('editModal');
    const closeBtn = document.getElementById('editClose');
    const cancelBtn = document.getElementById('editCancel');
    const form = document.getElementById('editForm');
    const fName = document.getElementById('fName');
    const fEmployment = document.getElementById('fEmployment');
    const fGender = document.getElementById('fGender');
    const fPrimary = document.getElementById('fPrimary');
    const fHighest = document.getElementById('fHighest');
    const fDiscipline = document.getElementById('fDiscipline');

    // Populate dropdowns
    function fill(sel, arr){ sel.innerHTML = arr.map(x=>`<option>${x}</option>`).join(''); }
    fill(fEmployment, employmentCodes); fill(fGender, genders); fill(fPrimary, degrees); fill(fHighest, degrees); fill(fDiscipline, disciplines);

    let editing = null; // id or null
    function openModal(row){
      editing = row?.id ?? null;
      fName.value = row?.name ?? '';
      fEmployment.value = row?.employmentType ?? employmentCodes[0];
      fGender.value = row?.gender ?? genders[0];
      fPrimary.value = row?.primaryTeaching ?? degrees[0];
      fHighest.value = row?.highestDegree ?? degrees[0];
      fDiscipline.value = row?.discipline ?? disciplines[0];
      modal.classList.remove('hidden');
    }
    function closeModal(){ modal.classList.add('hidden'); }
    closeBtn.addEventListener('click', closeModal); cancelBtn.addEventListener('click', closeModal);
    addBtn.addEventListener('click', () => openModal(null));

    rows.addEventListener('click', (e) => {
      const ed = e.target.closest('[data-action="edit"]');
      const del = e.target.closest('[data-action="del"]');
      if (ed){ const id = Number(ed.dataset.id); const row = data.find(x=>x.id===id); openModal(row); }
      if (del){ const id = Number(del.dataset.id); data = data.filter(x=>x.id!==id); render(); /* TODO[backend]: db.deleteFacultyRow(id) */ }
    });

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const row = {
        id: editing ?? (Math.max(0,...data.map(x=>x.id))+1),
        heiId,
        name: fName.value.trim(),
        employmentType: fEmployment.value,
        gender: fGender.value,
        primaryTeaching: fPrimary.value,
        highestDegree: fHighest.value,
        discipline: fDiscipline.value,
        createdAt: new Date().toISOString(),
      };
      if (!row.name) return;
      if (editing){ data = data.map(x=> x.id===editing ? row : x); /* TODO[backend]: db.updateFacultyRow(row) */ }
      else { data.unshift(row); /* TODO[backend]: db.createFacultyRow(row) */ }
      render(); closeModal();
    });

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
