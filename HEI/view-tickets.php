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
  <title>HEI Tickets — PRISM</title>
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

</body>
</html>
