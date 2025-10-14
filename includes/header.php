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
  <div class="flex items-center gap-3">
    <img src="/PRISM/assets/CHED_logo.png" alt="CHED Logo" class="h-8 w-8"/>
    <h1 class="text-lg font-semibold">CHED P<span class="text-lg font-thin">ortal for</span>  R<span class="font-thin">epository</span>, I<span class="font-thin">nsights, and</span> S<span class="font-thin">ubmission</span> M<span class="font-thin">anagement</span></h1>
  </div>
  <div class="flex items-center gap-4">
    <a href="/PRISM/" class="text-sm text-white/90">Home</a>
    <?php if ($showHEI): ?>
      <a href="/PRISM/HEI/hei-dashboard.php" class="text-sm text-white/90">HEI</a>
    <?php endif; ?>
    <a href="/PRISM/CHED/ched-dashboard.php" class="text-sm text-white/90">CHED</a>
    <a href="/PRISM/php/ched_logout.php" class="text-sm underline">Logout</a>
  </div>
</div>
