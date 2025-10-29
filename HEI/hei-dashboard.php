<?php
  
// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// HEI protector
require_once __DIR__ . '/../includes/hei_protect.php';

// Database helper
require_once __DIR__ . '/../classes/database.php';
$db = new database();

// Determine HEI context from session (preferred) or GET
$heiId = $_SESSION['heiID'] ?? (isset($_GET['hei_id']) ? (int)$_GET['hei_id'] : null);

// Fetch server-side data for dashboard
$stats = $heiId ? $db->getHEIDashboardStats($heiId) : null;
$recentTickets = $heiId ? $db->getRecentTicketsForHEI($heiId, 4) : [];

// Build recent comments across the HEI by collecting comments for recent tickets
$recentComments = [];
if ($recentTickets) {
  foreach ($recentTickets as $t) {
    $comments = $db->getCommentsForTicket($t['id']);
    foreach ($comments as $c) {
      $c['ticketId'] = $t['id'];
      $c['ticketTitle'] = $t['ticket_title'] ?? $t['ticket_title'] ?? ($t['ticket_title'] ?? $t['ticket_title'] ?? $t['ticket_title'] ?? ($t['ticket_title'] ?? ''));
      $recentComments[] = $c;
    }
  }
  // sort by created_at desc and keep latest 6
  usort($recentComments, function($a,$b){
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
  });
  $recentComments = array_slice($recentComments, 0, 4);
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HEI Dashboard — PRISM</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    /* Responsive grid gaps for HEI dashboard columns */
    .hei-dashboard-grid {
      column-gap: clamp(0.75rem, 2vw, 1rem);
      row-gap: clamp(0.5rem, 1.2vw, 0.9rem);
    }
    /* Reserve a fixed viewport for recent lists so they can show up to 4 items without
       resizing the panel. Each item will have a consistent min-height and the list will
       scroll when there are more than 4 entries. */
    .hei-recent-list {
      --hei-item-h: clamp(3.5rem, 3.9rem + 0.2vw, 4.5rem);
      --hei-item-gap: clamp(0.5rem, 0.7rem, 0.9rem);
      /* (4 items * item height) + (3 gaps between them) */
      max-height: calc((var(--hei-item-h) * 4) + (var(--hei-item-gap) * 3));
      overflow-y: auto;
      /* keep a small right padding so scrollbar doesn't overlap content */
      padding-right: 0.25rem;
    }
    /* Ensure each child inside the recent list has at least the reserved item height */
    .hei-recent-list > * {
      min-height: var(--hei-item-h);
      display: flex;
      align-items: center;
    }
  </style>
</head>
<body class="hei-min-h-screen">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="hei-flex-1">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="hei-main">
      <div class="hei-max-w-7xl hei-space-y-6">
        <section class="hei-card">
          <header class="hei-card-header">
            <h2 class="hei-font-semibold">Overview</h2>
            <p class="hei-text-sm">Your ticket workload and latest comments</p>
          </header>
          <div id="stats" class="p-5 hei-grid hei-md-grid-cols-3 hei-gap-4">
            <?php if ($stats): ?>
              <div class="p-5 hei-rounded-xl border hei-shadow-sm">
                <div class="hei-items-center hei-justify-between" style="display: flex;">
                  <div>
                    <p class="hei-text-sm">Open/Pending</p>
                    <p class="text-2xl"><?php echo (int)($stats['open'] + $stats['pending']); ?></p>
                  </div>
                  <div class="p-3 hei-rounded-xl" style="background: #f59e42;"><i data-lucide="ticket" class="h-6 w-6" style="color: #fff;"></i></div>
                </div>
              </div>

              <div class="p-5 hei-rounded-xl border hei-shadow-sm">
                <div class="hei-items-center hei-justify-between" style="display: flex;">
                  <div>
                    <p class="hei-text-sm">Urgent/High</p>
                    <p class="text-2xl"><?php echo 0; /* TODO: derive urgent/high from priorities */ ?></p>
                  </div>
                  <div class="p-3 hei-rounded-xl" style="background: #f43f5e;"><i data-lucide="alert-triangle" class="h-6 w-6" style="color: #fff;"></i></div>
                </div>
              </div>

              <div class="p-5 rounded-xl border shadow-sm">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Resolved/Closed</p>
                    <p class="text-2xl"><?php echo (int)($stats['resolved']); ?></p>
                  </div>
                  <div class="p-3 rounded-lg bg-emerald-600"><i data-lucide="check-circle" class="h-6 w-6 text-white"></i></div>
                </div>
              </div>
            <?php else: ?>
              <div class="p-4 text-sm text-slate-500">No dashboard data available.</div>
            <?php endif; ?>
          </div>
        </section>

  <div class="hei-grid hei-lg-grid-cols-2 hei-gap-8 hei-dashboard-grid">
          <section class="hei-card">
            <header class="hei-card-header">
              <h3 class="hei-font-semibold">Recent Tickets</h3>
              <p class="hei-text-sm">Most recent updates assigned to you</p>
            </header>
            <div id="recentTickets" class="hei-p-4 space-y-2 hei-recent-list">
              <?php if (!empty($recentTickets)): ?>
                <?php foreach ($recentTickets as $t): ?>
                  <?php
                    $ticketId = htmlspecialchars($t['id']);
                    $title = htmlspecialchars($t['ticket_title'] ?? 'Untitled');
                    $priority = htmlspecialchars($t['ticket_priority'] ?? '-');
                    $statusRaw = $t['ticket_status'] ?? '-';
                    $createdRaw = $t['ticket_created_at'] ?? $t['created_at'] ?? $t['ticket_created'] ?? '';
                    $createdAt = '-';
                    if (!empty($createdRaw)) {
                      $ts = strtotime($createdRaw);
                      $createdAt = ($ts !== false) ? date('M j, Y H:i', $ts) : htmlspecialchars($createdRaw);
                    }

                    // map status to badge class similar to CHED dashboard
                    $s = strtolower($statusRaw);
                    $badgeClass = 'prism-badge prism-badge-status-default';
                    if ($s === 'open' || $s === 'new') $badgeClass = 'prism-badge prism-badge-status-open';
                    elseif ($s === 'pending' || $s === 'in progress') $badgeClass = 'prism-badge prism-badge-status-pending';
                    elseif ($s === 'closed' || $s === 'resolved') $badgeClass = 'prism-badge prism-badge-status-closed';
                    elseif ($s === 'urgent' || $s === 'high') $badgeClass = 'prism-badge prism-badge-status-urgent';
                  ?>
                  <a class="block p-3 rounded-lg border hover:bg-slate-50 flex items-start gap-4" href="./ticket-details.php?ticket_id=<?php echo $ticketId; ?>">
                    <div class="p-2 rounded-lg bg-blue-50 flex-shrink-0">
                      <i data-lucide="ticket" class="h-5 w-5 text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                      <p class="font-semibold text-sm"><?php echo $title; ?></p>
                      <p class="text-xs text-slate-500 mt-1">Priority: <?php echo $priority; ?> · Created: <?php echo $createdAt; ?></p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                      <span class="<?php echo $badgeClass; ?>"><?php echo htmlspecialchars($statusRaw); ?></span>
                    </div>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="p-4 text-sm text-slate-500">No tickets found.</div>
              <?php endif; ?>
            </div>
            <div class="hei-p-4 hei-border-t">
              <a href="./view-tickets.php" class="hei-btn">View tickets</a>
            </div>
          </section>

          <section class="hei-card">
            <header class="hei-card-header">
              <h3 class="hei-font-semibold">Recent Comments</h3>
              <p class="hei-text-sm">Latest discussion on your tickets</p>
            </header>
            <div id="recentComments" class="hei-p-4 space-y-2 hei-recent-list">
              <?php if (!empty($recentComments)): ?>
                <?php foreach ($recentComments as $c): ?>
                  <?php
                    $userName = htmlspecialchars($c['user_name'] ?? ($c['user_type'] . '#' . ($c['user_ID'] ?? '')) );
                    $avatarInitial = strtoupper(substr($c['user_name'] ?? ($c['user_type'] ?? 'U'), 0, 1));
                    $commentText = nl2br(htmlspecialchars($c['comment'] ?? $c['content'] ?? ''));
                    $created = !empty($c['created_at']) ? date('F j, Y g:ia', strtotime($c['created_at'])) : '-';
                    $ticketId = isset($c['ticketId']) ? (int)$c['ticketId'] : null;
                    $ticketTitle = htmlspecialchars($c['ticketTitle'] ?? '');
                  ?>
                  <a class="block p-3 rounded-lg border hover:bg-slate-50 flex items-start gap-3" href="<?php echo $ticketId ? ('./ticket-details.php?ticket_id=' . $ticketId) : '#'; ?>">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs"><?php echo $avatarInitial; ?></div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm"><span class="font-bold"><?php echo $userName; ?></span><?php echo $ticketTitle ? (' — <span class="text-slate-500">' . $ticketTitle . '</span>') : ''; ?></p>
                      <p class="text-sm mt-1"><?php echo $commentText; ?></p>
                      <p class="text-xs text-slate-500 mt-2"><?php echo $created; ?></p>
                    </div>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="p-4 text-sm text-slate-500">No comments yet.</div>
              <?php endif; ?>
            </div>
          </section>
        </div>
      </div>
    </main>
  </div>

</body>
</html>
