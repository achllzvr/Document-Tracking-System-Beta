
<?php
// Upload handler for completed templates (HEI uploads)
// Expected to be accessed via /PRISM/tickets/upload.php?ticket_id=NN

require_once __DIR__ . '/../includes/dev_logs.php';
require_once __DIR__ . '/../includes/hei_protect.php';
require_once __DIR__ . '/../classes/database.php';
$db = new database();

// Minimal response helper
function respond($ok, $msg, $data = []){
    if (php_sapi_name() === 'cli'){
        echo ($ok ? "OK: " : "ERROR: ") . $msg . PHP_EOL;
        if ($data) print_r($data);
        return;
    }
    header('Content-Type: application/json');
    echo json_encode(array_merge(['ok' => (bool)$ok, 'message' => $msg], $data));
    exit;
}

// If Composer autoloader exists, include it so PhpSpreadsheet (or other deps) load
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

$ticketId = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : (isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0);
// Allow commit-by-token to omit ticket_id in the request because token payload contains it
$commitToken = isset($_GET['commit_token']) ? trim($_GET['commit_token']) : '';
if (!$ticketId && !$commitToken) {
    respond(false, 'Missing ticket_id');
}

// If this is a commit-by-token POST, handle it immediately before any parsing/file logic
if ($commitToken && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $token = preg_replace('/[^a-zA-Z0-9_\-]/', '', $commitToken);
    $tmpFile = __DIR__ . '/../uploads/tmp/' . $token . '.json';
    if (!is_file($tmpFile)) respond(false, 'Invalid or expired preview token.');
    $data = json_decode(file_get_contents($tmpFile), true);
    if (!$data || empty($data['parsed_rows'])) respond(false, 'No parsed data found for token.');

    $rowsToInsert = $data['parsed_rows'];
    // determine hei id to associate
    $targetHeiId = $data['hei_user_id'] ?: $data['hei_id'] ?: ($_SESSION['heiID'] ?? null);

    // attach ticket id from the stored preview payload when committing by token
    $payloadTicketId = isset($data['ticket_id']) && $data['ticket_id'] ? (int)$data['ticket_id'] : null;
    $inserted = $db->createEnrollmentRowsBatch($targetHeiId, $rowsToInsert, $payloadTicketId);
    if ($inserted === false){
        respond(false, 'Batch insert failed.');
    }

    // cleanup temp preview file after successful commit
    @unlink($tmpFile);

    respond(true, 'Batch commit successful', ['inserted' => $inserted]);
}

