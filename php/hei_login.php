<?php

session_start();
require_once __DIR__ . '/../classes/database.php';

if (!empty($_SESSION['heiUserID'])) {
    // already authenticated — send to dashboard
    header('Location: /PRISM/HEI/hei-dashboard.php');
    exit;
}

$con = new database();
$error = '';
$sweetAlertConfig = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $con->loginHEIUser($email, $password);

    if ($user) {
        $_SESSION['heiUserID'] = $user['hei_user_ID'];
        $_SESSION['heiID'] = $user['hei_ID'];
        $_SESSION['heiName'] = $user['hei_last_name'] . ', ' . $user['hei_first_name'];
        $_SESSION['heiRole'] = $user['hei_role'];

        $sweetAlertConfig = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Login Successful',
            text: 'Welcome, " . htmlspecialchars($_SESSION['heiName'], ENT_QUOTES) . "!',
            confirmButtonText: 'Continue'
        }).then(() => {
            window.location.href = '/PRISM/HEI/hei-dashboard.php';
        });
        </script>";
    } else {
        $error = 'Invalid email or password.';
        $sweetAlertConfig = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: 'Invalid email or password.'
        });
        </script>";
    }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HEI Login</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root{--ph-blue:#0038A8;--ph-blue-light:#3366FF}
  </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8 relative" style="background: linear-gradient(135deg, #e6eef9 0%, #fef9e6 100%);">
  <div class="fixed inset-0 pointer-events-none" style="background-image: url('/PRISM/src/assets/4ec9875a2abae0c471afd06897a613cfc08b40a6.png'); background-repeat: no-repeat; background-position: center center; background-size: 500px; opacity: 0.02;"></div>

  <div class="w-full max-w-md relative z-10 shadow-xl border-t-4 bg-white rounded" style="border-top-color: var(--ph-blue);">
    <div class="p-6">
      <div class="space-y-4 text-center">
        <div class="flex justify-center">
          <div class="p-3 rounded-full" style="background: linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%); box-shadow: 0 4px 12px rgba(0, 56, 168, 0.3);">
            <i data-lucide="graduation-cap" class="w-8 h-8 text-white"></i>
          </div>
        </div>
        <div>
          <h1 class="text-xl font-semibold">HEI Access</h1>
          <p class="text-sm text-slate-500">Sign in to manage your institution data</p>
        </div>
      </div>

      <form method="post" action="/PRISM/php/hei_login.php" class="mt-6 space-y-4">
        <?php if ($error): ?>
          <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="space-y-1">
          <label class="text-sm">Email</label>
          <input name="email" value="<?php echo isset($_POST['email'])?htmlspecialchars($_POST['email']):'';?>" class="w-full border px-3 py-2 rounded" required />
        </div>

        <div class="space-y-1">
          <label class="text-sm">Password</label>
          <input name="password" type="password" class="w-full border px-3 py-2 rounded" required />
        </div>

        <button name="login" type="submit" class="w-full text-white px-4 py-2 rounded" style="background: linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%); box-shadow: 0 2px 8px rgba(0,56,168,0.3);">Sign In</button>

      </form>
    </div>
  </div>

  <script> if (window.lucide) lucide.createIcons(); </script>
  <?php echo $sweetAlertConfig; ?>
</body>
</html>
