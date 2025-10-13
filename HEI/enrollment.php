<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Enrollment Data — HEI</title>
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
              <p class="text-sm text-slate-500">Add or edit enrollment rows (mock-only)</p>
            </div>
            <button id="addBtn" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-emerald-600 text-white"><i data-lucide="plus" class="h-4 w-4"></i>Add Row</button>
          </header>
          <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="text-slate-600 border-b">
                <tr>
                  <th class="py-2">AY</th>
                  <th>Term</th>
                  <th>Program</th>
                  <th>Major</th>
                  <th>Year Level</th>
                  <th>Sex</th>
                  <th>Total</th>
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
        <div><label class="block text-xs">AY</label><input id="fAY" type="number" min="2000" max="2099" class="w-full px-2 py-1 rounded border" required /></div>
        <div><label class="block text-xs">Term</label><select id="fTerm" class="w-full px-2 py-1 rounded border"><option value="1">1</option><option value="2">2</option></select></div>
        <div class="col-span-2"><label class="block text-xs">Program</label><input id="fProgram" class="w-full px-2 py-1 rounded border" required /></div>
        <div class="col-span-2"><label class="block text-xs">Major</label><input id="fMajor" class="w-full px-2 py-1 rounded border" /></div>
        <div><label class="block text-xs">Year Level</label><input id="fYearLevel" type="number" min="1" max="7" class="w-full px-2 py-1 rounded border" required /></div>
        <div><label class="block text-xs">Sex</label><select id="fSex" class="w-full px-2 py-1 rounded border"><option value="m">Male</option><option value="f">Female</option></select></div>
        <div class="col-span-2"><label class="block text-xs">Total Count</label><input id="fTotal" type="number" min="0" class="w-full px-2 py-1 rounded border" required /></div>
        <div class="col-span-2 flex items-center justify-end gap-2 pt-2">
          <button type="button" id="editCancel" class="px-3 py-2 rounded border">Cancel</button>
          <button class="px-3 py-2 rounded bg-emerald-600 text-white">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script type="module">
  import { enrollmentData, heis } from '../assets/mock/mock-data.js';

    const params = new URLSearchParams(location.search);
    const heiId = Number(params.get('hei_id')) || 1;
    let data = enrollmentData.filter(r => r.heiId === heiId);

    const rows = document.getElementById('rows');
    const addBtn = document.getElementById('addBtn');

    function tr(r){
      return `<tr class="border-b">
        <td class="py-2">${r.acadYear}</td>
        <td>${r.term}</td>
        <td>${r.program}</td>
        <td>${r.major||'-'}</td>
        <td>${r.yearLevel}</td>
        <td>${r.sex==='m'?'Male':'Female'}</td>
        <td>${r.totalCount}</td>
        <td>
          <button class="text-blue-600 hover:underline text-sm" data-action="edit" data-id="${r.id}">Edit</button>
          <button class="text-rose-600 hover:underline text-sm ml-3" data-action="del" data-id="${r.id}">Delete</button>
        </td>
      </tr>`;
    }

    function render(){ rows.innerHTML = data.map(tr).join('') || '<tr><td colspan="8" class="py-6 text-center text-slate-500">No rows yet.</td></tr>'; }
    render();

    // Modal logic
    const modal = document.getElementById('editModal');
    const closeBtn = document.getElementById('editClose');
    const cancelBtn = document.getElementById('editCancel');
    const form = document.getElementById('editForm');
    let editing = null; // id or null
    function openModal(row){
      editing = row?.id ?? null;
      document.getElementById('fAY').value = row?.acadYear ?? new Date().getFullYear();
      document.getElementById('fTerm').value = row?.term ?? '1';
      document.getElementById('fProgram').value = row?.program ?? '';
      document.getElementById('fMajor').value = row?.major ?? '';
      document.getElementById('fYearLevel').value = row?.yearLevel ?? 1;
      document.getElementById('fSex').value = row?.sex ?? 'm';
      document.getElementById('fTotal').value = row?.totalCount ?? 0;
      modal.classList.remove('hidden');
    }
    function closeModal(){ modal.classList.add('hidden'); }
    closeBtn.addEventListener('click', closeModal); cancelBtn.addEventListener('click', closeModal);

    addBtn.addEventListener('click', () => openModal(null));
    rows.addEventListener('click', (e) => {
      const ed = e.target.closest('[data-action="edit"]');
      const del = e.target.closest('[data-action="del"]');
      if (ed){ const id = Number(ed.dataset.id); const row = data.find(x=>x.id===id); openModal(row); }
      if (del){ const id = Number(del.dataset.id); data = data.filter(x=>x.id!==id); render(); /* TODO[backend]: db.deleteEnrollmentRow(id) */ }
    });

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const row = {
        id: editing ?? (Math.max(0,...data.map(x=>x.id))+1),
        heiId,
        acadYear: Number(document.getElementById('fAY').value),
        term: document.getElementById('fTerm').value,
        program: document.getElementById('fProgram').value.trim(),
        major: document.getElementById('fMajor').value.trim(),
        yearLevel: Number(document.getElementById('fYearLevel').value),
        sex: document.getElementById('fSex').value,
        totalCount: Number(document.getElementById('fTotal').value),
        createdAt: new Date().toISOString(),
      };
      if (!row.program) return;
      if (editing){ data = data.map(x=> x.id===editing ? row : x); /* TODO[backend]: db.updateEnrollmentRow(row) */ }
      else { data.unshift(row); /* TODO[backend]: db.createEnrollmentRow(row) */ }
      render(); closeModal();
    });

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
