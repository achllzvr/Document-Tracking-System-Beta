<?php

// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// CHED protector
require_once __DIR__ . '/../includes/ched_protect.php';

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Ticket — CHED</title>
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
            <h2 class="font-semibold">New Ticket</h2>
            <p class="text-sm text-slate-500">Set organization, details, and due date</p>
          </div>
          <div class="p-5 grid md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label for="hei" class="block text-sm font-medium mb-1">Institution</label>
              <select id="hei" required class="w-full px-3 py-2 rounded border" aria-label="Institution"></select>
              <!-- TODO[backend]: load HEIs from db.getHEIs() -->
            </div>
            <div class="md:col-span-2">
              <label for="title" class="block text-sm font-medium mb-1">Title</label>
              <input id="title" required class="w-full px-3 py-2 rounded border" placeholder="e.g., Q1 2026 Enrollment Data Submission" />
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
                <option selected>Medium</option>
                <option>Low</option>
              </select>
            </div>
            <div>
              <label for="due" class="block text-sm font-medium mb-1">Due Date</label>
              <input id="due" type="date" class="w-full px-3 py-2 rounded border" />
            </div>
            <div>
              <label for="assignee" class="block text-sm font-medium mb-1">Assignee</label>
              <input id="assignee" list="assigneeList" class="w-full px-3 py-2 rounded border" placeholder="Type assignee name" />
              <datalist id="assigneeList"></datalist>
              <!-- TODO[backend]: populate assignees from db.getHEIUsers(heiId) -->
            </div>
            <div class="md:col-span-2">
              <label for="desc" class="block text-sm font-medium mb-1">Description</label>
              <textarea id="desc" rows="4" class="w-full px-3 py-2 rounded border" placeholder="Describe the task, template to use, and any notes..."></textarea>
            </div>
          </div>
          <div class="px-5 py-4 border-t flex items-center justify-between bg-slate-50">
            <a href="./view-tickets.php" class="text-sm text-slate-600 hover:underline">Cancel</a>
            <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-blue-600 text-white">
              <i data-lucide="save" class="h-4 w-4"></i>
              Create Ticket
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script type="module">
  // TODO[backend]: Replace mock import with server-provided data (DB queries or API endpoints).
  // Required data: tickets, heis, notifications

    // Populate HEIs
    const heiSel = document.getElementById('hei');
    heis.forEach(h => { const o=document.createElement('option'); o.value=h.id; o.textContent=h.name; heiSel.appendChild(o); });

    // Populate assignee datalist from existing ticket assignees (mock)
    const assigneeList = document.getElementById('assigneeList');
    Array.from(new Set(tickets.map(t=>t.assigneeName).filter(Boolean))).forEach(n=>{
      const opt = document.createElement('option'); opt.value = n; assigneeList.appendChild(opt);
    });

    document.getElementById('ticketForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const heiId = Number(heiSel.value);
      const hei = heis.find(h=>h.id===heiId);
      const title = /** @type {HTMLInputElement} */(document.getElementById('title')).value.trim();
      const category = /** @type {HTMLSelectElement} */(document.getElementById('category')).value;
      const priority = /** @type {HTMLSelectElement} */(document.getElementById('priority')).value;
      const dueDate = /** @type {HTMLInputElement} */(document.getElementById('due')).value || null;
      const assigneeName = /** @type {HTMLInputElement} */(document.getElementById('assignee')).value.trim();
      const description = /** @type {HTMLTextAreaElement} */(document.getElementById('desc')).value.trim();

      if (!hei || !title) return;

      // Create mock ticket
      const now = new Date().toISOString();
      const newId = Math.max(0, ...tickets.map(t=>t.id)) + 1;
      const newTicket = {
        id: newId,
        heiId: hei.id,
        heiName: hei.name,
        title,
        category,
        priority,
        status: 'Open',
        assigneeId: null,
        assigneeName: assigneeName || '',
        dueDate,
        description,
        createdBy: 'Maria Santos (CHED)',
        createdAt: now,
        updatedAt: now,
      };
      tickets.unshift(newTicket);

      // Mock notification
      notifications.unshift({ id: 'notif-'+newId, userId: 'ched-1', type: 'ticket', title: 'Ticket Created', message: `Created: ${title} (${hei.name})`, read: false, createdAt: now, link: `./ticket-details.php?ticket_id=${newId}` });

      // Notify and redirect
      Swal.fire({ icon: 'success', title: 'Ticket created (mock)', timer: 1200, showConfirmButton: false }).then(() => {
        location.href = `./ticket-details.php?ticket_id=${newId}`;
      });

      // TODO[backend]: db.createTicket({heiId, title, category, priority, dueDate, assignee, description})
      // TODO[backend]: api.notifications.create({type:'ticket', ...})
    });

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
