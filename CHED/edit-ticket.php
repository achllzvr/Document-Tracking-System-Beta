<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Ticket — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>
  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-3xl mx-auto">
        <form id="ticketForm" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">Ticket Details</h2>
            <p class="text-sm text-slate-500">Update fields and status</p>
          </div>
          <div class="p-5 grid md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label for="hei" class="block text-sm font-medium mb-1">Institution</label>
              <select id="hei" required class="w-full px-3 py-2 rounded border" aria-label="Institution"></select>
              <!-- TODO[backend]: load HEIs from db.getHEIs() -->
            </div>
            <div class="md:col-span-2">
              <label for="title" class="block text-sm font-medium mb-1">Title</label>
              <input id="title" required class="w-full px-3 py-2 rounded border" />
            </div>
            <div>
              <label for="category" class="block text-sm font-medium mb-1">Category</label>
              <select id="category" required class="w-full px-3 py-2 rounded border">
                <option value="Enrollment">Enrollment</option>
                <option value="Faculty">Faculty</option>
                <option value="Graduates">Graduates</option>
                <option value="Institutional Profile">Institutional Profile</option>
              </select>
            </div>
            <div>
              <label for="priority" class="block text-sm font-medium mb-1">Priority</label>
              <select id="priority" required class="w-full px-3 py-2 rounded border">
                <option>Urgent</option>
                <option>High</option>
                <option>Medium</option>
                <option>Low</option>
              </select>
            </div>
            <div>
              <label for="status" class="block text-sm font-medium mb-1">Status</label>
              <select id="status" required class="w-full px-3 py-2 rounded border">
                <option>Open</option>
                <option>In Progress</option>
                <option>Pending</option>
                <option>Resolved</option>
                <option>Closed</option>
              </select>
            </div>
            <div>
              <label for="due" class="block text-sm font-medium mb-1">Due Date</label>
              <input id="due" type="date" class="w-full px-3 py-2 rounded border" />
            </div>
            <div>
              <label for="assignee" class="block text-sm font-medium mb-1">Assignee</label>
              <input id="assignee" list="assigneeList" class="w-full px-3 py-2 rounded border" />
              <datalist id="assigneeList"></datalist>
              <!-- TODO[backend]: populate assignees from db.getHEIUsers(heiId) -->
            </div>
            <div class="md:col-span-2">
              <label for="desc" class="block text-sm font-medium mb-1">Description</label>
              <textarea id="desc" rows="4" class="w-full px-3 py-2 rounded border"></textarea>
            </div>
          </div>
          <div class="px-5 py-4 border-t flex items-center justify-between bg-slate-50">
            <a id="cancelLink" href="./view-tickets.php" class="text-sm text-slate-600 hover:underline">Cancel</a>
            <div class="flex items-center gap-2">
              <a id="openDetails" class="text-sm underline" href="#">Open details</a>
              <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-blue-600 text-white">
                <i data-lucide="save" class="h-4 w-4"></i>
                Save Changes
              </button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script type="module">
  // TODO[backend]: Replace mock import with server-provided data (DB queries or API endpoints).
  // Required data: tickets, heis

    const params = new URLSearchParams(location.search);
    const id = Number(params.get('ticket_id')) || tickets[0]?.id;
    const t = tickets.find(x => x.id === id);
    const backLink = document.getElementById('backLink');
    const cancelLink = document.getElementById('cancelLink');
    const openDetails = document.getElementById('openDetails');
    if (openDetails) openDetails.href = `./ticket-details.php?ticket_id=${id}`;
    if (backLink) backLink.href = `./ticket-details.php?ticket_id=${id}`;
    if (cancelLink) cancelLink.href = `./ticket-details.php?ticket_id=${id}`;

    const heiSel = document.getElementById('hei');
    heis.forEach(h => { const o=document.createElement('option'); o.value=h.id; o.textContent=h.name; heiSel.appendChild(o); });

    const assigneeList = document.getElementById('assigneeList');
    Array.from(new Set(tickets.map(t=>t.assigneeName).filter(Boolean))).forEach(n=>{
      const opt = document.createElement('option'); opt.value = n; assigneeList.appendChild(opt);
    });

    if (!t) {
      document.getElementById('ticketForm').innerHTML = '<div class="p-6">Ticket not found.</div>';
    } else {
      /** Prefill **/
      heiSel.value = String(t.heiId);
      document.getElementById('title').value = t.title;
      document.getElementById('category').value = t.category;
      document.getElementById('priority').value = t.priority;
      document.getElementById('status').value = t.status;
      document.getElementById('due').value = t.dueDate || '';
      document.getElementById('assignee').value = t.assigneeName || '';
      document.getElementById('desc').value = t.description || '';
    }

    document.getElementById('ticketForm').addEventListener('submit', (e) => {
      e.preventDefault();
      if (!t) return;
      const heiId = Number(heiSel.value);
      const hei = heis.find(h=>h.id===heiId);
      t.heiId = hei?.id ?? t.heiId;
      t.heiName = hei?.name ?? t.heiName;
      t.title = /** @type {HTMLInputElement} */(document.getElementById('title')).value.trim();
      t.category = /** @type {HTMLSelectElement} */(document.getElementById('category')).value;
      t.priority = /** @type {HTMLSelectElement} */(document.getElementById('priority')).value;
      t.status = /** @type {HTMLSelectElement} */(document.getElementById('status')).value;
      t.dueDate = /** @type {HTMLInputElement} */(document.getElementById('due')).value || null;
      t.assigneeName = /** @type {HTMLInputElement} */(document.getElementById('assignee')).value.trim();
      t.description = /** @type {HTMLTextAreaElement} */(document.getElementById('desc')).value.trim();
      t.updatedAt = new Date().toISOString();

      Swal.fire({ icon: 'success', title: 'Ticket updated (mock)', timer: 1200, showConfirmButton: false }).then(() => {
        location.href = `./ticket-details.php?ticket_id=${t.id}`;
      });

      // TODO[backend]: db.updateTicket(t)
    });

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
