<?php
// Shared sidebar include
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<aside class="w-64 bg-white border-r border-slate-200">
  <nav class="p-4 space-y-1">
  <?php if (isset($_SESSION['chedID'])) {
    // CHED user menu
  ?>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/CHED/ched-dashboard.php">Dashboard</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/CHED/view-heis.php">Institutions</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/CHED/view-tickets.php">Tickets</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/CHED/data-enrollment.php">Data — Enrollment</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/CHED/data-faculty.php">Data — Faculty</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/CHED/data-graduates.php">Data — Graduates</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/CHED/manage-data-templates.php">Templates</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/CHED/hei-analytics.php">HEI Analytics</a>
  <?php 
    } elseif (isset($_SESSION['heiUserID'])) {
    // HEI user menu
  ?>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/HEI/hei-dashboard.php">Dashboard</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/HEI/view-tickets.php">Tickets</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/HEI/enrollment.php">Enrollment</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/HEI/faculty.php">Faculty</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/HEI/graduates.php">Graduates</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/HEI/hei-analytics.php">Analytics</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/HEI/institution-profile.php">Institution Profile</a>
  <?php
    } else {
    // Guest view — do not redirect from an include
  ?>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/HEI/login.php">HEI Login</a>
    <a class="block px-3 py-2 rounded hover:bg-slate-50" href="/PRISM/CHED/login.php">CHED Login</a>
  <?php
    }
  ?>
  </nav>
</aside>
