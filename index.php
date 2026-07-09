<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config.php';

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: admin/login.php');
    exit;
}

$statusFile = STATUS_FILE;
$data = json_decode(file_get_contents($statusFile), true);
$data = is_array($data) ? $data : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain = $_POST['domain'] ?? '';
    $action = $_POST['action'] ?? '';

    if (isset($data[$domain])) {
        if ($action === 'aktif') {
            $data[$domain]['status'] = 'aktif';
            $data[$domain]['activated_at'] = date('Y-m-d');
            $data[$domain]['due_date'] = date('Y-m-d', strtotime('+30 days'));
        } elseif ($action === 'extend') {
            $currentDue = $data[$domain]['due_date'] ?? null;
            $baseDate = new DateTimeImmutable('now');
            if ($currentDue) {
                $parsedDue = DateTimeImmutable::createFromFormat('Y-m-d', $currentDue)
                    ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $currentDue);
                if ($parsedDue && $parsedDue > $baseDate) {
                    $baseDate = $parsedDue;
                }
            }
            $data[$domain]['due_date'] = $baseDate->modify('+30 days')->format('Y-m-d');
        } else {
            $data[$domain]['status'] = 'isolir';
        }
        file_put_contents($statusFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    header("Location: index.php");
    exit;
}

function days_left($due_date_str) {
    if (empty($due_date_str)) {
        return null;
    }

    $due = DateTimeImmutable::createFromFormat('Y-m-d', $due_date_str)
        ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $due_date_str);

    if (!$due) {
        return null;
    }

    return (int) (new DateTimeImmutable('now'))->diff($due)->format('%r%a');
}

$stats = [
    'total' => count($data),
    'aktif' => 0,
    'isolir' => 0,
    'overdue' => 0,
];

foreach ($data as $item) {
    $status = $item['status'] ?? 'isolir';
    $daysLeft = days_left($item['due_date'] ?? null);

    if ($status === 'aktif') {
        $stats['aktif']++;
        if ($daysLeft !== null && $daysLeft < 0) {
            $stats['overdue']++;
        }
    } else {
        $stats['isolir']++;
    }
}

