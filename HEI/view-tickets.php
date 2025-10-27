<?php

// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// HEI protector
require_once __DIR__ . '/../includes/hei_protect.php';

// Database helper
require_once __DIR__ . '/../classes/database.php';
$db = new database();

// HEI context
// Note: CHED's getTickets() expects tickets.hei_ID to contain the HEI user id
// so prefer the session's heiUserID (HEI_user.hei_user_ID). Fall back to the
// institutional heiID only for display/compatibility.
$heiUserId = $_SESSION['heiUserID'] ?? null;
$heiId = $_SESSION['heiID'] ?? (isset($_GET['hei_id']) ? (int)$_GET['hei_id'] : null);

// Read filter inputs (GET)
$fCategory = $_GET['fCategory'] ?? 'all';
$fPriority = $_GET['fPriority'] ?? 'all';
$fStatus = $_GET['fStatus'] ?? 'all';
$fDue = $_GET['fDue'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;

$filters = [];
if ($fCategory !== 'all' && $fCategory !== '') $filters['category'] = $fCategory;
if ($fPriority !== 'all' && $fPriority !== '') $filters['priority'] = $fPriority;
if ($fStatus !== 'all' && $fStatus !== '') $filters['status'] = $fStatus;
if ($fDue !== '') $filters['due'] = $fDue;

// Fetch tickets for this HEI
// Use CHED-style getTickets() with hei_ID set to the HEI user id (if available).
$rows = [];
$totalTickets = 0;
if ($heiUserId) {
  // ensure filters include the hei user id
  $filters['hei_ID'] = (int)$heiUserId;
  $all = $db->getTickets($filters);
  $totalTickets = is_array($all) ? count($all) : 0;
  // simple server-side pagination by slicing the returned array
  $offset = max(0, ($page - 1) * $perPage);
  $rows = $totalTickets > 0 ? array_slice($all, $offset, $perPage) : [];
} else {
  $rows = [];
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HEI Tickets — PRISM</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">Filters</h2>
            <p class="text-sm text-slate-500">Filter by category, priority, status</p>
          </header>
          <div class="p-5 grid md:grid-cols-4 gap-3">
            <form method="get" class="contents">
              <input type="hidden" name="hei_id" value="<?php echo htmlspecialchars($heiUserId ?? $heiId); ?>" />
              <select id="fCategory" name="fCategory" class="px-3 py-2 rounded border">
                <option value="all">All Categories</option>
                <option value="Enrollment" <?php echo ($fCategory === 'Enrollment') ? 'selected' : ''; ?>>Enrollment</option>
                <option value="Faculty" <?php echo ($fCategory === 'Faculty') ? 'selected' : ''; ?>>Faculty</option>
                <option value="Graduates" <?php echo ($fCategory === 'Graduates') ? 'selected' : ''; ?>>Graduates</option>
                <option value="Institutional Profile" <?php echo ($fCategory === 'Institutional Profile') ? 'selected' : ''; ?>>Institutional Profile</option>
              </select>

              <select id="fPriority" name="fPriority" class="px-3 py-2 rounded border">
                <option value="all">All Priorities</option>
                <option value="Urgent" <?php echo ($fPriority === 'Urgent') ? 'selected' : ''; ?>>Urgent</option>
                <option value="High" <?php echo ($fPriority === 'High') ? 'selected' : ''; ?>>High</option>
                <option value="Medium" <?php echo ($fPriority === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                <option value="Low" <?php echo ($fPriority === 'Low') ? 'selected' : ''; ?>>Low</option>
              </select>

              <select id="fStatus" name="fStatus" class="px-3 py-2 rounded border">
                <option value="all">All Statuses</option>
                <option value="Open" <?php echo ($fStatus === 'Open') ? 'selected' : ''; ?>>Open</option>
                <option value="In Progress" <?php echo ($fStatus === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="Pending" <?php echo ($fStatus === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Resolved" <?php echo ($fStatus === 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                <option value="Closed" <?php echo ($fStatus === 'Closed') ? 'selected' : ''; ?>>Closed</option>
              </select>

              <input id="fDue" name="fDue" type="date" value="<?php echo htmlspecialchars($fDue, ENT_QUOTES); ?>" class="px-3 py-2 rounded border" />

              <button type="submit" class="px-3 py-2 rounded bg-slate-200">Apply</button>
              <a href="view-tickets.php" class="px-3 py-2 rounded border">Reset</a>
            </form>
          </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <h2 class="font-semibold">Assigned Tickets</h2>
            <div id="pager" class="text-sm text-slate-600">
              <?php
                $start = ($page - 1) * $perPage + 1;
                $end = min($totalTickets, $page * $perPage);
                if ($totalTickets > 0) {
                  echo "Showing {$start}–{$end} of " . (int)$totalTickets;
                } else {
                  echo "No tickets";
                }
              ?>
            </div>
          </header>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="text-sm text-slate-500 bg-slate-50">
                <tr>
                  <th class="px-6 py-4 font-medium">Title</th>
                  <th class="px-6 py-4 font-medium">Category</th>
                  <th class="px-6 py-4 font-medium">Priority</th>
                  <th class="px-6 py-4 font-medium">Status</th>
                  <th class="px-6 py-4 font-medium">Due Date</th>
                  <th class="px-6 py-4 font-medium">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <?php
                if (!empty($rows)):
                  foreach ($rows as $ticket):
                    $id = htmlspecialchars($ticket['id'] ?? $ticket['ticket_ID'] ?? '0', ENT_QUOTES);
                    $title = htmlspecialchars($ticket['ticket_title'] ?? 'Untitled', ENT_QUOTES);
                    $category = htmlspecialchars($ticket['ticket_category'] ?? '—', ENT_QUOTES);
                    $priority = htmlspecialchars($ticket['ticket_priority'] ?? '—', ENT_QUOTES);
                    $status = htmlspecialchars($ticket['ticket_status'] ?? '—', ENT_QUOTES);
                    $dueRaw = $ticket['ticket_due_date'] ?? $ticket['due_date'] ?? null;
                    // badge classes
                    $priorityClass = match(strtolower($priority)) {
                      'urgent' => 'bg-red-600 text-white',
                      'high' => 'bg-blue-600 text-white',
                      'medium' => 'bg-sky-500 text-white',
                      'low' => 'bg-gray-200 text-slate-800',
                      default => 'bg-gray-100 text-slate-700'
                    };
                    $statusClass = match(strtolower($status)) {
                      'open' => 'bg-blue-700 text-white',
                      'in progress' => 'bg-sky-100 text-sky-800',
                      'pending' => 'bg-yellow-100 text-amber-800',
                      'resolved' => 'bg-emerald-100 text-emerald-800',
                      'closed' => 'bg-slate-200 text-slate-800',
                      default => 'bg-gray-100 text-slate-700'
                    };
                ?>
                <tr class="hover:bg-slate-50">
                  <td class="px-6 py-4 align-top">
                    <div class="font-medium text-slate-800"><?php echo $title; ?></div>
                    <div class="text-xs text-slate-400 mt-1">ID: <?php echo $id; ?></div>
                  </td>
                  <td class="px-6 py-4 align-top">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-slate-50 text-sm text-slate-700 border"><?php echo $category; ?></div>
                  </td>
                  <td class="px-6 py-4 align-top">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm <?php echo $priorityClass; ?>"><?php echo $priority; ?></span>
                  </td>
                  <td class="px-6 py-4 align-top">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                  </td>
                  <td class="px-6 py-4 align-top">
                    <div class="text-sm text-slate-700 flex items-center gap-2">
                      <i data-lucide="calendar" class="h-4 w-4 text-slate-400"></i>
                      <?php echo $dueRaw ? date('F j, Y', strtotime($dueRaw)) : '—'; ?>
                    </div>
                  </td>
                  <td class="px-6 py-4 align-top">
                    <a href="ticket-details.php?ticket_id=<?php echo $id; ?>" class="inline-flex items-center gap-2 text-sm text-slate-700 hover:text-slate-900">
                      <i data-lucide="eye" class="h-4 w-4"></i>
                      View
                    </a>
                  </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                  <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">No tickets found.</td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Upload modal -->
  <div id="uploadModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="uploadTitle">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl overflow-hidden">
      <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
        <div>
          <h3 id="uploadTitle" class="font-semibold">Upload Completed Template</h3>
          <p class="text-sm text-slate-500">Parse the file locally to preview before submission</p>
        </div>
        <button id="uploadClose" class="p-2 rounded hover:bg-slate-50" aria-label="Close upload modal"><i data-lucide="x" class="h-5 w-5"></i></button>
      </div>
      <div class="p-5 space-y-4">
        <input id="fileInput" type="file" accept=".xlsx,.csv" class="block" />
        <div id="grid" class="overflow-x-auto border rounded">
          <table class="min-w-full text-xs">
            <thead id="gridHead" class="bg-slate-50"></thead>
            <tbody id="gridBody"></tbody>
          </table>
        </div>
      </div>
      <div class="px-5 py-4 border-t bg-slate-50 flex items-center justify-between">
        <p class="text-xs text-slate-500">Note: In FE phase, nothing is sent to server.</p>
        <button id="mockSubmit" class="px-3 py-2 rounded bg-emerald-600 text-white">Simulate Submit</button>
      </div>
    </div>
  </div>

</body>
</html>
