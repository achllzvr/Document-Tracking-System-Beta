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
      <div class="grid md:grid-cols-1 gap-6">
        <section id="commentsBox" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden"></section>
      </div>
    </div>
  </main>

  <!-- Upload modal removed -->

  <script type="module">
  import { tickets, comments, templates } from '../assets/mock/mock-data.js';

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

    // ETL box removed

    // Download and Upload controls
    const downloadBtn = document.getElementById('downloadBtn');
  // Upload controls removed

    if (downloadBtn) downloadBtn.addEventListener('click',(e)=>{
      e.preventDefault(); const tpl = templates.find(tp=>tp.category===t?.category) || templates[0]; if (tpl){ window.open(tpl.file||'#','_blank'); }
      // TODO[backend]: serve tickets/download-template.php?ticket_id=...
    });
    // Upload modal handlers removed

    // File parse handlers removed

    // Upload simulation removed

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
