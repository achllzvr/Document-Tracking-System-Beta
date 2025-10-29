<?php
$require_path = __DIR__ . '/../includes/dev_logs.php';
if (file_exists($require_path)) require_once $require_path;
// Start session and allow access to either HEI or CHED authenticated users
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['heiUserID']) && empty($_SESSION['chedID'])){
  // Not authenticated — redirect to generic index
  header('Location: /PRISM/index.php');
  exit;
}
require_once __DIR__ . '/../classes/database.php';
$db = new database();

$ticketId = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
if (!$ticketId){
    header('Location: /PRISM/HEI/ticket-details.php');
    exit;
}

$rows = $db->getEnrollmentRowsByTicket($ticketId);
$ticket = $db->getTicketById($ticketId);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Enrollment Details — Ticket <?php echo htmlspecialchars($ticketId); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>
  <div class="flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="flex-1 p-6">
      <div class="max-w-5xl mx-auto">
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b">
            <h2 class="font-semibold">Enrollment Records for Ticket #<?php echo (int)$ticketId; ?></h2>
            <?php if ($ticket): ?><p class="text-sm text-slate-500"><?php echo htmlspecialchars($ticket['ticket_title'] ?? ''); ?></p><?php endif; ?>
          </div>
          <div class="p-4">
            <div class="flex items-center justify-between mb-4">
              <div class="text-sm text-slate-600">Total rows: <strong><?php echo count($rows); ?></strong></div>
              <div class="flex items-center gap-2">
                <a class="px-3 py-2 rounded border" href="/PRISM/HEI/ticket-details.php?ticket_id=<?php echo (int)$ticketId; ?>">Back to ticket</a>
              </div>
            </div>

            <div style="overflow:auto">
              <table class="min-w-full text-sm" style="width:100%;border-collapse:collapse">
                <thead class="bg-slate-50">
                  <tr>
                    <th class="px-3 py-2 text-left">#</th>
                    <th class="px-3 py-2 text-left">Acad Year</th>
                    <th class="px-3 py-2 text-left">Term</th>
                    <th class="px-3 py-2 text-left">Program</th>
                    <th class="px-3 py-2 text-left">Major</th>
                    <th class="px-3 py-2 text-left">Year Level</th>
                    <th class="px-3 py-2 text-left">Sex</th>
                    <th class="px-3 py-2 text-left">Total</th>
                    <th class="px-3 py-2 text-left">Inserted At</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $i => $r): ?>
                  <tr style="border-top:1px solid #eee">
                    <td class="px-3 py-2"><?php echo $i+1; ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($r['enr_acad_year'] ?? ''); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($r['enr_term'] ?? ''); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($r['enr_program'] ?? ''); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($r['enr_program_major'] ?? ''); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($r['enr_year_level'] ?? ''); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars(strtoupper($r['enr_sex'] ?? '')); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($r['enr_total_count'] ?? ''); ?></td>
                    <td class="px-3 py-2"><?php echo !empty($r['enr_created_at']) ? htmlspecialchars(date('F j, Y g:ia', strtotime($r['enr_created_at']))) : ''; ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
