<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$data = [
    "raskanet.madignet.cloud" => [
        "status" => "aktif",
        "activated_at" => "2025-10-13",
        "due_date" => "2025-11-18"
    ],
    "afzalnet.madignet.cloud" => [
        "status" => "aktif",
        "activated_at" => "2025-10-13",
        "due_date" => "2025-11-18"
    ],
    "wanmik.madignet.cloud" => [
        "status" => "aktif",
        "activated_at" => "2025-10-13",
        "due_date" => "2025-11-14"
    ],
    "ponputamalia.madignet.cloud" => [
        "status" => "aktif",
        "activated_at" => "2025-10-13",
        "due_date" => "2025-11-18"
    ]
];

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
