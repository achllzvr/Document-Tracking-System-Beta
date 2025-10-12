<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ticket Details — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <div class="bg-blue-600 text-white px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3"><i data-lucide="ticket" class="h-6 w-6"></i><h1 class="text-lg font-semibold">Ticket Details</h1></div>
    <a href="./view-tickets.php" class="text-sm underline">Back to Tickets</a>
  </div>
  <main class="flex-1 p-6 overflow-y-auto">
    <div class="max-w-3xl mx-auto space-y-6">
      <section id="ticketHeader" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden"></section>
      <section class="grid md:grid-cols-2 gap-6">
        <div id="comments" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden"></div>
        <div id="etlSummary" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden"></div>
      </section>
    </div>
  </main>

  <template id="commentRow">
    <div class="p-3 border-b">
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs"></div>
        <div class="flex-1 min-w-0">
          <p class="text-sm"></p>
          <p class="text-xs text-slate-500"></p>
        </div>
      </div>
    </div>
  </template>

  <script type="module">
    import { tickets, comments, etlJobs } from '../assets/mock/mock-data.js';

    const params = new URLSearchParams(location.search);
    const id = Number(params.get('ticket_id')) || tickets[0]?.id;
    const t = tickets.find(x => x.id === id);

    // Header
    const header = document.getElementById('ticketHeader');
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
          <p><span class="text-slate-500">Status:</span> <select id="status" class="px-2 py-1 rounded border"><option>Open</option><option>In Progress</option><option>Pending</option><option>Resolved</option><option>Closed</option></select></p>
          <p><span class="text-slate-500">Due:</span> ${t.dueDate || '-'}</p>
        </div>
        <div class="px-5 pb-5">
          <p class="text-sm">${t.description}</p>
        </div>`;
      const statusSel = document.getElementById('status');
      statusSel.value = t.status;
      statusSel.addEventListener('change', () => {
        // Mock update
        t.status = statusSel.value;
        Swal.fire({ icon: 'success', title: 'Status updated (mock)', timer: 1000, showConfirmButton: false });
        // TODO[backend]: db.updateTicketStatus(id, status)
      });
    }

    // Comments
    const commentsBox = document.getElementById('comments');
    commentsBox.innerHTML = `
      <div class="px-5 pt-5 pb-3 border-b">
        <h3 class="font-semibold">Comments</h3>
        <p class="text-sm text-slate-500">Discuss ticket progress</p>
      </div>
      <div id="commentList"></div>
      <form id="commentForm" class="p-4 border-t grid grid-cols-[1fr_auto] gap-3">
        <input id="commentInput" required class="px-3 py-2 rounded border" placeholder="Write a comment..." aria-label="Add comment" />
        <button class="px-3 py-2 rounded bg-blue-600 text-white">Post</button>
      </form>`;

    const list = document.getElementById('commentList');
    function renderComments() {
      const rows = comments.filter(c => c.ticketId === id).sort((a,b)=> new Date(a.createdAt)-new Date(b.createdAt));
      list.innerHTML = rows.map(c => {
        const initials = (c.userName||'')[0]?.toUpperCase() + ((c.userName||'').split(' ')[1]?.[0]?.toUpperCase()||'');
        return `
          <div class="p-3 border-b">
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">${initials||'U'}</div>
              <div class="flex-1 min-w-0">
                <p class="text-sm"><span class="font-medium">${c.userName}</span> — ${c.content}</p>
                <p class="text-xs text-slate-500">${new Date(c.createdAt).toLocaleString()}</p>
              </div>
            </div>
          </div>`;
      }).join('');
    }
    renderComments();

    document.getElementById('commentForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const input = document.getElementById('commentInput');
      const content = input.value.trim();
      if (!content) return;
      comments.push({ id: Math.random(), ticketId: id, userId: 1, userName: 'Maria Santos (CHED)', userRole: 'CHED', content, createdAt: new Date().toISOString() });
      input.value = '';
      renderComments();
      // TODO[backend]: api.tickets.addComment(ticketId, content)
    });

    // ETL summary (mock)
    const etl = etlJobs.filter(j => j.ticketId === id);
    const etlBox = document.getElementById('etlSummary');
    etlBox.innerHTML = `
      <div class="px-5 pt-5 pb-3 border-b">
        <h3 class="font-semibold">Ingestion Summary</h3>
        <p class="text-sm text-slate-500">Latest data ingestion runs</p>
      </div>
      <div class="p-4 space-y-3">
        ${etl.length ? etl.map(j => `
          <div class="p-3 rounded border ${j.status==='Success'?'border-emerald-200 bg-emerald-50': j.status==='Failed'?'border-rose-200 bg-rose-50':'border-amber-200 bg-amber-50'}">
            <div class="flex items-center justify-between">
              <p class="text-sm">${j.domain} — ${j.heiName}</p>
              <span class="inline-flex text-xs px-2 py-1 rounded border">${j.status}</span>
            </div>
            <p class="text-xs text-slate-600 mt-1">${j.successRows} / ${j.totalRows} rows processed</p>
            ${j.errors?.length ? `<div class="mt-2 text-xs text-rose-700 space-y-1">${j.errors.map(e=>`• ${e}`).join('<br>')}</div>` : ''}
          </div>
        `).join('') : '<p class="text-sm text-slate-500">No ETL jobs yet.</p>'}
      </div>`;

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>