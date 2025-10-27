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
  <title>Templates & Mappings — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.19.3/dist/xlsx.full.min.js"></script>
</head>
<body class="ched-min-h-screen">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>
  <div class="ched-flex-1">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="ched-main">
      <div class="ched-max-w-7xl ched-space-y-6">
        <section class="ched-card">
          <header class="ched-card-header ched-items-center ched-justify-between" style="display: flex;">
            <div>
              <h2 class="ched-font-semibold">Domain Templates</h2>
              <p class="ched-text-sm">Upload or replace the templates used by HEIs</p>
            </div>
            <button id="addTemplate" class="ched-btn ched-btn-primary">Add Template</button>
          </header>
          <div class="p-5 ched-overflow-x-auto">
            <table class="min-w-full ched-text-sm">
              <thead class="ched-text-slate-800" style="border-bottom: 1px solid #e5e7eb;">
                <tr>
                  <th class="py-2 text-left">Name</th>
                  <th class="text-left">Category</th>
                  <th class="text-left">Version</th>
                  <th class="text-left">File</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="tplRows"></tbody>
            </table>
          </div>
        </section>

        <section class="ched-card">
          <header class="ched-card-header ched-items-center ched-justify-between" style="display: flex;">
            <div>
              <h2 class="ched-font-semibold">Column Mappings</h2>
              <p class="ched-text-sm">Define how uploaded columns map to system fields</p>
            </div>
            <button id="addMapping" class="ched-btn ched-btn-outline">Create Mapping</button>
          </header>
          <div class="p-5">
            <div id="mapEmpty" class="ched-text-sm" style="color: #64748b;">No mappings created yet. Click "Create Mapping" to add one.</div>
            <div id="mapList" class="hidden ched-grid ched-gap-3"></div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Upload/Replace Template Modal -->
  <div id="uploadModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="uploadTitle">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl overflow-hidden">
      <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
        <h3 id="uploadTitle" class="font-semibold">Upload Template</h3>
        <button id="uploadClose" class="p-2 rounded hover:bg-slate-50" aria-label="Close"><i data-lucide="x" class="h-5 w-5"></i></button>
      </div>
      <form id="uploadForm" class="p-5 space-y-3">
        <div>
          <label class="block text-xs">Name</label>
          <input id="uName" class="w-full px-2 py-1 rounded border" required />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs">Category</label>
            <select id="uCategory" class="w-full px-2 py-1 rounded border">
              <option>Enrollment</option>
              <option>Faculty</option>
              <option>Graduates</option>
            </select>
          </div>
          <div>
            <label class="block text-xs">Version</label>
            <input id="uVersion" class="w-full px-2 py-1 rounded border" placeholder="e.g. 1.1" />
          </div>
        </div>
        <div>
          <label class="block text-xs">Spreadsheet File (.xlsx)</label>
          <input id="uFile" type="file" accept=".xlsx" class="w-full" />
        </div>
        <div id="uPreview" class="border rounded p-3 text-xs text-slate-600 hidden">Preview will appear here after selecting a file.</div>
        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" id="uploadCancel" class="px-3 py-2 rounded border">Cancel</button>
          <button class="px-3 py-2 rounded bg-blue-600 text-white">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Mapping Editor Modal -->
  <div id="mappingModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="mapTitle">
    <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl overflow-hidden">
      <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
        <h3 id="mapTitle" class="font-semibold">Create Mapping</h3>
        <button id="mapClose" class="p-2 rounded hover:bg-slate-50" aria-label="Close"><i data-lucide="x" class="h-5 w-5"></i></button>
      </div>
      <form id="mapForm" class="p-5 space-y-4">
        <div class="grid md:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs">Target Domain</label>
            <select id="mDomain" class="w-full px-2 py-1 rounded border">
              <option>Enrollment</option>
              <option>Faculty</option>
              <option>Graduates</option>
            </select>
          </div>
          <div>
            <label class="block text-xs">Template</label>
            <select id="mTemplate" class="w-full px-2 py-1 rounded border"></select>
          </div>
        </div>
        <div>
          <label class="block text-xs mb-1">Column Mappings</label>
          <div id="mFields" class="grid md:grid-cols-2 gap-2">
            <!-- JS will inject rows like: [Source Column] -> [Target Field] -->
          </div>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" id="mapCancel" class="px-3 py-2 rounded border">Cancel</button>
          <button class="px-3 py-2 rounded bg-blue-600 text-white">Save Mapping</button>
        </div>
      </form>
    </div>
  </div>

  <script type="module">
  // TODO[backend]: Replace mock import with server-provided data (DB queries or API endpoints).
  // Required data: templates
    // TODO[backend]: replace reads/writes with db.getTemplates(), db.saveTemplate(), db.getMappings(), db.saveMapping()

    // State (mock only)
    let tpl = templates.slice();
    let mappings = [];

    // Elements
    const tplRows = document.getElementById('tplRows');
    const addTemplateBtn = document.getElementById('addTemplate');
    const uploadModal = document.getElementById('uploadModal');
    const uploadClose = document.getElementById('uploadClose');
    const uploadCancel = document.getElementById('uploadCancel');
    const uploadForm = document.getElementById('uploadForm');
    const uName = document.getElementById('uName');
    const uCategory = document.getElementById('uCategory');
    const uVersion = document.getElementById('uVersion');
    const uFile = document.getElementById('uFile');
    const uPreview = document.getElementById('uPreview');

    const mapEmpty = document.getElementById('mapEmpty');
    const mapList = document.getElementById('mapList');
    const addMappingBtn = document.getElementById('addMapping');
    const mappingModal = document.getElementById('mappingModal');
    const mapClose = document.getElementById('mapClose');
    const mapCancel = document.getElementById('mapCancel');
    const mapForm = document.getElementById('mapForm');
    const mDomain = document.getElementById('mDomain');
    const mTemplate = document.getElementById('mTemplate');
    const mFields = document.getElementById('mFields');

    // Render templates list
    function renderTpl() {
      tplRows.innerHTML = tpl.map(t => `
        <tr class="border-b">
          <td class="py-2">${t.name}</td>
          <td>${t.category}</td>
          <td>${t.version||'-'}</td>
          <td><a class="text-blue-600 underline" href="${t.file}" target="_blank" rel="noopener">Download</a></td>
          <td class="text-right">
            <button class="text-sm text-blue-600 hover:underline" data-act="replace" data-id="${t.id}">Replace</button>
            <button class="text-sm text-rose-600 hover:underline ml-3" data-act="remove" data-id="${t.id}">Remove</button>
          </td>
        </tr>
      `).join('') || '<tr><td colspan="5" class="py-6 text-center text-slate-500">No templates yet.</td></tr>';
    }
    renderTpl();

    // Upload modal helpers
    function openUpload(t){
      uploadModal.classList.remove('hidden');
      uName.value = t?.name || '';
      uCategory.value = t?.category || 'Enrollment';
      uVersion.value = t?.version || '';
      uFile.value = '';
      uPreview.textContent = 'Preview will appear here after selecting a file.';
      uPreview.classList.add('hidden');
      uploadForm.dataset.editId = t?.id || '';
    }
    function closeUpload(){ uploadModal.classList.add('hidden'); }
    addTemplateBtn.addEventListener('click', ()=> openUpload(null));
    uploadClose.addEventListener('click', closeUpload); uploadCancel.addEventListener('click', closeUpload);

    // Basic SheetJS preview for first sheet
    uFile.addEventListener('change', async (e) => {
      const file = e.target.files?.[0]; if (!file) return;
      try {
        const buf = await file.arrayBuffer();
        const wb = XLSX.read(buf, { type: 'array' });
        const ws = wb.Sheets[wb.SheetNames[0]];
        const json = XLSX.utils.sheet_to_json(ws, { header: 1 });
        const rows = json.slice(0, 10).map(r=>`<tr>${r.map(c=>`<td class='border px-2 py-1'>${String(c??'')}</td>`).join('')}</tr>`).join('');
        uPreview.innerHTML = `<div class='text-xs mb-2 text-slate-600'>Previewing first 10 rows of ${wb.SheetNames[0]}</div><div class='overflow-x-auto'><table class='text-xs border'>${rows}</table></div>`;
        uPreview.classList.remove('hidden');
      } catch(err){
        uPreview.textContent = 'Could not preview file.'; uPreview.classList.remove('hidden');
      }
    });

    uploadForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const id = uploadForm.dataset.editId ? Number(uploadForm.dataset.editId) : null;
      const item = {
        id: id ?? (Math.max(0, ...tpl.map(x=>x.id))+1),
        name: uName.value.trim(),
        category: uCategory.value,
        version: uVersion.value.trim() || '1.0',
        file: '#', // Placeholder link in FE phase
      };
      if (!item.name) return;
      if (id) { tpl = tpl.map(x=> x.id===id ? { ...x, ...item } : x); /* TODO[backend]: db.updateTemplate(item) */ }
      else { tpl.push(item); /* TODO[backend]: db.createTemplate(item, file) */ }
      renderTpl(); closeUpload();
    });

    // Template list actions
    tplRows.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-act]'); if (!btn) return;
      const id = Number(btn.dataset.id); const act = btn.dataset.act;
      const t = tpl.find(x=>x.id===id);
      if (act==='replace') openUpload(t);
      if (act==='remove') { tpl = tpl.filter(x=>x.id!==id); renderTpl(); /* TODO[backend]: db.deleteTemplate(id) */ }
    });

    // Mapping list
    function renderMappings(){
      if (!mappings.length) { mapEmpty.classList.remove('hidden'); mapList.classList.add('hidden'); mapList.innerHTML=''; return; }
      mapEmpty.classList.add('hidden'); mapList.classList.remove('hidden');
      mapList.innerHTML = mappings.map(m => `
        <div class="border rounded-lg p-3">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium">${m.domain} — ${m.templateName}</p>
              <p class="text-xs text-slate-500">${m.fields.length} mapped fields</p>
            </div>
            <div class="flex items-center gap-2">
              <button class="text-sm text-blue-600 hover:underline" data-map-act="edit" data-id="${m.id}">Edit</button>
              <button class="text-sm text-rose-600 hover:underline" data-map-act="remove" data-id="${m.id}">Remove</button>
            </div>
          </div>
        </div>
      `).join('');
    }
    renderMappings();

    function openMapping(m){
      mappingModal.classList.remove('hidden');
      mapForm.dataset.editId = m?.id || '';
      mDomain.value = m?.domain || 'Enrollment';
      mTemplate.innerHTML = tpl.map(t => `<option value="${t.id}" ${m && m.templateId===t.id ? 'selected':''}>${t.name}</option>`).join('');
      const fields = m?.fields || [
        { source: 'A: Program', target: 'program' },
        { source: 'B: Major', target: 'major' },
        { source: 'C: Count', target: 'totalCount' },
      ];
      mFields.innerHTML = fields.map((f, i)=>`
        <div class="grid grid-cols-2 gap-2 items-center">
          <input class="px-2 py-1 rounded border" value="${f.source}" data-idx="${i}" data-k="source" />
          <input class="px-2 py-1 rounded border" value="${f.target}" data-idx="${i}" data-k="target" />
        </div>
      `).join('');
    }
    function closeMapping(){ mappingModal.classList.add('hidden'); }
    addMappingBtn.addEventListener('click', ()=> openMapping(null));
    mapClose.addEventListener('click', closeMapping); mapCancel.addEventListener('click', closeMapping);

    mapForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const id = mapForm.dataset.editId ? Number(mapForm.dataset.editId) : null;
      const templateId = Number(mTemplate.value);
      const t = tpl.find(x=>x.id===templateId); const templateName = t? t.name : `Template #${templateId}`;
      const fields = Array.from(mFields.querySelectorAll('input')).reduce((acc, el, idx) => {
        const i = Number(el.dataset.idx);
        if (!acc[i]) acc[i] = { source:'', target:'' };
        acc[i][el.dataset.k] = el.value.trim();
        return acc;
      }, []);
      const mapping = { id: id ?? (Math.max(0,...mappings.map(x=>x.id))+1), domain: mDomain.value, templateId, templateName, fields };
      if (id) { mappings = mappings.map(x=> x.id===id ? mapping : x); /* TODO[backend]: db.updateMapping(mapping) */ }
      else { mappings.push(mapping); /* TODO[backend]: db.createMapping(mapping) */ }
      renderMappings(); closeMapping();
    });

    // Map list actions
    mapList.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-map-act]'); if (!btn) return;
      const id = Number(btn.dataset.id); const act = btn.dataset.mapAct;
      const m = mappings.find(x=>x.id===id);
      if (act==='edit') openMapping(m);
      if (act==='remove') { mappings = mappings.filter(x=>x.id!==id); renderMappings(); /* TODO[backend]: db.deleteMapping(id) */ }
    });

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
