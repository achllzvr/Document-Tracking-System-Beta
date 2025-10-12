<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Enrollment Data — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <div>
              <h2 class="font-semibold">Filters</h2>
              <p class="text-sm text-slate-500">Filter and browse enrollment records across HEIs</p>
            </div>
            <div class="flex items-center gap-2">
              <button id="exportCsv" class="px-3 py-2 rounded border">Export CSV</button>
              <button id="exportXlsx" class="px-3 py-2 rounded bg-blue-600 text-white">Export XLSX</button>
            </div>
          </header>
          <div class="p-5 grid md:grid-cols-6 gap-3">
            <div class="col-span-2">
              <label class="block text-xs">HEI</label>
              <select id="fHei" class="w-full px-2 py-1 rounded border"></select>
            </div>
            <div>
              <label class="block text-xs">Academic Year</label>
              <input id="fAy" type="number" min="2000" max="2100" class="w-full px-2 py-1 rounded border" placeholder="e.g. 2025" />
            </div>
            <div>
              <label class="block text-xs">Term</label>
              <select id="fTerm" class="w-full px-2 py-1 rounded border"><option value="">All</option><option>1</option><option>2</option></select>
            </div>
            <div>
              <label class="block text-xs">Year Level</label>
              <select id="fYear" class="w-full px-2 py-1 rounded border"><option value="">All</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option></select>
            </div>
            <div>
              <label class="block text-xs">Sex</label>
              <select id="fSex" class="w-full px-2 py-1 rounded border"><option value="">All</option><option value="m">Male</option><option value="f">Female</option></select>
            </div>
            <div class="md:col-span-3">
              <label class="block text-xs">Program</label>
              <input id="fProgram" class="w-full px-2 py-1 rounded border" placeholder="Search program" />
            </div>
            <div class="flex items-end">
              <button id="applyFilters" class="px-3 py-2 rounded bg-blue-600 text-white">Apply</button>
            </div>
          </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <h2 class="font-semibold">Results</h2>
            <div class="text-sm text-slate-500">Page <span id="curPage">1</span> / <span id="totalPages">1</span></div>
          </header>
          <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="text-slate-600 border-b">
                <tr>
                  <th class="py-2">HEI</th>
                  <th>AY</th>
                  <th>Term</th>
                  <th>Program</th>
                  <th>Major</th>
                  <th>Year</th>
                  <th>Sex</th>
                  <th>Count</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody id="rows"></tbody>
            </table>
          </div>
          <footer class="px-5 pb-5">
            <div class="flex items-center justify-between">
              <div class="text-sm text-slate-500" id="totalCount">0 records</div>
              <div class="flex items-center gap-2">
                <button id="prevPage" class="px-3 py-2 rounded border">Prev</button>
                <button id="nextPage" class="px-3 py-2 rounded border">Next</button>
              </div>
            </div>
          </footer>
        </section>
      </div>
    </main>
  </div>

  <script type="module">
    import { heis, enrollmentData, paginate } from '../assets/mock/mock-data.js';
    // TODO[backend]: Replace with db.getEnrollmentData(filters) and server-side export endpoints

    const fHei = document.getElementById('fHei');
    const fAy = document.getElementById('fAy');
    const fTerm = document.getElementById('fTerm');
    const fYear = document.getElementById('fYear');
    const fSex = document.getElementById('fSex');
    const fProgram = document.getElementById('fProgram');
    const applyBtn = document.getElementById('applyFilters');
    const rows = document.getElementById('rows');
    const curPageEl = document.getElementById('curPage');
    const totalPagesEl = document.getElementById('totalPages');
    const totalCountEl = document.getElementById('totalCount');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const exportCsvBtn = document.getElementById('exportCsv');
    const exportXlsxBtn = document.getElementById('exportXlsx');

    // Populate HEI select
    fHei.innerHTML = '<option value="">All HEIs</option>' + heis.map(h => `<option value="${h.id}">${h.name}</option>`).join('');

    const PER_PAGE = 10; let page = 1; let current = [];
    function fmt(r){
      const h = heis.find(x=>x.id===r.heiId); const name = h? h.name : `HEI #${r.heiId}`;
      return `<tr class="border-b">
        <td class="py-2">${name}</td>
        <td>${r.acadYear}</td>
        <td>${r.term}</td>
        <td>${r.program}</td>
        <td>${r.major||'-'}</td>
        <td>${r.yearLevel}</td>
        <td>${r.sex==='m'?'Male':'Female'}</td>
        <td>${r.totalCount}</td>
        <td>${new Date(r.createdAt).toLocaleString()}</td>
      </tr>`;
    }
    function apply(){
      const heiId = Number(fHei.value)||null;
      const ay = fAy.value ? Number(fAy.value) : null;
      const term = fTerm.value || null;
      const year = fYear.value ? Number(fYear.value) : null;
      const sex = fSex.value || null;
      const prog = fProgram.value.trim().toLowerCase();
      let arr = enrollmentData.slice();
      if (heiId) arr = arr.filter(r=>r.heiId===heiId);
      if (ay) arr = arr.filter(r=>r.acadYear===ay);
      if (term) arr = arr.filter(r=>String(r.term)===String(term));
      if (year) arr = arr.filter(r=>Number(r.yearLevel)===year);
      if (sex) arr = arr.filter(r=>r.sex===sex);
      if (prog) arr = arr.filter(r=>r.program.toLowerCase().includes(prog));
      current = arr; page = 1; render();
    }
    function render(){
      const { items, page: p, pages, total } = paginate(current, page, PER_PAGE);
      rows.innerHTML = items.map(fmt).join('') || '<tr><td colspan="9" class="py-6 text-center text-slate-500">No records.</td></tr>';
      curPageEl.textContent = String(p); totalPagesEl.textContent = String(pages);
      totalCountEl.textContent = `${total} record${total===1?'':'s'}`;
      prevBtn.disabled = p<=1; nextBtn.disabled = p>=pages;
    }
    applyBtn.addEventListener('click', apply);
    prevBtn.addEventListener('click', ()=>{ if(page>1){ page--; render(); } });
    nextBtn.addEventListener('click', ()=>{ const pages = Number(totalPagesEl.textContent)||1; if(page<pages){ page++; render(); } });

    function toCSV(rows){
      const header = ['HEI','AY','Term','Program','Major','Year','Sex','Count','Created'];
      const lines = rows.map(r=>{
        const h = heis.find(x=>x.id===r.heiId); const name = h? h.name : `HEI #${r.heiId}`;
        return [name,r.acadYear,r.term,r.program,r.major||'',r.yearLevel,r.sex==='m'?'Male':'Female',r.totalCount,new Date(r.createdAt).toISOString()].map(v=>`"${String(v).replace(/"/g,'\"')}"`).join(',');
      });
      return [header.join(','), ...lines].join('\n');
    }
    exportCsvBtn.addEventListener('click', async ()=>{
      try{
        const res = await fetch('/PRISM/PRISM/api/export.php', {
          method: 'POST', headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ filename: 'enrollment_' + (new Date()).toISOString().slice(0,19).replace(/[:T]/g,'_'), format: 'csv', data: current })
        });
        const j = await res.json();
        if (j.status === 'ok' && j.url) {
          const a = document.createElement('a'); a.href = j.url; a.download = j.url.split('/').pop(); document.body.appendChild(a); a.click(); a.remove();
          Swal.fire({ icon:'success', title:'Export started', text:'CSV export generated on server.' , timer:1200, showConfirmButton:false });
        } else {
          Swal.fire({ icon:'error', title:'Export failed', text: j.message || 'Unknown error' });
        }
      }catch(err){ console.error(err); Swal.fire({ icon:'error', title:'Export failed', text: String(err) }); }
    });
    exportXlsxBtn.addEventListener('click', ()=>{
      Swal.fire({ icon:'info', title:'Export XLSX', text:'Server-side XLSX export will be implemented later.', timer:1300, showConfirmButton:false });
      // TODO[backend]: server-side XLSX export
    });

    // Init
    apply();
    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
