<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

// Check for existing session
if (isset($_SESSION['chedID']) || isset($_SESSION['heiID'])) {
    // Redirect based on user role
    if (isset($_SESSION['chedID'])) {
        header("Location: /PRISM/CHED/ched-dashboard.php");
        exit();
    } else {
        header("Location: /PRISM/HEI/hei-dashboard.php");
        exit();
    }
}

// Database connection
require_once('../classes/database.php');

// Instance of the database class
$con = new database();

// Alert Initialization
$sweetAlertConfig = "";

// Login Form Submission
if(isset($_POST['login'])) {

    $id = $_POST['id'];
    $password = $_POST['password'];

    // Validate credentials
    $user = $con->loginCHEDUser($id, $password);

    if ($user) {

        // Set session variables based on role
        if ($user['ched_role'] === 'CHED') {
            $_SESSION['chedID'] = $user['ched_id'];
            $_SESSION['chedName'] = $user['ched_last_name'] . ', ' . $user['ched_first_name'];
            $_SESSION['chedRole'] = $user['ched_role'];

        $sweetAlertConfig = "
        <script>

        Swal.fire({
            icon: 'success',
            title: 'Login Successful',
            text: 'Welcome, " . htmlspecialchars($_SESSION['chedName']) . "!',
            confirmButtonText: 'Continue'
        }).then(() => {
            window.location.href = '/PRISM/CHED/ched-dashboard.php';
        });

        </script>";
        }

    } else {
        
        $sweetAlertConfig = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: 'Invalid username or password.'
        });
        </script>
        ";

    }
} else {
    $error = '';
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CHED Login</title>
  
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>
        
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root{--ph-blue:#0038A8;--ph-blue-light:#3366FF}
  </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8 relative" style="background: linear-gradient(135deg, #e6eef9 0%, #fef9e6 100%);">
  <div class="w-full max-w-md relative z-10 shadow-xl border-t-4 bg-white rounded" style="border-top-color: var(--ph-blue);">
    <div class="p-6">
      <div class="space-y-4 text-center">
        <div class="flex justify-center">
          <div class="p-3 rounded-full" style="background: linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%); box-shadow: 0 4px 12px rgba(0, 56, 168, 0.3);">
            <img src="../assets/CHED_logo.png" alt="CHED Logo" class="h-12 w-12 object-contain" />
          </div>
        </div>
        <div>
          <h1 class="text-xl font-semibold">CHED PRISM</h1>
          <p class="text-sm text-slate-500">Login to access the <span style="color: black; font-weight: 600;">P</span>ortal for <span style="color: black; font-weight: 600;">R</span>esearch, <span style="color: black; font-weight: 600;">I</span>nsights and <span style="color: black; font-weight: 600;">S</span>ubmission Management</p>
        </div>
      </div>

      <form method="POST" action="" class="mt-6 space-y-4">

        <!-- ID input field -->
        <div class="space-y-1">
          <label class="text-sm">PRISM ID</label>
          <input name="id" type="text" id="id" placeholder="Enter your PRISM ID" class="form-control w-full border px-3 py-2 rounded" required />
        </div>

        <div class="space-y-1">
          <label class="text-sm">Password</label>
          <input name="password" type="password" id="password" placeholder="Enter your password" class="form-control w-full border px-3 py-2 rounded" required />
        </div>

        <button name="login" type="submit" class="w-full text-white px-4 py-2 rounded" style="background: linear-gradient(135deg, var(--ph-blue) 0%, var(--ph-blue-light) 100%); box-shadow: 0 2px 8px rgba(0,56,168,0.3);">Sign In</button>

      </form>
    </div>
  </div>

  <?php echo $sweetAlertConfig; ?>

</body>
</html>