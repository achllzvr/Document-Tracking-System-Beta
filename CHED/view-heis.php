<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Institutions — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">Search & Filter</h2>
            <p class="text-sm text-slate-500">Find institutions by name, region, type, ownership</p>
          </header>
          <div class="p-5 grid md:grid-cols-4 gap-4">
            <div class="relative md:col-span-1">
              <i data-lucide="search" class="absolute left-3 top-3 h-4 w-4 text-slate-400"></i>
              <input id="search" class="w-full pl-9 px-3 py-2 rounded border" placeholder="Search by name..." />
            </div>
            <select id="region" class="px-3 py-2 rounded border">
              <option value="all">All Regions</option>
            </select>
            <select id="instType" class="px-3 py-2 rounded border">
              <option value="all">All Types</option>
            </select>
            <select id="ownership" class="px-3 py-2 rounded border">
              <option value="all">All Ownership</option>
            </select>
          </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <div>
              <h2 class="font-semibold">Institutions</h2>
              <p class="text-sm text-slate-500">Browse and manage institutional profiles</p>
            </div>
            <div id="pager" class="text-sm text-slate-600"></div>
          </header>
          <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="text-slate-600 border-b">
                <tr>
                  <th class="py-2">Name</th>
                  <th>Region</th>
                  <th>Type</th>
                  <th>Ownership</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="heiRows"></tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

  <script type="module">
    import { heis, regions, institutionTypes, ownershipForms, paginate } from '../assets/mock/mock-data.js';

    const search = document.getElementById('search');
    const region = document.getElementById('region');
    const instType = document.getElementById('instType');
    const ownership = document.getElementById('ownership');
    const rows = document.getElementById('heiRows');
    const pager = document.getElementById('pager');

    // Populate filter options
    regions.forEach(r => { const o=document.createElement('option'); o.value=r; o.textContent=r; region.appendChild(o); });
    institutionTypes.forEach(t => { const o=document.createElement('option'); o.value=t; o.textContent=t; instType.appendChild(o); });
    ownershipForms.forEach(f => { const o=document.createElement('option'); o.value=f; o.textContent=f; ownership.appendChild(o); });

    let state = { page: 1, perPage: 10 };

    function applyFilters() {
      const q = (search.value || '').toLowerCase();
      const r = region.value; const t = instType.value; const ow = ownership.value;
      return heis.filter(h => {
        const matchesQ = h.name.toLowerCase().includes(q) || h.shortName.toLowerCase().includes(q);
        const matchesR = r==='all' || h.region===r;
        const matchesT = t==='all' || h.institutionType===t;
        const matchesO = ow==='all' || h.ownershipForm===ow;
        return matchesQ && matchesR && matchesT && matchesO;
      });
    }

    function render() {
      const filtered = applyFilters();
      const { items, page, pages, total } = paginate(filtered, state.page, state.perPage);
      rows.innerHTML = items.map(h => `
        <tr class="border-b">
          <td class="py-2">
            <div>
              <p class="font-medium">${h.name}</p>
              <p class="text-xs text-slate-500">${h.shortName}</p>
            </div>
          </td>
          <td>${h.region}</td>
          <td>${h.institutionType}</td>
          <td>${h.ownershipForm}</td>
          <td><span class="inline-flex text-xs px-2 py-1 rounded border ${h.status==='Active'?'border-emerald-300 text-emerald-700 bg-emerald-50':'border-slate-200'}">${h.status}</span></td>
          <td>
            <a class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600" href="./hei-details.php?hei_id=${h.id}"><i data-lucide="eye" class="h-4 w-4"></i>View Details</a>
          </td>
        </tr>
      `).join('');
      pager.textContent = `Showing ${items.length} of ${total} • Page ${page} / ${pages}`;
    }

    [search, region, instType, ownership].forEach(el => el.addEventListener('input', ()=> { state.page=1; render(); }));

    render();
    if (window.lucide) lucide.createIcons();

    // TODO[backend]: replace with db.getHEIs(filters)
  </script>
</body>
</html>