// Only accept POST with file
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
        // Render a friendly upload page (uses JS fetch to call preview/commit endpoints returning JSON)
        ?>
        <!doctype html>
        <html lang="en"><head>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width,initial-scale=1" />
            <title>Upload Completed Template — Ticket <?php echo htmlspecialchars($ticketId); ?></title>
            <script src="https://cdn.tailwindcss.com"></script>
            <script src="https://unpkg.com/lucide@latest"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body class="min-h-screen bg-gray-50 text-slate-800">
            <?php require_once __DIR__ . '/../includes/header.php'; ?>
            <div class="flex">
                <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
                <main class="flex-1 p-6">
                    <div class="max-w-3xl mx-auto">
                        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b">
                                <h2 class="font-semibold">Upload Completed Template</h2>
                                <p class="text-sm text-slate-500">Ticket ID: <strong><?php echo (int)$ticketId; ?></strong></p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs text-slate-500">Ticket ID</label>
                                        <input id="uploadTicketId" type="text" value="<?php echo (int)$ticketId; ?>" class="w-full px-3 py-2 border rounded" readonly />
                                    </div>
                                    <div>
                                        <label class="text-xs text-slate-500">File</label>
                                        <input id="fileInput" type="file" accept=".xlsx,.xls,.csv" class="block w-full" />
                                        <div class="mt-2 text-xs text-slate-500">Selected file: <span id="selectedFileName">None</span></div>
                                    </div>
                                </div>

                                <div id="parseErrors" class="text-sm text-red-600"></div>

                                <div id="grid" class="overflow-x-auto border rounded">
                                    <table class="min-w-full text-xs">
                                        <thead id="gridHead" class="bg-slate-50"></thead>
                                        <tbody id="gridBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="px-6 py-4 border-t bg-slate-50 flex items-center justify-between">
                                <p class="text-xs text-slate-500" id="uploadNote">Preview mode will parse file and show rows; use Commit to insert.</p>
                                <div class="flex items-center gap-2">
                                    <a class="text-sm text-slate-600" href="/PRISM/HEI/ticket-details.php?ticket_id=<?php echo (int)$ticketId; ?>">← Back to ticket</a>
                                    <button id="previewBtn" class="px-3 py-2 rounded bg-amber-500 text-white">Preview</button>
                                    <button id="commitUpload" class="px-3 py-2 rounded bg-emerald-600 text-white hidden">Commit Upload</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>

            <script>
            (function(){
                var fileInput = document.getElementById('fileInput');
                var previewBtn = document.getElementById('previewBtn');
                var commitBtn = document.getElementById('commitUpload');
                var gridHead = document.getElementById('gridHead');
                var gridBody = document.getElementById('gridBody');
                var uploadNote = document.getElementById('uploadNote');
                var ticketIdInput = document.getElementById('uploadTicketId');
                var selectedFileName = document.getElementById('selectedFileName');
                var parseErrors = document.getElementById('parseErrors');

                function clearGrid(){ gridHead.innerHTML = ''; gridBody.innerHTML = ''; parseErrors.innerHTML = ''; }
                function showError(msg){ if (window.Swal && Swal.fire) return Swal.fire({icon:'error', title:'Error', text: String(msg)}); alert(msg); }
                function showSuccess(msg){ if (window.Swal && Swal.fire) return Swal.fire({icon:'success', title:'Success', text: String(msg)}); alert(msg); }

                if (fileInput) fileInput.addEventListener('change', function(){ var f = fileInput.files && fileInput.files[0]; selectedFileName.textContent = f ? f.name : 'None'; });

                previewBtn && previewBtn.addEventListener('click', function(e){
                    e.preventDefault(); clearGrid();
                    var ticketId = ticketIdInput && ticketIdInput.value || '';
                    if (!ticketId) return showError('Missing ticket id');
                    if (!fileInput || !fileInput.files || fileInput.files.length === 0) return showError('Please choose a file to upload.');

                    var fd = new FormData(); fd.append('completedFile', fileInput.files[0]); fd.append('ticket_id', ticketId);
                    previewBtn.disabled = true; previewBtn.textContent = 'Parsing...';
                    fetch('/PRISM/tickets/upload.php?preview=1&ticket_id=' + encodeURIComponent(ticketId), { method: 'POST', body: fd })
                        .then(function(res){ return res.json(); })
                        .then(function(json){ previewBtn.disabled = false; previewBtn.textContent = 'Preview';
                            if (!json) return showError('Empty server response');
                            if (!json.ok){ if (Array.isArray(json.errors) && json.errors.length) parseErrors.innerHTML = '<ul>'+json.errors.map(function(e){ return '<li>'+e+'</li>'; }).join('')+'</ul>'; return showError(json.message || 'Parsing failed'); }
                            var rows = json.parsed_rows || [];
                            if (!rows.length){ uploadNote.textContent = 'No rows parsed. Check template format.'; commitBtn.classList.add('hidden'); return; }
                            var headers = ['Program','Program Major','Year Level','Sex','Total Count','Acad Year'];
                            gridHead.innerHTML = '<tr>'+headers.map(function(h){ return '<th class="px-4 py-2 text-left">'+h+'</th>'; }).join('')+'</tr>';
                            gridBody.innerHTML = rows.map(function(r){
                                return '<tr class="odd:bg-white even:bg-slate-50">'
                                    + '<td class="px-4 py-2">'+(r.program||'')+'</td>'
                                    + '<td class="px-4 py-2">'+(r.program_major||'')+'</td>'
                                    + '<td class="px-4 py-2">'+(r.year_level||'')+'</td>'
                                    + '<td class="px-4 py-2">'+(r.sex||'')+'</td>'
                                    + '<td class="px-4 py-2">'+(r.total_count||'')+'</td>'
                                    + '<td class="px-4 py-2">'+(r.acad_year||'')+'</td>'
                                    + '</tr>';
                            }).join('\n');
                            if (Array.isArray(json.errors) && json.errors.length){ parseErrors.innerHTML = '<div class="font-semibold">Warnings / Errors:</div><ul>' + json.errors.map(function(e){ return '<li>'+e+'</li>'; }).join('') + '</ul>'; }
                            uploadNote.textContent = json.message + ' — ' + (json.rows_parsed || rows.length) + ' row(s) parsed.';
                            commitBtn.classList.remove('hidden');
                        }).catch(function(err){ previewBtn.disabled = false; previewBtn.textContent = 'Preview'; showError('Network or parsing error: ' + (err && err.message ? err.message : err)); });
                });

                commitBtn && commitBtn.addEventListener('click', function(e){ e.preventDefault(); var ticketId = ticketIdInput && ticketIdInput.value || ''; if (!ticketId) return showError('Missing ticket id'); if (!fileInput || !fileInput.files || fileInput.files.length === 0) return showError('Please choose a file to upload.');
                    if (window.Swal && Swal.fire){ Swal.fire({title:'Confirm import', text:'Insert parsed rows into the database for ticket ID ' + ticketId + '?', icon:'warning', showCancelButton:true}).then(function(result){ if (!result.isConfirmed) return; doCommit(ticketId); }); } else { if (!confirm('Insert parsed rows into the database for ticket ID ' + ticketId + '?')) return; doCommit(ticketId); }
                });

                function doCommit(ticketId){ var fd = new FormData(); fd.append('completedFile', fileInput.files[0]); fd.append('ticket_id', ticketId); commitBtn.disabled = true; commitBtn.textContent = 'Committing...'; fetch('/PRISM/tickets/upload.php?ticket_id=' + encodeURIComponent(ticketId), { method: 'POST', body: fd }).then(function(res){ return res.json(); }).then(function(json){ commitBtn.disabled = false; commitBtn.textContent = 'Commit Upload'; if (!json) return showError('Empty server response'); if (!json.ok){ if (Array.isArray(json.errors) && json.errors.length) parseErrors.innerHTML = '<ul>' + json.errors.map(function(e){ return '<li>'+e+'</li>'; }).join('') + '</ul>'; return showError(json.message || 'Commit failed'); } uploadNote.textContent = json.message + ' — inserted: ' + (json.inserted || 0) + ', errors: ' + (json.errors ? json.errors.length : 0); commitBtn.classList.add('hidden'); showSuccess('Upload committed. Inserted: ' + (json.inserted || 0)); }).catch(function(err){ commitBtn.disabled = false; commitBtn.textContent = 'Commit Upload'; showError('Network error: ' + (err && err.message ? err.message : err)); }); }

                if (window.lucide && lucide.replace) lucide.replace();
            })();
            </script>
        </body></html>
        <?php
        exit;
}


