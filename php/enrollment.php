<?php
require_once __DIR__ . '/db.php';

// For simplicity this page shows a summary using mock aggregates from DB if available
$stmt = $pdo->query("SELECT hei_ID, inst_name, inst_region FROM institutional_profile_data LIMIT 100");
$institutions = $stmt->fetchAll();

// Mock calculated numbers — in production you'd compute from enrollment_data table
$totalStudents = rand(10000,30000);
$maleStudents = rand(4000,20000);
$femaleStudents = $totalStudents - $maleStudents;

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Enrollment Data</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <div>
      <h1 class="text-2xl font-semibold">Enrollment Data</h1>
      <p class="text-gray-600">View and export enrollment statistics across all HEIs</p>
    </div>

    <div class="grid md:grid-cols-4 gap-4">
      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-600">Total Students</p>
        <p class="text-2xl font-bold"><?php echo number_format($totalStudents); ?></p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-600">Male Students</p>
        <p class="text-2xl font-bold"><?php echo number_format($maleStudents); ?></p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-600">Female Students</p>
        <p class="text-2xl font-bold"><?php echo number_format($femaleStudents); ?></p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-600">HEIs Reporting</p>
        <p class="text-2xl font-bold"><?php echo count($institutions); ?></p>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b">
        <h2 class="font-medium">Search & Filter</h2>
        <p class="text-gray-500 text-sm">Find enrollment data by institution or region</p>
      </div>
      <div class="p-4">
        <form method="get">
          <div class="flex gap-4">
            <input name="q" class="flex-1 border rounded p-2" placeholder="Search by institution name..." />
            <select name="region" class="border rounded p-2">
              <option value="">All Regions</option>
              <?php
              $regions = $pdo->query("SELECT DISTINCT inst_region FROM institutional_profile_data ORDER BY inst_region")->fetchAll(PDO::FETCH_COLUMN);
              foreach($regions as $r) echo '<option value="'.h($r).'">'.h($r)."</option>";
              ?>
            </select>
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Export All</button>
          </div>
        </form>
      </div>
      <div class="p-4 overflow-x-auto">
        <table class="w-full text-left">
          <thead class="text-sm text-gray-600 border-b">
            <tr>
              <th class="py-2">Institution</th>
              <th>Region</th>
              <th>Total Enrolled</th>
              <th>Male</th>
              <th>Female</th>
              <th>Last Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($institutions as $inst): ?>
            <tr class="border-b">
              <td class="py-2"><?php echo h($inst['inst_name'] ?? 'N/A'); ?></td>
              <td><?php echo h($inst['inst_region'] ?? ''); ?></td>
              <td><?php echo number_format(rand(100,8000)); ?></td>
              <td><?php echo number_format(rand(10,4000)); ?></td>
              <td><?php echo number_format(rand(10,4000)); ?></td>
              <td><?php echo date('Y-m-d'); ?></td>
              <td><a href="#" class="text-blue-600">View Details</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>
