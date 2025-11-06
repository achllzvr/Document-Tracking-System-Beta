<?php
// Secure download handler for ticket templates
// Serves the attached template file(s) for a given ticket_ID.

require_once __DIR__ . '/../includes/dev_logs.php';
// allow access to both HEI and CHED pages but require a logged-in session
session_start();
if (empty($_SESSION)) {
    // Not logged in — respond with 403
    http_response_code(403);
    echo "Access denied. Please log in.";
    exit;
}

require_once __DIR__ . '/../classes/database.php';
$db = new database();

$ticketId = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
if (! $ticketId) {
    http_response_code(400);
    echo "Missing or invalid ticket_id.";
    exit;
}

$ticket = $db->getTicketById($ticketId);
if (! $ticket) {
    http_response_code(404);
    echo "Ticket not found.";
    exit;
}

$templates = $db->getTemplatesForTicket($ticketId);
if (empty($templates)) {
    http_response_code(404);
    echo "No templates attached to this ticket.";
    exit;
}

// Helper: convert stored web-relative path to filesystem path
function relToFs($rel){
    // Expected stored form: /PRISM/uploads/templates/<folder>/<file>
    $rel = trim($rel);
    if ($rel === '') return null;
    // If already absolute filesystem path, return as-is
    if (is_file($rel)) return $rel;
    // Normalize common stored forms to project-relative path
    // Examples of stored values we expect:
    //  - /PRISM/uploads/templates/<cat>/<file>
    //  - /uploads/templates/<cat>/<file>
    //  - uploads/templates/<cat>/<file>
    //  - relative paths like uploads/templates/<file>

    // Remove leading slash if present for easier checks
    if (strpos($rel, '/') === 0) $rel = substr($rel, 1);

    $projectRoot = dirname(__DIR__); // one level up from tickets/

    // If the stored path begins with 'PRISM/uploads', strip the leading 'PRISM/'
    if (stripos($rel, 'PRISM/uploads/') === 0) {
        $relPath = substr($rel, strlen('PRISM/')) ; // now 'uploads/...'
        $candidate = $projectRoot . '/' . $relPath;
        if (is_file($candidate)) return $candidate;
    }

    // If it begins with 'uploads/', map directly under project root
    if (stripos($rel, 'uploads/') === 0) {
        $candidate = $projectRoot . '/' . $rel;
        if (is_file($candidate)) return $candidate;
    }

    // If it contains '/uploads/templates/' anywhere, try to find that suffix under project root
    $pos = stripos($rel, 'uploads/templates/');
    if ($pos !== false) {
        $suffix = substr($rel, $pos);
        $candidate = $projectRoot . '/' . $suffix;
        if (is_file($candidate)) return $candidate;
    }

    // As a last resort, try interpreting it as a direct path under project root
    $candidate2 = $projectRoot . '/' . $rel;
    if (is_file($candidate2)) return $candidate2;

    // try basename under uploads folder
    $basename = basename($rel);
    $candidate3 = $projectRoot . '/uploads/' . $basename;
    if (is_file($candidate3)) return $candidate3;

    return null;
}

$files = [];
foreach ($templates as $t) {
    $rel = $t['template_file_rel_path'] ?? '';
    if (! $rel) continue;
    $fs = relToFs($rel);
    if ($fs && is_file($fs)) {
        $files[] = ['path' => $fs, 'meta' => $t];
    }
}

if (empty($files)) {
    http_response_code(404);
    echo "Attached template files not found on disk.";
    exit;
}

// If only one file, stream it directly
if (count($files) === 1) {
    $f = $files[0]['path'];
    $meta = $files[0]['meta'];
    $basename = basename($f);
    $downloadName = preg_replace('/[^A-Za-z0-9._-]/', '_', ($meta['template_name'] ?? pathinfo($basename, PATHINFO_FILENAME))) . '.' . pathinfo($basename, PATHINFO_EXTENSION);

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($f));
    // flush output buffers
    while (ob_get_level()) ob_end_clean();
    readfile($f);
    exit;
}

// Multiple files: create a temporary zip and stream
$zipName = sys_get_temp_dir() . '/ticket_' . $ticketId . '_templates_' . uniqid() . '.zip';
$zip = new ZipArchive();
if ($zip->open($zipName, ZipArchive::CREATE) !== TRUE) {
    http_response_code(500);
    echo "Could not create temporary archive.";
    exit;
}

foreach ($files as $file) {
    $fs = $file['path'];
    $meta = $file['meta'];
    $entryName = basename($fs);
    // prepend template name to avoid duplicate names
    $prefix = preg_replace('/[^A-Za-z0-9._-]/', '_', ($meta['template_name'] ?? 'template'));
    $entryName = $prefix . '_' . $entryName;
    $zip->addFile($fs, $entryName);
}
$zip->close();

if (!is_file($zipName)){
    http_response_code(500);
    echo "Failed to build archive.";
    exit;
}

$outName = 'ticket_' . $ticketId . '_templates.zip';
header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $outName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($zipName));
while (ob_get_level()) ob_end_clean();
readfile($zipName);

// cleanup
@unlink($zipName);
exit;

?>
