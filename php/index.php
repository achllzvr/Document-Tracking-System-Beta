<?php
require_once __DIR__ . '/db.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>PRISM - PHP Pages</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">PRISM - PHP portal (temporary)</h1>
      <nav class="space-x-4">
        <a href="enrollment.php" class="text-blue-600">Enrollment</a>
        <a href="faculty.php" class="text-blue-600">Faculty</a>
        <a href="graduates.php" class="text-blue-600">Graduates</a>
        <a href="code_tables.php" class="text-blue-600">Templates & Mappings</a>
      </nav>
    </div>

    <p class="text-sm text-gray-600">This is a temporary PHP wrapper of selected pages. It uses the attached SQL schema to render forms and tables.</p>
  </div>
</body>
</html>
