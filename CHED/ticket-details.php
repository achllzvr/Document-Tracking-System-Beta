<?php

// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// CHED protector
require_once __DIR__ . '/../includes/ched_protect.php';

// Database
require_once __DIR__ . '/../classes/database.php';
$db = new database();

// Ticket id
$ticketId = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;

// Handle status change or comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // status update from CHED
  if (isset($_POST['status'])) {
    $newStatus = trim($_POST['status']);
    $chedId = $_SESSION['chedID'] ?? null;
    
    // Get current ticket status to check for state transitions
    $currentTicket = $db->getTicketById($ticketId);
    $currentStatus = $currentTicket['ticket_status'] ?? '';
    
    // Special handling: Reopening from "For Review" to "In Progress" deletes records
    if ($newStatus === 'In Progress' && $currentStatus === 'For Review') {
      $hasRecords = $db->checkTicketHasRecords($ticketId);
      if ($hasRecords) {
        // Reopen ticket and delete records
        $result = $db->reopenTicketAndDeleteRecords($ticketId);
        if ($result['success']) {
          $_SESSION['ticket_reopened'] = true;
          $_SESSION['records_deleted'] = $result['deleted'];
        }
      } else {
        // No records to delete, just update status
        $db->changeTicketStatus($ticketId, $newStatus, $chedId);
      }
    } else {
      // Normal status change (no record deletion)
      $db->changeTicketStatus($ticketId, $newStatus, $chedId);
    }
    
    header('Location: ticket-details.php?ticket_id=' . $ticketId);
    exit;
  }

  // comment submission
  if (isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if ($comment !== '') {
      $chedId = $_SESSION['chedID'] ?? null;
      $db->addCommentToTicket($ticketId, 'ched', $chedId, $comment);
    }
    header('Location: ticket-details.php?ticket_id=' . $ticketId);
    exit;
  }

  // update attached templates (CHED)
  if (isset($_POST['update_templates'])) {
    $templateIds = isset($_POST['template_ids']) && is_array($_POST['template_ids']) ? array_values(array_map('intval', $_POST['template_ids'])) : [];
    // validate templates are active
    $allActive = $db->getTemplates(['status' => 'active']);
    $allowed = array_map(function($r){ return (int)$r['template_ID']; }, $allActive ?: []);
    $diff = array_values(array_diff($templateIds, $allowed));
    if (!empty($diff)) {
      // invalid selection - ignore and redirect back
      header('Location: ticket-details.php?ticket_id=' . $ticketId);
      exit;
    }

    // require at least one attached active template
    if (empty($templateIds)) {
      // don't accept empty list; redirect back (front-end will enforce too)
      header('Location: ticket-details.php?ticket_id=' . $ticketId);
      exit;
    }

    $db->setTemplatesForTicket($ticketId, $templateIds, $_SESSION['chedID'] ?? null);
    header('Location: ticket-details.php?ticket_id=' . $ticketId);
    exit;
  }
}

// Fetch ticket and comments
$ticket = $ticketId ? $db->getTicketById($ticketId) : null;
$comments = $ticketId ? $db->getCommentsForTicket($ticketId) : [];
// fetch attached templates and active templates for editing
$attachedTemplates = $ticketId ? $db->getTemplatesForTicket($ticketId) : [];
$activeTemplates = $db->getTemplates(['status' => 'active']);

