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
    $chedUserID = $_SESSION['chedID'];
    $title = $_POST['title'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $dueDate = $_POST['due'];
    $description = $_POST['desc'];
    
    // Determine if this is single or batch mode
    $isBatchMode = isset($_POST['batch_mode']) && $_POST['batch_mode'] === '1';
    
    // Get HEI IDs
    $heiIds = [];
    if ($isBatchMode) {
        // Batch mode: get multiple HEI IDs from checkboxes
        $heiIds = isset($_POST['hei_ids']) && is_array($_POST['hei_ids']) ? array_map('intval', $_POST['hei_ids']) : [];
        if (empty($heiIds)) {
            $sweetAlertConfig = "
            <script>
            Swal.fire({
              icon: 'error',
              title: 'No HEIs Selected',
              text: 'Please select at least one institution to create tickets for.'
            });
            </script>";
            goto skip_processing;
        }
    } else {
        // Single mode: get single HEI ID from dropdown
        $heiId = isset($_POST['hei']) ? (int)$_POST['hei'] : 0;
        if (!$heiId) {
            $sweetAlertConfig = "
            <script>
            Swal.fire({
              icon: 'error',
              title: 'No Institution Selected',
              text: 'Please select an institution.'
            });
            </script>";
            goto skip_processing;
        }
        $heiIds = [$heiId];
    } 
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
      // Create tickets (single or batch)
      if (count($heiIds) === 1) {
        // Single ticket creation
        $ticketID = $con->createTicket($heiIds[0], $chedUserID, $title, $category, $priority, $dueDate, $description);
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
      } else {
        // Batch ticket creation
        $result = $con->createTicketBatch($heiIds, $chedUserID, $title, $category, $priority, $dueDate, $description);
        
        if ($result['success'] > 0) {
          // Attach templates to all created tickets
          $attachmentErrors = 0;
          foreach ($result['ticket_ids'] as $tId) {
            $ok = $con->setTemplatesForTicket($tId, $templateIds, $chedUserID);
            if (!$ok) $attachmentErrors++;
          }
          
          $totalHEIs = count($heiIds);
          $message = "Successfully created {$result['success']} ticket(s) for {$result['success']} HEI(s).";
          if ($result['failed'] > 0) {
            $message .= " Failed to create {$result['failed']} ticket(s).";
          }
          if ($attachmentErrors > 0) {
            $message .= " Warning: {$attachmentErrors} ticket(s) created but template attachment failed.";
          }
          
          $sweetAlertConfig = "
          <script>
          Swal.fire({
            icon: '" . ($result['failed'] > 0 || $attachmentErrors > 0 ? 'warning' : 'success') . "',
            title: 'Batch Ticket Creation',
            html: '" . addslashes($message) . "',
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
            title: 'Batch Creation Failed',
            text: 'Failed to create any tickets. Please try again.'
          });
          </script>";
        }
      }
    }
  }
  
  skip_processing:
    

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
          // fetch all HEIs with additional profile data for filtering
          $allHeis = $con->getHEIs();
          // Get unique regions and municipalities for filter dropdowns
          $conn = $con->opencon();
          $regionsStmt = $conn->query("SELECT DISTINCT ipd.inst_region, nr.region_number, nr.region_division FROM institutional_profile_data ipd LEFT JOIN national_regions nr ON ipd.inst_region = nr.region_ID WHERE ipd.inst_region IS NOT NULL ORDER BY nr.region_number");
          $regions = $regionsStmt->fetchAll(PDO::FETCH_ASSOC);
          $municipalitiesStmt = $conn->query("SELECT DISTINCT inst_municipality_city FROM institutional_profile_data WHERE inst_municipality_city IS NOT NULL AND inst_municipality_city != '' ORDER BY inst_municipality_city");
          $municipalities = $municipalitiesStmt->fetchAll(PDO::FETCH_COLUMN);
        ?>
        <form  method="post" action="" id="ticketForm" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">New Ticket</h2>
            <p class="text-sm text-slate-500">Set organization, details, and due date</p>
          </div>
          <div class="p-5 grid md:grid-cols-2 gap-4">
            <!-- Batch Mode Toggle -->
            <div class="md:col-span-2">
              <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-lg border">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" name="selection_mode" value="single" checked class="w-4 h-4" onchange="toggleSelectionMode()" />
                  <span class="text-sm font-medium">Single Institution</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" name="selection_mode" value="batch" class="w-4 h-4" onchange="toggleSelectionMode()" />
                  <span class="text-sm font-medium">Multiple Institutions (Batch)</span>
                </label>
              </div>
              <input type="hidden" name="batch_mode" id="batch_mode" value="0" />
            </div>

            <!-- Single Institution Selector (Default) -->
            <div class="md:col-span-2" id="singleSelector">
              <label for="hei" class="block text-sm font-medium mb-1">Institution</label>
              <select name="hei" id="hei" class="w-full px-3 py-2 rounded border" aria-label="Institution">
                <option value="" disabled selected>Select Institution</option>
                <?php
                foreach ($allHeis as $hei) {
                    echo '<option value="' . htmlspecialchars($hei['id']) . '">' . htmlspecialchars($hei['name']) . '</option>';
                }
                ?>
              </select>
            </div>

            <!-- Batch Institution Selector (Hidden by default) -->
            <div class="md:col-span-2 hidden" id="batchSelector">
              <label class="block text-sm font-medium mb-2">Select Multiple Institutions</label>
              
              <!-- Filter Controls -->
              <div class="mb-3 p-3 bg-slate-50 rounded border">
                <div class="text-xs font-medium text-slate-600 mb-2">Quick Filters:</div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                  <div>
                    <label class="text-xs text-slate-500">Region:</label>
                    <select id="filterRegion" class="w-full px-2 py-1 text-sm rounded border" onchange="applyFilters()">
                      <option value="">All Regions</option>
                      <?php foreach ($regions as $region): ?>
                        <option value="<?php echo htmlspecialchars($region['inst_region']); ?>">Region <?php echo htmlspecialchars($region['region_number']); ?><?php echo htmlspecialchars($region['region_division']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div>
                    <label class="text-xs text-slate-500">Municipality/City:</label>
                    <select id="filterMunicipality" class="w-full px-2 py-1 text-sm rounded border" onchange="applyFilters()">
                      <option value="">All Municipalities</option>
                      <?php foreach ($municipalities as $muni): ?>
                        <option value="<?php echo htmlspecialchars($muni); ?>"><?php echo htmlspecialchars($muni); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="flex items-end gap-2">
                    <button type="button" onclick="selectAllFiltered()" class="flex-1 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded border border-blue-300 hover:bg-blue-200">Select All</button>
                    <button type="button" onclick="deselectAll()" class="flex-1 px-2 py-1 text-xs bg-slate-100 text-slate-700 rounded border hover:bg-slate-200">Clear All</button>
                  </div>
                </div>
              </div>

              <!-- HEI Checkboxes -->
              <div class="max-h-64 overflow-y-auto border rounded p-3">
                <div class="text-xs text-slate-500 mb-2"><span id="selectedCount">0</span> institution(s) selected</div>
                <div id="heiCheckboxList" class="grid grid-cols-1 gap-1">
                  <?php
                  // Fetch HEIs with region and municipality data
                  $heiStmt = $conn->query("SELECT ipd.hei_ID, ipd.inst_name, ipd.inst_region, ipd.inst_municipality_city, nr.region_number, nr.region_division FROM institutional_profile_data ipd LEFT JOIN national_regions nr ON ipd.inst_region = nr.region_ID ORDER BY ipd.inst_name");
                  $heisWithData = $heiStmt->fetchAll(PDO::FETCH_ASSOC);
                  
                  foreach ($heisWithData as $hei):
                  ?>
                    <label class="hei-checkbox-item flex items-start gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer" 
                           data-region="<?php echo htmlspecialchars($hei['inst_region'] ?? ''); ?>" 
                           data-municipality="<?php echo htmlspecialchars($hei['inst_municipality_city'] ?? ''); ?>">
                      <input type="checkbox" name="hei_ids[]" value="<?php echo (int)$hei['hei_ID']; ?>" class="mt-0.5" onchange="updateSelectedCount()" />
                      <div class="flex-1">
                        <div class="text-sm font-medium"><?php echo htmlspecialchars($hei['inst_name']); ?></div>
                        <div class="text-xs text-slate-500">
                          <?php if ($hei['region_number']): ?>
                            Region <?php echo htmlspecialchars($hei['region_number']); ?><?php echo htmlspecialchars($hei['region_division']); ?>
                          <?php endif; ?>
                          <?php if ($hei['inst_municipality_city']): ?>
                            • <?php echo htmlspecialchars($hei['inst_municipality_city']); ?>
                          <?php endif; ?>
                        </div>
                      </div>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
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

    // Toggle between single and batch selection mode
    function toggleSelectionMode() {
      var mode = document.querySelector('input[name="selection_mode"]:checked').value;
      var singleSelector = document.getElementById('singleSelector');
      var batchSelector = document.getElementById('batchSelector');
      var batchModeInput = document.getElementById('batch_mode');
      var heiSelect = document.getElementById('hei');
      
      if (mode === 'batch') {
        singleSelector.classList.add('hidden');
        batchSelector.classList.remove('hidden');
        batchModeInput.value = '1';
        heiSelect.removeAttribute('required');
      } else {
        singleSelector.classList.remove('hidden');
        batchSelector.classList.add('hidden');
        batchModeInput.value = '0';
        heiSelect.setAttribute('required', 'required');
      }
    }

    // Apply filters to HEI checkbox list
    function applyFilters() {
      var regionFilter = document.getElementById('filterRegion').value;
      var municipalityFilter = document.getElementById('filterMunicipality').value;
      var items = document.querySelectorAll('.hei-checkbox-item');
      
      items.forEach(function(item) {
        var region = item.getAttribute('data-region');
        var municipality = item.getAttribute('data-municipality');
        var show = true;
        
        if (regionFilter && region !== regionFilter) show = false;
        if (municipalityFilter && municipality !== municipalityFilter) show = false;
        
        item.style.display = show ? '' : 'none';
      });
      
      updateSelectedCount();
    }

    // Select all filtered (visible) HEIs
    function selectAllFiltered() {
      var items = document.querySelectorAll('.hei-checkbox-item');
      items.forEach(function(item) {
        if (item.style.display !== 'none') {
          var checkbox = item.querySelector('input[type="checkbox"]');
          checkbox.checked = true;
        }
      });
      updateSelectedCount();
    }

    // Deselect all HEIs
    function deselectAll() {
      var checkboxes = document.querySelectorAll('.hei-checkbox-item input[type="checkbox"]');
      checkboxes.forEach(function(cb) {
        cb.checked = false;
      });
      updateSelectedCount();
    }

    // Update selected count display
    function updateSelectedCount() {
      var checkboxes = document.querySelectorAll('.hei-checkbox-item input[type="checkbox"]:checked');
      var count = checkboxes.length;
      var countDisplay = document.getElementById('selectedCount');
      if (countDisplay) {
        countDisplay.textContent = count;
      }
    }

    // Form validation for batch mode
    document.getElementById('ticketForm').addEventListener('submit', function(e) {
      var mode = document.querySelector('input[name="selection_mode"]:checked').value;
      if (mode === 'batch') {
        var checkedBoxes = document.querySelectorAll('.hei-checkbox-item input[type="checkbox"]:checked');
        if (checkedBoxes.length === 0) {
          e.preventDefault();
          Swal.fire({
            icon: 'warning',
            title: 'No Institutions Selected',
            text: 'Please select at least one institution to create tickets for.'
          });
          return false;
        }
      }
    });
  </script>

  <?php echo $sweetAlertConfig; ?>

</body>
</html>
