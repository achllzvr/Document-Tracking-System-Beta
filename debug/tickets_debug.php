<?php
// Debug page: /PRISM/debug/tickets_debug.php
// Shows resolved HEI id, table schemas, sample ticket rows, and outputs from DB helper functions.

require_once __DIR__ . '/../includes/dev_logs.php';
require_once __DIR__ . '/../includes/hei_protect.php';
require_once __DIR__ . '/../classes/database.php';

$db = new database();
$conn = $db->opencon();

$heiId = $_SESSION['heiID'] ?? (isset($_GET['hei_id']) ? (int)$_GET['hei_id'] : null);

header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html><head><meta charset="utf-8"><title>PRISM — Tickets Debug</title>
<style>body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial,sans-serif;padding:18px}pre{background:#f6f7fb;border:1px solid #e6e9f2;padding:12px;overflow:auto}</style>
</head><body>
<h1>PRISM — Tickets Debug</h1>
<p>This page is read-only and protected by the HEI protector. Use it to inspect DB schema and helper outputs.</p>
<h2>Resolved HEI id</h2>
<pre><?php echo htmlspecialchars(var_export($heiId, true)); ?></pre>

<h2>SHOW CREATE TABLE `tickets`</h2>
<pre><?php
try {
    $row = $conn->query("SHOW CREATE TABLE `tickets`")->fetch(PDO::FETCH_ASSOC);
    echo htmlspecialchars(print_r($row, true));
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?></pre>

<h2>SHOW CREATE TABLE `ticket_comments`</h2>
<pre><?php
try {
    $row = $conn->query("SHOW CREATE TABLE `ticket_comments`")->fetch(PDO::FETCH_ASSOC);
    echo htmlspecialchars(print_r($row, true));
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?></pre>

<h2>Sample tickets for HEI (LIMIT 10)</h2>
<pre><?php
try {
    if ($heiId) {
        $stmt = $conn->prepare("SELECT ticket_ID, hei_ID, ticket_title, ticket_category, ticket_priority, ticket_status, ticket_due_date FROM tickets WHERE hei_ID = ? ORDER BY ticket_created_at DESC LIMIT 10");
        $stmt->execute([$heiId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo htmlspecialchars(print_r($rows, true));
    } else {
        echo "No heiId provided in session or ?hei_id.\nTo test, provide ?hei_id=... in the URL or ensure you're logged in as an HEI user.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?></pre>

<h2>Output of getTicketsForHEI(heiId, [], 1, 25)</h2>
<pre><?php
try {
    if ($heiId) {
        $ticketData = $db->getTicketsForHEI($heiId, [], 1, 25);
        echo htmlspecialchars(print_r($ticketData, true));
    } else {
        echo "No heiId set.\n";
    }
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?></pre>

<h2>Sample comments (per ticket)</h2>
<pre><?php
try {
    if ($heiId && !empty($rows)) {
        foreach ($rows as $t) {
            $tid = $t['ticket_ID'];
            echo "-- Comments for ticket_ID={$tid} --\n";
            $comments = $db->getCommentsForTicket($tid);
            echo htmlspecialchars(print_r($comments, true));
            echo "\n";
        }
    } else {
        echo "No tickets found to show comments.\n";
    }
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?></pre>

<p>Notes:
<ul>
<li>If the ticket rows are present but `getTicketsForHEI()` returned empty, the issue may be in the helper's WHERE/filters or binding. Use the output above to compare.</li>
<li>If comments appear empty, `ticket_comments` uses columns `comment_ID`,`ticket_ID`,`ched_user_ID`,`hei_user_ID`,`comment_desc`,`comment_timestamp` — helpers must map against these names.</li>
</ul>
</p>
</body></html>