<?php
require_once __DIR__ . '/db.php';

// Fetch faculty records
$faculty = $pdo->query("SELECT f.faculty_ID, f.fac_name, f.fac_employment_type_code, f.fac_gender_code, f.fac_primary_teaching_code, f.fac_highest_degree_attained_code, f.fac_created_at, i.inst_name
FROM faculty_data f
LEFT JOIN institutional_profile_data i ON f.hei_ID = i.hei_ID
ORDER BY f.faculty_ID DESC LIMIT 200")->fetchAll();

// Lookup tables for employment, degree, discipline
$employment = $pdo->query("SELECT employment_code, employment_desc FROM employment_codes")->fetchAll();
$degrees = $pdo->query("SELECT degree_code, degree_name FROM degree_codes")->fetchAll();
$disciplines = $pdo->query("SELECT discipline_code, specific_discipline_name FROM discipline_codes")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faculty'])) {
    // Minimal insert (no validation for brevity)
    $stmt = $pdo->prepare("INSERT INTO faculty_data (hei_ID, fac_name, fac_employment_type_code, fac_gender_code, fac_primary_teaching_code, fac_highest_degree_attained_code) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$_POST['hei_ID'] ?: null, $_POST['fac_name'], $_POST['employment_type'] ?: null, $_POST['sex'], $_POST['primary_teaching'] ?: null, $_POST['degree'] ?: null]);
    header('Location: faculty.php');
    exit;
}

function codeOptions($arr) {
    $html = '';
    foreach($arr as $r) {
        $k = current($r); $v = next($r); // first and second column
        $html .= '<option value="'.htmlspecialchars($k).'">'.htmlspecialchars($v)."</option>";
    }
    return $html;
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Faculty Data</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold">Faculty Data Entry</h1>
        <p class="text-gray-600">Manage faculty records for your institution</p>
      </div>
      <div>
        <button onclick="document.getElementById('addForm').classList.toggle('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded">Add Faculty</button>
      </div>
    </div>

    <div id="addForm" class="hidden bg-white rounded-lg shadow p-4">
      <form method="post">
        <input type="hidden" name="add_faculty" value="1" />
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm">Full Name *</label>
            <input name="fac_name" class="w-full border rounded p-2" required />
          </div>
          <div>
            <label class="block text-sm">Sex</label>
            <select name="sex" class="w-full border rounded p-2">
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div>
            <label class="block text-sm">Employment Type</label>
            <select name="employment_type" class="w-full border rounded p-2">
              <option value="">Select type</option>
              <?php foreach($employment as $e) echo '<option value="'.htmlspecialchars($e['employment_code']).'">'.htmlspecialchars($e['employment_desc'])."</option>"; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm">Highest Degree</label>
            <select name="degree" class="w-full border rounded p-2">
              <option value="">Select degree</option>
              <?php foreach($degrees as $d) echo '<option value="'.htmlspecialchars($d['degree_code']).'">'.htmlspecialchars($d['degree_name'])."</option>"; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm">Primary Discipline</label>
            <select name="primary_teaching" class="w-full border rounded p-2">
              <option value="">Select discipline</option>
              <?php foreach($disciplines as $di) echo '<option value="'.htmlspecialchars($di['major_discipline_code'] ?? $di['discipline_code']).'">'.htmlspecialchars($di['major_discipline_name'] ?? $di['specific_discipline_name'])."</option>"; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm">Institution</label>
            <select name="hei_ID" class="w-full border rounded p-2">
              <option value="">Select HEI</option>
              <?php
              $his = $pdo->query('SELECT hei_ID, inst_name FROM institutional_profile_data ORDER BY inst_name')->fetchAll();
              foreach($his as $h) echo '<option value="'.htmlspecialchars($h['hei_ID']).'">'.htmlspecialchars($h['inst_name'])."</option>";
              ?>
            </select>
          </div>
        </div>
        <div class="mt-4">
          <button class="bg-green-600 text-white px-4 py-2 rounded">Save Faculty</button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b">
        <h2 class="font-medium">Faculty Records (<?php echo count($faculty); ?>)</h2>
        <p class="text-gray-500 text-sm">Current faculty data entries</p>
      </div>
      <div class="p-4 overflow-x-auto">
        <table class="w-full text-left">
          <thead class="text-sm text-gray-600 border-b">
            <tr>
              <th class="py-2">Name</th>
              <th>Employment Type</th>
              <th>Highest Degree</th>
              <th>Discipline</th>
              <th>Sex</th>
              <th>Institution</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($faculty as $f): ?>
            <tr class="border-b">
              <td class="py-2"><?php echo htmlspecialchars($f['fac_name']); ?></td>
              <td><?php echo htmlspecialchars($f['fac_employment_type_code']); ?></td>
              <td><?php echo htmlspecialchars($f['fac_highest_degree_attained_code']); ?></td>
              <td><?php echo htmlspecialchars($f['fac_primary_teaching_code']); ?></td>
              <td><?php echo htmlspecialchars($f['fac_gender_code']); ?></td>
              <td><?php echo htmlspecialchars($f['inst_name'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
