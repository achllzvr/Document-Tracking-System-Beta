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
<div class="bg-blue-600 text-white px-6 py-4 flex items-center justify-between">
  <div class="flex items-center gap-3"><i data-lucide="box" class="h-6 w-6"></i><h1 class="text-lg font-semibold">PRISM</h1></div>
  <div class="flex items-center gap-4">
    <a href="/PRISM/" class="text-sm text-white/90">Home</a>
    <?php if ($showHEI): ?>
      <a href="/PRISM/HEI/hei-dashboard.php" class="text-sm text-white/90">HEI</a>
    <?php endif; ?>
    <a href="/PRISM/CHED/ched-dashboard.php" class="text-sm text-white/90">CHED</a>
    <a href="/PRISM/php/ched_logout.php" class="text-sm underline">Logout</a>
  </div>
</div>
