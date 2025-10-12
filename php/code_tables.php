<?php
require_once __DIR__ . '/db.php';

$degrees = $pdo->query('SELECT degree_code, degree_name FROM degree_codes ORDER BY degree_name')->fetchAll();
$disciplines = $pdo->query('SELECT discipline_code, specific_discipline_name FROM discipline_codes ORDER BY specific_discipline_name')->fetchAll();
$employment = $pdo->query('SELECT employment_code, employment_desc FROM employment_codes ORDER BY employment_desc')->fetchAll();

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Code Tables</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <h1 class="text-2xl font-semibold">Code Tables Management</h1>
    <p class="text-gray-600">Manage reference tables and lookup codes used throughout the system</p>

    <div class="grid md:grid-cols-3 gap-4">
      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-medium">Degree Codes</h3>
        <table class="w-full text-left mt-2 text-sm"><tbody>
          <?php foreach($degrees as $d) echo '<tr class="border-b"><td class="py-2">'.htmlspecialchars($d['degree_code']).'</td><td>'.htmlspecialchars($d['degree_name'])."</td></tr>"; ?>
        </tbody></table>
      </div>

      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-medium">Discipline Codes</h3>
        <table class="w-full text-left mt-2 text-sm"><tbody>
          <?php foreach($disciplines as $d) echo '<tr class="border-b"><td class="py-2">'.htmlspecialchars($d['discipline_code']).'</td><td>'.htmlspecialchars($d['specific_discipline_name'])."</td></tr>"; ?>
        </tbody></table>
      </div>

      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-medium">Employment Types</h3>
        <table class="w-full text-left mt-2 text-sm"><tbody>
          <?php foreach($employment as $e) echo '<tr class="border-b"><td class="py-2">'.htmlspecialchars($e['employment_code']).'</td><td>'.htmlspecialchars($e['employment_desc'])."</td></tr>"; ?>
        </tbody></table>
      </div>
    </div>

  </div>
</body>
</html>
