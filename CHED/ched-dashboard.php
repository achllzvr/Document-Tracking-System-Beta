<?php

// Start the session
session_start();

// Check for existing session
if (!isset($_SESSION['chedID']) || isset($_SESSION['heiID'])) {
  
  if (!isset($_SESSION['chedID'])) {
    // If a CHED user is logged in, redirect to CHED dashboard
    header("Location: ../php/ched_login.php");
    exit();
  } elseif (isset($_SESSION['heiID'])) {
    // If an HEI user is logged in, redirect to HEI dashboard
    header("Location: ../php/hei_login.php");
    exit();
  }

}

// Database connection
require_once('../classes/database.php');

// Instance of the database class
$con = new database();

// Alert Initialization
$sweetAlertConfig = "";

// Set User Name from Session
$userName = isset($_SESSION['chedName']) ? $_SESSION['chedName'] : 'Unknown User';

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CHED Dashboard — PRISM</title>
    <link rel="icon" type="image/png" href="../assets/media/ched_logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  </head>
  <body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>

    <div class="flex-1 flex">
      <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

      <!-- Main -->
      <main class="flex-1 p-6 overflow-y-auto">
        <div class="max-w-7xl mx-auto space-y-8">
          <!-- Stats cards -->
          <section id="stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"></section>

          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Recent Tickets -->
            <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
              <header class="px-5 pt-5 pb-3 border-b">
                <h2 class="font-semibold">Recent Tickets</h2>
                <p class="text-sm text-slate-500">Latest ticket activity across institutions</p>
              </header>
              <div id="recentTickets" class="p-4 space-y-2"></div>
              <div class="p-4 border-t">
                <a href="./view-tickets.php" class="inline-flex items-center gap-2 text-sm px-3 py-2 rounded border hover:bg-slate-50">
                  View all tickets
                </a>
              </div>
            </section>

            <!-- Recent ETL Jobs -->
            <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
              <header class="px-5 pt-5 pb-3 border-b">
                <h2 class="font-semibold">Recent ETL Jobs</h2>
                <p class="text-sm text-slate-500">Data ingestion and processing status</p>
              </header>
              <div id="recentETL" class="p-4 space-y-2"></div>
              <div class="p-4 border-t">
                <a href="./etl-jobs.php" class="inline-flex items-center gap-2 text-sm px-3 py-2 rounded border hover:bg-slate-50">
                  View all jobs
                </a>
              </div>
            </section>
          </div>
          <!-- Data updates summary -->
          <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
              <h3 class="font-semibold mb-3">Data Updates Summary</h3>
              <div class="grid md:grid-cols-3 gap-4">
                <div class="p-4 bg-slate-50 rounded-lg border">
                  <p class="text-xs text-slate-500">Enrollment</p>
                  <p class="text-2xl font-semibold">1,245</p>
                  <p class="text-xs text-slate-400">Records updated this week</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-lg border">
                  <p class="text-xs text-slate-500">Faculty</p>
                  <p class="text-2xl font-semibold">892</p>
                  <p class="text-xs text-slate-400">Records updated this week</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-lg border">
                  <p class="text-xs text-slate-500">Graduates</p>
                  <p class="text-2xl font-semibold">567</p>
                  <p class="text-xs text-slate-400">Records updated this week</p>
                </div>
              </div>
            </div>
          </section>
        </div>
      </main>
    </div>

    <!-- Notifications dropdown -->
    <div id="notifDropdown" class="hidden fixed z-50 right-4 top-16 w-96 bg-white shadow-xl border border-slate-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b flex items-center justify-between">
        <div>
          <p class="font-semibold">Notifications</p>
          <p class="text-xs text-slate-500">Latest updates</p>
        </div>
        <button id="notifMarkRead" class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Mark all read</button>
      </div>
      <div id="notifList" class="max-h-96 overflow-y-auto divide-y"></div>
    </div>

    <script type="module">
      import { tickets, heis, etlJobs, notifications, paginate } from '../assets/mock/mock-data.js';

      const $ = (sel, el=document) => el.querySelector(sel);
      const $$ = (sel, el=document) => Array.from(el.querySelectorAll(sel));

      function statCard(label, value, icon, color) {
        return `
          <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-slate-500">${label}</p>
                <p class="text-2xl mt-1">${value}</p>
              </div>
              <div class="p-3 rounded-lg ${color}"><i data-lucide="${icon}" class="h-6 w-6 text-white"></i></div>
            </div>
          </div>`;
      }

      // Stats
      const openTickets = tickets.filter(t => ['Open','In Progress','Pending'].includes(t.status));
      const urgentTickets = tickets.filter(t => ['Urgent','High'].includes(t.priority));
      $('#stats').innerHTML = [
        statCard('Total HEIs', heis.length, 'building-2', 'bg-blue-500'),
        statCard('Open Tickets', openTickets.length, 'ticket', 'bg-orange-500'),
        statCard('Urgent Tickets', urgentTickets.length, 'alert-circle', 'bg-red-500'),
        statCard('Active Users', 247, 'users', 'bg-emerald-500'),
      ].join('');

      // Recent tickets
      const recent = [...tickets].sort((a,b)=> new Date(b.updatedAt)-new Date(a.updatedAt)).slice(0,5);
      $('#recentTickets').innerHTML = recent.map(t => `
        <a href="./ticket-details.php?ticket_id=${t.id}" class="flex items-start gap-3 p-3 hover:bg-slate-50 rounded-lg">
          <div class="flex-1 min-w-0">
            <p class="text-sm truncate">${t.title}</p>
            <p class="text-xs text-slate-500">${t.heiName}</p>
          </div>
          <div class="flex flex-col items-end gap-1">
            <span class="inline-flex items-center text-xs px-2 py-1 rounded border ${t.priority==='Urgent'?'border-rose-300 text-rose-700 bg-rose-50': t.priority==='High'?'border-amber-300 text-amber-700 bg-amber-50':'border-slate-200 text-slate-700 bg-slate-50'}">${t.priority}</span>
            <span class="inline-flex items-center text-xs px-2 py-1 rounded border border-slate-200">${t.status}</span>
          </div>
        </a>
      `).join('');

      // Recent ETL
      const latestETL = [...etlJobs].slice(0,5);
      $('#recentETL').innerHTML = latestETL.map(j => `
        <div class="flex items-start gap-3 p-3 hover:bg-slate-50 rounded-lg">
          <div class="mt-1">${j.status==='Success' ? '<i data-lucide="check-circle" class="h-5 w-5 text-emerald-500"></i>' : j.status==='Failed' ? '<i data-lucide="x-circle" class="h-5 w-5 text-rose-500"></i>' : '<i data-lucide="alert-circle" class="h-5 w-5 text-amber-500"></i>'}</div>
          <div class="flex-1 min-w-0">
            <p class="text-sm">${j.domain} - ${j.heiName}</p>
            <p class="text-xs text-slate-500">${j.successRows} / ${j.totalRows} rows processed</p>
          </div>
          <span class="inline-flex items-center text-xs px-2 py-1 rounded border ${j.status==='Success'?'border-emerald-300 text-emerald-700 bg-emerald-50': j.status==='Failed'?'border-rose-300 text-rose-700 bg-rose-50':'border-amber-300 text-amber-700 bg-amber-50'}">${j.status}</span>
        </div>
      `).join('');

      // Notifications panel (mock-only)
      const notif = { items: [...notifications], unread: notifications.filter(n=>!n.read).length };
      const notifBtn = document.getElementById('notifBtn');
      const notifBadge = document.getElementById('notifBadge');
      const notifPanel = document.getElementById('notifDropdown');
      const notifList = document.getElementById('notifList');
      const notifMark = document.getElementById('notifMarkRead');

      function renderNotif() {
        if (notif.unread > 0) { notifBadge.textContent = String(notif.unread); notifBadge.classList.remove('hidden'); }
        else { notifBadge.classList.add('hidden'); }
        notifList.innerHTML = notif.items.length ? notif.items.map(n => `
          <a href="${n.link || '#'}" class="flex items-start gap-3 p-3 hover:bg-slate-50">
            <i data-lucide="bell" class="h-4 w-4 mt-1"></i>
            <div class="min-w-0">
              <p class="text-sm">${n.title}</p>
              <p class="text-xs text-slate-500">${n.message}</p>
            </div>
            ${n.read ? '' : '<span class="ml-auto inline-block w-2 h-2 rounded-full bg-rose-500"></span>'}
          </a>
        `).join('') : '<div class="p-6 text-center text-slate-500 text-sm">No notifications.</div>';
        if (window.lucide) lucide.createIcons();
      }
      function togglePanel(forceOpen) {
        const open = !notifPanel.classList.contains('hidden');
        const willOpen = forceOpen ?? !open;
        if (willOpen) notifPanel.classList.remove('hidden'); else notifPanel.classList.add('hidden');
      }
      notifBtn.addEventListener('click', (e) => { e.stopPropagation(); togglePanel(true); });
      document.addEventListener('click', () => togglePanel(false));
      notifMark.addEventListener('click', () => { notif.items = notif.items.map(n => ({...n, read: true})); notif.unread = 0; renderNotif(); });
      renderNotif();

      // Accessibility improvements
      notifBtn.setAttribute('aria-haspopup','true');
      notifBtn.setAttribute('aria-expanded','false');
      notifBtn.addEventListener('click',()=>{
        const open = !notifPanel.classList.contains('hidden');
        notifBtn.setAttribute('aria-expanded', String(open));
      });

      // TODO[backend]: replace all mock sources with API calls
    </script>
  </body>
</html>
