<?php
  
// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// CHED protector
require_once __DIR__ . '/../includes/ched_protect.php';

// Database helper
require_once __DIR__ . '/../classes/database.php';
$db = new database();

// Get filter parameters
$selectedYear = $_GET['year'] ?? '';
$selectedTerm = $_GET['term'] ?? '';
$selectedProgram = $_GET['program'] ?? '';
$selectedRegion = $_GET['region'] ?? '';
$selectedHEI = $_GET['hei'] ?? '';

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
if ($selectedRegion !== '' && $selectedRegion !== 'all') {
    $filters['region'] = $selectedRegion;
}
if ($selectedHEI !== '' && $selectedHEI !== 'all') {
    $filters['hei_id'] = $selectedHEI;
}

// Fetch available filter options
$availableYears = $db->getCHEDAvailableAcademicYears();
$availableTerms = $db->getCHEDAvailableTerms();
$availablePrograms = $db->getCHEDAvailablePrograms();
$availableRegions = $db->fetchAllRegions();
$availableHEIs = $db->getHEIs([]); // Get all HEIs for dropdown

// Fetch summary statistics
$summary = $db->getCHEDEnrollmentSummary($filters);
$pendingTickets = $db->getCHEDTotalPendingTicketsCount();

// Fetch analytics data for charts
$trendData = $db->getCHEDEnrollmentTrend($filters);
$regionData = $db->getCHEDEnrollmentByRegion($filters);
$topHEIs = $db->getCHEDTopHEIsByEnrollment($filters, 10);
$programData = $db->getCHEDEnrollmentByProgram($filters, 10);
$sexData = $db->getCHEDEnrollmentBySex($filters);
$yearLevelData = $db->getCHEDEnrollmentByYearLevel($filters);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>System Analytics — PRISM</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="/PRISM/assets/prism-global.css">
  <link rel="stylesheet" href="/PRISM/assets/ched-global.css">
