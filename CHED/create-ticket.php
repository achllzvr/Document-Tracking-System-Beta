<?php

// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// CHED protector
require_once __DIR__ . '/../includes/ched_protect.php';

// Instance of database connection
require_once('../includes/database_conn.php');

// SweetAlert Initialization
$sweetAlertConfig = "";

// Form submission handling
if (isset($_POST['create_ticket'])) {
    $heiId = $_POST['hei'];
    $chedUserID = $_SESSION['chedID'];
    $title = $_POST['title'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $dueDate = $_POST['due'];
    $description = $_POST['desc']; 

    // Insert ticket into database
    $con->createTicket($heiId, $chedUserID, $title, $category, $priority, $dueDate, $description);

    // Check if insertion was successful
    if ($con) {
        $sweetAlertConfig = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Ticket Created',
            text: 'The ticket has been successfully created.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = './view-tickets.php';
        });
        </script>";
    } else {
        $sweetAlertConfig = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Creation Failed',
            text: 'There was an error creating the ticket. Please try again.'
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
  <title>Create Ticket — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>
  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-3xl mx-auto">
        <form  method="post" action="" id="ticketForm" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">New Ticket</h2>
            <p class="text-sm text-slate-500">Set organization, details, and due date</p>
          </div>
          <div class="p-5 grid md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label for="hei" class="block text-sm font-medium mb-1">Institution</label>
              <select name="hei" id="hei" required class="w-full px-3 py-2 rounded border" aria-label="Institution">

              <!-- Default option -->
              <option value="" disabled selected>Select Institution</option>

              <!-- HEI Fetch from database  -->
              <?php
              $heis = $con->getHEIs();
              foreach ($heis as $hei) {
                  echo '<option value="' . htmlspecialchars($hei['id']) . '">' . htmlspecialchars($hei['name']) . '</option>';
              }
              ?>

              </select>
            </div>
            <div class="md:col-span-2">
              <label for="title" class="block text-sm font-medium mb-1">Title</label>
              <input name="title" id="title" required class="w-full px-3 py-2 rounded border" placeholder="e.g., Q1 2026 Enrollment Data Submission" />
            </div>
            <div>
              <label for="category" class="block text-sm font-medium mb-1">Category</label>
              <select name="category" id="category" required class="w-full px-3 py-2 rounded border">
                <option value="Enrollment">Enrollment</option>
                <option value="Faculty">Faculty</option>
                <option value="Graduates">Graduates</option>
                <option value="Institutional Profile">Institutional Profile</option>
              </select>
            </div>
            <div>
              <label for="priority" class="block text-sm font-medium mb-1">Priority</label>
              <select name="priority" id="priority" required class="w-full px-3 py-2 rounded border">
                <option>Urgent</option>
                <option>High</option>
                <option selected>Medium</option>
                <option>Low</option>
              </select>
            </div>
            <div>
              <label for="due" class="block text-sm font-medium mb-1">Due Date</label>
              <input name="due" id="due" type="date" class="w-full px-3 py-2 rounded border" />
            </div>
            <div class="md:col-span-2">
              <label for="desc" class="block text-sm font-medium mb-1">Description</label>
              <textarea name="desc" id="desc" rows="4" class="w-full px-3 py-2 rounded border" placeholder="Describe the task, template to use, and any notes..."></textarea>
            </div>
          </div>
          <div class="px-5 py-4 border-t flex items-center justify-between bg-slate-50">
            <a href="./view-tickets.php" class="text-sm text-slate-600 hover:underline">Cancel</a>
            <button name="create_ticket" type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-blue-600 text-white">
              <i data-lucide="save" class="h-4 w-4"></i>
              Create Ticket
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <?php echo $sweetAlertConfig; ?>

</body>
</html>