// Check for missed due date
$dueDateInfo = $ticketId ? $db->checkAndUpdateMissedDueDate($ticketId) : ['is_missed' => false, 'has_records' => false, 'status_updated' => false];
// Refresh ticket if status was updated
if ($dueDateInfo['status_updated']) {
    $ticket = $db->getTicketById($ticketId);
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ticket Details — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>
  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-y-auto">
      <!-- Back button (mirror HEI layout) -->
      <div style="max-width: 64rem; margin: 0 auto; position: relative;">
        <a href="/PRISM/CHED/view-tickets.php" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium absolute left-0 top-0 mt-4 ml-2 shadow-sm" style="z-index:10;">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
        </a>
      </div>

      <div class="max-w-3xl mx-auto space-y-6">
        <section id="ticketHeader" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <?php if (! $ticket): ?>
            <div class="p-6">Ticket not found.</div>
          <?php else: ?>
            <div class="px-5 pt-5 pb-3 border-b">
              <h2 class="font-semibold"><?php echo htmlspecialchars($ticket['ticket_title'] ?? 'Untitled'); ?></h2>
              <p class="text-sm text-slate-500"><?php echo htmlspecialchars($ticket['hei_name'] ?? ''); ?></p>
            </div>
            <div class="p-5 grid md:grid-cols-2 gap-4 text-sm">
              <p><span class="text-slate-500">Category:</span> <?php echo htmlspecialchars($ticket['ticket_category'] ?? '-'); ?></p>
              <p><span class="text-slate-500">Priority:</span> 
                <?php 
                  $priority = strtolower($ticket['ticket_priority'] ?? 'default');
                  $priorityClass = 'prism-badge prism-badge-status-' . preg_replace('/\s+/', '', $priority);
                ?>
                <span class="<?php echo $priorityClass; ?>">
                  <?php echo htmlspecialchars($ticket['ticket_priority'] ?? '-'); ?>
                </span>
              </p>
              <div class="flex items-center gap-2">
                <p><span class="text-slate-500">Status:</span>
                <form method="post" class="inline-block align-middle">
                  <?php 
                    $status = strtolower($ticket['ticket_status'] ?? 'default');
                    $statusClass = 'prism-badge prism-badge-status-' . preg_replace('/\s+/', '', $status);
                  ?>
                  <select name="status" id="statusSelect" class="px-2 py-1 rounded border">
                    <option value="Open" <?php echo (($ticket['ticket_status'] ?? '') === 'Open') ? 'selected' : ''; ?>>Open</option>
                    <option value="In Progress" <?php echo (($ticket['ticket_status'] ?? '') === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                    <option value="For Review" <?php echo (($ticket['ticket_status'] ?? '') === 'For Review') ? 'selected' : ''; ?>>For Review</option>
                    <option value="Closed" <?php echo (($ticket['ticket_status'] ?? '') === 'Closed') ? 'selected' : ''; ?>>Closed</option>
                  </select>
                </form>
              </p>
              </div>
              <p><span class="text-slate-500">Due:</span> 
                <?php echo !empty($ticket['ticket_due_date']) ? htmlspecialchars(date('F j, Y', strtotime($ticket['ticket_due_date']))) : '-'; ?>
                <?php if ($dueDateInfo['is_missed']): ?>
                  <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                    <i data-lucide="alert-triangle" class="h-3 w-3"></i>
                    Missed Deadline
                  </span>
                <?php endif; ?>
              </p>
            </div>
            <div class="px-5 pb-5">
              <p><span class="text-slate-500 text-sm">Description:</span>
              <p class="text-sm"><?php echo nl2br(htmlspecialchars($ticket['ticket_description'] ?? '')); ?></p>
            </div>
            <div class="px-5 pb-5">
              <div class="flex items-center gap-3 mb-3">
                <a class="inline-flex items-center gap-2 px-3 py-2 rounded border hover:bg-slate-50" href="/PRISM/tickets/download-template.php?ticket_id=<?php echo (int)$ticketId; ?>" id="downloadBtn"><i data-lucide="download" class="h-4 w-4"></i> Download Template</a>
                <button class="inline-flex items-center gap-2 px-3 py-2 rounded bg-blue-600 text-white" id="uploadBtn" data-ticket-id="<?php echo (int)$ticketId; ?>"><i data-lucide="upload" class="h-4 w-4"></i> Upload Completed</button>
                <button id="editAttachmentsBtn" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded border hover:bg-slate-50"><i data-lucide="edit-2" class="h-4 w-4"></i> Edit Attachments</button>
              </div>

              <div class="text-sm">
                <div class="text-slate-500 text-xs">Attached Templates</div>
                <?php if (empty($attachedTemplates)): ?>
                  <div class="mt-2 text-sm text-amber-600">No templates attached.</div>
                <?php else: ?>
                  <div class="mt-2 flex flex-wrap gap-2">
                    <?php foreach ($attachedTemplates as $at): ?>
                      <span class="prism-badge prism-badge-status-open"><?php echo htmlspecialchars($at['template_name']); ?> <?php echo $at['template_version'] ? ('v'.htmlspecialchars($at['template_version'])) : ''; ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </section>

        <section class="grid md:grid-cols-1 gap-6">
          <!-- Edit Attachments Modal -->
          <div id="attachmentsModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
            <div class="bg-white rounded-xl w-full max-w-2xl p-4">
              <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold">Edit Attached Templates</h3>
                <button id="closeAttachments" class="text-slate-500">Close</button>
              </div>
              <form method="post">
                <input type="hidden" name="update_templates" value="1" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-64 overflow-auto border rounded p-2">
                  <?php if (empty($activeTemplates)): ?>
                    <div class="text-sm text-slate-500">No active templates available.</div>
                  <?php else: ?>
                    <?php foreach ($activeTemplates as $t): ?>
                      <?php $checked = in_array((int)$t['template_ID'], array_map(function($r){ return (int)$r['template_ID']; }, $attachedTemplates ?: [])); ?>
                      <label class="inline-flex items-center gap-2 px-2 py-1 border rounded">
                        <input type="checkbox" name="template_ids[]" value="<?php echo (int)$t['template_ID']; ?>" <?php echo $checked ? 'checked' : ''; ?> />
                        <div>
                          <div class="text-sm font-medium"><?php echo htmlspecialchars($t['template_name']); ?></div>
                          <div class="text-xs text-slate-500">v<?php echo htmlspecialchars($t['template_version'] ?? ''); ?> • <?php echo htmlspecialchars($t['template_category']); ?></div>
                        </div>
                      </label>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                  <button type="button" id="cancelAttachments" class="px-3 py-2 rounded border">Cancel</button>
                  <button type="submit" class="px-3 py-2 rounded bg-emerald-600 text-white">Save</button>
                </div>
              </form>
            </div>
          </div>
          <!-- comments section moved below the enrollment summary -->
        </section>

        <?php
          // Fetch any enrollment rows linked to this ticket (if present) — mirror HEI behavior
          $enrollmentRows = $ticketId ? $db->getEnrollmentRowsByTicket($ticketId) : [];

          // Prepare aggregated data for charts
          $programTotals = [];
          $yearSex = []; // [year_level][sex] => count
          foreach ($enrollmentRows as $er){
            $prog = $er['enr_program'] ?? 'Unknown';
            $maj = $er['enr_program_major'] ?? '';
            $label = $prog . ($maj ? ' — ' . $maj : '');
            $cnt = (int)($er['enr_total_count'] ?? 0);
            if (!isset($programTotals[$label])) $programTotals[$label] = 0;
            $programTotals[$label] += $cnt;

            $lvl = $er['enr_year_level'] ?? '';
            $sex = strtolower($er['enr_sex'] ?? 'm') === 'f' ? 'female' : 'male';
            if (!isset($yearSex[$lvl])) $yearSex[$lvl] = ['male'=>0,'female'=>0];
            $yearSex[$lvl][$sex] += $cnt;
          }
        ?>

        <?php if (!empty($enrollmentRows)): ?>
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <div>
              <h3 class="font-semibold">Imported Enrollment Summary</h3>
              <p class="text-sm text-slate-500">Visual summary of imported records for this ticket</p>
            </div>
            <div>
              <a class="px-3 py-2 rounded bg-slate-100 border text-sm" href="/PRISM/tickets/enrollment-details.php?ticket_id=<?php echo (int)$ticketId; ?>">View detailed records</a>
            </div>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <canvas id="chedProgTotalsChart" height="200"></canvas>
            </div>
            <div>
              <canvas id="chedYearSexChart" height="200"></canvas>
            </div>
          </div>
        </section>
        <?php
          // Precompute arrays for Chart.js
          $ched_prog_labels = array_keys($programTotals);
          $ched_prog_data = array_values($programTotals);
          // Normalize year keys: remove empty keys and sort numerically
          $ched_years = array_keys($yearSex);
          $ched_years = array_values(array_filter($ched_years, function($v){ return $v !== '' && $v !== null; }));
          usort($ched_years, function($a,$b){ return intval($a) <=> intval($b); });
          $ched_male = [];
          $ched_female = [];
          foreach ($ched_years as $y){
            $ched_male[] = (int)(($yearSex[$y]['male'] ?? 0));
            $ched_female[] = (int)(($yearSex[$y]['female'] ?? 0));
          }
        ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
          (function(){
            var progLabels = <?php echo json_encode($ched_prog_labels); ?>;
            var progData = <?php echo json_encode($ched_prog_data); ?>;
            var ctx1 = document.getElementById('chedProgTotalsChart').getContext('2d');
            if (typeof Chart !== 'undefined'){
              new Chart(ctx1, {
                type: 'bar',
                data: { labels: progLabels, datasets: [{ label: 'Total students', data: progData, backgroundColor: '#2563eb' }] },
                options: { responsive:true, plugins:{legend:{display:false}}, scales:{x:{ticks:{maxRotation:45,minRotation:0}}} }
              });
            } else {
              console.warn('Chart.js not available — progTotalsChart not rendered');
              ctx1.canvas.parentNode.innerHTML = '<div class="text-sm text-slate-500">Chart unavailable (Chart.js not loaded)</div>';
            }

            var years = <?php echo json_encode($ched_years); ?>;
            var maleData = <?php echo json_encode($ched_male); ?>;
            var femaleData = <?php echo json_encode($ched_female); ?>;
            var ctx2 = document.getElementById('chedYearSexChart').getContext('2d');
            if (typeof Chart !== 'undefined'){
              new Chart(ctx2, {
                type: 'bar',
                data: { labels: years, datasets:[{label:'Male', data: maleData, backgroundColor:'#0ea5e9'},{label:'Female', data:femaleData, backgroundColor:'#fb7185'}] },
                options:{responsive:true, plugins:{title:{display:true,text:'By year level and sex'}}, scales:{x:{stacked:true}, y:{stacked:false}} }
              });
            } else {
              console.warn('Chart.js not available — yearSexChart not rendered');
              ctx2.canvas.parentNode.innerHTML = '<div class="text-sm text-slate-500">Chart unavailable (Chart.js not loaded)</div>';
            }
          })();
        </script>
        <?php endif; ?>

        <!-- Reinsert comments section here so imported summary appears above comments -->
        <section class="grid md:grid-cols-1 gap-6">
          <div id="comments" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 pt-5 pb-3 border-b">
              <h3 class="font-semibold">Comments</h3>
              <p class="text-sm text-slate-500">Discuss ticket progress</p>
            </div>
            <div id="commentList">
              <?php if (empty($comments)): ?>
                <div class="p-5 text-sm text-slate-500">No comments yet.</div>
              <?php else: ?>
                <?php foreach ($comments as $c): ?>
                  <div class="p-3 border-b">
                    <div class="flex items-start gap-3">
                      <div class="px-2">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs"><?php echo strtoupper(substr($c['user_name'] ?? 'U',0,1)); ?></div>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm"><span class="font-bold"><?php echo htmlspecialchars($c['user_name'] ?? ($c['user_type'].'#'.$c['user_ID'])); ?></span>: <?php echo nl2br(htmlspecialchars($c['comment'])); ?></p>
                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars(date('F j, Y g:ia', strtotime($c['created_at']))); ?></p>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <form id="commentForm" method="post" class="p-4 border-t grid grid-cols-[1fr_auto] gap-3">
              <input name="comment" id="commentInput" required class="px-3 py-2 rounded border" placeholder="Write a comment..." aria-label="Add comment" />
              <button class="px-3 py-2 rounded bg-blue-600 text-white">Post</button>
            </form>
          </div>
        </section>
      </div>
    </main>

    <script>if (window.lucide) lucide.createIcons();</script>
    <script>
      (function(){
        var modal = document.getElementById('attachmentsModal');
        var openBtn = document.getElementById('editAttachmentsBtn');
        var closeBtn = document.getElementById('closeAttachments');
        var cancelBtn = document.getElementById('cancelAttachments');
        if (openBtn && modal){
          openBtn.addEventListener('click', function(){ modal.classList.remove('hidden'); modal.classList.add('flex'); });
        }
        [closeBtn, cancelBtn].forEach(function(b){ if (b) b.addEventListener('click', function(){ modal.classList.add('hidden'); modal.classList.remove('flex'); }); });
      })();
    </script>

    <!-- Upload modal (mirror HEI) -->
    <div id="uploadModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:60;align-items:center;justify-content:center">
      <div style="background:white;max-width:720px;margin:40px auto;border-radius:8px;padding:18px;">
        <h3 style="margin:0 0 8px 0;font-size:18px">Upload Completed Template</h3>
        <p style="margin:0 0 12px 0;color:#666">Choose the completed Excel file. Parsing will start automatically and you'll be redirected to a review page.</p>
        <input type="file" id="completedFileInput" accept=".xls,.xlsx,.csv" />
        <div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end">
          <button id="cancelUpload" class="px-3 py-2 rounded border">Cancel</button>
        </div>
        <div id="uploadStatus" style="margin-top:10px;color:#333;display:none">Parsing...</div>
      </div>
    </div>

    <script>
    (function(){
      var uploadBtn = document.getElementById('uploadBtn');
      var modal = document.getElementById('uploadModal');
      var input = document.getElementById('completedFileInput');
      var cancel = document.getElementById('cancelUpload');
      var status = document.getElementById('uploadStatus');

      uploadBtn && uploadBtn.addEventListener('click', function(e){
        modal.style.display = 'flex';
        input.value = null;
        status.style.display = 'none';
      });

      cancel && cancel.addEventListener('click', function(){ modal.style.display = 'none'; });

      input && input.addEventListener('change', function(){
        var f = input.files && input.files[0];
        if (!f) return;
        status.style.display = 'block'; status.textContent = 'Parsing file — please wait...';
        var ticketId = uploadBtn && uploadBtn.getAttribute('data-ticket-id');
        var fd = new FormData();
        fd.append('completedFile', f);
        fd.append('ticket_id', ticketId || '');
        fd.append('store_preview', 1);

        fetch('/PRISM/tickets/upload.php?preview=1&ticket_id=' + encodeURIComponent(ticketId || ''), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(res){
          var ct = (res.headers.get('content-type') || '');
          if (!res.ok) return res.text().then(function(t){ throw new Error('Server error ' + res.status + ': ' + t); });
          if (ct.indexOf('application/json') === -1) return res.text().then(function(t){ throw new Error('Unexpected server response (not JSON).'); });
          return res.json();
        })
        .then(function(j){
          if (j && j.ok){
            var tok = (j.token || (j.data && j.data.token));
            if (tok){
              window.location.href = '/PRISM/tickets/upload-review.php?token=' + encodeURIComponent(tok);
            } else {
              window.location.href = '/PRISM/tickets/upload.php?ticket_id=' + encodeURIComponent(ticketId || '');
            }
          } else {
            status.textContent = 'Parsing failed: ' + (j && j.message ? j.message : 'Unknown');
          }
        }).catch(function(e){
          status.textContent = 'Network error while uploading file.';
          console.error(e);
        });
      });
    })();

    // Show notification if ticket was reopened
    <?php if (isset($_SESSION['ticket_reopened']) && $_SESSION['ticket_reopened']): ?>
      Swal.fire({
        icon: 'info',
        title: 'Ticket Reopened',
        text: 'Status changed to "In Progress". <?php echo isset($_SESSION['records_deleted']) ? $_SESSION['records_deleted'] . ' enrollment records have been deleted.' : 'All records have been deleted.'; ?>',
        confirmButtonText: 'OK'
      });
      <?php 
        unset($_SESSION['ticket_reopened']);
        unset($_SESSION['records_deleted']);
      ?>
    <?php endif; ?>

    // Add confirmation for status changes that will delete records
    var statusSelect = document.getElementById('statusSelect');
    var currentStatus = '<?php echo addslashes($ticket['ticket_status'] ?? ''); ?>';
    
    if (statusSelect) {
      statusSelect.addEventListener('change', function(e){
        var newStatus = this.value;
        
        // Check if changing FROM "For Review" TO "In Progress"
        if (newStatus === 'In Progress' && currentStatus === 'For Review') {
          e.preventDefault();
          Swal.fire({
            icon: 'warning',
            title: 'Reopen Ticket?',
            text: 'Changing status to "In Progress" will DELETE all saved enrollment records. The HEI will need to re-upload. Are you sure?',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete records and reopen',
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              this.form.submit();
            } else {
              // Reset select to current value
              this.value = currentStatus;
            }
          });
        } else {
          // Allow other status changes without confirmation
          this.form.submit();
        }
      });
    }
    </script>
</body>
</html>