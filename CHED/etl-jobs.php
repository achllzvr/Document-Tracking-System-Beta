<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ETL Jobs — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <div>
              <h2 class="font-semibold">Jobs</h2>
              <p class="text-sm text-slate-500">Latest ingestion runs from HEI uploads</p>
            </div>
            <div class="text-sm text-slate-500">Total: <span id="totalCount">0</span></div>
          </header>
          <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="text-slate-600 border-b">
                <tr>
                  <th class="py-2 text-left">Job ID</th>
                  <th class="text-left">HEI</th>
                  <th class="text-left">Ticket</th>
                  <th class="text-left">Domain</th>
                  <th class="text-left">Status</th>
                  <th class="text-left">Counts</th>
                  <th class="text-left">Uploaded By</th>
                  <th class="text-left">Created</th>
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

  <!-- Detail Modal -->
  <div id="detailModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="detailTitle">
    <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl overflow-hidden">
      <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
        <h3 id="detailTitle" class="font-semibold">Job Details</h3>
        <button id="detailClose" class="p-2 rounded hover:bg-slate-50" aria-label="Close"><i data-lucide="x" class="h-5 w-5"></i></button>
      </div>
      <div class="p-5 space-y-3" id="detailContent"></div>
      <div class="px-5 pb-5 flex items-center justify-end gap-2">
        <button id="reprocessBtn" class="px-3 py-2 rounded border">Reprocess</button>
      </div>
    </div>
  </div>

  <script type="module">
    import { etlJobs, tickets, heis } from '../assets/mock/mock-data.js';
    // TODO[backend]: Replace with db.getEtlJobs(), db.getTicketById(), db.reprocessEtlJob(id)

    const rows = document.getElementById('rows');
    const totalCount = document.getElementById('totalCount');
    const modal = document.getElementById('detailModal');
    const modalClose = document.getElementById('detailClose');
    const modalContent = document.getElementById('detailContent');
    const reprocessBtn = document.getElementById('reprocessBtn');

    let data = etlJobs.slice();
    let currentId = null;

    function badge(status){
      const s = String(status).toLowerCase();
      if (s==='success') return '<span class="px-2 py-0.5 text-xs rounded bg-emerald-100 text-emerald-700 border border-emerald-200">Success</span>';
      if (s==='failed') return '<span class="px-2 py-0.5 text-xs rounded bg-rose-100 text-rose-700 border border-rose-200">Failed</span>';
      return '<span class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-700 border border-amber-200">Pending</span>';
    }
    function tr(j){
      const h = heis.find(x=>x.id===j.heiId); const heiName = h? h.name : `HEI #${j.heiId}`;
      const t = tickets.find(x=>x.id===j.ticketId); const tTitle = t? t.title : `Ticket #${j.ticketId}`;
      return `<tr class="border-b">
        <td class="py-2">${j.id}</td>
        <td>${heiName}</td>
        <td>${tTitle}</td>
        <td>${j.domain}</td>
        <td>${badge(j.status)}</td>
        <td>${j.successRows}/${j.totalRows} success${j.errorRows?`, ${j.errorRows} error(s)`:''}</td>
        <td>${j.uploadedBy||'-'}</td>
        <td>${new Date(j.createdAt).toLocaleString()}</td>
        <td><button class="text-blue-600 hover:underline text-sm" data-act="view" data-id="${j.id}">View</button></td>
      </tr>`;
    }
    function render(){
      totalCount.textContent = String(data.length);
      rows.innerHTML = data.map(tr).join('') || '<tr><td colspan="9" class="py-6 text-center text-slate-500">No jobs.</td></tr>';
    }
    render();

    function openModal(id){
      currentId = id; const j = data.find(x=>String(x.id)===String(id)); if(!j) return;
      const h = heis.find(x=>x.id===j.heiId); const heiName = h? h.name : `HEI #${j.heiId}`;
      const t = tickets.find(x=>x.id===j.ticketId); const tTitle = t? t.title : `Ticket #${j.ticketId}`;
      const errs = Array.isArray(j.errors) && j.errors.length ? `<ul class='list-disc pl-6 text-rose-700'>${j.errors.map(e=>`<li>${e}</li>`).join('')}</ul>` : '<p class="text-slate-600">No errors.</p>';
      modalContent.innerHTML = `
        <div class="grid md:grid-cols-2 gap-3 text-sm">
          <div><span class="text-slate-500">Job ID:</span> <span class="font-medium">${j.id}</span></div>
          <div><span class="text-slate-500">Status:</span> ${badge(j.status)}</div>
          <div><span class="text-slate-500">HEI:</span> ${heiName}</div>
          <div><span class="text-slate-500">Ticket:</span> ${tTitle}</div>
          <div><span class="text-slate-500">Domain:</span> ${j.domain}</div>
          <div><span class="text-slate-500">Created:</span> ${new Date(j.createdAt).toLocaleString()}</div>
          <div><span class="text-slate-500">Rows:</span> ${j.successRows}/${j.totalRows} success, ${j.errorRows} errors</div>
          <div><span class="text-slate-500">Uploaded By:</span> ${j.uploadedBy||'-'}</div>
        </div>
        <div class="mt-4">
          <p class="font-medium mb-1">Errors</p>
          ${errs}
        </div>
      `;
      modal.classList.remove('hidden');
    }
    function closeModal(){ modal.classList.add('hidden'); currentId = null; }
    modalClose.addEventListener('click', closeModal);

    rows.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-act="view"]'); if (!btn) return;
      openModal(btn.dataset.id);
    });

    reprocessBtn.addEventListener('click', async () => {
      if (!currentId) return;
      // Simulate reprocess
      Swal.fire({ icon:'info', title:'Reprocessing…', timer: 900, showConfirmButton: false });
      /* TODO[backend]: await db.reprocessEtlJob(currentId) */
      // Mock: mark as Success with same counts and clear errors
      data = data.map(j => String(j.id)===String(currentId) ? { ...j, status:'Success', errorRows:0, errors: [] } : j);
      render(); openModal(currentId);
    });

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
