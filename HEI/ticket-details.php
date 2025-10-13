<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ticket Details — HEI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.19.3/dist/xlsx.full.min.js"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
    <div class="max-w-4xl mx-auto space-y-6">
      <section id="header" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden"></section>
      <div class="grid md:grid-cols-2 gap-6">
        <section id="commentsBox" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden"></section>
        <section id="etlBox" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden"></section>
      </div>
    </div>
  </main>

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
  import { tickets, comments, templates, etlJobs, heis } from '../assets/mock/mock-data.js';

    const params = new URLSearchParams(location.search);
    const id = Number(params.get('ticket_id')) || tickets[0]?.id;
    const t = tickets.find(x => x.id === id);
    const header = document.getElementById('header');

    if (!t) {
      header.innerHTML = '<div class="p-6">Ticket not found.</div>';
    } else {
      header.innerHTML = `
        <div class="px-5 pt-5 pb-3 border-b">
          <h2 class="font-semibold">${t.title}</h2>
          <p class="text-sm text-slate-500">${t.heiName}</p>
        </div>
        <div class="p-5 grid md:grid-cols-2 gap-4 text-sm">
          <p><span class="text-slate-500">Category:</span> ${t.category}</p>
          <p><span class="text-slate-500">Priority:</span> ${t.priority}</p>
          <p><span class="text-slate-500">Status:</span> ${t.status}</p>
          <p><span class="text-slate-500">Due:</span> ${t.dueDate || '-'}</p>
        </div>
        <div class="px-5 pb-5 flex items-center gap-3">
          <a class="inline-flex items-center gap-2 px-3 py-2 rounded border hover:bg-slate-50" href="#" id="downloadBtn"><i data-lucide="download" class="h-4 w-4"></i> Download Template</a>
          <button class="inline-flex items-center gap-2 px-3 py-2 rounded bg-emerald-600 text-white" id="uploadBtn"><i data-lucide="upload" class="h-4 w-4"></i> Upload Completed</button>
        </div>`;
    }

    // Comments box
    const commentsBox = document.getElementById('commentsBox');
    commentsBox.innerHTML = `
      <div class="px-5 pt-5 pb-3 border-b">
        <h3 class="font-semibold">Comments</h3>
      </div>
      <div id="commentList"></div>
      <form id="commentForm" class="p-4 border-t grid grid-cols-[1fr_auto] gap-3">
        <input id="commentInput" required class="px-3 py-2 rounded border" placeholder="Write a comment..." aria-label="Add comment" />
        <button class="px-3 py-2 rounded bg-emerald-600 text-white">Post</button>
      </form>`;

    const list = document.getElementById('commentList');
    function renderComments(){
      const rows = comments.filter(c=>c.ticketId===id).sort((a,b)=> new Date(a.createdAt)-new Date(b.createdAt));
      list.innerHTML = rows.map(c=>`
        <div class="p-3 border-b">
          <p class="text-sm"><span class="font-medium">${c.userName}</span> — ${c.content}</p>
          <p class="text-xs text-slate-500">${new Date(c.createdAt).toLocaleString()}</p>
        </div>`).join('') || '<div class="p-4 text-sm text-slate-500">No comments yet.</div>';
    }
    renderComments();
    document.getElementById('commentForm').addEventListener('submit', (e)=>{
      e.preventDefault(); const input = document.getElementById('commentInput'); const content = input.value.trim(); if (!content) return;
      comments.push({ id: Math.random(), ticketId: id, userId: 0, userName: 'You', userRole: 'HEI', content, createdAt: new Date().toISOString() }); input.value=''; renderComments();
      // TODO[backend]: api.tickets.addComment(ticketId, content)
    });

    // ETL box
    const etlBox = document.getElementById('etlBox');
    function renderETL(){
      const jobs = etlJobs.filter(j=>j.ticketId===id);
      etlBox.innerHTML = `
        <div class="px-5 pt-5 pb-3 border-b"><h3 class="font-semibold">Ingestion Summary</h3></div>
        <div class="p-4 space-y-3">${jobs.length? jobs.map(j=>`
          <div class="p-3 rounded border ${j.status==='Success'?'border-emerald-200 bg-emerald-50': j.status==='Failed'?'border-rose-200 bg-rose-50':'border-amber-200 bg-amber-50'}">
            <div class="flex items-center justify-between">
              <p class="text-sm">${j.domain} — ${j.heiName}</p>
              <span class="inline-flex text-xs px-2 py-1 rounded border">${j.status}</span>
            </div>
            <p class="text-xs text-slate-600 mt-1">${j.successRows} / ${j.totalRows} rows processed</p>
            ${j.errors?.length ? `<div class="mt-2 text-xs text-rose-700 space-y-1">${j.errors.map(e=>`• ${e}`).join('<br>')}</div>` : ''}
          </div>`).join('') : '<p class="text-sm text-slate-500">No ETL jobs yet.</p>'}
        </div>`;
    }
    renderETL();

    // Download and Upload controls
    const downloadBtn = document.getElementById('downloadBtn');
    const uploadBtn = document.getElementById('uploadBtn');
    const modal = document.getElementById('uploadModal');
    const modalClose = document.getElementById('uploadClose');
    const fileInput = document.getElementById('fileInput');
    const gridHead = document.getElementById('gridHead');
    const gridBody = document.getElementById('gridBody');
    const mockSubmit = document.getElementById('mockSubmit');

    if (downloadBtn) downloadBtn.addEventListener('click',(e)=>{
      e.preventDefault(); const tpl = templates.find(tp=>tp.category===t?.category) || templates[0]; if (tpl){ window.open(tpl.file||'#','_blank'); }
      // TODO[backend]: serve tickets/download-template.php?ticket_id=...
    });
    if (uploadBtn) uploadBtn.addEventListener('click',()=>{ modal.classList.remove('hidden'); fileInput.value=''; gridHead.innerHTML=''; gridBody.innerHTML=''; });
    function closeUpload(){ modal.classList.add('hidden'); }
    modalClose.addEventListener('click', closeUpload);
    modal.addEventListener('click', (e)=>{ if (e.target===modal) closeUpload(); });

    fileInput.addEventListener('change', async (e) => {
      const file = e.target.files?.[0]; if (!file) return;
      const data = await file.arrayBuffer();
      const wb = XLSX.read(data, { type:'array' });
      const ws = wb.Sheets[wb.SheetNames[0]];
      const json = XLSX.utils.sheet_to_json(ws, { header: 1 });
      const rows = json.slice(0, 100);
      const headerRow = rows[0] || [];
      gridHead.innerHTML = `<tr>${headerRow.map(h=>`<th class='px-2 py-1 text-left border-b'>${String(h||'')}</th>`).join('')}</tr>`;
      gridBody.innerHTML = rows.slice(1).map(r=>`<tr>${headerRow.map((_,i)=>`<td class='px-2 py-1 border-b'>${String(r[i]??'')}</td>`).join('')}</tr>`).join('');
    });

    mockSubmit.addEventListener('click', () => {
      const now = new Date().toISOString();
      etlJobs.unshift({ id: 'etl-'+Math.random().toString(36).slice(2,8), heiId: t.heiId, heiName: heis.find(h=>h.id===t.heiId)?.name || '', ticketId: t.id, domain: t.category, status: 'Success', totalRows: 100, successRows: 100, errorRows: 0, errors: [], uploadedBy: 'You', createdAt: now });
      comments.push({ id: Math.random(), ticketId: t.id, userId: 0, userName: 'You', userRole: 'HEI', content: 'Uploaded template (mock).', createdAt: now });
      renderETL(); renderComments(); closeUpload();
      Swal.fire({ icon: 'success', title: 'Upload simulated', timer: 1200, showConfirmButton: false });
      // TODO[backend]: POST to tickets/upload-data.php
    });

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
