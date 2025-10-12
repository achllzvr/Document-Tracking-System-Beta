<?php
// Simple export endpoint (server-side placeholder)
// Accepts POST JSON with { filename?: string, format?: 'csv'|'json', data: [...] }
// Writes a file to ../exports/ and returns { status, url }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed, use POST"]);
    exit;
}

$body = file_get_contents('php://input');
if (!$body) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Empty request body"]);
    exit;
}

$payload = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON payload"]);
    exit;
}

$data = $payload['data'] ?? null;
$format = $payload['format'] ?? 'csv';
$filename = $payload['filename'] ?? 'export_' . date('Ymd_His');

if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No data array provided"]);
    exit;
}

// Ensure exports directory exists
$exportsDir = __DIR__ . '/../exports';
if (!is_dir($exportsDir)) {
    mkdir($exportsDir, 0755, true);
}

if ($format === 'csv') {
    $filepath = $exportsDir . '/' . preg_replace('/[^A-Za-z0-9_\\-]/', '_', $filename) . '.csv';
    $fp = fopen($filepath, 'w');
    if ($fp === false) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Unable to open file for writing"]);
        exit;
    }

    // write headers from keys of first row
    $first = $data[0] ?? null;
    if ($first && is_array($first)) {
        fputcsv($fp, array_keys($first));
    }
    foreach ($data as $row) {
        // normalize row to simple array of values
        if (is_array($row)) {
            // flatten associative arrays by keys used in header
            if ($first && is_array($first)) {
                $vals = [];
                foreach (array_keys($first) as $k) {
                    $vals[] = isset($row[$k]) ? $row[$k] : '';
                }
                fputcsv($fp, $vals);
            } else {
                fputcsv($fp, array_values($row));
            }
        } else {
            fputcsv($fp, [$row]);
        }
    }
    fclose($fp);
    $relative = '/PRISM/PRISM/exports/' . basename($filepath);
    echo json_encode(["status" => "ok", "message" => "Export generated", "url" => $relative]);
    exit;
} elseif ($format === 'json') {
    $filepath = $exportsDir . '/' . preg_replace('/[^A-Za-z0-9_\\-]/', '_', $filename) . '.json';
    file_put_contents($filepath, json_encode($data));
    $relative = '/PRISM/PRISM/exports/' . basename($filepath);
    echo json_encode(["status" => "ok", "message" => "Export generated", "url" => $relative]);
    exit;
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Unsupported format"]);
    exit;
}

?>
