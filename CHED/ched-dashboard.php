<?php

// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// CHED protector
require_once __DIR__ . '/../includes/ched_protect.php';

// Database connection
require_once('../classes/database.php');

// Instance of the database class
$con = new database();

// Alert Initialization
$sweetAlertConfig = "";

// Set User Name from Session
$userName = isset($_SESSION['chedName']) ? $_SESSION['chedName'] : 'Unknown User';

// On page load, fetch stats
$totalHEIs = $con->getTotalHEIs();
$pendingTickets = $con->getTotalPendingTickets();

// Enrollment Updates
$totalEnrollmentUpdates = $con->getTotalEnrollmentUpdates();

// Faculty Updates
$totalFacultyUpdates = $con->getTotalFacultyUpdates();

// Graduates Updates
$totalGraduatesUpdates = $con->getTotalGraduatesUpdates();

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CHED PRISM – Dashboard</title>
    <link rel="icon" type="image/png" href="/PRISM/assets/CHED_logo.png" />
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

      <!-- Main -->
      <main class="flex-1 p-6 overflow-y-auto">
        <div class="max-w-7xl mx-auto space-y-8">
          <!-- Stats cards -->
          <section id="stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Total HEIs Card -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
              <div class="p-6">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Total HEIs</p>
                    <p class="text-2xl mt-1" id="totalHeis">
                      <?php
                      echo $totalHEIs;
                      ?>
                    </p>
                  </div>
                  <div class="p-3 rounded-lg bg-blue-500"><i data-lucide="building-2" class="h-6 w-6 text-white"></i></div>
                </div>
              </div>
            </div>

            <!-- Pending Tickets Card -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
              <div class="p-6">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Pending Tickets</p>
                    <p class="text-2xl mt-1" id="pendingTickets"><?php echo $pendingTickets; ?></p>
                  </div>
                  <div class="p-3 rounded-lg bg-green-500"><i data-lucide="ticket" class="h-6 w-6 text-white"></i></div>
                </div>
              </div>
            </div>

            <!-- Placeholder Card 1 -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
              <div class="p-6">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Placeholder 1</p>
                    <p class="text-2xl mt-1">—</p>
                  </div>
                  <div class="p-3 rounded-lg bg-gray-300"><i data-lucide="placeholder" class="h-6 w-6 text-white"></i></div>
                </div>
              </div>
            </div>

            <!-- Placeholder Card 2 -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
              <div class="p-6">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-500">Placeholder 2</p>
                    <p class="text-2xl mt-1">—</p>
                  </div>
                  <div class="p-3 rounded-lg bg-gray-300"><i data-lucide="placeholder" class="h-6 w-6 text-white"></i></div>
                </div>
              </div>
            </div>

          </section>

          <div class="grid lg:grid-cols-1 gap-6">
            <!-- Recent Tickets -->
            <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
              <header class="px-5 pt-5 pb-3 border-b">
                <h2 class="font-semibold">Recent Tickets</h2>
                <p class="text-sm text-slate-500">Latest ticket activity across institutions</p>
              </header>
              <div id="recentTickets" class="p-4 space-y-2">
                <?php

                // Fetch recent tickets
                $recentTickets = $con->getRecentTickets();
                if (!empty($recentTickets)) {
                  foreach ($recentTickets as $ticket) {
                    echo '<div class="p-3 rounded-lg border hover:bg-slate-50">';
                    echo '<p class="font-semibold">[' . htmlspecialchars($ticket['hei_name'] ?? $ticket['inst_name'] ?? 'Unknown') . '] ' . htmlspecialchars($ticket['ticket_title'] ?? $ticket['ticket_category'] ?? 'No title') . '</p>';
                    echo '<p class="text-xs text-slate-500">Status: ' . htmlspecialchars($ticket['ticket_status'] ?? '-') . ' | Priority: ' . htmlspecialchars($ticket['ticket_priority'] ?? '-') . ' | Created: ' . htmlspecialchars($ticket['ticket_created_at'] ?? $ticket['created_at'] ?? '-') . '</p>';
                    echo '</div>';
                  }
                } else {
                  echo '<p class="text-sm text-slate-500">No recent tickets found.</p>';
                }

                ?>
              </div>
              <div class="p-4 border-t">
                <a href="./view-tickets.php" class="inline-flex items-center gap-2 text-sm px-3 py-2 rounded border hover:bg-slate-50">
                  View all tickets
                </a>
              </div>
            </section>

          </div>

          <!-- Data updates summary -->
          <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
              <h3 class="font-semibold mb-3">Data Updates Summary</h3>
              <div class="grid md:grid-cols-3 gap-4">
                <div class="p-4 bg-slate-50 rounded-lg border">
                  <p class="text-xs text-slate-500">Enrollment</p>
                  <p class="text-2xl font-semibold">
                    <?php
                    
                    // Fetch total enrollment updates this week
                    echo $totalEnrollmentUpdates;

                    ?>
                  </p>
                  <p class="text-xs text-slate-400">Records updated this week</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-lg border">
                  <p class="text-xs text-slate-500">Faculty</p>
                  <p class="text-2xl font-semibold">
                    <?php
                    
                    // Fetch total faculty updates this week
                    echo $totalFacultyUpdates;

                    ?>
                  </p>
                  <p class="text-xs text-slate-400">Records updated this week</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-lg border">
                  <p class="text-xs text-slate-500">Graduates</p>
                  <p class="text-2xl font-semibold">
                    <?php
                    
                    // Fetch total graduates updates this week
                    echo $totalGraduatesUpdates;

                    ?>
                  </p>
                  <p class="text-xs text-slate-400">Records updated this week</p>
                </div>
              </div>
            </div>
          </section>
        </div>
      </main>
    </div>

    <!-- Notifications dropdown -->
    <div id="notifDropdown" class="hidden fixed z-50 right-4 top-16 w-96 bg-white shadow-xl border border-slate-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b flex items-center justify-between">
        <div>
          <p class="font-semibold">Notifications</p>
          <p class="text-xs text-slate-500">Latest updates</p>
        </div>
        <button id="notifMarkRead" class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Mark all read</button>
      </div>
      <div id="notifList" class="max-h-96 overflow-y-auto divide-y"></div>
    </div>

    <script>
      // Run after DOM is ready to avoid 'null' element errors from other scripts
      (function(){
        function onReady(fn){
          if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
          else fn();
        }

        onReady(function(){
          // Safe updater for stat elements used by fetches or other scripts
          window.updateStat = function(id, value){
            try {
              const el = document.getElementById(id);
              if (!el) return false; // element missing; caller should handle gracefully
              el.textContent = String(value);
              return true;
            } catch (e) {
              console.warn('updateStat failed for', id, e);
              return false;
            }
          };

          // Initialize lucide icons if available
          if (window.lucide && typeof lucide.createIcons === 'function') {
            try { lucide.createIcons(); } catch (e) { console.warn('lucide init failed', e); }
          }
        });
      })();
    </script>

    </body>
</html>
