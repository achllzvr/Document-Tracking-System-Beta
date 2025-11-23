<?php
// Upload review page for previews stored by tickets/upload.php
// Expects ?token=...
session_start();

require_once __DIR__ . '/../includes/dev_logs.php';

$token = isset($_GET['token']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['token']) : null;
if (!$token){
    http_response_code(400);
    echo "Missing token";
    exit;
}

$tmpFile = __DIR__ . '/../uploads/tmp/' . $token . '.json';
if (!is_file($tmpFile)){
    http_response_code(404);
    echo "Preview not found or expired.";
    exit;
}

$payload = json_decode(file_get_contents($tmpFile), true);
if (!$payload){
    http_response_code(500);
    echo "Invalid preview payload.";
    exit;
}

$fileWeb = isset($payload['file_web']) ? $payload['file_web'] : null;
$parsed = isset($payload['parsed_rows']) ? $payload['parsed_rows'] : [];
$errors = isset($payload['errors']) ? $payload['errors'] : [];
$ticketId = isset($payload['ticket_id']) ? $payload['ticket_id'] : '';

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Upload — PRISM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/PRISM/assets/prism-global.css">
    <link rel="stylesheet" href="/PRISM/assets/hei-global.css">
</head>
<body class="hei-min-h-screen">
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <div class="hei-flex-1">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="hei-main">
            <div class="hei-max-w-7xl hei-space-y-6">
                
                <!-- Page Header -->
                <section class="hei-card">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                                    <i data-lucide="file-check-2" class="h-7 w-7 text-emerald-600"></i>
                                    Review Parsed Upload
                                </h1>
                                <p class="text-sm text-slate-500 mt-1">Verify data before committing to the database</p>
                            </div>
                            <a href="/PRISM/HEI/ticket-details.php?ticket_id=<?php echo urlencode($ticketId); ?>" 
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                                Back to Ticket
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Upload Information -->
                <section class="hei-card">
                    <header class="hei-card-header">
                        <h2 class="hei-font-semibold text-slate-800">Upload Information</h2>
                    </header>
                    <div class="p-6 space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-blue-50">
                                <i data-lucide="ticket" class="h-5 w-5 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500 uppercase tracking-wide">Ticket ID</div>
                                <div class="font-medium text-slate-800"><?php echo htmlspecialchars($ticketId); ?></div>
                            </div>
                        </div>

                        <?php if ($fileWeb): ?>
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-emerald-50">
                                <i data-lucide="file-spreadsheet" class="h-5 w-5 text-emerald-600"></i>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500 uppercase tracking-wide">Uploaded File</div>
                                <a href="<?php echo htmlspecialchars($fileWeb); ?>" 
                                   target="_blank" 
                                   class="font-medium text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1">
                                    View Original File
                                    <i data-lucide="external-link" class="h-3 w-3"></i>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-purple-50">
                                <i data-lucide="database" class="h-5 w-5 text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500 uppercase tracking-wide">Parsed Records</div>
                                <div class="font-medium text-slate-800"><?php echo count($parsed); ?> rows ready to save</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Parsing Errors/Warnings -->
                <?php if (!empty($errors)): ?>
                <section class="hei-card border-l-4 border-amber-500">
                    <header class="px-6 py-4 bg-amber-50 border-b border-amber-100">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-triangle" class="h-5 w-5 text-amber-600"></i>
                            <h2 class="font-semibold text-amber-900">Parsing Warnings</h2>
                        </div>
                        <p class="text-sm text-amber-700 mt-1">The following issues were detected during parsing:</p>
                    </header>
                    <div class="p-6">
                        <ul class="space-y-2">
                            <?php foreach($errors as $err): ?>
                                <li class="flex items-start gap-2 text-sm text-amber-800">
                                    <i data-lucide="circle-alert" class="h-4 w-4 mt-0.5 flex-shrink-0"></i>
                                    <span><?php echo htmlspecialchars($err); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Parsed Data Table -->
                <section class="hei-card">
                    <header class="hei-card-header flex items-center justify-between">
                        <div>
                            <h2 class="hei-font-semibold text-slate-800">Parsed Data Preview</h2>
                            <p class="hei-text-sm">Review all <?php echo count($parsed); ?> records before saving</p>
                        </div>
                        <div class="text-sm px-3 py-1 bg-slate-100 rounded-full font-medium text-slate-700">
                            <?php echo count($parsed); ?> Records
                        </div>
                    </header>
                    <div class="hei-overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 font-semibold text-center">#</th>
                                    <th class="px-4 py-3 font-semibold">Acad Year</th>
                                    <th class="px-4 py-3 font-semibold">Term</th>
                                    <th class="px-4 py-3 font-semibold">Program</th>
                                    <th class="px-4 py-3 font-semibold">Major</th>
                                    <th class="px-4 py-3 font-semibold text-center">Year Level</th>
                                    <th class="px-4 py-3 font-semibold text-center">Sex</th>
                                    <th class="px-4 py-3 font-semibold text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            <?php foreach($parsed as $i => $r): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center text-slate-500 font-medium"><?php echo $i+1; ?></td>
                                    <td class="px-4 py-3 text-slate-700"><?php echo htmlspecialchars($r['enr_acad_year'] ?? $r['acad_year'] ?? '—'); ?></td>
                                    <td class="px-4 py-3 text-slate-700"><?php echo htmlspecialchars($r['enr_term'] ?? $r['term'] ?? '—'); ?></td>
                                    <td class="px-4 py-3 text-slate-800 font-medium"><?php echo htmlspecialchars($r['enr_program'] ?? $r['program'] ?? '—'); ?></td>
                                    <td class="px-4 py-3 text-slate-700"><?php echo htmlspecialchars($r['enr_program_major'] ?? $r['program_major'] ?? '—'); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                            <?php echo htmlspecialchars($r['enr_year_level'] ?? $r['year_level'] ?? '—'); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php 
                                            $sex = strtoupper($r['enr_sex'] ?? $r['sex'] ?? '');
                                            $sexClass = $sex === 'M' ? 'bg-cyan-100 text-cyan-800' : ($sex === 'F' ? 'bg-pink-100 text-pink-800' : 'bg-slate-100 text-slate-600');
                                        ?>
                                        <span class="inline-flex items-center justify-center px-2 py-1 <?php echo $sexClass; ?> rounded-full text-xs font-semibold">
                                            <?php echo $sex ?: '—'; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                        <?php echo htmlspecialchars($r['enr_total_count'] ?? $r['total_count'] ?? '—'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Action Buttons -->
                <section class="hei-card">
                    <div class="p-6 flex items-center justify-between bg-slate-50">
                        <div>
                            <p class="text-sm text-slate-600">
                                <i data-lucide="info" class="h-4 w-4 inline mr-1"></i>
                                Review the data carefully. Once saved, records will be committed to the database.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="/PRISM/HEI/ticket-details.php?ticket_id=<?php echo urlencode($ticketId); ?>" 
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-white transition">
                                <i data-lucide="x" class="h-4 w-4"></i>
                                Cancel
                            </a>
                            <button id="saveBtn" 
                                    class="inline-flex items-center gap-2 px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-medium shadow-sm">
                                <i data-lucide="check" class="h-4 w-4"></i>
                                Save Records
                            </button>
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <script>
    // Initialize Lucide icons
    if (window.lucide) lucide.createIcons();

    document.getElementById('saveBtn').addEventListener('click', function(){
        var btn = this;
        var originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');

        fetch('/PRISM/tickets/upload.php?commit_token=' + encodeURIComponent('<?php echo $token; ?>'), { 
            method: 'POST', 
            credentials: 'same-origin' 
        })
        .then(function(res){
            var ct = res.headers.get('content-type') || '';
            if (!res.ok) {
                return res.text().then(function(txt){ throw new Error(txt || ('HTTP ' + res.status)); });
            }
            if (ct.indexOf('application/json') === -1){
                return res.text().then(function(txt){ throw new Error(txt || 'Unexpected server response'); });
            }
            return res.json();
        })
        .then(function(j){
            if (j && j.ok){
                Swal.fire({
                    icon: 'success',
                    title: 'Records Saved!',
                    text: 'Successfully saved ' + (j.inserted || '<?php echo count($parsed); ?>') + ' records.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function(){
                    window.location.href = '/PRISM/HEI/ticket-details.php?ticket_id=' + encodeURIComponent('<?php echo $ticketId; ?>') + '&saved=1';
                });
            } else {
                throw new Error(j && j.message ? j.message : 'Save failed');
            }
        }).catch(function(e){
            var msg = e && e.message ? e.message : 'Network or server error';
            if (msg && msg.indexOf('/HEI/login.php') !== -1) {
                msg = 'Session expired — please sign in again.';
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: msg,
                confirmButtonText: 'Try Again'
            });
            
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            if (window.lucide) lucide.createIcons();
        });
    });
    </script>
</body>
</html>
