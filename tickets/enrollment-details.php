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

// Pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 25;
// Validate perPage values
if (!in_array($perPage, [25, 50, 100])) {
    $perPage = 25;
}
$offset = ($page - 1) * $perPage;

// Get total count and paginated rows
$totalRows = $db->getEnrollmentRowsCountByTicket($ticketId);
$totalPages = $totalRows > 0 ? ceil($totalRows / $perPage) : 1;
$page = min($page, $totalPages); // Ensure page doesn't exceed total pages

$rows = $db->getEnrollmentRowsByTicket($ticketId, $perPage, $offset);
$ticket = $db->getTicketById($ticketId);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Enrollment Details — Ticket <?php echo htmlspecialchars($ticketId); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
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
              <div class="flex items-center gap-4">
                <div class="text-sm text-slate-600">Total rows: <strong><?php echo number_format($totalRows); ?></strong></div>
                <?php if ($totalRows > 0): ?>
                <div class="text-sm text-slate-500">
                  Showing <?php echo number_format($offset + 1); ?>-<?php echo number_format(min($offset + $perPage, $totalRows)); ?>
                </div>
                <?php endif; ?>
                <div class="flex items-center gap-2">
                  <label class="text-sm text-slate-600">Per page:</label>
                  <select onchange="window.location.href='?ticket_id=<?php echo $ticketId; ?>&page=1&per_page=' + this.value" class="px-2 py-1 border rounded text-sm">
                    <option value="25" <?php echo $perPage === 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $perPage === 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $perPage === 100 ? 'selected' : ''; ?>>100</option>
                  </select>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <?php
                  // choose appropriate ticket-details path based on authenticated user type
                  $backBase = isset($_SESSION['chedID']) ? '/PRISM/CHED/ticket-details.php' : '/PRISM/HEI/ticket-details.php';
                ?>
                <a class="px-3 py-2 rounded border" href="<?php echo $backBase; ?>?ticket_id=<?php echo (int)$ticketId; ?>">Back to ticket</a>
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
                <?php if (empty($rows)): ?>
                  <tr>
                    <td colspan="9" class="px-3 py-8 text-center text-slate-500">
                      <div class="flex flex-col items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        <div class="font-medium">No enrollment records found</div>
                        <div class="text-sm">This ticket has no uploaded data yet.</div>
                      </div>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($rows as $i => $r): 
                    $rowNum = $offset + $i + 1;
                  ?>
                    <tr style="border-top:1px solid #eee">
                      <td class="px-3 py-2"><?php echo $rowNum; ?></td>
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
                <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t flex items-center justify-between">
              <div class="text-sm text-slate-600">
                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
              </div>
              <div class="flex items-center gap-2">
                <?php if ($page > 1): ?>
                  <a href="?ticket_id=<?php echo $ticketId; ?>&page=1&per_page=<?php echo $perPage; ?>" class="px-3 py-1 rounded border hover:bg-slate-50">First</a>
                  <a href="?ticket_id=<?php echo $ticketId; ?>&page=<?php echo $page - 1; ?>&per_page=<?php echo $perPage; ?>" class="px-3 py-1 rounded border hover:bg-slate-50">Previous</a>
                <?php endif; ?>
                
                <?php
                  // Show page numbers
                  $startPage = max(1, $page - 2);
                  $endPage = min($totalPages, $page + 2);
                  for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                  <a href="?ticket_id=<?php echo $ticketId; ?>&page=<?php echo $i; ?>&per_page=<?php echo $perPage; ?>" 
                     class="px-3 py-1 rounded border <?php echo $i === $page ? 'bg-blue-600 text-white' : 'hover:bg-slate-50'; ?>">
                    <?php echo $i; ?>
                  </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                  <a href="?ticket_id=<?php echo $ticketId; ?>&page=<?php echo $page + 1; ?>&per_page=<?php echo $perPage; ?>" class="px-3 py-1 rounded border hover:bg-slate-50">Next</a>
                  <a href="?ticket_id=<?php echo $ticketId; ?>&page=<?php echo $totalPages; ?>&per_page=<?php echo $perPage; ?>" class="px-3 py-1 rounded border hover:bg-slate-50">Last</a>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
