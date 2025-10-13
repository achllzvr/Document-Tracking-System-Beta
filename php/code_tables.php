<?php
require_once __DIR__ . '/db.php';

$degrees = $pdo->query('SELECT degree_code, degree_name FROM degree_codes ORDER BY degree_name')->fetchAll();
$disciplines = $pdo->query('SELECT discipline_code, specific_discipline_name FROM discipline_codes ORDER BY specific_discipline_name')->fetchAll();
$employment = $pdo->query('SELECT employment_code, employment_desc FROM employment_codes ORDER BY employment_desc')->fetchAll();

// Use shared CHED layout
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Code Tables — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">Code Tables Management</h2>
            <p class="text-sm text-slate-500">Manage reference tables and lookup codes used throughout the system</p>
          </header>
          <div class="p-5 grid md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
              <h3 class="font-medium">Degree Codes</h3>
              <table class="w-full text-left mt-2 text-sm"><tbody>
                <?php foreach($degrees as $d) echo '<tr class="border-b"><td class="py-2">'.htmlspecialchars($d['degree_code']).'</td><td>'.htmlspecialchars($d['degree_name']).'</td></tr>'; ?>
              </tbody></table>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
              <h3 class="font-medium">Discipline Codes</h3>
              <table class="w-full text-left mt-2 text-sm"><tbody>
                <?php foreach($disciplines as $d) echo '<tr class="border-b"><td class="py-2">'.htmlspecialchars($d['discipline_code']).'</td><td>'.htmlspecialchars($d['specific_discipline_name']).'</td></tr>'; ?>
              </tbody></table>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
              <h3 class="font-medium">Employment Types</h3>
              <table class="w-full text-left mt-2 text-sm"><tbody>
                <?php foreach($employment as $e) echo '<tr class="border-b"><td class="py-2">'.htmlspecialchars($e['employment_code']).'</td><td>'.htmlspecialchars($e['employment_desc']).'</td></tr>'; ?>
              </tbody></table>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <script>
    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
