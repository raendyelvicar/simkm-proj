<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'guest';
$pageTitle = $pageTitle ?? 'Dashboard SIMKM';
$roleLabel = ucfirst($role);
$username = $_SESSION['username'] ?? 'Pengguna';
$displayName = $username;
$avatarPhoto = '';
$accountMeta = '';
if (!empty($_SESSION['user_id'])) {
    $currentUser = (new \App\Repositories\UserRepository())->find((int) $_SESSION['user_id']);
    $avatarPhoto = $currentUser ? profile_photo_url($currentUser->profile) : '';

    if ($currentUser) {
        $displayName = $currentUser->fullName !== '' ? $currentUser->fullName : ($currentUser->name !== '' ? $currentUser->name : $username);
    }

    if ($currentUser && $role === 'student') {
        $metaParts = [];
        if ($currentUser->student_number !== '') {
            $metaParts[] = 'NPM ' . $currentUser->student_number;
        }
        if ($currentUser->faculty !== '') {
            $metaParts[] = $currentUser->faculty;
        }
        $accountMeta = implode(' · ', $metaParts);
    } elseif ($role === 'counselor') {
        $counselorProfile = (new \App\Repositories\CounselorRepository())->find((int) $_SESSION['user_id']);
        if (!empty($counselorProfile['registration_number'])) {
            $accountMeta = 'NIP/NIK ' . $counselorProfile['registration_number'];
        }
    }
}
$avatarInitial = strtoupper(substr($displayName, 0, 1));

$currentPath = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