// If this is a commit-by-token request, the file is not required (we use the stored preview)
if (!(isset($_GET['commit_token']) && $_GET['commit_token'])){
    if (!isset($_FILES['completedFile']) || $_FILES['completedFile']['error'] !== UPLOAD_ERR_OK){
        respond(false, 'No file uploaded or upload error.');
    }
}

// If this is not a commit-by-token request, process the uploaded file and parse it
if (!(isset($_GET['commit_token']) && $_GET['commit_token'])){
    $uploaded = $_FILES['completedFile'];
    $tmp = $uploaded['tmp_name'];
    $origName = basename($uploaded['name']);

    // store uploaded file under uploads/hei/{hei_user_id}/tickets/{ticketId}/
    $heiUserId = $_SESSION['heiUserID'] ?? null;
    $heiId = $_SESSION['heiID'] ?? null;
    $base = __DIR__ . '/../uploads/hei';
    $destDir = $base . '/' . ($heiUserId ?: ('hei_' . ($heiId ?: 'unknown'))) . '/tickets/' . $ticketId;
    if (!is_dir($destDir)) mkdir($destDir, 0775, true);
    $destPath = $destDir . '/' . preg_replace('/[^A-Za-z0-9._-]/', '_', $origName);
    if (!move_uploaded_file($tmp, $destPath)){
        // try copy fallback
        if (!@copy($tmp, $destPath)){
            respond(false, 'Failed to move uploaded file to destination.');
        }
    }

    // Try to load with PhpSpreadsheet (preferred), fallback to PHPExcel
    $loaded = false;
    $sheet = null;
    try{
        if (class_exists('\PhpOffice\\PhpSpreadsheet\\IOFactory')){
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($destPath);
            $spreadsheet = $reader->load($destPath);
            $loaded = true;
        } else {
            respond(false, 'Spreadsheet library not available. Please install phpoffice/phpspreadsheet or PHPExcel.');
        }
    } catch (Exception $e){
        respond(false, 'Failed to parse spreadsheet: ' . $e->getMessage());
    }

    // Find Summary sheet
    $sheetName = 'Summary';
    $ws = null;
    if ($spreadsheet->sheetNameExists($sheetName ?? '')){
        $ws = $spreadsheet->getSheetByName($sheetName);
    } else {
        // attempt case-insensitive match
        foreach ($spreadsheet->getSheetNames() as $n){
            if (strtolower($n) === strtolower($sheetName)){
                $ws = $spreadsheet->getSheetByName($n);
                break;
            }
        }
    }
    if (!$ws) {
        // fallback to first sheet
        $ws = $spreadsheet->getSheet(0);
    }

}

