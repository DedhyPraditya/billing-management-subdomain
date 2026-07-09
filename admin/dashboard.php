<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../billing_helpers.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Baca file status.json
$statusFile = STATUS_FILE;
$statusData = json_decode(file_get_contents($statusFile), true);
if (!is_array($statusData)) {
    $statusData = [];
}

$today = new DateTimeImmutable('now');
// auto-isolir kalau lewat due_date
$changed = false;
foreach ($statusData as $domain => $info) {
    // pastikan struktur minimal
    if (!is_array($info)) {
        $statusData[$domain] = [
            'status' => 'isolir',
            'activated_at' => null,
            'due_date' => null
        ];
        $changed = true;
        continue;
    }
    $due = $info['due_date'] ?? null;
    $status = $info['status'] ?? 'isolir';

    if ($due) {
        // normalize date string
        $dueDateObj = parse_billing_date($due);
        if ($dueDateObj && $status === 'aktif' && $today > $dueDateObj) {
            $statusData[$domain]['status'] = 'isolir';
            $changed = true;
        }
    }
}

// Simpan kalau ada perubahan auto-isolir
if ($changed) {
    file_put_contents($statusFile, json_encode($statusData, JSON_PRETTY_PRINT));
}

// Proses form POST (aksi dari admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain = $_POST['domain'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($domain && isset($statusData[$domain])) {
        if ($action === 'aktif') {
            $statusData[$domain]['status'] = 'aktif';
            $statusData[$domain]['activated_at'] = date('Y-m-d');
            $statusData[$domain]['due_date'] = calculate_due_date_from_activation($statusData[$domain]['activated_at']);
        } elseif ($action === 'isolir') {
            $statusData[$domain]['status'] = 'isolir';
            // keep due_date as-is (optional: set activated_at null)
        } elseif ($action === 'extend') {
            $statusData[$domain]['due_date'] = calculate_extended_due_date($statusData[$domain]['due_date'] ?? null, $today);
        }
        file_put_contents($statusFile, json_encode($statusData, JSON_PRETTY_PRINT));
    }
    header('Location: dashboard.php');
    exit;
}

// Helper: hitung hari tersisa (bisa negatif jika lewat)
function days_left($due_date_str) {
    if (empty($due_date_str)) return null;
    $due = parse_billing_date($due_date_str);
    if (!$due) return null;
    $now = new DateTimeImmutable('now');
    $diff = $now->diff($due);
    $days = (int)$diff->format('%r%a'); // signed days
    return $days;
}

$stats = [
    'total' => count($statusData),
    'aktif' => 0,
    'isolir' => 0,
    'due_soon' => 0,
    'overdue' => 0,
];

foreach ($statusData as $info) {
    $status = $info['status'] ?? 'isolir';
    $daysLeft = days_left($info['due_date'] ?? null);

    if ($status === 'aktif') {
        $stats['aktif']++;
        if ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7) {
            $stats['due_soon']++;
        }
        if ($daysLeft !== null && $daysLeft < 0) {
            $stats['overdue']++;
        }
    } else {
        $stats['isolir']++;
    }
}