</head>
<body class="ched-min-h-screen">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="ched-flex-1">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="ched-main">
      <div class="ched-max-w-7xl ched-space-y-6">
        
        <!-- Page Header -->
        <section class="ched-card">
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                  <i data-lucide="bar-chart-3" class="h-7 w-7 text-blue-600"></i>
                  System-Wide Analytics
                </h1>
                <p class="text-sm text-slate-500 mt-1">View enrollment trends and distribution metrics across all institutions</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Filters Section -->
        <section class="ched-card">
          <header class="ched-card-header">
            <h2 class="ched-font-semibold text-slate-800">Filters</h2>
            <p class="ched-text-sm">Filter data by academic year, region, and institution</p>
          </header>
          <div class="p-6">
            <form method="get" class="grid md:grid-cols-3 lg:grid-cols-6 gap-4">
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

              <!-- Region Filter -->
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-2">Region</label>
                <select name="region" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="all">All Regions</option>
                  <?php foreach ($availableRegions as $region): ?>
                    <option value="<?php echo htmlspecialchars($region['region_ID']); ?>" <?php echo ($selectedRegion == $region['region_ID']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($region['region_number'] . ' - ' . $region['region_division']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- HEI Filter -->
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-2">Institution</label>
                <select name="hei" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="all">All Institutions</option>
                  <?php foreach ($availableHEIs as $hei): ?>
                    <option value="<?php echo htmlspecialchars($hei['id']); ?>" <?php echo ($selectedHEI == $hei['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($hei['name']); ?>
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
                  <span class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                    Term: <?php echo htmlspecialchars($filters['term']); ?>
                  </span>
                <?php endif; ?>
                <?php if (!empty($filters['program'])): ?>
                  <span class="inline-flex items-center gap-1 px-3 py-1 bg-teal-100 text-teal-700 rounded-full text-xs font-medium">
                    Program: <?php echo htmlspecialchars($filters['program']); ?>
                  </span>
                <?php endif; ?>
                <?php if (!empty($filters['region'])): 
                    $regionName = '';
                    foreach ($availableRegions as $r) {
                        if ($r['region_ID'] == $filters['region']) {
                            $regionName = $r['region_number'];
                            break;
                        }
                    }
                ?>
                  <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                    Region: <?php echo htmlspecialchars($regionName); ?>
                  </span>
                <?php endif; ?>
                <?php if (!empty($filters['hei_id'])): 
                    $heiName = '';
                    foreach ($availableHEIs as $h) {
                        if ($h['id'] == $filters['hei_id']) {
                            $heiName = $h['name'];
                            break;
                        }
                    }
                ?>
                  <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                    HEI: <?php echo htmlspecialchars($heiName); ?>
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
        <div class="grid md:grid-cols-4 gap-4">
          <div class="ched-card">
            <div class="p-5">
              <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                  <i data-lucide="users" class="h-6 w-6 text-blue-600"></i>
                </div>
                <div>
                  <div class="text-xs text-slate-500 font-medium">Total Students</div>
                  <div class="text-2xl font-bold text-slate-800 mt-1"><?php echo number_format($summary['total_students']); ?></div>
                </div>
              </div>
            </div>
          </div>

          <div class="ched-card">
            <div class="p-5">
              <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                  <i data-lucide="school" class="h-6 w-6 text-purple-600"></i>
                </div>
                <div>
                  <div class="text-xs text-slate-500 font-medium">Institutions</div>
                  <div class="text-2xl font-bold text-slate-800 mt-1"><?php echo number_format($summary['hei_count']); ?></div>
                </div>
              </div>
            </div>
          </div>

          <div class="ched-card">
            <div class="p-5">
              <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                  <i data-lucide="graduation-cap" class="h-6 w-6 text-emerald-600"></i>
                </div>
                <div>
                  <div class="text-xs text-slate-500 font-medium">Programs</div>
                  <div class="text-2xl font-bold text-slate-800 mt-1"><?php echo number_format($summary['program_count']); ?></div>
                </div>
              </div>
            </div>
          </div>

          <div class="ched-card">
            <div class="p-5">
              <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                  <i data-lucide="ticket" class="h-6 w-6 text-amber-600"></i>
                </div>
                <div>
                  <div class="text-xs text-slate-500 font-medium">Pending Tickets</div>
                  <div class="text-2xl font-bold text-slate-800 mt-1"><?php echo number_format($pendingTickets); ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid md:grid-cols-2 gap-6">
          
          <!-- Enrollment Trend Chart -->
          <section class="ched-card">
            <header class="px-6 py-4 border-b border-slate-200">
              <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="trending-up" class="h-5 w-5 text-blue-600"></i>
                Enrollment Trend Over Years
              </h3>
              <p class="text-xs text-slate-500 mt-1">Historical enrollment data by academic year</p>
            </header>
            <div class="p-6">
              <canvas id="trendChart" style="max-height: 300px;"></canvas>
            </div>
          </section>

          <!-- Top Programs Chart -->
          <section class="ched-card">
            <header class="px-6 py-4 border-b border-slate-200">
              <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="book-open" class="h-5 w-5 text-purple-600"></i>
                Top 10 Programs by Enrollment
              </h3>
              <p class="text-xs text-slate-500 mt-1">Programs with highest student counts</p>
            </header>
            <div class="p-6">
              <canvas id="programChart" style="max-height: 300px;"></canvas>
            </div>
          </section>

          <!-- Gender Distribution Chart -->
          <section class="ched-card">
            <header class="px-6 py-4 border-b border-slate-200">
              <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="pie-chart" class="h-5 w-5 text-pink-600"></i>
                Gender Distribution
              </h3>
              <p class="text-xs text-slate-500 mt-1">Male vs Female enrollment ratio</p>
            </header>
            <div class="p-6">
              <canvas id="sexChart" style="max-height: 300px;"></canvas>
            </div>
          </section>

          <!-- Distribution by Year Level Chart -->
          <section class="ched-card">
            <header class="px-6 py-4 border-b border-slate-200">
              <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="layers" class="h-5 w-5 text-cyan-600"></i>
                Distribution by Year Level
              </h3>
              <p class="text-xs text-slate-500 mt-1">Student distribution across year levels</p>
            </header>
            <div class="p-6">
              <canvas id="yearLevelChart" style="max-height: 300px;"></canvas>
            </div>
          </section>

        </div>

        <!-- Summary Statistics Table -->
        <section class="ched-card">
          <header class="ched-card-header">
            <div>
              <h3 class="ched-font-semibold text-slate-800">Summary Statistics</h3>
              <p class="ched-text-sm">Detailed breakdown of enrollment data</p>
            </div>
          </header>
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Metric</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Value</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                  <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-sm font-medium text-slate-700">Total Students Enrolled</td>
                    <td class="px-4 py-3 text-sm text-slate-900 text-right font-semibold"><?php echo number_format($summary['total_students']); ?></td>
                  </tr>
                  <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-sm font-medium text-slate-700">Number of Institutions</td>
                    <td class="px-4 py-3 text-sm text-slate-900 text-right font-semibold"><?php echo number_format($summary['hei_count']); ?></td>
                  </tr>
                  <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-sm font-medium text-slate-700">Number of Programs</td>
                    <td class="px-4 py-3 text-sm text-slate-900 text-right font-semibold"><?php echo number_format($summary['program_count']); ?></td>
                  </tr>
                  <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-sm font-medium text-slate-700">Male Students</td>
                    <td class="px-4 py-3 text-sm text-slate-900 text-right font-semibold">
                      <?php 
                        $maleCount = 0;
                        foreach ($sexData as $row) {
                          if (strtolower($row['sex']) == 'male') {
                            $maleCount = $row['student_count'];
                            break;
                          }
                        }
                        $malePercent = $summary['total_students'] > 0 ? ($maleCount / $summary['total_students']) * 100 : 0;
                        echo number_format($maleCount) . ' (' . number_format($malePercent, 1) . '%)';
                      ?>
                    </td>
                  </tr>
                  <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-sm font-medium text-slate-700">Female Students</td>
                    <td class="px-4 py-3 text-sm text-slate-900 text-right font-semibold">
                      <?php 
                        $femaleCount = 0;
                        foreach ($sexData as $row) {
                          if (strtolower($row['sex']) == 'female') {
                            $femaleCount = $row['student_count'];
                            break;
                          }
                        }
                        $femalePercent = $summary['total_students'] > 0 ? ($femaleCount / $summary['total_students']) * 100 : 0;
                        echo number_format($femaleCount) . ' (' . number_format($femalePercent, 1) . '%)';
                      ?>
                    </td>
                  </tr>
                  <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-sm font-medium text-slate-700">Average Enrollment per HEI</td>
                    <td class="px-4 py-3 text-sm text-slate-900 text-right font-semibold">
                      <?php echo $summary['hei_count'] > 0 ? number_format($summary['total_students'] / $summary['hei_count'], 0) : '0'; ?>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

      </div>
    </main>
  </div>

  <!-- Chart.js Initialization -->
  <script>
    // PHP data to JavaScript
    const trendData = <?php echo json_encode($trendData); ?>;
    const regionData = <?php echo json_encode($regionData); ?>;
    const heiData = <?php echo json_encode($topHEIs); ?>;
    const programData = <?php echo json_encode($programData); ?>;
    const sexData = <?php echo json_encode($sexData); ?>;
    const yearLevelData = <?php echo json_encode($yearLevelData); ?>;

    // Initialize Chart.js charts
    document.addEventListener('DOMContentLoaded', function() {
      // 1. Enrollment Trend Chart
      const trendCtx = document.getElementById('trendChart').getContext('2d');
      new Chart(trendCtx, {
        type: 'line',
        data: {
          labels: trendData.map(d => d.acad_year),
          datasets: [{
            label: 'Total Students',
            data: trendData.map(d => parseInt(d.student_count)),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4
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

      // 2. Top Programs Chart (Horizontal Bar)
      const programCtx = document.getElementById('programChart').getContext('2d');
      new Chart(programCtx, {
        type: 'bar',
        data: {
          labels: programData.map(d => d.program),
          datasets: [{
            label: 'Students',
            data: programData.map(d => parseInt(d.student_count)),
            backgroundColor: '#f59e0b'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return 'Students: ' + context.parsed.x.toLocaleString();
                }
              }
            }
          },
          scales: {
            x: {
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

      // 3. Gender Distribution Chart (Pie)
      const sexCtx = document.getElementById('sexChart').getContext('2d');
      new Chart(sexCtx, {
        type: 'pie',
        data: {
          labels: sexData.map(d => d.sex),
          datasets: [{
            data: sexData.map(d => parseInt(d.student_count)),
            backgroundColor: ['#3b82f6', '#ec4899', '#6b7280']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed || 0;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((value / total) * 100).toFixed(1);
                  return label + ': ' + value.toLocaleString() + ' (' + percentage + '%)';
                }
              }
            }
          }
        }
      });

      // 4. Distribution by Year Level Chart (Bar)
      const yearLevelCtx = document.getElementById('yearLevelChart').getContext('2d');
      new Chart(yearLevelCtx, {
        type: 'bar',
        data: {
          labels: yearLevelData.map(d => d.year_level),
          datasets: [{
            label: 'Students',
            data: yearLevelData.map(d => parseInt(d.student_count)),
            backgroundColor: '#06b6d4'
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

      // Initialize Lucide icons
      if (window.lucide) {
        lucide.createIcons();
      }
    });
  </script>
</body>
</html>
