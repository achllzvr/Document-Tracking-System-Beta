<?php

if (!isset($_SESSION['chedID']) || !isset($_SESSION['heiID'])) {

  // If not logged in, redirect to login page
  if (!isset($_SESSION['chedID'])) {
    $showHEI = true;
  } elseif (!isset($_SESSION['heiID'])) {
    $showHEI = false;
  } else {
    $showHEI = false;
  }

}

?>
<aside class="w-64 bg-white border-r border-slate-200">
  <nav class="p-4 space-y-1">
    <?php if ($showHEI): ?>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/CHED/ched-dashboard.php"><i data-lucide="home" class="h-4 w-4"></i><span>Dashboard</span></a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/CHED/view-heis.php"><i data-lucide="building-2" class="h-4 w-4"></i><span>Institutions</span></a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/CHED/view-tickets.php"><i data-lucide="ticket" class="h-4 w-4"></i><span>Tickets</span></a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/CHED/data-enrollment.php"><i data-lucide="book-open" class="h-4 w-4"></i><span>Enrollment Data</span></a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/CHED/data-faculty.php"><i data-lucide="users" class="h-4 w-4"></i><span>Faculty Data</span></a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/CHED/data-graduates.php"><i data-lucide="award" class="h-4 w-4"></i><span>Graduates Data</span></a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/CHED/manage-data-templates.php"><i data-lucide="file-spreadsheet" class="h-4 w-4"></i><span>Templates &amp; Mappings</span></a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/CHED/etl-jobs.php"><i data-lucide="server-cog" class="h-4 w-4"></i><span>ETL Jobs</span></a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/php/code_tables.php"><i data-lucide="code" class="h-4 w-4"></i><span>Code Tables</span></a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-3" href="/PRISM/CHED/hei-analytics.php"><i data-lucide="bar-chart-2" class="h-4 w-4"></i><span>HEI Analytics</span></a>
    <?php endif; ?>
    <!-- HEI nav links -->
  </nav>
</aside>