$today = new DateTimeImmutable('now');
$adminUser = $_SESSION['admin_user'] ?? ADMIN_USER;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Utama | WarungCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="admin/assets/css/app.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg topbar navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="brand-mark"><i class="bi bi-router-fill"></i></span>
                <span>WarungCloud</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link active">Panel Utama</a>
                    </li>
                    <li class="nav-item">
                        <a href="admin/dashboard.php" class="nav-link">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="admin/management.php" class="nav-link">Manajemen Data</a>
                    </li>
                    <li class="nav-item my-2 my-lg-0">
                        <span class="user-chip">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($adminUser) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="admin/logout.php" class="btn btn-outline-light btn-sm px-3">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="app-shell">
        <div class="container">
            <section class="page-hero mb-4">
                <div class="hero-body">
                    <span class="eyebrow">
                        <i class="bi bi-window-stack"></i>
                        Panel Ringkas
                    </span>
                    <h1 class="hero-title">Kontrol cepat untuk status layanan pelanggan.</h1>
                    <p class="hero-copy">
                        Halaman ini cocok untuk aksi cepat saat admin hanya perlu melihat status,
                        mengaktifkan kembali pelanggan, atau melakukan isolir tanpa masuk ke form detail.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <span class="soft-badge"><i class="bi bi-calendar-event"></i> <?= $today->format('d M Y') ?></span>
                        <span class="soft-badge"><i class="bi bi-hdd-stack"></i> <?= $stats['total'] ?> total pelanggan</span>
                    </div>
                </div>
            </section>

            <section class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="metric-card">
                            <span class="metric-icon icon-primary"><i class="bi bi-hdd-network"></i></span>
                            <div class="metric-value"><?= $stats['total'] ?></div>
                            <p class="metric-label">Total domain</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <span class="metric-icon icon-success"><i class="bi bi-check2-circle"></i></span>
                            <div class="metric-value"><?= $stats['aktif'] ?></div>
                            <p class="metric-label">Layanan aktif</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <span class="metric-icon icon-danger"><i class="bi bi-exclamation-octagon"></i></span>
                            <div class="metric-value"><?= $stats['isolir'] + $stats['overdue'] ?></div>
                            <p class="metric-label">Butuh perhatian</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="app-card p-4">
                <div class="card-title-row flex-wrap">
                    <div>
                        <h2 class="section-title">Aksi cepat pelanggan</h2>
                        <p class="section-subtitle">Kelola perubahan status langsung dari tabel sederhana ini.</p>
                    </div>
                    <div class="search-box" style="min-width: min(100%, 320px);">
                        <i class="bi bi-search"></i>
                        <input type="search" id="quickSearch" class="form-control" placeholder="Cari domain atau status...">
                    </div>
                </div>

                <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0" id="quickTable">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Countdown</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $domain => $client): ?>
                                    <?php
                                        $status = $client['status'] ?? 'isolir';
                                        $daysLeft = days_left($client['due_date'] ?? null);
                                        if ($status === 'aktif') {
                                            $statusBadge = '<span class="status-pill status-aktif"><i class="bi bi-check-circle-fill"></i> Aktif</span>';
                                            if ($daysLeft === null) {
                                                $countBadge = '<span class="status-pill status-neutral"><i class="bi bi-dash-circle"></i> Belum ada tanggal</span>';
                                            } elseif ($daysLeft >= 0) {
                                                $countBadge = '<span class="status-pill ' . ($daysLeft <= 7 ? 'status-warning' : 'status-aktif') . '"><i class="bi bi-calendar-check"></i> ' . htmlspecialchars((string) $daysLeft) . ' hari</span>';
                                            } else {
                                                $countBadge = '<span class="status-pill status-isolir"><i class="bi bi-exclamation-octagon"></i> Telat ' . htmlspecialchars((string) abs($daysLeft)) . ' hari</span>';
                                            }
                                        } else {
                                            $statusBadge = '<span class="status-pill status-isolir"><i class="bi bi-slash-circle-fill"></i> Isolir</span>';
                                            $countBadge = '<span class="status-pill status-neutral"><i class="bi bi-pause-circle"></i> Nonaktif</span>';
                                        }
                                    ?>
                                    <tr data-search="<?= htmlspecialchars(strtolower($domain . ' ' . $status . ' ' . ($client['due_date'] ?? ''))) ?>">
                                        <td>
                                            <span class="domain-title"><?= htmlspecialchars($domain) ?></span>
                                            <span class="domain-meta">Kontrol cepat layanan</span>
                                        </td>
                                        <td><?= $statusBadge ?></td>
                                        <td><?= htmlspecialchars($client['due_date'] ?? '-') ?></td>
                                        <td><?= $countBadge ?></td>
                                        <td>
                                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                                <form method="POST" class="m-0">
                                                    <input type="hidden" name="domain" value="<?= htmlspecialchars($domain) ?>">
                                                    <input type="hidden" name="action" value="<?= $status === 'aktif' ? 'isolir' : 'aktif' ?>">
                                                    <button type="submit" class="btn btn-sm <?= $status === 'aktif' ? 'btn-soft-warning' : 'btn-success' ?>">
                                                        <i class="bi <?= $status === 'aktif' ? 'bi-slash-circle' : 'bi-check2-circle' ?> me-1"></i>
                                                        <?= $status === 'aktif' ? 'Isolir' : 'Aktifkan' ?>
                                                    </button>
                                                </form>
                                                <form method="POST" class="m-0">
                                                    <input type="hidden" name="domain" value="<?= htmlspecialchars($domain) ?>">
                                                    <input type="hidden" name="action" value="extend">
                                                    <button type="submit" class="btn btn-sm btn-soft-primary">
                                                        <i class="bi bi-calendar-plus me-1"></i>Perpanjang
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($data)): ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="bi bi-inboxes"></i>
                                                <h3 class="h5 mb-2">Belum ada data pelanggan</h3>
                                                <p class="mb-0">Tambah data lewat menu manajemen untuk mulai mengelola billing.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('quickSearch');
        const rows = document.querySelectorAll('#quickTable tbody tr[data-search]');

        if (!searchInput || !rows.length) {
            return;
        }

        searchInput.addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();
            rows.forEach(function (row) {
                row.style.display = row.dataset.search.includes(keyword) ? '' : 'none';
            });
        });
    });
    </script>
</body>
</html>
