<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.lucide){lucide.createIcons();}});</script>

<!-- Global Styles -->
<link rel="stylesheet" href="/PRISM/assets/prism-global.css" />


<!-- HEI or CHED specific styles -->
<?php if (isset($_SESSION['chedID'])): ?>
  <link rel="stylesheet" href="/PRISM/assets/ched-global.css" />
<?php elseif (isset($_SESSION['heiUserID'])): ?>
  <link rel="stylesheet" href="/PRISM/assets/hei-global.css" />
<?php else: ?>
  <!-- Default fallback: global only -->
<?php endif; ?>

<div class="prism-header-gradient text-white px-6 py-4 flex items-center justify-between">
  <div class="flex items-center gap-3">
    <img src="/PRISM/assets/CHED_logo.png" alt="CHED Logo" class="h-8 w-8"/>
    <h1 class="text-lg font-semibold">CHED P<span class="text-lg font-thin">ortal for</span>  R<span class="font-thin">epository</span>, I<span class="font-thin">nsights, and</span> S<span class="font-thin">ubmission</span> M<span class="font-thin">anagement</span></h1>
  </div>
  <div class="flex items-center gap-4">
    <a href="/PRISM/php/ched_logout.php" class="logout-btn">
      <i data-lucide="log-out" class="w-4 h-4 lucide"></i>
      <span>Logout</span>
    </a>
  </div>
</div>
