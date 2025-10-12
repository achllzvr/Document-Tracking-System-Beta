<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Calendar — HEI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <div class="bg-emerald-600 text-white px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3"><i data-lucide="calendar" class="h-6 w-6"></i><h1 class="text-lg font-semibold">My Calendar</h1></div>
    <a href="./hei-dashboard.php" class="text-sm underline">Back to Dashboard</a>
  </div>
  <div class="flex-1 flex">
    <aside class="w-64 bg-white border-r border-slate-200">
      <nav class="p-4 space-y-1">
        <a class="block px-3 py-2 rounded hover:bg-slate-50" href="./hei-dashboard.php">Dashboard</a>
        <a class="block px-3 py-2 rounded hover:bg-slate-50" href="./users.php">Users</a>
        <a class="block px-3 py-2 rounded bg-slate-100" href="./set-calendar.php">Calendar</a>
      </nav>
    </aside>
    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-4xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <div>
              <h2 class="font-semibold">Events</h2>
              <p class="text-sm text-slate-500">Add personal events; ticket due dates are shown below (mock).</p>
            </div>
            <button id="addEvent" class="px-3 py-2 rounded bg-emerald-600 text-white">Add Event</button>
          </header>
          <div class="p-5">
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <h3 class="font-medium mb-2">My Events</h3>
                <ul id="eventList" class="space-y-3 text-sm"></ul>
              </div>
              <div>
                <h3 class="font-medium mb-2">Ticket Due Dates</h3>
                <ul id="ticketList" class="space-y-2 text-sm text-slate-700"></ul>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Event modal -->
  <div id="eventModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="bg-white w-full max-w-md rounded-xl shadow-xl overflow-hidden">
      <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
        <h3 id="eventTitle" class="font-semibold">Add Event</h3>
        <button id="eventClose" class="p-2 rounded hover:bg-slate-50" aria-label="Close"><i data-lucide="x" class="h-5 w-5"></i></button>
      </div>
      <form id="eventForm" class="p-5 space-y-3">
        <div>
          <label class="block text-xs">Title</label>
          <input id="eTitle" class="w-full px-2 py-1 rounded border" required />
        </div>
        <div class="grid md:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs">Date</label>
            <input id="eDate" type="date" class="w-full px-2 py-1 rounded border" required />
          </div>
          <div>
            <label class="block text-xs">Time</label>
            <input id="eTime" type="time" class="w-full px-2 py-1 rounded border" />
          </div>
        </div>
        <div>
          <label class="block text-xs">Notes</label>
          <textarea id="eNotes" class="w-full px-2 py-1 rounded border" rows="3"></textarea>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" id="eventCancel" class="px-3 py-2 rounded border">Cancel</button>
          <button class="px-3 py-2 rounded bg-emerald-600 text-white">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script type="module">
    import { tickets } from '../../assets/mock/mock-data.js';
    // TODO[backend]: db.getCalendarEvents(heiId), db.saveCalendarEvent(event), db.deleteCalendarEvent(id)

    let events = [
      { id: 1, title: 'Faculty meeting', date: new Date().toISOString().slice(0,10), time: '09:00', notes: 'Discuss enrollment issues' }
    ];

    const eventList = document.getElementById('eventList');
    const ticketList = document.getElementById('ticketList');
    const addEventBtn = document.getElementById('addEvent');
    const eventModal = document.getElementById('eventModal');
    const eventClose = document.getElementById('eventClose');
    const eventForm = document.getElementById('eventForm');
    const eventCancel = document.getElementById('eventCancel');
    const eTitle = document.getElementById('eTitle');
    const eDate = document.getElementById('eDate');
    const eTime = document.getElementById('eTime');
    const eNotes = document.getElementById('eNotes');

    function renderEvents(){
      eventList.innerHTML = events.map(ev => `
        <li class="border rounded p-3">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium">${ev.title}</p>
              <p class="text-xs text-slate-500">${ev.date} ${ev.time? '• '+ev.time : ''}</p>
            </div>
            <div class="flex items-center gap-2">
              <button class="text-sm text-blue-600" data-act="edit" data-id="${ev.id}">Edit</button>
              <button class="text-sm text-rose-600" data-act="delete" data-id="${ev.id}">Delete</button>
            </div>
          </div>
          <p class="text-sm text-slate-700 mt-2">${ev.notes||''}</p>
        </li>
      `).join('') || '<li class="text-slate-500">No events.</li>';
    }

    function renderTickets(){
      ticketList.innerHTML = tickets.map(t => `<li class="border rounded p-2"><div class="flex items-center justify-between"><div><p class="font-medium">${t.title}</p><p class="text-xs text-slate-500">${t.heiName} — Due ${t.dueDate}</p></div><div><a class="text-sm text-blue-600" href="../CHED/ticket-details.php?ticket_id=${t.id}">View</a></div></div></li>`).join('');
    }

    function openModal(ev){
      eventModal.classList.remove('hidden');
      eventForm.dataset.editId = ev?.id || '';
      eTitle.value = ev?.title || '';
      eDate.value = ev?.date || new Date().toISOString().slice(0,10);
      eTime.value = ev?.time || '';
      eNotes.value = ev?.notes || '';
    }
    function closeModal(){ eventModal.classList.add('hidden'); }

    addEventBtn.addEventListener('click', ()=> openModal(null));
    eventClose.addEventListener('click', closeModal); eventCancel.addEventListener('click', closeModal);

    eventForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const id = eventForm.dataset.editId ? Number(eventForm.dataset.editId) : null;
      const obj = { id: id ?? (Math.max(0,...events.map(x=>x.id))+1), title: eTitle.value.trim(), date: eDate.value, time: eTime.value, notes: eNotes.value.trim() };
      if (id) { events = events.map(x=> x.id===id ? obj : x); /* TODO[backend]: db.updateCalendarEvent(obj) */ }
      else { events.push(obj); /* TODO[backend]: db.createCalendarEvent(obj) */ }
      renderEvents(); closeModal();
    });

    eventList.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-act]'); if (!btn) return;
      const id = Number(btn.dataset.id); const act = btn.dataset.act;
      if (act==='edit') { const ev = events.find(x=>x.id===id); openModal(ev); }
      if (act==='delete') { events = events.filter(x=>x.id!==id); renderEvents(); /* TODO[backend]: db.deleteCalendarEvent(id) */ }
    });

    // Init
    renderEvents(); renderTickets();
    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
