<?php
// Minimal review page for previews stored by tickets/upload.php
// Expects ?token=...
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
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Upload Review</title>
    <link href="/PRISM/src/styles/globals.css" rel="stylesheet">
    <style>table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:6px}</style>
</head>
<body class="p-6">
    <h1 class="text-2xl font-bold mb-4">Review parsed upload</h1>

    <div class="mb-4">
        <strong>Ticket:</strong> <?php echo htmlspecialchars($ticketId); ?>
    </div>

    <?php if ($fileWeb): ?>
        <div class="mb-4">
            <strong>Uploaded file:</strong>
            <a href="<?php echo htmlspecialchars($fileWeb); ?>" target="_blank">View file</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="mb-4 text-red-700">
            <strong>Parsing warnings/errors</strong>
            <ul>
                <?php foreach($errors as $err) echo '<li>'.htmlspecialchars($err).'</li>'; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h2 class="text-xl font-semibold mt-4">Parsed rows (<?php echo count($parsed); ?>)</h2>
    <div style="overflow:auto;margin-top:8px">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Acad Year</th>
                    <th>Term</th>
                    <th>Program</th>
                    <th>Major</th>
                    <th>Year Level</th>
                    <th>Sex</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($parsed as $i => $r): ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                        <td><?php echo htmlspecialchars($r['enr_acad_year'] ?? $r['acad_year'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($r['enr_term'] ?? $r['term'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($r['enr_program'] ?? $r['program'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($r['enr_program_major'] ?? $r['program_major'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($r['enr_year_level'] ?? $r['year_level'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars(($r['enr_sex'] ?? $r['sex'] ?? '') ? strtoupper($r['enr_sex'] ?? $r['sex'] ?? '') : ''); ?></td>
                        <td><?php echo htmlspecialchars($r['enr_total_count'] ?? $r['total_count'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">
        <button id="saveBtn" class="btn btn-primary">Save records</button>
        <a class="btn" href="/PRISM/HEI/ticket-details.php?ticket_id=<?php echo urlencode($ticketId); ?>" style="margin-left:12px">Back to ticket</a>
    </div>

    <script>
    document.getElementById('saveBtn').addEventListener('click', function(){
        var btn = this; btn.disabled = true; btn.textContent = 'Saving...';
        fetch('/PRISM/tickets/upload.php?commit_token=' + encodeURIComponent('<?php echo $token; ?>'), { method: 'POST', credentials: 'same-origin' })
        .then(function(res){
            // If server responded with non-JSON (for example a redirect to login page), show the body text
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
                btn.textContent = 'Saved ✓';
                window.location.href = '/PRISM/HEI/ticket-details.php?ticket_id=' + encodeURIComponent('<?php echo $ticketId; ?>') + '&saved=1';
            } else {
                alert('Save failed: ' + (j && j.message ? j.message : 'unknown'));
                btn.disabled = false; btn.textContent = 'Save records';
            }
        }).catch(function(e){
            // If the response was an HTML login page due to an expired session, inform the user to re-login
            var msg = e && e.message ? e.message : 'Network or server error';
            if (msg && msg.indexOf('/HEI/login.php') !== -1) msg = 'Session expired — please sign in again.';
            alert('Save failed: ' + msg);
            btn.disabled = false; btn.textContent = 'Save records';
        });
    });
    </script>
</body>
</html>