// --- Dynamic header detection + multi-year, sex-expanded parsing ---
$rowsParsed = 0;
$inserted = 0;
$errors = [];
$maxRow = $ws->getHighestRow();

// preview mode (dry-run) flag
$isPreview = isset($_GET['preview']) && (($_GET['preview'] === '1') || (strtolower($_GET['preview']) === 'true'));

// collect parsed rows first (shaped for createEnrollmentRowsBatch: keys: acad_year,term,program,program_major,year_level,sex,total_count)
$parsedRows = [];

// helper: column index -> letter (PhpSpreadsheet Coordinate helper)
function colLetter($i){
    return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
}

// 1) Find header row and program column by searching for a cell containing 'PROGRAM'
$headerRow = null;
$programColIndex = null;
for ($r = 1; $r <= 20; $r++){
    for ($c = 1; $c <= 60; $c++){
        $val = trim((string)$ws->getCell(colLetter($c) . $r)->getValue());
        if ($val !== '' && stripos($val, 'PROGRAM') !== false){
            $headerRow = $r;
            $programColIndex = $c;
            break 2;
        }
    }
}
// Fallback to expected positions if not found
if (!$headerRow){
    $headerRow = 8; // typical in your sheet
    $programColIndex = 2; // column B
}

// find major column (look to the right of program header)
$majorColIndex = null;
for ($c = $programColIndex + 1; $c <= $programColIndex + 4; $c++){
    $h = trim((string)$ws->getCell(colLetter($c) . $headerRow)->getValue());
    if ($h !== '' && stripos($h, 'MAJOR') !== false){ $majorColIndex = $c; break; }
}
if (!$majorColIndex) $majorColIndex = $programColIndex + 1;

// Determine year blocks by scanning the next header row (sub-headers like Male/Female/Total)
$subHeaderRow = $headerRow + 1;
$yearBlocks = []; // array of ['level'=>int,'male'=>colIndex,'female'=>colIndex,'total'=>colIndex]
$col = $majorColIndex + 1;
$yearLevel = 1;
while ($col <= 60 && $yearLevel <= 10){
    $maleH = trim((string)$ws->getCell(colLetter($col) . $subHeaderRow)->getValue());
    $femaleH = trim((string)$ws->getCell(colLetter($col+1) . $subHeaderRow)->getValue());
    $totalH = trim((string)$ws->getCell(colLetter($col+2) . $subHeaderRow)->getValue());
    // If the subheaders match Male/Female/Total, register a block
    if ($maleH !== '' && $femaleH !== '' && $totalH !== '' && stripos($maleH, 'male') !== false && stripos($femaleH, 'female') !== false && stripos($totalH, 'total') !== false){
        $yearBlocks[] = ['level' => $yearLevel, 'male' => $col, 'female' => $col+1, 'total' => $col+2];
        $col += 3; $yearLevel++;
        continue;
    }
    // If we encounter a TOTAL header in the subheader row or header row, stop (end of year blocks)
    $maybe = trim((string)$ws->getCell(colLetter($col) . $headerRow)->getValue());
    if ($maybe !== '' && stripos($maybe, 'TOTAL') !== false) break;
    $maybe2 = trim((string)$ws->getCell(colLetter($col) . $subHeaderRow)->getValue());
    if ($maybe2 !== '' && stripos($maybe2, 'TOTAL') !== false) break;
    // If no clear pattern, advance one column and try to detect triplet window
    $col++;
}

