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
$recentTickets = $heiId ? $db->getRecentTicketsForHEI($heiId, 5) : [];

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
  $recentComments = array_slice($recentComments, 0, 6);
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

        <div class="hei-grid hei-lg-grid-cols-2 hei-gap-6">
          <section class="hei-card">
            <header class="hei-card-header">
              <h3 class="hei-font-semibold">Recent Tickets</h3>
              <p class="hei-text-sm">Most recent updates assigned to you</p>
            </header>
            <div id="recentTickets" class="hei-p-4 hei-divide-y">
              <?php if (!empty($recentTickets)): ?>
                <?php foreach ($recentTickets as $t): ?>
                  <a class="hei-block hei-py-3" href="./ticket-details.php?ticket_id=<?php echo htmlspecialchars($t['id']); ?>">
                    <div class="hei-flex hei-items-start hei-gap-3">
                      <div class="hei-flex-1 hei-min-w-0">
                        <p class="hei-text-sm hei-font-medium"><?php echo htmlspecialchars($t['ticket_title'] ?? $t['ticket_title'] ?? ($t['ticket_title'] ?? 'Untitled')); ?></p>
                        <p class="hei-text-xs hei-text-muted">Due: <?php echo htmlspecialchars($t['ticket_due_date'] ?? '-'); ?></p>
                      </div>
                      <span class="hei-inline-flex hei-text-xs hei-px-2 hei-py-1 hei-rounded hei-border"><?php echo htmlspecialchars($t['ticket_status'] ?? $t['ticket_status'] ?? ''); ?></span>
                    </div>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="hei-p-4 hei-text-sm hei-text-muted">No tickets found.</div>
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
            <div id="recentComments" class="hei-p-4 hei-divide-y">
              <?php if (!empty($recentComments)): ?>
                <?php foreach ($recentComments as $c): ?>
                  <div class="hei-py-3">
                    <p class="hei-text-sm"><span class="hei-font-medium"><?php echo htmlspecialchars($c['user_ID'] ?? $c['user_ID']); ?></span> — <?php echo htmlspecialchars($c['comment'] ?? $c['comment'] ?? $c['content'] ?? ''); ?></p>
                    <p class="hei-text-xs hei-text-muted"><?php echo htmlspecialchars(date('M j, Y g:ia', strtotime($c['created_at']))); ?></p>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="hei-p-4 hei-text-sm hei-text-muted">No comments yet.</div>
              <?php endif; ?>
            </div>
          </section>
        </div>
      </div>
    </main>
  </div>

  <script type="module">
  // TODO[backend]: Replace mock import with server-provided data (DB queries or API endpoints).
  // Required data: tickets, comments, heis

    // Determine current HEI context (mock): hei_id param or use tickets[0].heiId
    const params = new URLSearchParams(location.search);
    const currentHeiId = Number(params.get('hei_id')) || tickets[0]?.heiId || heis[0]?.id;
    const myTickets = tickets.filter(t => t.heiId === currentHeiId);

    // Stats
    const openCount = myTickets.filter(t=>['Open','In Progress','Pending'].includes(t.status)).length;
    const urgentCount = myTickets.filter(t=>['Urgent','High'].includes(t.priority)).length;
    const closedCount = myTickets.filter(t=>['Resolved','Closed'].includes(t.status)).length;
    const stats = document.getElementById('stats');
    function card(label, value, color, icon){return `<div class="p-5 rounded-xl border shadow-sm"><div class="flex items-center justify-between"><div><p class="text-sm text-slate-500">${label}</p><p class="text-2xl">${value}</p></div><div class="p-3 rounded-lg ${color}"><i data-lucide="${icon}" class="h-6 w-6 text-white"></i></div></div></div>`}
    stats.innerHTML = [
      card('Open/Pending', openCount, 'bg-amber-500', 'ticket'),
      card('Urgent/High', urgentCount, 'bg-rose-500', 'alert-triangle'),
      card('Resolved/Closed', closedCount, 'bg-emerald-600', 'check-circle'),
    ].join('');

    // Recent tickets
    const recent = [...myTickets].sort((a,b)=> new Date(b.updatedAt)-new Date(a.updatedAt)).slice(0,5);
    document.getElementById('recentTickets').innerHTML = recent.map(t => `
      <a class="block py-3" href="./ticket-details.php?ticket_id=${t.id}">
        <div class="flex items-start gap-3">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium">${t.title}</p>
            <p class="text-xs text-slate-500">Due: ${t.dueDate || '-'}</p>
          </div>
          <span class="inline-flex text-xs px-2 py-1 rounded border">${t.status}</span>
        </div>
      </a>`).join('');

    // Recent comments for this HEI
    const ticketIds = new Set(myTickets.map(t=>t.id));
    const recentComments = comments.filter(c => ticketIds.has(c.ticketId)).slice(-6).reverse();
    document.getElementById('recentComments').innerHTML = recentComments.map(c => `
      <div class="py-3">
        <p class="text-sm"><span class="font-medium">${c.userName}</span> — ${c.content}</p>
        <p class="text-xs text-slate-500">${new Date(c.createdAt).toLocaleString()}</p>
      </div>`).join('') || '<div class="p-4 text-sm text-slate-500">No comments yet.</div>';

    if (window.lucide) lucide.createIcons();
    // TODO[backend]: hydrate from API for current HEI user
  </script>
</body>
</html>
