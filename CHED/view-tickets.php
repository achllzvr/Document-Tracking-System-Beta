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
  <body class="ched-min-h-screen">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="ched-flex-1">
      <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="ched-main">
      <div class="ched-max-w-7xl ched-space-y-6">
        <section class="ched-card">
          <header class="ched-card-header">
            <h2 class="ched-font-semibold">Search & Filter</h2>
            <p class="ched-text-sm">Find tickets across institutions — filter by HEI, category, priority, status, or due date.</p>
          </header>
          <div class="p-5 ched-grid ched-md-grid-cols-6 ched-gap-3">
            <form id="filterForm" method="get" class="contents">
              <select id="fHei" name="fHei" class="ched-select">
                <option value="all">All HEIs</option>
                <?php foreach ($heis as $h) { $sel = ($fHei !== 'all' && (int)$fHei === (int)$h['id']) ? 'selected' : ''; ?>
                <option value="<?php echo htmlspecialchars($h['id'], ENT_QUOTES); ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($h['name'], ENT_QUOTES); ?></option>
                <?php } ?>
              </select>
              <select id="fCategory" name="fCategory" class="ched-select">
                <option value="all">All Categories</option>
                <option value="Enrollment" <?php echo ($fCategory === 'Enrollment') ? 'selected' : ''; ?>>Enrollment</option>
                <option value="Faculty" <?php echo ($fCategory === 'Faculty') ? 'selected' : ''; ?>>Faculty</option>
                <option value="Graduates" <?php echo ($fCategory === 'Graduates') ? 'selected' : ''; ?>>Graduates</option>
                <option value="Institutional Profile" <?php echo ($fCategory === 'Institutional Profile') ? 'selected' : ''; ?>>Institutional Profile</option>
              </select>

              <select id="fPriority" name="fPriority" class="ched-select">
                <option value="all">All Priorities</option>
                <option value="Urgent" <?php echo ($fPriority === 'Urgent') ? 'selected' : ''; ?>>Urgent</option>
                <option value="High" <?php echo ($fPriority === 'High') ? 'selected' : ''; ?>>High</option>
                <option value="Medium" <?php echo ($fPriority === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                <option value="Low" <?php echo ($fPriority === 'Low') ? 'selected' : ''; ?>>Low</option>
              </select>

              <select id="fStatus" name="fStatus" class="ched-select">
                <option value="all">All Statuses</option>
                <option value="Open" <?php echo ($fStatus === 'Open') ? 'selected' : ''; ?>>Open</option>
                <option value="In Progress" <?php echo ($fStatus === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="Pending" <?php echo ($fStatus === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Resolved" <?php echo ($fStatus === 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                <option value="Closed" <?php echo ($fStatus === 'Closed') ? 'selected' : ''; ?>>Closed</option>
              </select>

              <input id="fDue" name="fDue" type="date" value="<?php echo htmlspecialchars($fDue, ENT_QUOTES); ?>" class="ched-input" />

              <button type="submit" class="ched-btn">Apply</button>
              <a href="view-tickets.php" class="ched-btn ched-btn-outline">Reset</a>
            </form>
          </div>
        </section>

        <section class="ched-card">
          <header class="ched-card-header ched-items-center ched-justify-between" style="display: flex;">
            <h2 class="ched-font-semibold">Tickets</h2>
            <div class="ched-items-center ched-gap-3" style="display: flex;">
              <a href="./create-ticket.php" class="ched-btn ched-btn-primary ched-items-center ched-gap-2" style="display: inline-flex; font-size: 0.875rem;">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Create Ticket
              </a>
              <div id="pager" class="ched-text-sm" style="color: #64748b;"></div>
            </div>
          </header>

          <div class="ched-overflow-x-auto">
              <table class="w-full text-left">
                <thead class="ched-text-sm" style="color: #64748b; background: #f8fafc;">
                  <tr>
                    <th class="px-6 py-4 ched-font-semibold">Title</th>
                    <th class="px-6 py-4 ched-font-semibold">HEI</th>
                    <th class="px-6 py-4 ched-font-semibold">Category</th>
                    <th class="px-6 py-4 ched-font-semibold">Priority</th>
                    <th class="px-6 py-4 ched-font-semibold">Status</th>
                    <th class="px-6 py-4 ched-font-semibold">Due Date</th>
                    <th class="px-6 py-4 ched-font-semibold">Actions</th>
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
                      'urgent' => 'prism-badge prism-badge-status-urgent',
                      'high' => 'prism-badge prism-badge-status-high',
                      'medium' => 'prism-badge prism-badge-status-medium',
                      'low' => 'prism-badge prism-badge-status-low',
                      default => 'prism-badge prism-badge-status-default'
                    };
                    $statusClass = match(strtolower($status)) {
                      'open' => 'prism-badge prism-badge-status-open',
                      'new' => 'prism-badge prism-badge-status-new',
                      'in progress' => 'prism-badge prism-badge-status-inprogress',
                      'pending' => 'prism-badge prism-badge-status-pending',
                      'resolved' => 'prism-badge prism-badge-status-resolved',
                      'closed' => 'prism-badge prism-badge-status-closed',
                      'urgent' => 'prism-badge prism-badge-status-urgent',
                      'high' => 'prism-badge prism-badge-status-high',
                      'medium' => 'prism-badge prism-badge-status-medium',
                      'low' => 'prism-badge prism-badge-status-low',
                      default => 'prism-badge prism-badge-status-default'
                    };
                  ?>
                  <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 align-top">
                      <div class="ched-font-semibold ched-text-slate-800"><?php echo $title; ?></div>
                      <div class="ched-text-xs" style="margin-top: 0.25rem; color: #94a3b8;">ID: <?php echo $id; ?></div>
                    </td>
                    <td class="px-6 py-4 align-top">
                      <div class="ched-text-sm ched-text-slate-800"><?php echo $heiName; ?></div>
                    </td>
                    <td class="px-6 py-4 align-top">
                      <div class="inline-flex ched-items-center px-3 py-1 rounded-full bg-slate-50 ched-text-sm ched-text-slate-800 border"><?php echo $category; ?></div>
                    </td>
                    <td class="px-6 py-4 align-top">
                      <span class="<?php echo $priorityClass; ?>"><?php echo $priority; ?></span>
                    </td>
                    <td class="px-6 py-4 align-top">
                      <span class="<?php echo $statusClass; ?>"><?php echo $status; ?></span>
                    </td>
                    <td class="px-6 py-4 align-top">
                      <div class="ched-text-sm ched-items-center ched-gap-2" style="display: flex; color: #1e293b;">
                        <i data-lucide="calendar" class="h-4 w-4" style="color: #94a3b8;"></i>
                        <?php echo $dueRaw ? date('F j, Y', strtotime($dueRaw)) : '—'; ?>
                      </div>
                    </td>
                    <td class="px-6 py-4 align-top">
                      <a href="ticket-details.php?ticket_id=<?php echo $id; ?>" class="inline-flex ched-items-center ched-gap-2 ched-text-sm ched-text-slate-800 hover:ched-text-slate-900">
                        <i data-lucide="eye" class="h-4 w-4"></i>
                        View
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (empty($tickets)): ?>
                  <tr>
                    <td colspan="8" class="px-6 py-8 text-center ched-text-sm" style="color: #64748b;">No tickets found.</td>
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