<?php
// CHED login page
// Assumptions: table `ched_users` exists (see CHED_document_repository.sql).
// Adjust column names below if your schema differs (username/email/password fields).
require_once __DIR__ . '/db.php';
session_start();

$sweetAlertConfig = '';
$error = '';

if (isset($_SESSION['ched_ID'])) {
  header('Location: /PRISM/CHED/ched-dashboard.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $identifier = trim($_POST['user'] ?? '');
  $password = $_POST['password'] ?? '';

  // Example query: try matching username or email. Update column names as needed.
  $stmt = $pdo->prepare('SELECT ched_ID, ched_firstname, ched_lastname, ched_password FROM ched_users WHERE ched_username = :id OR ched_email = :id LIMIT 1');
  $stmt->execute([':id' => $identifier]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['ched_password'])) {
    $_SESSION['ched_ID'] = $user['ched_ID'];
    $_SESSION['chedFN'] = $user['ched_firstname'] ?? '';
    $_SESSION['chedLN'] = $user['ched_lastname'] ?? '';

    $sweetAlertConfig = "<script>Swal.fire({icon:'success',title:'Login Successful',text:'Welcome, ".addslashes(htmlspecialchars($_SESSION['chedFN']))."',confirmButtonText:'Continue'}).then(()=>{window.location.href='/PRISM/CHED/ched-dashboard.php'});</script>";
  } else {
    $error = 'Invalid username or password.';
    $sweetAlertConfig = "";
  }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CHED Login</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* small helper to mimic the React card top border color */
    :root{--ph-blue:#0038A8;--ph-blue-light:#3366FF}
  </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8 relative" style="background: linear-gradient(135deg, #e6eef9 0%, #fef9e6 100%);">
  <!-- Watermark background image -->
  <div class="fixed inset-0 pointer-events-none" style="background-image: url('/PRISM/assets/CHED_logo.png'); background-repeat: no-repeat; background-position: center center; background-size: 500px; opacity: 0.02;"></div>

  <div class="w-full max-w-md relative z-10 shadow-xl border-t-4 bg-white rounded" style="border-top-color: var(--ph-blue);">
    <div class="p-6">
      <div class="space-y-4 text-center">
        <div class="flex justify-center">
          <div class="p-3 rounded-full" style="background: linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%); box-shadow: 0 4px 12px rgba(0, 56, 168, 0.3);">
            <i data-lucide="graduation-cap" class="w-8 h-8 text-white"></i>
          </div>
        </div>
        <div>
          <h1 class="text-xl font-semibold">CHED HEI Data Portal</h1>
          <p class="text-sm text-slate-500">Sign in to access the Higher Education Information System</p>
        </div>
      </div>

      <form method="post" action="" class="mt-6 space-y-4">
        <?php if ($error): ?>
          <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="space-y-1">
          <label class="text-sm">Username</label>
          <input name="user" value="<?php echo isset($_POST['user'])?htmlspecialchars($_POST['user']):'';?>" class="w-full border px-3 py-2 rounded" required />
        </div>

        <div class="space-y-1">
          <label class="text-sm">Password</label>
          <input name="password" type="password" class="w-full border px-3 py-2 rounded" required />
        </div>

        <button name="login" type="submit" class="w-full text-white px-4 py-2 rounded" style="background: linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%); box-shadow: 0 2px 8px rgba(0,56,168,0.3);">Sign In</button>

        <div class="mt-4 p-4 bg-gray-50 rounded text-sm text-gray-600">
          <p class="mb-2">Demo Credentials:</p>
          <div class="space-y-1 text-xs text-gray-500">
            <p><strong>CHED:</strong> ched_admin / admin123</p>
            <p><strong>HEI Head:</strong> hei_head / head123</p>
            <p><strong>HEI Sub-User:</strong> hei_user / user123</p>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script> if (window.lucide) lucide.createIcons(); </script>
  <?php echo $sweetAlertConfig; ?>
</body>
</html>
