<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'guest';
$pageTitle = $pageTitle ?? 'Dashboard SIMKM';
$roleLabel = ucfirst($role);
$username = $_SESSION['username'] ?? 'Pengguna';
$avatarInitial = strtoupper(substr($username, 0, 1));

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

        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
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
                <span class="avatar-circle"><?= htmlspecialchars($avatarInitial) ?></span>
                <span class="d-none d-sm-flex flex-column align-items-start lh-1">
                    <span class="fw-semibold small"><?= htmlspecialchars($username) ?></span>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end account-menu shadow-sm" aria-labelledby="accountMenuToggle">
                <li>
                    <h6 class="dropdown-header">Masuk sebagai <?= htmlspecialchars($roleLabel) ?></h6>
                </li>
                <li><a class="dropdown-item" href="/profile">👤 Profil Saya</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="/logout" class="m-0">
                        <button type="submit" class="dropdown-item text-danger">🚪 Logout</button>
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
            <nav>
                <a href="/dashboard" class="nav-link <?= navActive('/dashboard', $currentPath) ?>">
                    <span>🏠</span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="/diary" class="nav-link <?= navActive('/diary', $currentPath) ?>">
                    <span>📖</span>
                    <span class="nav-label">Diary</span>
                </a>
                <a href="/assessment" class="nav-link <?= navActive('/assessment', $currentPath) ?>">
                    <span>📝</span>
                    <span class="nav-label">Assessment</span>
                </a>
                <a href="/article" class="nav-link <?= navActive('/article', $currentPath) ?>">
                    <span>📰</span>
                    <span class="nav-label">Artikel</span>
                </a>
                <?php if ($role === 'konselor'): ?>
                    <a href="/consultations" class="nav-link <?= navActive('/consultations', $currentPath) ?>">
                        <span>💬</span>
                        <span class="nav-label">Konsultasi Masuk</span>
                    </a>
                <?php else: ?>
                    <a href="/counselor" class="nav-link <?= navActive('/counselor', $currentPath) ?>">
                        <span>💬</span>
                        <span class="nav-label">Konselor</span>
                    </a>
                <?php endif; ?>
                <?php if (in_array($role, ['admin', 'konselor'], true)): ?>
                    <a href="/students" class="nav-link <?= navActive('/students', $currentPath) ?>">
                        <span>🎓</span>
                        <span class="nav-label">Data Mahasiswa</span>
                    </a>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <a href="/admin/counselors" class="nav-link <?= navActive('/admin/counselors', $currentPath) ?>">
                        <span>🧑‍⚕️</span>
                        <span class="nav-label">Kelola Konselor</span>
                    </a>
                    <a href="/admin/approvals" class="nav-link <?= navActive('/admin/approvals', $currentPath) ?>">
                        <span>✅</span>
                        <span class="nav-label">Persetujuan Akun</span>
                    </a>
                <?php endif; ?>
                <a href="/profile" class="nav-link <?= navActive('/profile', $currentPath) ?>">
                    <span>👤</span>
                    <span class="nav-label">Profil</span>
                </a>
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