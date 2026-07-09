<?php
$domain = $_SERVER['HTTP_HOST'];
$api_url = "https://billing.madignet.cloud/api.php?domain=" . urlencode($domain);
$response = @file_get_contents($api_url);
if ($response !== false) {
    $data = json_decode($response, true);
    if (isset($data['status']) && $data['status'] === 'isolir') {
        header("Location: https://billing.madignet.cloud/isolir.html");
        exit;
    }
}