function navActive(string $path, string $currentPath): string
{
    if ($path === '/') {
        return $currentPath === '/' ? 'active' : '';
    }

    return ($currentPath === $path || str_starts_with($currentPath, $path . '/')) ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --sidebar: #111827;
            --surface: #ffffff;
            --border: #e5e7eb;
            --text: #1f2937;
            --muted: #6b7280;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7fb;
            color: var(--text);
        }

        .topbar {
            height: 70px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 20;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        .topbar-brand {
            line-height: 1.15;
        }

        .topbar-page-title {
            font-size: 0.78rem;
            color: var(--muted);
        }

        .account-toggle {
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 4px 12px 4px 4px;
        }

        .account-toggle::after {
            margin-left: 10px;
        }

        .account-toggle-meta {
            font-size: 0.72rem;
            color: var(--muted);
            font-weight: 400;
        }

        .avatar-circle {
            position: relative;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            background: var(--primary);
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .avatar-circle img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .account-menu {
            min-width: 220px;
        }

        .app-shell {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--sidebar), #1f2937);
            color: white;
            padding: 20px 14px;
            transition: width 0.25s ease;
        }

        .sidebar.collapsed {
            width: 76px;
        }

        .sidebar .brand {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 24px;
            display: block;
            color: white;
            text-decoration: none;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #d1d5db;
            padding: 10px 12px;
            border-radius: 10px;
            text-decoration: none;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .sidebar.collapsed .nav-label {
            display: none;
        }

        .sidebar .nav-group {
            margin-bottom: 8px;
        }

        .sidebar .nav-group-toggle {
            cursor: pointer;
            list-style: none;
            margin-bottom: 0;
        }

        .sidebar .nav-group-toggle::-webkit-details-marker,
        .sidebar .nav-group-toggle::marker {
            display: none;
            content: '';
        }

        .sidebar .nav-group-toggle .nav-group-chevron {
            margin-left: auto;
            font-size: 0.65rem;
            transition: transform 0.2s ease;
        }

        .sidebar .nav-group[open] > .nav-group-toggle .nav-group-chevron {
            transform: rotate(180deg);
        }

        .sidebar .nav-group[open] > .nav-group-toggle {
            color: white;
        }

        .sidebar .nav-group-children {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding-left: 16px;
            margin: 4px 0 8px;
            border-left: 1px solid rgba(255, 255, 255, 0.12);
        }

        .sidebar .nav-group-children .nav-link {
            padding: 8px 12px;
            font-size: 0.9em;
            margin-bottom: 0;
        }

        .sidebar.collapsed .nav-group-children,
        .sidebar.collapsed .nav-group-toggle .nav-group-chevron {
            display: none;
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .main-content {
            flex: 1;
            padding: 24px;
        }

        .card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        /* Shared clickable column-header link, used by every sortable table app-wide
           (see sort_link() in src/Helpers/functions.php). */
        a.sortable-th {
            color: inherit;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
        }

        a.sortable-th:hover {
            color: var(--primary);
        }

        a.sortable-th.active {
            color: var(--primary);
            font-weight: 700;
        }

        .main-footer {
            border-top: 1px solid var(--border);
            background: var(--surface);
            padding: 16px 24px;
            text-align: center;
            color: var(--muted);
        }

        @media (max-width: 768px) {
            .topbar {
                padding: 0 16px;
            }

            .app-shell {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 16px;
            }
        }
    </style>
    <?php if (!empty($extraStyles)): ?>
        <style>
            <?= $extraStyles ?>
        </style>
    <?php endif; ?>
</head>

<body>
    <header class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" id="toggleSidebar" type="button">☰</button>
            <div class="topbar-brand">
                <div class="fw-semibold">SIMKM</div>
                <div class="topbar-page-title"><?= htmlspecialchars($pageTitle) ?></div>
            </div>
        </div>

        <div class="dropdown">
            <button class="btn btn-light account-toggle dropdown-toggle d-flex align-items-center gap-2" type="button"
                id="accountMenuToggle" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar-circle">
                    <?php if ($avatarPhoto): ?>
                        <img src="<?= htmlspecialchars($avatarPhoto) ?>" alt="" onerror="this.remove()">
                    <?php endif; ?>
                    <?= htmlspecialchars($avatarInitial) ?>
                </span>
                <span class="d-none d-sm-flex flex-column align-items-start lh-1">
                    <span class="fw-semibold small"><?= htmlspecialchars($displayName) ?></span>
                    <?php if ($accountMeta): ?>
                        <span class="account-toggle-meta"><?= htmlspecialchars($accountMeta) ?></span>
                    <?php endif; ?>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end account-menu shadow-sm" aria-labelledby="accountMenuToggle">
                <li>
                    <h6 class="dropdown-header">
                        <?= htmlspecialchars($displayName) ?>
                        <br><span class="fw-normal">Masuk sebagai <?= htmlspecialchars($roleLabel) ?></span>
                        <?php if ($accountMeta): ?>
                            <br><span class="fw-normal"><?= htmlspecialchars($accountMeta) ?></span>
                        <?php endif; ?>
                    </h6>
                </li>
                <li><a class="dropdown-item" href="/profile">👤 Profil Saya</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="/logout" class="m-0">
                        <button type="submit" class="dropdown-item text-danger">🚪 Keluar</button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <script>
                if (localStorage.getItem('simkm.sidebarCollapsed') === '1') {
                    document.getElementById('sidebar').classList.add('collapsed');
                }
            </script>
            <?php
            // roles omitted => visible to everyone logged in. Items with 'children' render
            // as a collapsible group instead of a direct link. Order here is render order.
            function navGroupHasActiveChild(array $children, string $currentPath): bool
            {
                foreach ($children as $child) {
                    if (navActive($child['path'], $currentPath) === 'active') {
                        return true;
                    }
                }

                return false;
            }

            $navItems = [
                ['path' => '/dashboard', 'icon' => '🏠', 'label' => 'Dashboard'],
                ['path' => '/assessment', 'icon' => '📝', 'label' => 'Assessment', 'roles' => ['student', 'counselor']],
                ['path' => '/article', 'icon' => '📰', 'label' => 'Artikel'],
                [
                    'icon' => '💬', 'label' => 'Konsultasi', 'roles' => ['counselor'],
                    'children' => [
                        ['path' => '/consultations', 'icon' => '📨', 'label' => 'Konsultasi Masuk'],
                        ['path' => '/booking-requests', 'icon' => '📥', 'label' => 'Permintaan Booking'],
                        ['path' => '/schedule', 'icon' => '📅', 'label' => 'Jadwal Konsultasi'],
                        ['path' => '/shared-diaries', 'icon' => '📔', 'label' => 'Diary Dibagikan'],
                    ],
                ],
                ['path' => '/tips', 'icon' => '💡', 'label' => 'Tips Harian', 'roles' => ['counselor']],
                ['path' => '/diary', 'icon' => '📖', 'label' => 'Diary', 'roles' => ['student']],
                ['path' => '/self-help', 'icon' => '🌱', 'label' => 'Self Help', 'roles' => ['student']],
                [
                    'icon' => '💬', 'label' => 'Konsultasi', 'roles' => ['student'],
                    'children' => [
                        ['path' => '/counselor', 'icon' => '🧑‍⚕️', 'label' => 'Cari Konselor'],
                        ['path' => '/bookings', 'icon' => '📅', 'label' => 'Booking Saya'],
                    ],
                ],
                [
                    'icon' => '🛠️', 'label' => 'Administrasi', 'roles' => ['admin'],
                    'children' => [
                        ['path' => '/students', 'icon' => '🎓', 'label' => 'Data Mahasiswa'],
                        ['path' => '/admin/counselors', 'icon' => '🧑‍⚕️', 'label' => 'Kelola Konselor'],
                        ['path' => '/admin/approvals', 'icon' => '✅', 'label' => 'Persetujuan Akun'],
                        ['path' => '/admin/booking-cancellations', 'icon' => '🚫', 'label' => 'Persetujuan Pembatalan Booking'],
                        ['path' => '/admin/settings', 'icon' => '⚙️', 'label' => 'Pengaturan'],
                    ],
                ],
                ['path' => '/laporan', 'icon' => '📊', 'label' => 'Laporan'],
                ['path' => '/profile', 'icon' => '👤', 'label' => 'Profil'],
            ];
            ?>
            <nav>
                <?php foreach ($navItems as $item): ?>
                    <?php if (!empty($item['roles']) && !in_array($role, $item['roles'], true)): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php if (!empty($item['children'])): ?>
                        <details class="nav-group" <?= navGroupHasActiveChild($item['children'], $currentPath) ? 'open' : '' ?>>
                            <summary class="nav-link nav-group-toggle">
                                <span><?= $item['icon'] ?></span>
                                <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
                                <span class="nav-group-chevron">▾</span>
                            </summary>
                            <div class="nav-group-children">
                                <?php foreach ($item['children'] as $child): ?>
                                    <a href="<?= $child['path'] ?>" class="nav-link <?= navActive($child['path'], $currentPath) ?>">
                                        <span><?= $child['icon'] ?></span>
                                        <span class="nav-label"><?= htmlspecialchars($child['label']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php else: ?>
                        <a href="<?= $item['path'] ?>" class="nav-link <?= navActive($item['path'], $currentPath) ?>">
                            <span><?= $item['icon'] ?></span>
                            <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="content-wrapper">
            <main class="main-content">
                <?php if (!empty($content)) : ?>
                    <?= $content ?>
                <?php else : ?>
                    <div class="card p-4">
                        <h2 class="h4 mb-2">Halaman Utama</h2>
                        <p class="text-muted mb-0">Tempat isi konten utama aplikasi Anda.</p>
                    </div>
                <?php endif; ?>
            </main>

            <footer class="main-footer">
                <strong>© <?= date('Y') ?> SIMKM</strong>
            </footer>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive"
                aria-atomic="true" data-bs-autohide="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body"><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive"
                aria-atomic="true" data-bs-autohide="true" data-bs-delay="6000">
                <div class="d-flex">
                    <div class="toast-body"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('toggleSidebar');
            const sidebar = document.getElementById('sidebar');
            const STORAGE_KEY = 'simkm.sidebarCollapsed';

            if (!toggleButton || !sidebar) {
                return;
            }

            const tooltips = Array.from(sidebar.querySelectorAll('.nav-link')).map(function(link) {
                const label = link.querySelector('.nav-label');
                link.setAttribute('data-bs-toggle', 'tooltip');
                link.setAttribute('data-bs-placement', 'right');
                link.setAttribute('title', label ? label.textContent.trim() : '');
                return new bootstrap.Tooltip(link);
            });

            function syncTooltips() {
                const collapsed = sidebar.classList.contains('collapsed');
                tooltips.forEach(function(tip) {
                    tip.hide();
                    collapsed ? tip.enable() : tip.disable();
                });
            }

            syncTooltips();

            toggleButton.addEventListener('click', function() {
                const collapsed = sidebar.classList.toggle('collapsed');
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
                syncTooltips();
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toast-container .toast').forEach(function(el) {
                new bootstrap.Toast(el).show();
            });
        });
    </script>
</body>

</html>