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
  <title>HEI Dashboard — PRISM</title>
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
            <h2 class="font-semibold">Overview</h2>
            <p class="text-sm text-slate-500">Your ticket workload and latest comments</p>
          </header>
          <div id="stats" class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4"></div>
        </section>

        <div class="grid lg:grid-cols-2 gap-6">
          <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <header class="px-5 pt-5 pb-3 border-b">
              <h3 class="font-semibold">Recent Tickets</h3>
              <p class="text-sm text-slate-500">Most recent updates assigned to you</p>
            </header>
            <div id="recentTickets" class="p-4 divide-y"></div>
            <div class="p-4 border-t">
              <a href="./view-tickets.php" class="text-sm inline-flex items-center gap-2 px-3 py-2 rounded border hover:bg-slate-50">View tickets</a>
            </div>
          </section>

          <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <header class="px-5 pt-5 pb-3 border-b">
              <h3 class="font-semibold">Recent Comments</h3>
              <p class="text-sm text-slate-500">Latest discussion on your tickets</p>
            </header>
            <div id="recentComments" class="p-4 divide-y"></div>
          </section>
        </div>
      </div>
    </main>
  </div>

  <script type="module">
  // TODO[backend]: Replace mock import with server-provided data (DB queries or API endpoints).
  // Required data: tickets, comments, heis

    // Determine current HEI context (mock): hei_id param or use tickets[0].heiId
    const params = new URLSearchParams(location.search);
    const currentHeiId = Number(params.get('hei_id')) || tickets[0]?.heiId || heis[0]?.id;
    const myTickets = tickets.filter(t => t.heiId === currentHeiId);

    // Stats
    const openCount = myTickets.filter(t=>['Open','In Progress','Pending'].includes(t.status)).length;
    const urgentCount = myTickets.filter(t=>['Urgent','High'].includes(t.priority)).length;
    const closedCount = myTickets.filter(t=>['Resolved','Closed'].includes(t.status)).length;
    const stats = document.getElementById('stats');
    function card(label, value, color, icon){return `<div class="p-5 rounded-xl border shadow-sm"><div class="flex items-center justify-between"><div><p class="text-sm text-slate-500">${label}</p><p class="text-2xl">${value}</p></div><div class="p-3 rounded-lg ${color}"><i data-lucide="${icon}" class="h-6 w-6 text-white"></i></div></div></div>`}
    stats.innerHTML = [
      card('Open/Pending', openCount, 'bg-amber-500', 'ticket'),
      card('Urgent/High', urgentCount, 'bg-rose-500', 'alert-triangle'),
      card('Resolved/Closed', closedCount, 'bg-emerald-600', 'check-circle'),
    ].join('');

    // Recent tickets
    const recent = [...myTickets].sort((a,b)=> new Date(b.updatedAt)-new Date(a.updatedAt)).slice(0,5);
    document.getElementById('recentTickets').innerHTML = recent.map(t => `
      <a class="block py-3" href="./ticket-details.php?ticket_id=${t.id}">
        <div class="flex items-start gap-3">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium">${t.title}</p>
            <p class="text-xs text-slate-500">Due: ${t.dueDate || '-'}</p>
          </div>
          <span class="inline-flex text-xs px-2 py-1 rounded border">${t.status}</span>
        </div>
      </a>`).join('');

    // Recent comments for this HEI
    const ticketIds = new Set(myTickets.map(t=>t.id));
    const recentComments = comments.filter(c => ticketIds.has(c.ticketId)).slice(-6).reverse();
    document.getElementById('recentComments').innerHTML = recentComments.map(c => `
      <div class="py-3">
        <p class="text-sm"><span class="font-medium">${c.userName}</span> — ${c.content}</p>
        <p class="text-xs text-slate-500">${new Date(c.createdAt).toLocaleString()}</p>
      </div>`).join('') || '<div class="p-4 text-sm text-slate-500">No comments yet.</div>';

    if (window.lucide) lucide.createIcons();
    // TODO[backend]: hydrate from API for current HEI user
  </script>
</body>
</html>
