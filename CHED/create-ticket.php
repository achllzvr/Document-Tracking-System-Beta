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
  // Validate templates selection: require at least one active template attached
  $templateIds = isset($_POST['template_ids']) && is_array($_POST['template_ids']) ? array_values(array_map('intval', $_POST['template_ids'])) : [];

  if (empty($templateIds)) {
    // client didn't attach templates - show error
    $sweetAlertConfig = "
    <script>
    Swal.fire({
      icon: 'error',
      title: 'No Template Selected',
      text: 'Please attach at least one active template to the ticket before creating it.'
    });
    </script>";
  } else {
    // Verify templates are active and match category
    $allowed = $con->getTemplates(['status' => 'active', 'category' => $category]);
    $allowedIds = array_map(function($r){ return (int)$r['template_ID']; }, $allowed ?: []);
    $diff = array_values(array_diff($templateIds, $allowedIds));
    if (!empty($diff)) {
      $sweetAlertConfig = "
      <script>
      Swal.fire({
        icon: 'error',
        title: 'Invalid Template Selection',
        text: 'One or more selected templates are invalid for the chosen category. Please select active templates that match the category.'
      });
      </script>";
    } else {
      // Insert ticket into database
      $ticketID = $con->createTicket($heiId, $chedUserID, $title, $category, $priority, $dueDate, $description);
      if ($ticketID) {
        // attach templates
        $ok = $con->setTemplatesForTicket($ticketID, $templateIds, $chedUserID);
        if ($ok) {
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
            title: 'Attachment Failed',
            text: 'Ticket created but failed to attach templates. Please edit the ticket to attach templates.'
          }).then(() => {
            window.location.href = './view-tickets.php';
          });
          </script>";
        }
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
        <?php
          // fetch active templates for rendering the selector
          $activeTemplates = $con->getTemplates(['status' => 'active']);
        ?>
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

            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Attach Template(s) <span class="text-xs text-slate-400">(at least 1 required)</span></label>
              <div id="templateList" class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-auto border rounded p-2">
                <?php if (empty($activeTemplates)): ?>
                  <div class="text-sm text-slate-500">No active templates available. Please ask CHED admin to upload templates.</div>
                <?php else: ?>
                  <?php foreach ($activeTemplates as $t): ?>
                    <label class="inline-flex items-center gap-2 px-2 py-1 border rounded" data-category="<?php echo htmlspecialchars($t['template_category']); ?>">
                      <input type="checkbox" name="template_ids[]" value="<?php echo (int)$t['template_ID']; ?>" />
                      <div>
                        <div class="text-sm font-medium"><?php echo htmlspecialchars($t['template_name']); ?></div>
                        <div class="text-xs text-slate-500">v<?php echo htmlspecialchars($t['template_version'] ?? ''); ?> • <?php echo htmlspecialchars($t['template_category']); ?></div>
                      </div>
                    </label>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
              <p class="text-xs text-slate-400 mt-1">Templates are filtered by category when you change the Category field.</p>
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

  <script>
    // filter templates by selected category
    (function(){
      function filter(){
        var cat = document.getElementById('category').value;
        var nodes = document.querySelectorAll('#templateList [data-category]');
        nodes.forEach(function(n){
          if (!cat) { n.style.display = ''; return; }
          if (n.getAttribute('data-category') === cat) n.style.display = '';
          else n.style.display = 'none';
        });
      }
      var sel = document.getElementById('category');
      if (sel){ sel.addEventListener('change', filter); window.addEventListener('load', filter); }
    })();
  </script>

  <?php echo $sweetAlertConfig; ?>

</body>
</html>
