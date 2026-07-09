<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/billing_helpers.php';
header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


$domain = $_GET['domain'] ?? '';
if (!$domain) {
    echo json_encode(['error' => 'domain parameter missing']);
    exit;
}

$statusData = json_decode(file_get_contents(STATUS_FILE), true);
$data = $statusData[$domain] ?? null;

if (!$data) {
    echo json_encode(['status' => 'isolir', 'reason' => 'domain not found']);
    exit;
}

// Auto check jatuh tempo
$today = new DateTimeImmutable('today');
$dueDate = parse_billing_date($data['due_date'] ?? null);
if ($dueDate && $today > $dueDate) {
    $data['status'] = 'isolir';
}

echo json_encode([
    'domain' => $domain,
    'status' => $data['status'],
    'due_date' => $data['due_date'],
]);
