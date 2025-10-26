<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HEI Analytics — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="font-semibold">Analytics</h2>
              <p class="text-sm text-slate-500">Interactive analytics across HEIs (mock data)</p>
            </div>
            <div class="flex items-center gap-3">
              <label class="text-xs">Region</label>
              <select id="fRegion" class="px-2 py-1 rounded border"></select>
              <label class="text-xs">HEI</label>
              <select id="fHei" class="px-2 py-1 rounded border"></select>
              <label class="text-xs">Academic Year</label>
              <select id="fAy" class="px-2 py-1 rounded border"></select>
            </div>
          </div>

          <div class="grid md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-white border rounded-lg shadow-sm">
              <p class="text-xs text-slate-500">Total Enrollment</p>
              <p id="kEnrollment" class="text-2xl font-semibold">—</p>
            </div>
            <div class="p-4 bg-white border rounded-lg shadow-sm">
              <p class="text-xs text-slate-500">Total Faculty</p>
              <p id="kFaculty" class="text-2xl font-semibold">—</p>
            </div>
            <div class="p-4 bg-white border rounded-lg shadow-sm">
              <p class="text-xs text-slate-500">Total Graduates</p>
              <p id="kGraduates" class="text-2xl font-semibold">—</p>
            </div>
            <div class="p-4 bg-white border rounded-lg shadow-sm">
              <p class="text-xs text-slate-500">Open Tickets</p>
              <p id="kTickets" class="text-2xl font-semibold">—</p>
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white border rounded-lg p-4">
              <h3 class="font-medium mb-2">Enrollment Trend (AY)</h3>
              <canvas id="enrollTrend" aria-label="Enrollment trend chart" role="img"></canvas>
            </div>
            <div class="bg-white border rounded-lg p-4">
              <h3 class="font-medium mb-2">Faculty by Discipline</h3>
              <canvas id="facultyBar" aria-label="Faculty by discipline bar chart" role="img"></canvas>
            </div>
            <div class="bg-white border rounded-lg p-4 md:col-span-2">
              <h3 class="font-medium mb-2">Graduates by Program (Top)</h3>
              <canvas id="graduatesBar" aria-label="Graduates by program bar chart" role="img"></canvas>
            </div>
          </div>

          <div class="mt-6">
            <h3 class="font-semibold mb-2">Aggregated Metrics</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm border">
                <thead class="bg-slate-50"><tr><th class="p-2">Metric</th><th class="p-2">Value</th></tr></thead>
                <tbody id="aggTable"></tbody>
              </table>
            </div>
            <div class="mt-3 flex gap-2">
              <button id="exportCsv" class="px-3 py-2 rounded border">Export CSV</button>
              <button id="exportXlsx" class="px-3 py-2 rounded bg-blue-600 text-white">Export XLSX</button>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <script type="module">
  // TODO[backend]: Replace mock import with server-provided data (DB queries or API endpoints).
  // Required data: heis, regions, enrollmentData, facultyData, graduatesData, tickets
    // TODO[backend]: db.getAnalyticsAggregates(filters)

    // Elements
    const fRegion = document.getElementById('fRegion');
    const fHei = document.getElementById('fHei');
    const fAy = document.getElementById('fAy');
    const kEnrollment = document.getElementById('kEnrollment');
    const kFaculty = document.getElementById('kFaculty');
    const kGraduates = document.getElementById('kGraduates');
    const kTickets = document.getElementById('kTickets');
    const aggTable = document.getElementById('aggTable');
    const exportCsvBtn = document.getElementById('exportCsv');
    const exportXlsxBtn = document.getElementById('exportXlsx');

    // Chart contexts
    const enrollCtx = document.getElementById('enrollTrend').getContext('2d');
    const facultyCtx = document.getElementById('facultyBar').getContext('2d');
    const gradsCtx = document.getElementById('graduatesBar').getContext('2d');
    let enrollChart=null, facultyChart=null, gradsChart=null;

    // Populate filters
    fRegion.innerHTML = ['All',''].concat(regions).filter(Boolean).map(r=>`<option value="${r}">${r}</option>`).join('');
    fHei.innerHTML = '<option value="">All HEIs</option>' + heis.map(h=>`<option value="${h.id}">${h.name}</option>`).join('');
    const aySet = Array.from(new Set(enrollmentData.map(r=>r.acadYear))).sort();
    fAy.innerHTML = '<option value="">All AY</option>' + aySet.map(a=>`<option value="${a}">${a}</option>`).join('');

    function applyFilters(){
      const region = fRegion.value || null;
      const heiId = fHei.value ? Number(fHei.value) : null;
      const ay = fAy.value ? Number(fAy.value) : null;
      renderAll({ region, heiId, ay });
    }

    function renderAll({ region=null, heiId=null, ay=null } = {}){
      // Filter HEIs by region if provided
      const heiFilter = heiId ? heis.filter(h=>h.id===heiId) : (region ? heis.filter(h=>h.region===region) : heis.slice());
      const heiIds = new Set(heiFilter.map(h=>h.id));

      // Enrollment
      const enrollRows = enrollmentData.filter(r => heiIds.has(r.heiId) && (!ay || r.acadYear===ay));
      const totalEnroll = enrollRows.reduce((s,r)=>s+(r.totalCount||0),0);

      // Faculty
      const facRows = facultyData.filter(r=> heiIds.has(r.heiId));
      const totalFac = facRows.length;

      // Graduates
      const gradRows = graduatesData.filter(r=> heiIds.has(r.heiId) && (!ay || new Date(r.date).getFullYear()===ay));
      const totalGrad = gradRows.length;

      // Tickets
      const openTickets = tickets.filter(t => heiIds.has(t.heiId) && !['Completed','Cancelled'].includes(t.status)).length;

      // KPIs
      kEnrollment.textContent = totalEnroll.toLocaleString();
      kFaculty.textContent = String(totalFac);
      kGraduates.textContent = String(totalGrad);
      kTickets.textContent = String(openTickets);

      // Aggregated table
      aggTable.innerHTML = `
        <tr><td class="p-2">Total Enrollment</td><td class="p-2">${totalEnroll}</td></tr>
        <tr><td class="p-2">Total Faculty</td><td class="p-2">${totalFac}</td></tr>
        <tr><td class="p-2">Total Graduates</td><td class="p-2">${totalGrad}</td></tr>
        <tr><td class="p-2">Open Tickets</td><td class="p-2">${openTickets}</td></tr>
      `;

      // Charts
      // Enrollment trend across AY for selected HEIs
      const years = Array.from(new Set(enrollmentData.filter(r=> heiIds.has(r.heiId)).map(r=>r.acadYear))).sort();
      const enrollSeries = years.map(y => enrollmentData.filter(r=> heiIds.has(r.heiId) && r.acadYear===y).reduce((s,r)=>s+(r.totalCount||0),0));
      if (enrollChart) enrollChart.destroy();
      enrollChart = new Chart(enrollCtx, { type:'line', data:{ labels: years, datasets:[{ label:'Enrollment', data: enrollSeries, borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,0.12)', fill:true }] }, options:{ responsive:true, plugins:{legend:{display:false}} } });

      // Faculty by discipline
      const discCounts = {};
      facRows.forEach(f=>{ const d = f.discipline||'Other'; discCounts[d] = (discCounts[d]||0)+1; });
      const discLabels = Object.keys(discCounts); const discValues = discLabels.map(l=>discCounts[l]);
      if (facultyChart) facultyChart.destroy();
      facultyChart = new Chart(facultyCtx, { type:'bar', data:{ labels: discLabels, datasets:[{ label:'Faculty', data: discValues, backgroundColor:'#06b6d4' }] }, options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} } });

      // Graduates by program (top 10)
      const progCounts = {};
      gradRows.forEach(g=>{ const p = g.program || 'Unknown'; progCounts[p] = (progCounts[p]||0)+1; });
      const progEntries = Object.entries(progCounts).sort((a,b)=>b[1]-a[1]).slice(0,10);
      const progLabels = progEntries.map(e=>e[0]); const progValues = progEntries.map(e=>e[1]);
      if (gradsChart) gradsChart.destroy();
      gradsChart = new Chart(gradsCtx, { type:'bar', data:{ labels: progLabels, datasets:[{ label:'Graduates', data: progValues, backgroundColor:'#f97316' }] }, options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} } });
    }

    // Export CSV
    function toCSV(){
      // Minimal CSV export of aggregated metrics
      const rows = Array.from(aggTable.querySelectorAll('tbody tr')).map(tr => {
        const cols = Array.from(tr.querySelectorAll('td')).map(td=>td.textContent.trim().replace(/"/g,'""'));
        return '"'+cols.join('","')+'"';
      });
      return ['"Metric","Value"', ...rows].join('\n');
    }
    exportCsvBtn.addEventListener('click', async ()=>{
      // Package aggregated metrics into an array of objects for server-side CSV
      const rows = Array.from(aggTable.querySelectorAll('tbody tr')).map(tr => {
        const key = tr.children[0].textContent.trim();
        const val = tr.children[1].textContent.trim();
        return { Metric: key, Value: val };
      });
      try{
  const res = await fetch('/PRISM/api/export.php', {
          method: 'POST', headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ filename: 'analytics_' + (new Date()).toISOString().slice(0,19).replace(/[:T]/g,'_'), format: 'csv', data: rows })
        });
        const j = await res.json();
        if (j.status === 'ok' && j.url) {
          // download returned URL
          const a = document.createElement('a'); a.href = j.url; a.download = j.url.split('/').pop(); document.body.appendChild(a); a.click(); a.remove();
          Swal.fire({ icon:'success', title:'Export started', text:'CSV export generated on server.' , timer:1200, showConfirmButton:false });
        } else {
          Swal.fire({ icon:'error', title:'Export failed', text: j.message || 'Unknown error' });
        }
      }catch(err){
        console.error(err); Swal.fire({ icon:'error', title:'Export failed', text: String(err) });
      }
    });
    exportXlsxBtn.addEventListener('click', ()=>{
      Swal.fire({ icon:'info', title:'Export XLSX', text:'Server-side XLSX export will be implemented later.', timer:1200, showConfirmButton:false });
      // TODO[backend]: server-side XLSX export
    });

    // Initialize
    fRegion.addEventListener('change', applyFilters); fHei.addEventListener('change', applyFilters); fAy.addEventListener('change', applyFilters);
    // default
    fRegion.value=''; fHei.value=''; fAy.value='';
    applyFilters();

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
