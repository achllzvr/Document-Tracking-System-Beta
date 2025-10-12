<?php // Front-end first; no DB calls. ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HEI Analytics — HEI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden p-5">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="font-semibold">Key Performance Indicators</h2>
              <p class="text-sm text-slate-500">Overview of institution metrics (mock)</p>
            </div>
            <div class="flex items-center gap-3">
              <label class="text-xs">Academic Year</label>
              <select id="filterAy" class="px-2 py-1 rounded border"></select>
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
              <h3 class="font-medium mb-2">Enrollment Trend</h3>
              <canvas id="enrollTrend" aria-label="Enrollment trend chart" role="img"></canvas>
            </div>
            <div class="bg-white border rounded-lg p-4">
              <h3 class="font-medium mb-2">Faculty by Discipline</h3>
              <canvas id="facultyBar" aria-label="Faculty by discipline bar chart" role="img"></canvas>
            </div>
            <div class="bg-white border rounded-lg p-4 md:col-span-2">
              <h3 class="font-medium mb-2">Graduates by Sex</h3>
              <canvas id="graduatesPie" aria-label="Graduates by sex pie chart" role="img"></canvas>
            </div>
          </div>

          <div class="mt-6">
            <h3 class="font-semibold mb-2">Aggregated Table</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm border">
                <thead class="bg-slate-50"><tr><th class="p-2">Metric</th><th class="p-2">Value</th></tr></thead>
                <tbody id="aggTable"></tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <script type="module">
    import { heis, tickets, enrollmentData, facultyData, graduatesData } from '../../assets/mock/mock-data.js';
    // TODO[backend]: db.getAggregates(heiId, filters), analytics endpoints

    const params = new URLSearchParams(location.search);
    const heiId = Number(params.get('hei_id')) || heis[0]?.id || 1;

    // UI elements
    const filterAy = document.getElementById('filterAy');
    const kEnrollment = document.getElementById('kEnrollment');
    const kFaculty = document.getElementById('kFaculty');
    const kGraduates = document.getElementById('kGraduates');
    const kTickets = document.getElementById('kTickets');
    const aggTable = document.getElementById('aggTable');

    // Chart contexts
    const enrollCtx = document.getElementById('enrollTrend').getContext('2d');
    const facultyCtx = document.getElementById('facultyBar').getContext('2d');
    const gradsCtx = document.getElementById('graduatesPie').getContext('2d');

    // Compute available AYs from enrollmentData
    const aySet = Array.from(new Set(enrollmentData.filter(r => r.heiId===heiId).map(r => r.acadYear))).sort();
    const ayOptions = aySet.length ? aySet : [new Date().getFullYear()];
    filterAy.innerHTML = ayOptions.map(a => `<option value="${a}">${a}</option>`).join('');

    function computeKPIs(selectedAy){
      const enrollRows = enrollmentData.filter(r => r.heiId===heiId && (!selectedAy || r.acadYear===Number(selectedAy)));
      const totalEnroll = enrollRows.reduce((s,r)=>s+(r.totalCount||0),0);
      const facRows = facultyData.filter(r => r.heiId===heiId);
      const totalFac = facRows.length;
      const gradRows = graduatesData.filter(r => r.heiId===heiId && (!selectedAy || new Date(r.date).getFullYear()===Number(selectedAy)));
      const totalGrad = gradRows.length;
      const openTickets = tickets.filter(t => t.heiId===heiId && !['Completed','Cancelled'].includes(t.status)).length;
      return { totalEnroll, totalFac, totalGrad, openTickets, enrollRows, facRows, gradRows };
    }

    // Chart instances
    let enrollChart = null, facultyChart = null, gradsChart = null;

    function renderCharts(selectedAy){
      const { enrollRows, facRows, gradRows } = computeKPIs(selectedAy);

      // Enrollment trend by AY (aggregate per AY for this HEI)
      const years = Array.from(new Set(enrollmentData.filter(r=>r.heiId===heiId).map(r=>r.acadYear))).sort();
      const enrollSeries = years.map(y => enrollmentData.filter(r=>r.heiId===heiId && r.acadYear===y).reduce((s,r)=>s+(r.totalCount||0),0));
      if (enrollChart) enrollChart.destroy();
      enrollChart = new Chart(enrollCtx, { type: 'line', data: { labels: years, datasets: [{ label: 'Enrollment', data: enrollSeries, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.12)', fill:true }] }, options: { responsive:true, plugins:{legend:{display:false}} } });

      // Faculty by discipline (top 10)
      const discCounts = {};
      facRows.forEach(f => { const d = f.discipline || 'Other'; discCounts[d] = (discCounts[d]||0)+1; });
      const discLabels = Object.keys(discCounts); const discValues = discLabels.map(l=>discCounts[l]);
      if (facultyChart) facultyChart.destroy();
      facultyChart = new Chart(facultyCtx, { type: 'bar', data: { labels: discLabels, datasets: [{ label: 'Faculty', data: discValues, backgroundColor: '#3b82f6' }] }, options: { responsive:true, plugins:{legend:{display:false}}, scales:{x:{beginAtZero:true}} } });

      // Graduates by sex
      const sexCounts = gradRows.reduce((acc,r)=>{ const s = (r.sex==='m'?'Male':'Female'); acc[s]=(acc[s]||0)+1; return acc; },{});
      const gLabels = Object.keys(sexCounts); const gValues = gLabels.map(l=>sexCounts[l]);
      if (gradsChart) gradsChart.destroy();
      gradsChart = new Chart(gradsCtx, { type: 'pie', data: { labels: gLabels, datasets: [{ data: gValues, backgroundColor: ['#ef4444','#3b82f6'] }] }, options: { responsive:true } });
    }

    function renderKPIs(selectedAy){
      const { totalEnroll, totalFac, totalGrad, openTickets } = computeKPIs(selectedAy);
      kEnrollment.textContent = totalEnroll.toLocaleString();
      kFaculty.textContent = String(totalFac);
      kGraduates.textContent = String(totalGrad);
      kTickets.textContent = String(openTickets);

      aggTable.innerHTML = `
        <tr><td class="p-2">Total Enrollment</td><td class="p-2">${totalEnroll}</td></tr>
        <tr><td class="p-2">Total Faculty</td><td class="p-2">${totalFac}</td></tr>
        <tr><td class="p-2">Total Graduates</td><td class="p-2">${totalGrad}</td></tr>
        <tr><td class="p-2">Open Tickets</td><td class="p-2">${openTickets}</td></tr>
      `;
    }

    // Init render
    function init(){
      const selectedAy = filterAy.value || null;
      renderKPIs(selectedAy);
      renderCharts(selectedAy);
      // TODO[backend]: record analytics view or fetch server-side aggregates
    }
    filterAy.addEventListener('change', init);
    // default selection
    filterAy.value = ayOptions[ayOptions.length-1] || filterAy.value;
    init();

    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
