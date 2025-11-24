<?php
  
// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// HEI protector
require_once __DIR__ . '/../includes/hei_protect.php';

// Database helper
require_once __DIR__ . '/../classes/database.php';
$db = new database();

// Get HEI context from session
$heiId = $_SESSION['heiID'] ?? null;

if (!$heiId) {
    die('HEI ID not found in session.');
}

// Get filter parameters
$selectedYear = $_GET['year'] ?? '';
$selectedTerm = $_GET['term'] ?? '';
$selectedProgram = $_GET['program'] ?? '';

// Build filters array
$filters = [];
if ($selectedYear !== '' && $selectedYear !== 'all') {
    $filters['acad_year'] = $selectedYear;
}
if ($selectedTerm !== '' && $selectedTerm !== 'all') {
    $filters['term'] = $selectedTerm;
}
if ($selectedProgram !== '' && $selectedProgram !== 'all') {
    $filters['program'] = $selectedProgram;
}

// Fetch available filter options
$availableYears = $db->getAvailableAcademicYears($heiId);
$availableTerms = $db->getAvailableTerms($heiId);
$availablePrograms = $db->getAvailablePrograms($heiId);

// Fetch summary statistics
$summary = $db->getEnrollmentSummary($heiId, $filters);
$ticketCounts = $db->getTicketCountsForHEI($heiId);

// Fetch analytics data
$enrollmentTrend = $db->getEnrollmentTrend($heiId);
$enrollmentByProgram = $db->getEnrollmentByProgram($heiId, $filters);
$enrollmentBySex = $db->getEnrollmentBySex($heiId, $filters);
$enrollmentByYearLevel = $db->getEnrollmentByYearLevel($heiId, $filters);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HEI Analytics — PRISM</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="/PRISM/assets/prism-global.css">
  <link rel="stylesheet" href="/PRISM/assets/hei-global.css">