// If no year blocks detected, use fallback mapping assuming contiguous triplets after Major (D..X)
if (empty($yearBlocks)){
    $fallbackStart = $majorColIndex + 1;
    $yearBlocks = [];
    for ($i = 0; $i < 7; $i++){
        $c = $fallbackStart + ($i * 3);
        $yearBlocks[] = ['level' => $i+1, 'male' => $c, 'female' => $c+1, 'total' => $c+2];
    }
}

// Data start row (assume rows start after subheaders)
$dataStart = $subHeaderRow + 1;

// Find Academic Year and Term values by searching a small header area
$acadYear = null; $term = null;
        for ($r = 1; $r <= $headerRow; $r++){
    for ($c = 1; $c <= 6; $c++){
        // use calculated values in case the template stores values as formulas
        $val = trim((string)$ws->getCell(colLetter($c) . $r)->getCalculatedValue());
        if ($val !== '' && stripos($val, 'Academic Year') !== false){
            $v = trim((string)$ws->getCell(colLetter($c+1) . $r)->getCalculatedValue());
            if (preg_match('/(20\d{2})/', $v, $m)) $acadYear = $m[1];
        }
        if ($val !== '' && stripos($val, 'Term') !== false){
            $v = trim((string)$ws->getCell(colLetter($c+1) . $r)->getCalculatedValue());
            if ($v !== '') $term = $v;
        }
    }
}
if (!$acadYear) $acadYear = date('Y');

