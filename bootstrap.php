<?php

// Pakai direktori session lokal project agar login tidak bergantung pada konfigurasi server.
$projectSessionPath = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';

if (!is_dir($projectSessionPath)) {
    mkdir($projectSessionPath, 0777, true);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_save_path($projectSessionPath);
    session_start();
}
