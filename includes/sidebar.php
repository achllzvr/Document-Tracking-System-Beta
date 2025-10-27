<?php
// Shared sidebar include
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<script src="https://unpkg.com/lucide@latest"></script>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.lucide){lucide.createIcons();}});</script>
<aside class="w-64 bg-white border-r border-slate-200">
  <nav class="p-4 space-y-1">
  <?php if (isset($_SESSION['chedID'])) {
    // CHED user menu
  ?>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/CHED/ched-dashboard.php"><i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/CHED/view-heis.php"><i data-lucide="school" class="w-4 h-4"></i> Institutions</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/CHED/view-tickets.php"><i data-lucide="ticket" class="w-4 h-4"></i> Tickets</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/CHED/data-enrollment.php"><i data-lucide="users" class="w-4 h-4"></i> Data — Enrollment</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/CHED/data-faculty.php"><i data-lucide="user" class="w-4 h-4"></i> Data — Faculty</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/CHED/data-graduates.php"><i data-lucide="graduation-cap" class="w-4 h-4"></i> Data — Graduates</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/CHED/manage-data-templates.php"><i data-lucide="file-text" class="w-4 h-4"></i> Templates</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/CHED/hei-analytics.php"><i data-lucide="bar-chart-3" class="w-4 h-4"></i> HEI Analytics</a>
  <?php 
    } elseif (isset($_SESSION['heiUserID'])) {
    // HEI user menu
  ?>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/HEI/hei-dashboard.php"><i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/HEI/view-tickets.php"><i data-lucide="ticket" class="w-4 h-4"></i> Tickets</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/HEI/enrollment.php"><i data-lucide="users" class="w-4 h-4"></i> Enrollment</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/HEI/faculty.php"><i data-lucide="user" class="w-4 h-4"></i> Faculty</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/HEI/graduates.php"><i data-lucide="graduation-cap" class="w-4 h-4"></i> Graduates</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/HEI/hei-analytics.php"><i data-lucide="bar-chart-3" class="w-4 h-4"></i> Analytics</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/HEI/institution-profile.php"><i data-lucide="school" class="w-4 h-4"></i> Institution Profile</a>
  <?php
    } else {
    // Guest view — do not redirect from an include
  ?>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/HEI/login.php"><i data-lucide="log-in" class="w-4 h-4"></i> HEI Login</a>
  <a class="block px-3 py-2 rounded hover:bg-slate-50 flex items-center gap-2" href="/PRISM/CHED/login.php"><i data-lucide="log-in" class="w-4 h-4"></i> CHED Login</a>
  <?php
    }
  ?>
  </nav>
</aside>