</head>
<body class="hei-min-h-screen">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="hei-flex-1">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="hei-main">
      <div class="hei-max-w-7xl hei-space-y-6">
        
        <!-- Page Header -->
        <section class="hei-card">
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                  <i data-lucide="bar-chart-3" class="h-7 w-7 text-blue-600"></i>
                  Institutional Analytics
                </h1>
                <p class="text-sm text-slate-500 mt-1">View enrollment trends and distribution metrics</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Filters Section -->
        <section class="hei-card">
          <header class="hei-card-header">
            <h2 class="hei-font-semibold text-slate-800">Filters</h2>
            <p class="hei-text-sm">Filter data by academic year, term, and program</p>
          </header>
          <div class="p-6">
            <form method="get" class="grid md:grid-cols-4 gap-4">
              <!-- Academic Year Filter -->
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-2">Academic Year</label>
                <select name="year" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="all">All Years</option>
                  <?php foreach ($availableYears as $year): ?>
                    <option value="<?php echo htmlspecialchars($year); ?>" <?php echo ($selectedYear == $year) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($year); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Term Filter -->
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-2">Term</label>
                <select name="term" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="all">All Terms</option>
                  <?php foreach ($availableTerms as $term): ?>
                    <option value="<?php echo htmlspecialchars($term); ?>" <?php echo ($selectedTerm == $term) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($term); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Program Filter -->
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-2">Program</label>
                <select name="program" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="all">All Programs</option>
                  <?php foreach ($availablePrograms as $program): ?>
                    <option value="<?php echo htmlspecialchars($program); ?>" <?php echo ($selectedProgram == $program) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($program); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Apply Button -->
              <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                  <i data-lucide="filter" class="h-4 w-4"></i>
                  Apply Filters
                </button>
              </div>
            </form>
            
            <?php if (!empty($filters)): ?>
            <div class="mt-4 flex items-center gap-2">
              <span class="text-sm text-slate-600">Active filters:</span>
              <div class="flex flex-wrap gap-2">
                <?php if (!empty($filters['acad_year'])): ?>
                  <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                    Year: <?php echo htmlspecialchars($filters['acad_year']); ?>
                  </span>
                <?php endif; ?>
                <?php if (!empty($filters['term'])): ?>
                  <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                    Term: <?php echo htmlspecialchars($filters['term']); ?>
                  </span>
                <?php endif; ?>
                <?php if (!empty($filters['program'])): ?>
                  <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                    Program: <?php echo htmlspecialchars($filters['program']); ?>
                  </span>
                <?php endif; ?>
                <a href="?" class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-medium hover:bg-slate-200 transition">
                  <i data-lucide="x" class="h-3 w-3"></i>
                  Clear All
                </a>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </section>

        <!-- KPI Cards -->
        <section class="grid md:grid-cols-4 gap-4">
          <div class="hei-card">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-slate-500 uppercase tracking-wide">Total Enrollment</p>
                  <p class="text-2xl font-semibold text-slate-800 mt-1">
                    <?php echo number_format($summary['total_students'] ?? 0); ?>
                  </p>
                </div>
                <div class="p-3 rounded-lg bg-blue-100">
                  <i data-lucide="users" class="h-6 w-6 text-blue-600"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="hei-card">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-slate-500 uppercase tracking-wide">Total Programs</p>
                  <p class="text-2xl font-semibold text-slate-800 mt-1">
                    <?php echo number_format($summary['total_programs'] ?? 0); ?>
                  </p>
                </div>
                <div class="p-3 rounded-lg bg-purple-100">
                  <i data-lucide="book-open" class="h-6 w-6 text-purple-600"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="hei-card">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-slate-500 uppercase tracking-wide">Academic Years</p>
                  <p class="text-2xl font-semibold text-slate-800 mt-1">
                    <?php echo number_format($summary['total_years'] ?? 0); ?>
                  </p>
                </div>
                <div class="p-3 rounded-lg bg-amber-100">
                  <i data-lucide="calendar" class="h-6 w-6 text-amber-600"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="hei-card">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-slate-500 uppercase tracking-wide">Open Tickets</p>
                  <p class="text-2xl font-semibold text-slate-800 mt-1">
                    <?php echo number_format($ticketCounts['open'] + $ticketCounts['pending']); ?>
                  </p>
                </div>
                <div class="p-3 rounded-lg bg-emerald-100">
                  <i data-lucide="ticket" class="h-6 w-6 text-emerald-600"></i>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Charts Grid -->
        <div class="grid md:grid-cols-2 gap-6">
          
          <!-- Enrollment Trend Chart -->
          <section class="hei-card">
            <header class="px-6 py-4 border-b border-slate-200">
              <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="trending-up" class="h-5 w-5 text-blue-600"></i>
                Enrollment Trend Over Years
              </h3>
              <p class="text-xs text-slate-500 mt-1">Historical enrollment data by academic year</p>
            </header>
            <div class="p-6">
              <canvas id="enrollTrend" style="max-height: 300px;"></canvas>
            </div>
          </section>

          <!-- Enrollment by Program Chart -->
          <section class="hei-card">
            <header class="px-6 py-4 border-b border-slate-200">
              <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="book-open" class="h-5 w-5 text-purple-600"></i>
                Top 10 Programs by Enrollment
              </h3>
              <p class="text-xs text-slate-500 mt-1">Programs with highest student counts</p>
            </header>
            <div class="p-6">
              <canvas id="programBar" style="max-height: 300px;"></canvas>
            </div>
          </section>

          <!-- Enrollment by Sex Chart -->
          <section class="hei-card">
            <header class="px-6 py-4 border-b border-slate-200">
              <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="pie-chart" class="h-5 w-5 text-pink-600"></i>
                Gender Distribution
              </h3>
              <p class="text-xs text-slate-500 mt-1">Male vs Female enrollment ratio</p>
            </header>
            <div class="p-6">
              <canvas id="sexPie" style="max-height: 300px;"></canvas>
            </div>
          </section>

          <!-- Enrollment by Year Level Chart -->
          <section class="hei-card">
            <header class="px-6 py-4 border-b border-slate-200">
              <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="layers" class="h-5 w-5 text-cyan-600"></i>
                Distribution by Year Level
              </h3>
              <p class="text-xs text-slate-500 mt-1">Student distribution across year levels</p>
            </header>
            <div class="p-6">
              <canvas id="yearLevelBar" style="max-height: 300px;"></canvas>
            </div>
          </section>

        </div>

        <!-- Data Table -->
        <section class="hei-card">
          <header class="hei-card-header">
            <h2 class="hei-font-semibold text-slate-800">Summary Statistics</h2>
            <p class="hei-text-sm">Key metrics at a glance</p>
          </header>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                <tr>
                  <th class="px-6 py-3 font-semibold">Metric</th>
                  <th class="px-6 py-3 font-semibold text-right">Value</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr class="hover:bg-slate-50">
                  <td class="px-6 py-3 text-slate-700">Total Enrollment</td>
                  <td class="px-6 py-3 text-right font-semibold text-slate-800">
                    <?php echo number_format($summary['total_students'] ?? 0); ?>
                  </td>
                </tr>
                <tr class="hover:bg-slate-50">
                  <td class="px-6 py-3 text-slate-700">Total Programs</td>
                  <td class="px-6 py-3 text-right font-semibold text-slate-800">
                    <?php echo number_format($summary['total_programs'] ?? 0); ?>
                  </td>
                </tr>
                <tr class="hover:bg-slate-50">
                  <td class="px-6 py-3 text-slate-700">Academic Years Tracked</td>
                  <td class="px-6 py-3 text-right font-semibold text-slate-800">
                    <?php echo number_format($summary['total_years'] ?? 0); ?>
                  </td>
                </tr>
                <tr class="hover:bg-slate-50">
                  <td class="px-6 py-3 text-slate-700">Open Tickets</td>
                  <td class="px-6 py-3 text-right font-semibold text-slate-800">
                    <?php echo number_format($ticketCounts['open'] + $ticketCounts['pending']); ?>
                  </td>
                </tr>
                <tr class="hover:bg-slate-50">
                  <td class="px-6 py-3 text-slate-700">Resolved Tickets</td>
                  <td class="px-6 py-3 text-right font-semibold text-slate-800">
                    <?php echo number_format($ticketCounts['resolved']); ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </div>
    </main>
  </div>

  <script>
    // Initialize Lucide icons
    if (window.lucide) lucide.createIcons();

    // Prepare data from PHP
    const enrollmentTrend = <?php echo json_encode($enrollmentTrend); ?>;
    const enrollmentByProgram = <?php echo json_encode($enrollmentByProgram); ?>;
    const enrollmentBySex = <?php echo json_encode($enrollmentBySex); ?>;
    const enrollmentByYearLevel = <?php echo json_encode($enrollmentByYearLevel); ?>;

    // Chart.js configurations
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';

    // Enrollment Trend Chart (Line)
    const trendCtx = document.getElementById('enrollTrend').getContext('2d');
    new Chart(trendCtx, {
      type: 'line',
      data: {
        labels: enrollmentTrend.map(d => d.enr_acad_year),
        datasets: [{
          label: 'Total Enrollment',
          data: enrollmentTrend.map(d => d.total),
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Enrollment: ' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toLocaleString();
              }
            }
          }
        }
      }
    });

    // Enrollment by Program Chart (Bar)
    const programCtx = document.getElementById('programBar').getContext('2d');
    new Chart(programCtx, {
      type: 'bar',
      data: {
        labels: enrollmentByProgram.map(d => d.enr_program),
        datasets: [{
          label: 'Students',
          data: enrollmentByProgram.map(d => d.total),
          backgroundColor: '#8b5cf6',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Students: ' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toLocaleString();
              }
            }
          }
        }
      }
    });

    // Enrollment by Sex Chart (Pie)
    const sexCtx = document.getElementById('sexPie').getContext('2d');
    new Chart(sexCtx, {
      type: 'pie',
      data: {
        labels: enrollmentBySex.map(d => d.enr_sex === 'm' ? 'Male' : 'Female'),
        datasets: [{
          data: enrollmentBySex.map(d => d.total),
          backgroundColor: ['#3b82f6', '#ec4899'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 15,
              font: { size: 12 }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
              }
            }
          }
        }
      }
    });

    // Enrollment by Year Level Chart (Bar)
    const yearLevelCtx = document.getElementById('yearLevelBar').getContext('2d');
    new Chart(yearLevelCtx, {
      type: 'bar',
      data: {
        labels: enrollmentByYearLevel.map(d => 'Year ' + d.enr_year_level),
        datasets: [{
          label: 'Students',
          data: enrollmentByYearLevel.map(d => d.total),
          backgroundColor: '#06b6d4',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Students: ' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toLocaleString();
              }
            }
          }
        }
      }
    });
  </script>
</body>
</html>
