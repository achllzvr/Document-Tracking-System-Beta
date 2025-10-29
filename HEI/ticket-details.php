<?php
  
// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// HEI protector
require_once __DIR__ . '/../includes/hei_protect.php';

// Database helper
require_once __DIR__ . '/../classes/database.php';
$db = new database();

// Ticket id
$ticketId = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;

// Handle comment submission only for HEI users (HEI cannot change status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
  $comment = trim($_POST['comment']);
  if ($comment !== '') {
    $heiUserId = $_SESSION['heiUserID'] ?? null;
    $db->addCommentToTicket($ticketId, 'hei', $heiUserId, $comment);
  }
  header('Location: ticket-details.php?ticket_id=' . $ticketId);
  exit;
}

// Fetch ticket and comments
$ticket = $ticketId ? $db->getTicketById($ticketId) : null;
$comments = $ticketId ? $db->getCommentsForTicket($ticketId) : [];

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ticket Details — HEI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hei-min-h-screen">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="hei-flex-1">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Back button at top left -->
    <div style="max-width: 64rem; margin: 0 auto; position: relative;">
      <a href="view-tickets.php" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium absolute left-0 top-0 mt-4 ml-2 shadow-sm" style="z-index:10;">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
      </a>
    </div>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-3xl mx-auto space-y-6">
        <section id="ticketHeader" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden" style="margin-top:2.5rem;">
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
              <p><span class="text-slate-500">Status:</span>
                <?php 
                  $status = strtolower($ticket['ticket_status'] ?? 'default');
                  $statusClass = 'prism-badge prism-badge-status-' . preg_replace('/\s+/', '', $status);
                ?>
                <span class="<?php echo $statusClass; ?>">
                  <?php echo htmlspecialchars($ticket['ticket_status'] ?? '-'); ?>
                </span>
              </p>
              <p><span class="text-slate-500">Due:</span> <?php echo !empty($ticket['ticket_due_date']) ? htmlspecialchars(date('F j, Y', strtotime($ticket['ticket_due_date']))) : '-'; ?></p>
            </div>
            <div class="px-5 pb-5">
              <p><span class="text-slate-500 text-sm">Description:</span></p>
              <p class="text-sm"><?php echo nl2br(htmlspecialchars($ticket['ticket_description'] ?? '')); ?></p>
            </div>
            <div class="px-5 pb-5 flex items-center gap-3">
              <a class="inline-flex items-center gap-2 px-3 py-2 rounded border hover:bg-slate-50" href="/PRISM/tickets/download-template.php?ticket_id=<?php echo (int)$ticketId; ?>" id="downloadBtn"><i data-lucide="download" class="h-4 w-4"></i> Download Template</a>
              <button class="inline-flex items-center gap-2 px-3 py-2 rounded bg-blue-600 text-white" id="uploadBtn" data-ticket-id="<?php echo (int)$ticketId; ?>"><i data-lucide="upload" class="h-4 w-4"></i> Upload Completed</button>
            </div>
          <?php endif; ?>
        </section>

        <?php
          // Fetch any enrollment rows linked to this ticket (if present)
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
              <canvas id="progTotalsChart" height="200"></canvas>
            </div>
            <div>
              <canvas id="yearSexChart" height="200"></canvas>
            </div>
          </div>
        </section>
        <?php
          // Precompute arrays for Chart.js
          $hei_prog_labels = array_keys($programTotals);
          $hei_prog_data = array_values($programTotals);
          // Normalize year keys: remove empty keys and sort numerically
          $hei_years = array_keys($yearSex);
          $hei_years = array_values(array_filter($hei_years, function($v){ return $v !== '' && $v !== null; }));
          usort($hei_years, function($a,$b){ return intval($a) <=> intval($b); });
          $hei_male = [];
          $hei_female = [];
          foreach ($hei_years as $y){
            $hei_male[] = (int)(($yearSex[$y]['male'] ?? 0));
            $hei_female[] = (int)(($yearSex[$y]['female'] ?? 0));
          }
        ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
          (function(){
            var progLabels = <?php echo json_encode($hei_prog_labels); ?>;
            var progData = <?php echo json_encode($hei_prog_data); ?>;

            var ctx1 = document.getElementById('progTotalsChart').getContext('2d');
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

            var years = <?php echo json_encode($hei_years); ?>;
            var maleData = <?php echo json_encode($hei_male); ?>;
            var femaleData = <?php echo json_encode($hei_female); ?>;
            console.log('HEI chart data', { years: years, male: maleData, female: femaleData, progLabels: progLabels });
            var ctx2 = document.getElementById('yearSexChart').getContext('2d');
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

        <section class="grid md:grid-cols-1 gap-6">
          <div id="comments" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 pt-5 pb-3 border-b">
              <h3 class="font-semibold">Comments</h3>
              <p class="text-sm text-slate-500">Discuss ticket progress</p>
            </div>
            <div id="commentList">
              <?php if (empty($comments)): ?>
                <div class="p-4 text-sm text-slate-500">No comments yet.</div>
              <?php else: ?>
                <?php foreach ($comments as $c): ?>
                  <div class="p-5 border-b">
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

  <!-- Upload modal (hidden by default) -->
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

      fetch('/PRISM/tickets/upload.php?preview=1&ticket_id=' + encodeURIComponent(ticketId || ''), { method: 'POST', body: fd })
      .then(r => r.json())
        .then(function(j){
        if (j && j.ok){
          // If server returned a token, redirect to review page
          var tok = (j.token || (j.data && j.data.token));
          if (tok){
            window.location.href = '/PRISM/tickets/upload-review.php?token=' + encodeURIComponent(tok);
          } else {
            // fallback: open the old upload page with ?ticket_id
            window.location.href = '/PRISM/tickets/upload.php?ticket_id=' + encodeURIComponent(ticketId || '');
          }
        } else {
          status.textContent = 'Parsing failed: ' + (j && j.message ? j.message : 'Unknown');
        }
      }).catch(function(e){
        status.textContent = 'Network error while uploading file.';
      });
    });
  })();
  </script>
</body>
</html>
