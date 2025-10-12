<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HEI Tickets — PRISM</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.19.3/dist/xlsx.full.min.js"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <div class="bg-emerald-600 text-white px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3"><i data-lucide="ticket" class="h-6 w-6"></i><h1 class="text-lg font-semibold">Tickets</h1></div>
    <a href="./hei-dashboard.php" class="text-sm underline">Back to Dashboard</a>
  </div>
  <div class="flex-1 flex">
    <aside class="w-64 bg-white border-r border-slate-200">
      <nav class="p-4 space-y-1">
        <a class="block px-3 py-2 rounded hover:bg-slate-50" href="./hei-dashboard.php">Dashboard</a>
        <a class="block px-3 py-2 rounded bg-slate-100" href="./view-tickets.php">Tickets</a>
      </nav>
    </aside>
    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">Filters</h2>
            <p class="text-sm text-slate-500">Filter by category, priority, status</p>
          </header>
          <div class="p-5 grid md:grid-cols-4 gap-3">
            <select id="fCategory" class="px-3 py-2 rounded border"><option value="all">All Categories</option></select>
            <select id="fPriority" class="px-3 py-2 rounded border"><option value="all">All Priorities</option><option>Urgent</option><option>High</option><option>Medium</option><option>Low</option></select>
            <select id="fStatus" class="px-3 py-2 rounded border"><option value="all">All Statuses</option><option>Open</option><option>In Progress</option><option>Pending</option><option>Resolved</option><option>Closed</option></select>
          </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <h2 class="font-semibold">Assigned Tickets</h2>
            <div id="pager" class="text-sm text-slate-600"></div>
          </header>
          <div id="ticketList" class="p-5 divide-y"></div>
        </section>
      </div>
    </main>
  </div>

  <!-- Upload modal -->
  <div id="uploadModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="uploadTitle">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl overflow-hidden">
      <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
        <div>
          <h3 id="uploadTitle" class="font-semibold">Upload Completed Template</h3>
          <p class="text-sm text-slate-500">Parse the file locally to preview before submission</p>
        </div>
        <button id="uploadClose" class="p-2 rounded hover:bg-slate-50" aria-label="Close upload modal"><i data-lucide="x" class="h-5 w-5"></i></button>
      </div>
      <div class="p-5 space-y-4">
        <input id="fileInput" type="file" accept=".xlsx,.csv" class="block" />
        <div id="grid" class="overflow-x-auto border rounded">
          <table class="min-w-full text-xs">
            <thead id="gridHead" class="bg-slate-50"></thead>
            <tbody id="gridBody"></tbody>
          </table>
        </div>
      </div>
      <div class="px-5 py-4 border-t bg-slate-50 flex items-center justify-between">
        <p class="text-xs text-slate-500">Note: In FE phase, nothing is sent to server.</p>
        <button id="mockSubmit" class="px-3 py-2 rounded bg-emerald-600 text-white">Simulate Submit</button>
      </div>
    </div>
  </div>

  <script type="module">
    import { tickets, templates, etlJobs, comments, heis } from '../../assets/mock/mock-data.js';

    // Determine current HEI by param or ticket default
    const params = new URLSearchParams(location.search);
    const currentHeiId = Number(params.get('hei_id')) || tickets[0]?.heiId || heis[0]?.id;
    const myTickets = tickets.filter(t => t.heiId === currentHeiId);

    const fCategory = document.getElementById('fCategory');
    const fPriority = document.getElementById('fPriority');
    const fStatus = document.getElementById('fStatus');
    const list = document.getElementById('ticketList');
    const pager = document.getElementById('pager');

    // Populate categories
    Array.from(new Set(myTickets.map(t=>t.category))).forEach(c=>{ const o=document.createElement('option'); o.value=c; o.textContent=c; fCategory.appendChild(o); });

    let state = { page: 1, perPage: 10 };
    function applyFilters(){
      return myTickets.filter(t =>
        (fCategory.value==='all'||t.category===fCategory.value) &&
        (fPriority.value==='all'||t.priority===fPriority.value) &&
        (fStatus.value==='all'||t.status===fStatus.value)
      );
    }
    function render(){
      const filtered = applyFilters();
      const total = filtered.length; const pages = Math.max(1, Math.ceil(total/state.perPage));
      const clamped = Math.min(Math.max(1, state.page), pages); const start=(clamped-1)*state.perPage; const items=filtered.slice(start,start+state.perPage);
      pager.textContent = `Showing ${items.length} of ${total} • Page ${clamped}/${pages}`;
      list.innerHTML = items.map(t => `
        <div class="py-3 flex items-start gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-medium">${t.title}</p>
            <p class="text-xs text-slate-500">Due: ${t.dueDate || '-'}</p>
          </div>
          <div class="flex items-center gap-2">
            <a class="text-blue-600 hover:underline text-sm" href="./ticket-details.php?ticket_id=${t.id}">Open</a>
            <a class="text-slate-700 text-sm inline-flex items-center gap-1 px-2 py-1 rounded border hover:bg-slate-50" href="#" data-action="download" data-id="${t.id}"><i data-lucide="download" class="h-4 w-4"></i> Template</a>
            <button class="text-sm inline-flex items-center gap-1 px-2 py-1 rounded bg-emerald-600 text-white" data-action="upload" data-id="${t.id}"><i data-lucide="upload" class="h-4 w-4"></i> Upload</button>
          </div>
        </div>`).join('');
      if (window.lucide) lucide.createIcons();
    }
    [fCategory,fPriority,fStatus].forEach(el=>el.addEventListener('input',()=>{state.page=1;render();}));
    render();

    // Download template (mock link): pick the first template matching category
    list.addEventListener('click',(e)=>{
      const a = e.target.closest('[data-action="download"]');
      const b = e.target.closest('[data-action="upload"]');
      if (a){ e.preventDefault(); const id = Number(a.dataset.id); const t = myTickets.find(x=>x.id===id); const tpl = templates.find(tp=>tp.category===t?.category) || templates[0]; if (tpl){ window.open(tpl.file || '#', '_blank'); } return; }
      if (b){ e.preventDefault(); const id = Number(b.dataset.id); openUpload(id); return; }
    });

    // Upload modal logic using SheetJS
    const modal = document.getElementById('uploadModal');
    const modalClose = document.getElementById('uploadClose');
    const fileInput = document.getElementById('fileInput');
    const gridHead = document.getElementById('gridHead');
    const gridBody = document.getElementById('gridBody');
    const mockSubmit = document.getElementById('mockSubmit');
    let currentTicketId = null; let parsedRows = [];

    function openUpload(ticketId){ currentTicketId = ticketId; modal.classList.remove('hidden'); fileInput.value=''; gridHead.innerHTML=''; gridBody.innerHTML=''; }
    function closeUpload(){ modal.classList.add('hidden'); }
    modalClose.addEventListener('click', closeUpload);
    modal.addEventListener('click', (e)=>{ if (e.target===modal) closeUpload(); });

    fileInput.addEventListener('change', async (e) => {
      const file = e.target.files?.[0]; if (!file) return;
      const data = await file.arrayBuffer();
      const wb = XLSX.read(data, { type:'array' });
      const ws = wb.Sheets[wb.SheetNames[0]];
      const json = XLSX.utils.sheet_to_json(ws, { header: 1 });
      // Render grid (first 100 rows)
      parsedRows = json.slice(0, 100);
      const header = parsedRows[0] || [];
      gridHead.innerHTML = `<tr>${header.map(h=>`<th class='px-2 py-1 text-left border-b'>${String(h||'')}</th>`).join('')}</tr>`;
      gridBody.innerHTML = parsedRows.slice(1).map(r=>`<tr>${header.map((_,i)=>`<td class='px-2 py-1 border-b'>${String(r[i]??'')}</td>`).join('')}</tr>`).join('');
    });

    mockSubmit.addEventListener('click', () => {
      if (!currentTicketId){ closeUpload(); return; }
      // Create a mock ETL job result and a comment
      const t = myTickets.find(x=>x.id===currentTicketId);
      const now = new Date().toISOString();
      etlJobs.unshift({ id: 'etl-'+Math.random().toString(36).slice(2,8), heiId: t.heiId, heiName: heis.find(h=>h.id===t.heiId)?.name || '', ticketId: t.id, domain: t.category, status: 'Success', totalRows: parsedRows.length-1, successRows: parsedRows.length-1, errorRows: 0, errors: [], uploadedBy: 'You', createdAt: now });
      comments.push({ id: Math.random(), ticketId: t.id, userId: t.assigneeId || 0, userName: 'You', userRole: 'HEI', content: 'Uploaded template (mock).', createdAt: now });
      closeUpload();
      Swal.fire({ icon: 'success', title: 'Upload simulated', timer: 1200, showConfirmButton: false });
      // TODO[backend]: POST to tickets/upload-data.php and show actual ETL summary
    });

    // TODO[backend]: fetch tickets for logged-in HEI user
  </script>
</body>
</html>
