<?php
require_once __DIR__ . '/db.php';

$graduates = $pdo->query("SELECT g.graduates_ID, g.grad_name, g.grad_sex, g.grad_date, g.grad_program, g.grad_major, i.inst_name FROM graduates_data g LEFT JOIN institutional_profile_data i ON g.hei_ID = i.hei_ID ORDER BY g.graduates_ID DESC LIMIT 200")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_grad'])) {
    $stmt = $pdo->prepare("INSERT INTO graduates_data (hei_ID, grad_name, grad_sex, grad_date, grad_program, grad_major) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$_POST['hei_ID'] ?: null, $_POST['grad_name'], $_POST['grad_sex'], $_POST['grad_date'] ?: null, $_POST['grad_program'], $_POST['grad_major']]);
    header('Location: graduates.php'); exit;
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Graduates Data</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold">Graduates Data Entry</h1>
        <p class="text-gray-600">Manage graduates records for your institution</p>
      </div>
      <div>
        <button onclick="document.getElementById('addGrad').classList.toggle('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded">Add Graduate</button>
      </div>
    </div>

    <div id="addGrad" class="hidden bg-white rounded-lg shadow p-4">
      <form method="post">
        <input type="hidden" name="add_grad" value="1" />
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm">Name</label>
            <input name="grad_name" class="w-full border rounded p-2" required />
          </div>
          <div>
            <label class="block text-sm">Sex</label>
            <select name="grad_sex" class="w-full border rounded p-2"><option value="m">Male</option><option value="f">Female</option></select>
          </div>
          <div>
            <label class="block text-sm">Date</label>
            <input type="date" name="grad_date" class="w-full border rounded p-2" />
          </div>
          <div>
            <label class="block text-sm">Program</label>
            <input name="grad_program" class="w-full border rounded p-2" />
          </div>
          <div>
            <label class="block text-sm">Major</label>
            <input name="grad_major" class="w-full border rounded p-2" />
          </div>
          <div>
            <label class="block text-sm">Institution</label>
            <select name="hei_ID" class="w-full border rounded p-2">
              <option value="">Select HEI</option>
              <?php foreach($pdo->query('SELECT hei_ID, inst_name FROM institutional_profile_data ORDER BY inst_name')->fetchAll() as $h) echo '<option value="'.htmlspecialchars($h['hei_ID']).'">'.htmlspecialchars($h['inst_name'])."</option>"; ?>
            </select>
          </div>
        </div>
        <div class="mt-4"><button class="bg-green-600 text-white px-4 py-2 rounded">Save Graduate</button></div>
      </form>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b"><h2 class="font-medium">Graduates Records (<?php echo count($graduates); ?>)</h2><p class="text-gray-500 text-sm">Current graduates data entries</p></div>
      <div class="p-4 overflow-x-auto">
        <table class="w-full text-left">
          <thead class="text-sm text-gray-600 border-b"><tr><th class="py-2">Name</th><th>Sex</th><th>Date</th><th>Program</th><th>Major</th><th>Institution</th></tr></thead>
          <tbody>
            <?php foreach($graduates as $g): ?>
            <tr class="border-b"><td class="py-2"><?php echo htmlspecialchars($g['grad_name']); ?></td><td><?php echo htmlspecialchars($g['grad_sex']); ?></td><td><?php echo htmlspecialchars($g['grad_date']); ?></td><td><?php echo htmlspecialchars($g['grad_program']); ?></td><td><?php echo htmlspecialchars($g['grad_major']); ?></td><td><?php echo htmlspecialchars($g['inst_name'] ?? ''); ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>
