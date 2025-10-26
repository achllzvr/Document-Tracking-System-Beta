<?php

// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// CHED protector
require_once __DIR__ . '/../includes/ched_protect.php';

// Database connection
require_once('../includes/database_conn.php');

// Alert Initialization
$sweetAlertConfig = "";

// Set User Name from Session
$userName = isset($_SESSION['chedName']) ? $_SESSION['chedName'] : 'Unknown User';

// --- Filters: load HEIs and read GET filters ---
$heis = [];
if (isset($con) && method_exists($con, 'fetchInstitutions')) {
  $rawHeis = $con->fetchInstitutions();
  // normalize to id/name pairs
  foreach ($rawHeis as $h) {
    $hid = isset($h['HEI_id']) ? $h['HEI_id'] : ($h['hei_ID'] ?? ($h['id'] ?? null));
    $hname = $h['HEI_name'] ?? ($h['inst_name'] ?? ($h['name'] ?? ''));
    if ($hid) $heis[] = ['id' => (int)$hid, 'name' => $hname];
  }
}

// Read filter inputs (GET) and prepare filters for DB
$fHei = $_GET['fHei'] ?? 'all';
$fCategory = $_GET['fCategory'] ?? 'all';
$fPriority = $_GET['fPriority'] ?? 'all';
$fStatus = $_GET['fStatus'] ?? 'all';
$fDue = $_GET['fDue'] ?? '';

$filters = [];
if ($fHei !== 'all' && $fHei !== '') $filters['hei_ID'] = (int)$fHei;
if ($fCategory !== 'all' && $fCategory !== '') $filters['category'] = $fCategory;
if ($fPriority !== 'all' && $fPriority !== '') $filters['priority'] = $fPriority;
if ($fStatus !== 'all' && $fStatus !== '') $filters['status'] = $fStatus;
if ($fDue !== '') $filters['due'] = $fDue;

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CHED PRISM – View Tickets</title>
    <link rel="icon" type="image/png" href="../assets/media/ched_logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  </head>
  <body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>

    <div class="flex-1 flex">
      <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">Search & Filter</h2>
            <p class="text-sm text-slate-500">Find tickets across institutions — filter by HEI, category, priority, status, or due date.</p>
          </header>
          <div class="p-5 grid md:grid-cols-6 gap-3">
            <form id="filterForm" method="get" class="contents">
              <select id="fHei" name="fHei" class="px-3 py-2 rounded border">
                <option value="all">All HEIs</option>
                <?php foreach ($heis as $h) { $sel = ($fHei !== 'all' && (int)$fHei === (int)$h['id']) ? 'selected' : ''; ?>
                <option value="<?php echo htmlspecialchars($h['id'], ENT_QUOTES); ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($h['name'], ENT_QUOTES); ?></option>
                <?php } ?>
              </select>
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
            <h2 class="font-semibold">Tickets</h2>
            <div class="flex items-center gap-3">
              <a href="./create-ticket.php" class="inline-flex items-center gap-2 text-sm px-3 py-2 rounded bg-blue-600 text-white">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Create Ticket
              </a>
              <div id="pager" class="text-sm text-slate-600"></div>
            </div>
          </header>

          <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="text-sm text-slate-500 bg-slate-50">
                  <tr>
                    <th class="px-6 py-4 font-medium">Title</th>
                    <th class="px-6 py-4 font-medium">HEI</th>
                    <th class="px-6 py-4 font-medium">Category</th>
                    <th class="px-6 py-4 font-medium">Priority</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium">Due Date</th>
                    <th class="px-6 py-4 font-medium">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <?php 

                  $tickets = $con->getTickets($filters);
                  foreach ($tickets as $ticket): 
                    // defensive extraction
                    $id = htmlspecialchars($ticket['id'] ?? $ticket['ticket_ID'] ?? '0', ENT_QUOTES);
                    $title = htmlspecialchars($ticket['ticket_title'] ?? 'Untitled', ENT_QUOTES);
                    $heiName = htmlspecialchars($ticket['hei_name'] ?? $ticket['inst_name'] ?? ($ticket['hei_ID'] ?? '—'), ENT_QUOTES);
                    $category = htmlspecialchars($ticket['ticket_category'] ?? '—', ENT_QUOTES);
                    $priority = htmlspecialchars($ticket['ticket_priority'] ?? '—', ENT_QUOTES);
                    $status = htmlspecialchars($ticket['ticket_status'] ?? '—', ENT_QUOTES);
                    $dueRaw = $ticket['ticket_due_date'] ?? $ticket['due_date'] ?? null;
                    $due = $dueRaw ? date('m/d/Y', strtotime($dueRaw)) : '—';

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
                      <div class="text-sm text-slate-700"><?php echo $heiName; ?></div>
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
                  <?php endforeach; ?>
                  <?php if (empty($tickets)): ?>
                  <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-sm text-slate-500">No tickets found.</td>
                  </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

        </section>
      </div>
    </main>
  </div>

</body>
</html>