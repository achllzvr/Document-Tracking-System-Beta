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
    // map textual status to the DB value if needed. Here we store the text.
    $chedId = $_SESSION['chedID'] ?? null;
    $db->changeTicketStatus($ticketId, $newStatus, $chedId);
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
                  <select name="status" onchange="this.form.submit()" class="px-2 py-1 rounded border">
                    <option value="Open" <?php echo (($ticket['ticket_status'] ?? '') === 'Open') ? 'selected' : ''; ?>>Open</option>
                    <option value="In Progress" <?php echo (($ticket['ticket_status'] ?? '') === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Pending" <?php echo (($ticket['ticket_status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Resolved" <?php echo (($ticket['ticket_status'] ?? '') === 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                    <option value="Closed" <?php echo (($ticket['ticket_status'] ?? '') === 'Closed') ? 'selected' : ''; ?>>Closed</option>
                  </select>
                </form>
              </p>
              </div>
              <p><span class="text-slate-500">Due:</span> <?php echo !empty($ticket['ticket_due_date']) ? htmlspecialchars(date('F j, Y', strtotime($ticket['ticket_due_date']))) : '-'; ?></p>
            </div>
            <div class="px-5 pb-5">
              <p><span class="text-slate-500 text-sm">Description:</span>
              <p class="text-sm"><?php echo nl2br(htmlspecialchars($ticket['ticket_description'] ?? '')); ?></p>
            </div>
            <div class="px-5 pb-5 flex items-center gap-3">
              <a class="inline-flex items-center gap-2 px-3 py-2 rounded border hover:bg-slate-50" href="/PRISM/tickets/download-template.php?ticket_id=<?php echo (int)$ticketId; ?>" id="downloadBtn"><i data-lucide="download" class="h-4 w-4"></i> Download Template</a>
              <a class="inline-flex items-center gap-2 px-3 py-2 rounded bg-blue-600 text-white" href="/PRISM/tickets/upload.php?ticket_id=<?php echo (int)$ticketId; ?>" id="uploadBtn"><i data-lucide="upload" class="h-4 w-4"></i> Upload Completed</a>
            </div>
          <?php endif; ?>
        </section>

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
</body>
</html>