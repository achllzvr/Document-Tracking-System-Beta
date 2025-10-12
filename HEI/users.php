<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Users — HEI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-4xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <div>
              <h2 class="font-semibold">Organization Users</h2>
              <p class="text-sm text-slate-500">Manage sub-users for this HEI (mock-only)</p>
            </div>
            <button id="addUser" class="px-3 py-2 rounded bg-emerald-600 text-white">Add User</button>
          </header>
          <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="text-slate-600 border-b">
                <tr>
                  <th class="py-2 text-left">Name</th>
                  <th class="text-left">Email</th>
                  <th class="text-left">Role</th>
                  <th class="text-left">Active</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="rows"></tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Add/Edit modal -->
  <div id="userModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="bg-white w-full max-w-md rounded-xl shadow-xl overflow-hidden">
      <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
        <h3 id="userTitle" class="font-semibold">Add User</h3>
        <button id="userClose" class="p-2 rounded hover:bg-slate-50" aria-label="Close"><i data-lucide="x" class="h-5 w-5"></i></button>
      </div>
      <form id="userForm" class="p-5 space-y-3">
        <div>
          <label class="block text-xs">Name</label>
          <input id="uName" class="w-full px-2 py-1 rounded border" required />
        </div>
        <div>
          <label class="block text-xs">Email</label>
          <input id="uEmail" type="email" class="w-full px-2 py-1 rounded border" required />
        </div>
        <div>
          <label class="block text-xs">Role</label>
          <select id="uRole" class="w-full px-2 py-1 rounded border">
            <option>HR</option>
            <option>Registrar</option>
            <option>Admin</option>
          </select>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" id="userCancel" class="px-3 py-2 rounded border">Cancel</button>
          <button class="px-3 py-2 rounded bg-emerald-600 text-white">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script type="module">
    // Simple in-memory users mock for this HEI
    let users = [
      { id: 1, name: 'Maria Santos', email: 'maria.santos@up.edu.ph', role: 'HR', active: true },
      { id: 2, name: 'Juan dela Cruz', email: 'juan.delacruz@up.edu.ph', role: 'Registrar', active: true },
    ];
    // TODO[backend]: db.getSubusersByHEI(heiId), db.createSubuser(), db.updateSubuser(), db.setSubuserActive(), db.resetPassword()

    const rows = document.getElementById('rows');
    const addUserBtn = document.getElementById('addUser');
    const userModal = document.getElementById('userModal');
    const userClose = document.getElementById('userClose');
    const userForm = document.getElementById('userForm');
    const userCancel = document.getElementById('userCancel');
    const uName = document.getElementById('uName');
    const uEmail = document.getElementById('uEmail');
    const uRole = document.getElementById('uRole');

    function render(){
      rows.innerHTML = users.map(u => `
        <tr class="border-b">
          <td class="py-2">${u.name}</td>
          <td>${u.email}</td>
          <td>${u.role}</td>
          <td>${u.active? 'Yes':'No'}</td>
          <td>
            <button class="text-sm text-blue-600" data-act="edit" data-id="${u.id}">Edit</button>
            <button class="text-sm text-rose-600 ml-3" data-act="toggle" data-id="${u.id}">${u.active? 'Disable':'Enable'}</button>
            <button class="text-sm text-slate-600 ml-3" data-act="reset" data-id="${u.id}">Reset PW</button>
          </td>
        </tr>
      `).join('') || '<tr><td colspan="5" class="py-6 text-center text-slate-500">No users yet.</td></tr>';
    }
    render();

    function openModal(u){
      userModal.classList.remove('hidden');
      userForm.dataset.editId = u?.id || '';
      uName.value = u?.name || '';
      uEmail.value = u?.email || '';
      uRole.value = u?.role || 'HR';
    }
    function closeModal(){ userModal.classList.add('hidden'); }

    addUserBtn.addEventListener('click', ()=> openModal(null));
    userClose.addEventListener('click', closeModal); userCancel.addEventListener('click', closeModal);

    rows.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-act]'); if (!btn) return;
      const id = Number(btn.dataset.id); const act = btn.dataset.act;
      const u = users.find(x=>x.id===id);
      if (act==='edit') openModal(u);
      if (act==='toggle') { users = users.map(x => x.id===id ? { ...x, active: !x.active } : x); render(); /* TODO[backend]: db.setSubuserActive(id, active) */ }
      if (act==='reset') { Swal.fire({ icon:'success', title:'Password reset', text:`Password reset for ${u.email} (mock).`, timer:1200, showConfirmButton:false }); /* TODO[backend]: db.resetPassword(id) */ }
    });

    userForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const id = userForm.dataset.editId ? Number(userForm.dataset.editId) : null;
      const obj = { id: id ?? (Math.max(0,...users.map(x=>x.id))+1), name: uName.value.trim(), email: uEmail.value.trim(), role: uRole.value, active: true };
      if (id) { users = users.map(x=> x.id===id ? obj : x); /* TODO[backend]: db.updateSubuser(obj) */ }
      else { users.push(obj); /* TODO[backend]: db.createSubuser(obj) */ }
      render(); closeModal();
    });

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
