<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tickets — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">Filters</h2>
            <p class="text-sm text-slate-500">Filter by HEI, assignee, category, priority, status, due</p>
          </header>
          <div class="p-5 grid md:grid-cols-6 gap-3">
            <select id="fHei" class="px-3 py-2 rounded border"><option value="all">All HEIs</option></select>
            <input id="fAssignee" class="px-3 py-2 rounded border" placeholder="Assignee name" />
            <select id="fCategory" class="px-3 py-2 rounded border"><option value="all">All Categories</option></select>
            <select id="fPriority" class="px-3 py-2 rounded border"><option value="all">All Priorities</option><option>Urgent</option><option>High</option><option>Medium</option><option>Low</option></select>
            <select id="fStatus" class="px-3 py-2 rounded border"><option value="all">All Statuses</option><option>Open</option><option>In Progress</option><option>Pending</option><option>Resolved</option><option>Closed</option></select>
            <input id="fDue" type="date" class="px-3 py-2 rounded border" />
          </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <h2 class="font-semibold">Tickets</h2>
            <div class="flex items-center gap-3">
              <a href="./create-ticket.php" class="inline-flex items-center gap-2 text-sm px-3 py-2 rounded bg-blue-600 text-white">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Create Ticket
              </a>
              <div id="pager" class="text-sm text-slate-600"></div>
            </div>
          </header>
          <div id="ticketList" class="p-5 divide-y"></div>
        </section>
      </div>
    </main>
  </div>

  <script type="module">
    import { tickets, heis, paginate } from '../assets/mock/mock-data.js';

    const fHei = document.getElementById('fHei');
    const fAssignee = document.getElementById('fAssignee');
    const fCategory = document.getElementById('fCategory');
    const fPriority = document.getElementById('fPriority');
    const fStatus = document.getElementById('fStatus');
    const fDue = document.getElementById('fDue');
    const list = document.getElementById('ticketList');
    const pager = document.getElementById('pager');

    // Populate dropdowns
    heis.forEach(h => { const o=document.createElement('option'); o.value=h.id; o.textContent=h.name; fHei.appendChild(o); });
    const cats = Array.from(new Set(tickets.map(t=>t.category)));
    cats.forEach(c => { const o=document.createElement('option'); o.value=c; o.textContent=c; fCategory.appendChild(o); });

    let state = { page: 1, perPage: 10 };

    function applyFilters() {
      return tickets.filter(t => {
        const byHei = fHei.value==='all' || String(t.heiId)===String(fHei.value);
        const byAss = !fAssignee.value || (t.assigneeName||'').toLowerCase().includes(fAssignee.value.toLowerCase());
        const byCat = fCategory.value==='all' || t.category===fCategory.value;
        const byPri = fPriority.value==='all' || t.priority===fPriority.value;
        const bySta = fStatus.value==='all' || t.status===fStatus.value;
        const byDue = !fDue.value || (t.dueDate && t.dueDate === fDue.value);
        return byHei && byAss && byCat && byPri && bySta && byDue;
      });
    }

    function badge(cls, text){ return `<span class="inline-flex text-xs px-2 py-1 rounded border ${cls}">${text}</span>`; }

    function render() {
      const filtered = applyFilters();
      const { items, page, pages, total } = paginate(filtered, state.page, state.perPage);
      list.innerHTML = items.map(t => `
        <div class="py-3 flex items-start gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-medium">${t.title}</p>
            <p class="text-xs text-slate-500">${t.heiName}</p>
          </div>
          <div class="flex items-center gap-2">
            ${badge(t.priority==='Urgent'?'border-rose-300 text-rose-700 bg-rose-50': t.priority==='High'?'border-amber-300 text-amber-700 bg-amber-50':'border-slate-200 text-slate-700 bg-slate-50', t.priority)}
            ${badge('border-slate-200', t.status)}
            <a href="./ticket-details.php?ticket_id=${t.id}" class="text-blue-600 hover:underline text-sm">Open</a>
          </div>
        </div>
      `).join('');
      pager.textContent = `Showing ${items.length} of ${total} • Page ${page} / ${pages}`;
    }

    [fHei,fAssignee,fCategory,fPriority,fStatus,fDue].forEach(el => el.addEventListener('input', ()=>{ state.page=1; render(); }));

    render();
    if (window.lucide) lucide.createIcons();

    // TODO[backend]: db.getTickets(filters)
  </script>
</body>
</html>