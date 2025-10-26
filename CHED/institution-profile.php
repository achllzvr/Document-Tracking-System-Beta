<?php
  
// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// Database connection
require_once __DIR__ . '/../includes/database_conn.php';

// Fetch HEI ID from url
$heiId = $_GET['hei_id'] ?? null;

// Fetch Institution Profile using HEI ID
$institutionProfile = $con->getInstitutionProfile($heiId);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Institution Profile — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-4xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">
              <!-- Display Institution Name -->
              <?php
              echo $institutionProfile['inst_name'] ?? 'Institution';
              ?> Profile</h2>
            <p class="text-sm text-slate-500">
              Institution details set by 
              <!-- Display Institution Head Name -->
              <?php 
                echo ($institutionProfile['inst_head_last_name'] ?? 'Unknown') . ', ' . ($institutionProfile['inst_head_first_name'] ?? 'Unknown');
              ?>
            </p>
          </header>

          <div class="p-5 grid grid-cols-1 gap-3">
            <div>
              <label class="block text-xs text-slate-500">Institution Name</label>
              <div class="mt-1 text-sm text-slate-800">
                <?php echo htmlspecialchars($institutionProfile['inst_name'] ?? '—', ENT_QUOTES); ?>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-slate-500">Region</label>
                <div class="mt-1 text-sm text-slate-800">
                  <?php echo htmlspecialchars($institutionProfile['region_name'] ?? $institutionProfile['region'] ?? '—', ENT_QUOTES); ?>
                </div>
              </div>
              <div>
                <label class="block text-xs text-slate-500">Municipality</label>
                <div class="mt-1 text-sm text-slate-800">
                  <?php echo htmlspecialchars($institutionProfile['inst_municipality_city'] ?? '—', ENT_QUOTES); ?>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs text-slate-500">Address</label>
              <div class="mt-1 text-sm text-slate-800">
                <?php echo htmlspecialchars($institutionProfile['inst_street_brgy'] . ', ' . $institutionProfile['inst_municipality_city'] ?? '—', ENT_QUOTES); ?>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-slate-500">Head Title and Full Name</label>
                <div class="mt-1 text-sm text-slate-800">
                  <?php
                    $headTitle = $institutionProfile['inst_head_title_name'] ?? '';
                    $headFullName = trim(
                      ($institutionProfile['inst_head_first_name'] ?? '') . ' ' .
                      ($institutionProfile['inst_head_middle_name'] ?? '') . ' ' .
                      ($institutionProfile['inst_head_last_name'] ?? '') . ' ' .
                      ($institutionProfile['inst_head_suffix'] ?? '')
                    );
                    echo htmlspecialchars(trim($headTitle . ' ' . $headFullName) ?: '—', ENT_QUOTES);
                  ?>
                </div>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-slate-500">Email</label>
                <div class="mt-1 text-sm text-slate-800">
                  <?php echo htmlspecialchars($institutionProfile['contact_email'] ?? $institutionProfile['inst_email'] ?? '—', ENT_QUOTES); ?>
                </div>
              </div>
              <div>
                <label class="block text-xs text-slate-500">Phone</label>
                <div class="mt-1 text-sm text-slate-800">
                  <?php echo htmlspecialchars($institutionProfile['inst_telephone'] ?? '—', ENT_QUOTES); ?>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
              <a href="/PRISM/CHED/view-heis.php" class="px-3 py-2 rounded border">Back</a>
            </div>
          </div>

        </section>
      </div>
    </main>
  </div>

</body>
</html>