$adminUser = $_SESSION['admin_user'] ?? ADMIN_USER;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard | Billing Madignet</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<nav class="navbar navbar-expand-lg topbar navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <span class="brand-mark"><i class="bi bi-router-fill"></i></span>
            <span>WarungCloud</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="management.php" class="nav-link">Manajemen Data</a>
                </li>
                <li class="nav-item my-2 my-lg-0">
                    <span class="user-chip">
                        <i class="bi bi-person-circle"></i>
                        <?= htmlspecialchars($adminUser) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-outline-light btn-sm px-3">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="app-shell">
    <div class="container">
        <section class="app-card page-intro mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <span class="eyebrow">
                        <i class="bi bi-activity"></i>
                        Dashboard
                    </span>
                    <h1 class="page-intro-title">Ringkasan status pelanggan hari ini</h1>
                    <p class="page-intro-copy">
                        Pantau pelanggan aktif, layanan yang mendekati jatuh tempo, dan akun isolir dari satu tampilan ringkas.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="soft-badge"><i class="bi bi-calendar-event"></i> <?= $today->format('d M Y') ?></span>
                    <span class="soft-badge"><i class="bi bi-hdd-network"></i> <?= $stats['total'] ?> domain</span>
                    <span class="soft-badge"><i class="bi bi-lightning-charge"></i> <?= $stats['aktif'] ?> aktif</span>
                </div>
            </div>
        </section>

        <section class="mb-4">
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="metric-card">
                        <span class="metric-icon icon-primary"><i class="bi bi-hdd-stack"></i></span>
                        <div class="metric-value"><?= $stats['total'] ?></div>
                        <p class="metric-label">Total domain</p>
                        <p class="metric-note">Seluruh data pelanggan yang tercatat saat ini.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="metric-card">
                        <span class="metric-icon icon-success"><i class="bi bi-check2-circle"></i></span>
                        <div class="metric-value"><?= $stats['aktif'] ?></div>
                        <p class="metric-label">Status aktif</p>
                        <p class="metric-note">Pelanggan yang sedang berjalan normal.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="metric-card">
                        <span class="metric-icon icon-warning"><i class="bi bi-alarm"></i></span>
                        <div class="metric-value"><?= $stats['due_soon'] ?></div>
                        <p class="metric-label">Jatuh tempo dekat</p>
                        <p class="metric-note">Aktif tetapi tinggal 7 hari atau kurang.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="metric-card">
                        <span class="metric-icon icon-danger"><i class="bi bi-shield-x"></i></span>
                        <div class="metric-value"><?= $stats['isolir'] ?></div>
                        <p class="metric-label">Status isolir</p>
                        <p class="metric-note">Butuh aktivasi ulang atau tindak lanjut admin.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="app-card p-4">
            <div class="card-title-row flex-wrap">
                <div>
                    <h2 class="section-title">Daftar pelanggan</h2>
                    <p class="section-subtitle">Gunakan pencarian untuk menemukan domain dan lakukan aksi langsung dari tabel.</p>
                </div>
                <div class="search-box" style="min-width: min(100%, 320px);">
                    <i class="bi bi-search"></i>
                    <input type="search" id="clientSearch" class="form-control" placeholder="Cari domain atau status...">
                </div>
            </div>

            <div class="table-wrap">
                <div class="table-responsive">
                    <table class="table table-modern align-middle" id="clientTable">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Status</th>
                                <th>Aktivasi</th>
                                <th>Jatuh Tempo</th>
                                <th>Countdown</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($statusData as $domain => $info):
                            $status = $info['status'] ?? 'isolir';
                            $activated = $info['activated_at'] ?? '-';
                            $due = $info['due_date'] ?? '-';
                            $daysLeft = days_left($info['due_date'] ?? null);

                            if ($status === 'aktif') {
                                $statusLabel = '<span class="status-pill status-aktif"><i class="bi bi-check-circle-fill"></i> Aktif</span>';
                                if ($daysLeft === null) {
                                    $countLabel = '<span class="status-pill status-neutral"><i class="bi bi-dash-circle"></i> Belum ada tanggal</span>';
                                } elseif ($daysLeft > 7) {
                                    $countLabel = '<span class="status-pill status-aktif"><i class="bi bi-calendar-check"></i> ' . htmlspecialchars((string) $daysLeft) . ' hari</span>';
                                } elseif ($daysLeft >= 0) {
                                    $countLabel = '<span class="status-pill status-warning"><i class="bi bi-alarm"></i> ' . htmlspecialchars((string) $daysLeft) . ' hari</span>';
                                } else {
                                    $countLabel = '<span class="status-pill status-isolir"><i class="bi bi-exclamation-octagon"></i> Telat ' . htmlspecialchars((string) abs($daysLeft)) . ' hari</span>';
                                }
                            } else {
                                $statusLabel = '<span class="status-pill status-isolir"><i class="bi bi-slash-circle-fill"></i> Isolir</span>';
                                $countLabel = '<span class="status-pill status-neutral"><i class="bi bi-pause-circle"></i> Nonaktif</span>';
                            }
                        ?>
                            <tr data-search="<?= htmlspecialchars(strtolower($domain . ' ' . $status . ' ' . $activated . ' ' . $due)) ?>">
                                <td>
                                    <span class="domain-title"><?= htmlspecialchars($domain) ?></span>
                                    <span class="domain-meta">Pelanggan terdaftar</span>
                                </td>
                                <td><?= $statusLabel ?></td>
                                <td><?= htmlspecialchars($activated) ?></td>
                                <td><?= htmlspecialchars($due) ?></td>
                                <td><?= $countLabel ?></td>
                                <td>
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <form method="post" class="m-0">
                                            <input type="hidden" name="domain" value="<?= htmlspecialchars($domain) ?>">
                                            <input type="hidden" name="action" value="<?= $status === 'aktif' ? 'isolir' : 'aktif' ?>">
                                            <button class="btn btn-sm <?= $status === 'aktif' ? 'btn-soft-warning' : 'btn-success' ?>">
                                                <i class="bi <?= $status === 'aktif' ? 'bi-slash-circle' : 'bi-check2-circle' ?> me-1"></i>
                                                <?= $status === 'aktif' ? 'Isolir' : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                        <form method="post" class="m-0">
                                            <input type="hidden" name="domain" value="<?= htmlspecialchars($domain) ?>">
                                            <input type="hidden" name="action" value="extend">
                                            <button class="btn btn-sm btn-soft-primary" name="extend" value="1">
                                                <i class="bi bi-calendar-plus me-1"></i>Perpanjang 30 Hari
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($statusData)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="bi bi-inboxes"></i>
                                        <h3 class="h5 mb-2">Belum ada data pelanggan</h3>
                                        <p class="mb-0">Tambahkan data baru melalui menu manajemen agar dashboard mulai terisi.</p>
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
    const searchInput = document.getElementById('clientSearch');
    const rows = document.querySelectorAll('#clientTable tbody tr[data-search]');

    if (!searchInput || !rows.length) {
        return;
    }

    searchInput.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        rows.forEach(function (row) {
            const match = row.dataset.search.includes(keyword);
            row.style.display = match ? '' : 'none';
        });
    });
});
</script>
</body>
</html>
