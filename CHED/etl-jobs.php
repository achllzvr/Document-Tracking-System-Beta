<?php
// Page deprecated: return 410 Gone and show a friendly message with link back to dashboard
http_response_code(410);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Page Removed — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>
  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 text-center">
          <div class="mx-auto mb-4 h-14 w-14 rounded-full bg-slate-800/90 text-white flex items-center justify-center shadow">410</div>
          <h1 class="text-xl font-semibold">ETL Jobs page has been removed</h1>
          <p class="text-slate-600 mt-2">This page is no longer available. Please use Tickets or other sections for related workflows.</p>
          <a href="/PRISM/PRISM/CHED/ched-dashboard.php" class="inline-block mt-6 px-4 py-2 rounded bg-blue-600 text-white">Go to Dashboard</a>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
