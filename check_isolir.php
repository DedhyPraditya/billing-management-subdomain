<?php

function get_current_host(): string
{
    $host = $_SERVER['HTTP_X_FORWARDED_HOST']
        ?? $_SERVER['HTTP_HOST']
        ?? $_SERVER['SERVER_NAME']
        ?? '';

    if (str_contains($host, ',')) {
        $host = trim(explode(',', $host)[0]);
    }

    // Buang port agar cocok dengan key domain di status.json.
    if (substr_count($host, ':') === 1) {
        $host = explode(':', $host)[0];
    }

    return strtolower(trim($host));
}

function fetch_remote_json(string $url): ?array
{
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false && function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
    }

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : null;
}

function get_current_page(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return basename((string) $path);
}

function build_local_url(string $filename): string
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    $scheme = $isHttps ? 'https' : 'http';
    $host = get_current_host();

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $scriptDir = $scriptDir === '.' ? '' : rtrim($scriptDir, '/');
    $targetPath = ($scriptDir !== '' ? $scriptDir : '') . '/' . ltrim($filename, '/');

    return $scheme . '://' . $host . $targetPath;
}

$domain = get_current_host();
if ($domain === '') {
    return;
}

$apiUrl = 'https://billing.madignet.site/api.php?domain=' . rawurlencode($domain);
$data = fetch_remote_json($apiUrl);

if (($data['status'] ?? null) !== 'isolir') {
    if (get_current_page() === 'isolir.php') {
        $loginUrl = build_local_url('admin.php?id=login');

        if (!headers_sent()) {
            header('Location: ' . $loginUrl);
            exit;
        }

        echo '<script>window.location.href=' . json_encode($loginUrl) . ';</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        exit;
    }

    return;
}

$redirectUrl = build_local_url('isolir.php');

// Pakai header jika masih memungkinkan, fallback ke JS/meta refresh kalau file ini di-include setelah output.
if (!headers_sent()) {
    header('Location: ' . $redirectUrl);
    exit;
}

echo '<script>window.location.href=' . json_encode($redirectUrl) . ';</script>';
echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') . '"></noscript>';
exit;