// Iterate data rows until a PROGRAM cell with TOTAL is found
for ($r = $dataStart; $r <= $maxRow; $r++){
    $programCell = trim((string)$ws->getCell(colLetter($programColIndex) . $r)->getValue());
    if ($programCell === '') continue;
    if (strtoupper($programCell) === 'TOTAL') break;

    $program = $programCell;
    $programMajor = trim((string)$ws->getCell(colLetter($majorColIndex) . $r)->getValue());

    // For each year block, emit male/female rows when present
    foreach ($yearBlocks as $blk){
        $lvl = $blk['level'];
    // read calculated values so formulas like =E10+D10 are evaluated
    $mValRaw = trim((string)$ws->getCell(colLetter($blk['male']) . $r)->getCalculatedValue());
    $fValRaw = trim((string)$ws->getCell(colLetter($blk['female']) . $r)->getCalculatedValue());
    $tValRaw = trim((string)$ws->getCell(colLetter($blk['total']) . $r)->getCalculatedValue());
        $mVal = is_numeric(str_replace(',', '', $mValRaw)) ? (int)str_replace(',', '', $mValRaw) : 0;
        $fVal = is_numeric(str_replace(',', '', $fValRaw)) ? (int)str_replace(',', '', $fValRaw) : 0;
        $tVal = is_numeric(str_replace(',', '', $tValRaw)) ? (int)str_replace(',', '', $tValRaw) : null;

        if ($mVal > 0){
            $parsedRows[] = [
                'acad_year' => $acadYear,
                'term' => $term,
                'program' => $program,
                'program_major' => $programMajor,
                'year_level' => $lvl,
                'sex' => 'm',
                'total_count' => $mVal
            ];
            $rowsParsed++;
        }
        if ($fVal > 0){
            $parsedRows[] = [
                'acad_year' => $acadYear,
                'term' => $term,
                'program' => $program,
                'program_major' => $programMajor,
                'year_level' => $lvl,
                'sex' => 'f',
                'total_count' => $fVal
            ];
            $rowsParsed++;
        }

        // optional validation: if total provided and mismatches sum, record warning
        if ($tVal !== null && $tVal !== ($mVal + $fVal)){
            $errors[] = "Row {$r} Year {$lvl}: total ({$tVal}) does not match male+female (" . ($mVal+$fVal) . ") for program '{$program}'";
        }
    }
}

 
// If preview mode, return parsed rows without inserting
if ($isPreview){
    // Optionally store preview server-side and return a token for review flow
    $storePreview = (isset($_POST['store_preview']) && $_POST['store_preview']) || (isset($_GET['store_preview']) && ($_GET['store_preview'] === '1' || strtolower($_GET['store_preview']) === 'true'));

    if ($storePreview){
        $tmpDir = __DIR__ . '/../uploads/tmp';
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0775, true);
        try{
            $token = bin2hex(random_bytes(12));
        }catch(Exception $e){
            $token = uniqid('pv_', true);
        }

        // compute a web-accessible path for the uploaded file if possible
        $webPath = null;
        if (!empty($_SERVER['DOCUMENT_ROOT']) && strpos($destPath, $_SERVER['DOCUMENT_ROOT']) === 0){
            $webPath = substr($destPath, strlen($_SERVER['DOCUMENT_ROOT']));
        }

        $payload = [
            'token' => $token,
            'ticket_id' => $ticketId,
            'hei_user_id' => $heiUserId,
            'hei_id' => $heiId,
            'file_path' => $destPath,
            'file_web' => $webPath,
            'rows_parsed' => $rowsParsed,
            'parsed_rows' => $parsedRows,
            'errors' => $errors,
            'created_at' => date('c')
        ];

        file_put_contents($tmpDir . '/' . $token . '.json', json_encode($payload));
        respond(true, 'Preview stored', ['token' => $token, 'file' => $destPath, 'file_web' => $webPath, 'rows_parsed' => $rowsParsed, 'parsed_rows' => $parsedRows, 'errors' => $errors]);
    }

    respond(true, 'Preview parsed (no DB changes)', ['file' => $destPath, 'rows_parsed' => $rowsParsed, 'parsed_rows' => $parsedRows, 'errors' => $errors]);
}

// Support commit by token: read temp preview file and insert batch
if (isset($_GET['commit_token']) && $_GET['commit_token']){
    $token = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['commit_token']);
    $tmpFile = __DIR__ . '/../uploads/tmp/' . $token . '.json';
    if (!is_file($tmpFile)) respond(false, 'Invalid or expired preview token.');
    $data = json_decode(file_get_contents($tmpFile), true);
    if (!$data || empty($data['parsed_rows'])) respond(false, 'No parsed data found for token.');

    $rowsToInsert = $data['parsed_rows'];
    // determine hei id to associate
    $targetHeiId = $data['hei_user_id'] ?: $data['hei_id'] ?: $heiId;

    // attach ticket id from the stored preview payload when committing by token
    $payloadTicketId = isset($data['ticket_id']) && $data['ticket_id'] ? (int)$data['ticket_id'] : null;
    $inserted = $db->createEnrollmentRowsBatch($targetHeiId, $rowsToInsert, $payloadTicketId);
    if ($inserted === false){
        respond(false, 'Batch insert failed.');
    }

    // cleanup temp preview file after successful commit
    @unlink($tmpFile);

    respond(true, 'Batch commit successful', ['inserted' => $inserted]);
}

// Otherwise insert parsed rows into DB in a single batch
$targetHei = $heiId ?: $heiUserId ?: null;
if (empty($parsedRows)){
    respond(false, 'No parsed rows to insert', ['rows_parsed' => 0, 'errors' => $errors]);
}

// when directly committing (non-token flow) pass the ticket id so rows are linked
$inserted = $db->createEnrollmentRowsBatch($targetHei, $parsedRows, $ticketId ?: null);
if ($inserted === false){
    respond(false, 'Batch insert failed.', ['errors' => $errors]);
}

// respond with summary
respond(true, 'Upload processed', ['file' => $destPath, 'rows_parsed' => $rowsParsed, 'inserted' => $inserted, 'errors' => $errors]);