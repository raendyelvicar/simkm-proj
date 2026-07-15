<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// ================= HELPERS =================

/**
 * Classify a mental-health assessment score into a risk level.
 * Centralized so the >=75 / >=50 thresholds only live in one place.
 *
 * @return array{label:string, badgeClass:string}
 */
function klasifikasiRisiko(?float $skor): array
{
    if ($skor === null) {
        return ['label' => 'Belum Ada', 'badgeClass' => 'bg-secondary'];
    }
    if ($skor >= 75) {
        return ['label' => 'Berat', 'badgeClass' => 'bg-danger'];
    }
    if ($skor >= 50) {
        return ['label' => 'Sedang', 'badgeClass' => 'bg-warning text-dark'];
    }
    return ['label' => 'Ringan', 'badgeClass' => 'bg-success'];
}

/** Safe COUNT(*) helper: never trusts a query result blindly. */
function countTotal(mysqli $mysqli, string $sql): int
{
    $result = mysqli_query($mysqli, $sql);
    if (!$result) {
        return 0;
    }
    $row = mysqli_fetch_assoc($result);
    return (int)($row['total'] ?? 0);
}

// ================= DATA =================
$countUser       = countTotal($mysqli, "SELECT COUNT(*) as total FROM users");
$countAssessment = countTotal($mysqli, "SELECT COUNT(*) as total FROM assessment_results");
$countNotif      = countTotal($mysqli, "SELECT COUNT(*) as total FROM notifications");
$countDiary      = countTotal($mysqli, "SELECT COUNT(*) as total FROM diary_entries");
$countLogin      = countTotal($mysqli, "SELECT COUNT(*) as total FROM log_login");

// ================= CHART (grouped by real year-month, sorted chronologically) =================
$dataChart = [];
$queryChart = mysqli_query($mysqli, "
    SELECT DATE_FORMAT(assessment_date, '%Y-%m') as periode,
           DATE_FORMAT(assessment_date, '%b %Y') as label,
           COUNT(*) as total
    FROM assessment_results
    GROUP BY periode, label
    ORDER BY periode ASC
");
if ($queryChart) {
    while ($row = mysqli_fetch_assoc($queryChart)) {
        $dataChart[] = ['bulan' => $row['label'], 'total' => (int)$row['total']];
    }
}

// ================= PIE / DISTRIBUSI KONDISI MENTAL =================
$ringan = $sedang = $berat = 0;
$qPie = mysqli_query($mysqli, "SELECT total_skor FROM assessment_results");
if ($qPie) {
    while ($d = mysqli_fetch_assoc($qPie)) {
        $risiko = klasifikasiRisiko((float)$d['total_skor']);
        match ($risiko['label']) {
            'Berat'  => $berat++,
            'Sedang' => $sedang++,
            default  => $ringan++,
        };
    }
}

$totalMental = $ringan + $sedang + $berat;

$persenRingan = 0;
$persenSedang = 0;
$persenBerat  = 0;

if ($totalMental > 0) {
    $persenRingan = round(($ringan / $totalMental) * 100);
    $persenSedang = round(($sedang / $totalMental) * 100);
    $persenBerat  = round(($berat / $totalMental) * 100);
}

// ================= MONITORING =================
$qMonitoring = mysqli_query($mysqli, "
    SELECT u.id, u.nama,
           MAX(a.assessment_date) as terakhir,
           MAX(a.total_skor) as skor
    FROM users u
    LEFT JOIN assessment_results a ON u.id = a.user_id
    WHERE u.role = 'mahasiswa'
    GROUP BY u.id, u.nama
");

$role   = $_SESSION['role'] ?? 'mahasiswa';
$userId = (int)($_SESSION['user_id'] ?? 0);

// ================= FOTO PROFIL (prepared statement) =================
$fotoProfile = 'default.png';
$namaProfile = $_SESSION['username'];

if ($stmt = mysqli_prepare($mysqli, "SELECT profile_image, nama FROM users WHERE id = ?")) {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $userProfile = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!empty($userProfile['profile_image'])) {
        $fotoProfile = $userProfile['profile_image'];
    }
    if (!empty($userProfile['nama'])) {
        $namaProfile = $userProfile['nama'];
    }
}

// ================= STATUS ASSESSMENT TERAKHIR (prepared statement) =================
$lastStatus = "Belum Ada";

if ($stmt = mysqli_prepare($mysqli, "
    SELECT total_skor
    FROM assessment_results
    WHERE user_id = ?
    ORDER BY assessment_date DESC
    LIMIT 1
")) {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $resLast = mysqli_stmt_get_result($stmt);

    if ($resLast && mysqli_num_rows($resLast) > 0) {
        $d = mysqli_fetch_assoc($resLast);
        $lastStatus = klasifikasiRisiko((float)$d['total_skor'])['label'];
    }
    mysqli_stmt_close($stmt);
}

// ================= DIARY TERBARU (prepared statement) =================
$lastDiary = "Belum ada diary";

if ($stmt = mysqli_prepare($mysqli, "
    SELECT content
    FROM diary_entries
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 1
")) {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $resDiary = mysqli_stmt_get_result($stmt);

    if ($resDiary && mysqli_num_rows($resDiary) > 0) {
        $dDiary = mysqli_fetch_assoc($resDiary);
        $lastDiary = mb_substr($dDiary['content'], 0, 80) . "...";
    }
    mysqli_stmt_close($stmt);
}

// ================= TIPS HARIAN (server-side, so it's stable per page load) =================
$daftarTipsPhp = [
    "Jangan memaksakan diri ketika stres.",
    "Cobalah menuliskan perasaanmu di diary.",
    "Hindari multitasking terlalu sering.",
    "Cari suasana baru untuk relaksasi.",
    "Mendengarkan musik bisa membantu menenangkan pikiran.",
    "Jangan ragu meminta bantuan profesional.",
];
$tipsHariIni = $daftarTipsPhp[array_rand($daftarTipsPhp)];
?>

<?php include __DIR__ . '/layout/header.php'; ?>

<style>
    #sidebar {
        transition: all 0.3s;
    }

    #sidebar.active {
        margin-left: -250px;
    }
</style>

<div class="d-flex">
    <?php include __DIR__ . '/layout/sidebar.php'; ?>

    <div class="content-wrapper w-100">

        <div class="ms-auto d-flex align-items-center gap-3">
            <small class="text-muted me-3">
                Home / Dashboard
            </small>
        </div>

        <div class="container-fluid p-4 py-3">

            <div class="welcome-box mb-4">
                <h3 class="fw-bold mb-1">
                    Halo, <?= htmlspecialchars($_SESSION['username']) ?> 👋
                </h3>
                <p class="text-muted mb-0">
                    Selamat datang kembali di Dashboard SIMKM
                </p>
            </div>

            <!-- ================= INFO + TIPS (KHUSUS MAHASISWA) ================= -->
            <?php if ($role === 'mahasiswa'): ?>

                <div class="row mt-4 g-3">

                    <div class="col-md-4">
                        <div class="card p-3 shadow-sm border-start border-success border-4">
                            <h6>Status Assessment</h6>
                            <p>Status terakhir: <b><?= htmlspecialchars($lastStatus) ?></b></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card p-3 shadow-sm border-start border-info border-4">
                            <h6>Diary Terbaru</h6>
                            <ul>
                                <li><?= htmlspecialchars($lastDiary) ?></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card p-3 shadow-sm border-start border-warning border-4">
                            <h6>Tips Hari Ini</h6>
                            <p id="tipsText"><?= htmlspecialchars($tipsHariIni) ?></p>

                            <button class="btn btn-success btn-sm mt-2" onclick="mulaiTips()">
                                Lihat Tips Lainnya
                            </button>
                        </div>
                    </div>

                </div>

                <!-- ================= MODAL TIPS ================= -->
                <div class="modal fade" id="modalTips" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content p-3">

                            <h5>Tips Lainnya:</h5>
                            <p id="isiTips"></p>

                            <div class="text-end">
                                <button class="btn btn-primary" onclick="nextTips()">OK</button>
                            </div>

                        </div>
                    </div>
                </div>

            <?php endif; ?>


            <!-- ================= ADMIN MENU ================= -->
            <?php if ($role === 'admin'): ?>

                <div class="row mt-3">
                    <div class="col-md-4 mb-3">
                        <div class="card p-3 text-center shadow-sm">
                            <h6>Kelola Pengguna</h6>
                            <a href="../admin/manage_users.php" class="btn btn-primary btn-sm mt-2">Kelola</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card p-3 text-center shadow-sm">
                            <h6>Tambah User</h6>
                            <a href="../admin/add_user.php" class="btn btn-success btn-sm mt-2">Tambah</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card p-3 text-center shadow-sm">
                            <h6>Data Mahasiswa</h6>
                            <a href="../admin/view_students.php" class="btn btn-info btn-sm mt-2">Lihat</a>
                        </div>
                    </div>
                </div>

                <!-- ================= FILTER ================= -->
                <div class="card mt-3 p-3 shadow-sm border-0">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="date" id="startDate" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <input type="date" id="endDate" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100" onclick="filterData()">Filter Data</button>
                        </div>
                    </div>
                </div>

                <!-- ================= CARD ================= -->
                <div class="row mt-4">
                    <div class="col-md-2">
                        <div class="card stat-card bg-info text-white p-3">
                            <h3 id="totalUser"><?= (int)$countUser ?></h3>
                            <p>Total User</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning p-3 shadow-sm">
                            <h3 id="totalAssessment"><?= (int)$countAssessment ?></h3>
                            <p>Assessment</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white p-3 shadow-sm">
                            <h3 id="totalNotif"><?= (int)$countNotif ?></h3>
                            <p>Notifikasi</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white p-3 shadow-sm">
                            <h3 id="totalDiary"><?= (int)$countDiary ?></h3>
                            <p>Diary</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-dark text-white p-3 shadow-sm">
                            <h3 id="totalLogin"><?= (int)$countLogin ?></h3>
                            <p>Login</p>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

            <!-- ================= KHUSUS NON MAHASISWA ================= -->
            <?php if ($role !== 'mahasiswa'): ?>

                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card p-4">
                            <h5 class="mb-3">Grafik Assessment</h5>
                            <canvas id="chartAssessment"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <h5 class="mb-3">Distribusi Mental</h5>
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- ================= DISTRIBUSI KONDISI MENTAL ================= -->
                <div class="row mt-4">

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 h-100">

                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">📊 Distribusi Kondisi Mental</h5>
                            </div>

                            <div class="card-body">

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between">
                                        <strong>Ringan</strong>
                                        <span><?= (int)$persenRingan ?>%</span>
                                    </div>
                                    <div class="progress mt-2" style="height:20px;">
                                        <div class="progress-bar bg-success" style="width: <?= (int)$persenRingan ?>%">
                                            <?= (int)$ringan ?> Mahasiswa
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between">
                                        <strong>Sedang</strong>
                                        <span><?= (int)$persenSedang ?>%</span>
                                    </div>
                                    <div class="progress mt-2" style="height:20px;">
                                        <div class="progress-bar bg-warning text-dark" style="width: <?= (int)$persenSedang ?>%">
                                            <?= (int)$sedang ?> Mahasiswa
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>Berat</strong>
                                        <span><?= (int)$persenBerat ?>%</span>
                                    </div>
                                    <div class="progress mt-2" style="height:20px;">
                                        <div class="progress-bar bg-danger" style="width: <?= (int)$persenBerat ?>%">
                                            <?= (int)$berat ?> Mahasiswa
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-8 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">📈 Grafik Distribusi Kondisi Mental</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="mentalChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ================= REALTIME STATUS ================= -->
                <div class="row mt-4">

                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 p-3">
                            <h5>🟢 Kondisi Ringan</h5>
                            <h2 class="text-success" id="ringanRealtime"><?= (int)$ringan ?></h2>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 p-3">
                            <h5>🟡 Kondisi Sedang</h5>
                            <h2 class="text-warning" id="sedangRealtime"><?= (int)$sedang ?></h2>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 p-3">
                            <h5>🔴 Kondisi Berat</h5>
                            <h2 class="text-danger" id="beratRealtime"><?= (int)$berat ?></h2>
                        </div>
                    </div>

                </div>

                <!-- ================= MONITORING KESEHATAN MENTAL ================= -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card p-3 shadow-sm border-0">
                            <h5>Monitoring Kesehatan Mental Mahasiswa</h5>

                            <table class="table table-bordered mt-3">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Mahasiswa</th>
                                        <th>Terakhir Assessment</th>
                                        <th>Tingkat Risiko</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if ($qMonitoring && mysqli_num_rows($qMonitoring) > 0): ?>

                                        <?php while ($m = mysqli_fetch_assoc($qMonitoring)): ?>
                                            <?php
                                            $skor   = $m['skor'] !== null ? (float)$m['skor'] : null;
                                            $risiko = klasifikasiRisiko($skor);
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($m['nama']) ?></td>
                                                <td>
                                                    <?= $m['terakhir'] ? htmlspecialchars(date('d F Y', strtotime($m['terakhir']))) : '-' ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= htmlspecialchars($risiko['badgeClass']) ?>">
                                                        <?= htmlspecialchars($risiko['label']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../admin/detail_student.php?id=<?= (int)$m['id'] ?>" class="btn btn-sm btn-primary">
                                                        Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>

                                    <?php else: ?>

                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                Belum ada data monitoring
                                            </td>
                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>
                    </div>
                </div>

                <!-- ================= LAPORAN ================= -->
                <div class="mt-4">
                    <button class="btn btn-dark" data-bs-toggle="collapse" href="#laporanBox">
                        📑 Menu Laporan
                    </button>
                    <div id="laporanBox" class="collapse show mt-3">
                        <a href="export_dashboard.php" class="btn btn-outline-danger shadow-sm">Export PDF</a>
                        <a href="export_all_pdf.php" class="btn btn-danger shadow-sm">Export Semua Data</a>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

<script>
    // ================= STATE =================
    const dataChart = <?= json_encode($dataChart, JSON_UNESCAPED_UNICODE) ?>;
    const ringanCount = <?= (int)$ringan ?>;
    const sedangCount = <?= (int)$sedang ?>;
    const beratCount = <?= (int)$berat ?>;

    const daftarTips = [
        "Jangan memaksakan diri ketika stres.",
        "Cobalah menuliskan perasaanmu di diary.",
        "Hindari multitasking terlalu sering.",
        "Cari suasana baru untuk relaksasi.",
        "Mendengarkan musik bisa membantu menenangkan pikiran.",
        "Jangan ragu meminta bantuan profesional."
    ];

    let indexTips = 0;
    let modalTipsInstance = null;
    let realtimeTimer = null;

    // ================= SINGLE DOMContentLoaded ENTRY POINT =================
    document.addEventListener("DOMContentLoaded", function() {

        initSidebar();
        initCharts();
        initFilterDefaults();
        loadDashboardAwal();
        startRealtimePolling();

    });

    // ================= SIDEBAR =================
    function initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarBtn = document.getElementById('sidebarCollapse');
        if (!sidebar) return;

        if (localStorage.getItem("sidebar") === "hide") {
            sidebar.classList.add("active");
        }

        if (sidebarBtn) {
            sidebarBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                localStorage.setItem("sidebar", sidebar.classList.contains('active') ? "hide" : "show");
            });
        }
    }

    // ================= CHARTS =================
    function initCharts() {
        const elAssessment = document.getElementById('chartAssessment');
        if (elAssessment) {
            new Chart(elAssessment, {
                type: 'line',
                data: {
                    labels: dataChart.map(i => i.bulan),
                    datasets: [{
                        label: 'Total Assessment',
                        data: dataChart.map(i => i.total),
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                }
            });
        }

        const elPie = document.getElementById('pieChart');
        if (elPie) {
            new Chart(elPie, {
                type: 'doughnut',
                data: {
                    labels: ['Ringan', 'Sedang', 'Berat'],
                    datasets: [{
                        data: [ringanCount, sedangCount, beratCount],
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b']
                    }]
                }
            });
        }

        const mentalChart = document.getElementById('mentalChart');
        if (mentalChart) {
            new Chart(mentalChart, {
                type: 'bar',
                data: {
                    labels: ['Ringan', 'Sedang', 'Berat'],
                    datasets: [{
                        label: 'Jumlah Mahasiswa',
                        data: [ringanCount, sedangCount, beratCount],
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    // ================= FILTER =================
    function initFilterDefaults() {
        const startInput = document.getElementById('startDate');
        const endInput = document.getElementById('endDate');
        if (startInput && endInput) {
            const today = new Date().toISOString().split('T')[0];
            startInput.value = today;
            endInput.value = today;
        }
    }

    function filterData() {
        const start = document.getElementById('startDate')?.value;
        const end = document.getElementById('endDate')?.value;
        if (!start || !end) return;

        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.style.display = 'block';


        fetch(`./ajax/dashboard_filter.php?from=${encodeURIComponent(start)}&to=${encodeURIComponent(end)}`)
            .then(res => res.json())
            .then(data => updateBox(data))
            .catch(err => console.error(err))
            .finally(() => {
                if (overlay) overlay.style.display = 'none';
            });
    }

    function updateBox(data) {
        // Keys are the actual DOM element ids; values are the matching keys in the JSON response.
        const map = {
            totalUser: 'user',
            totalAssessment: 'assessment',
            totalNotif: 'notif',
            totalDiary: 'diary',
            totalLogin: 'login'
        };
        for (const [elId, key] of Object.entries(map)) {
            const el = document.getElementById(elId);
            if (el) el.innerText = data[key] || 0;
        }
    }

    function loadDashboardAwal() {
        fetch('./ajax/dashboard_filter.php')
            .then(res => res.json())
            .then(data => updateBox(data))
            .catch(err => console.error(err));
    }

    // ================= TIPS STEP BY STEP =================
    function mulaiTips() {
        indexTips = 0;
        const isiTips = document.getElementById("isiTips");
        if (isiTips) isiTips.innerText = daftarTips[indexTips];

        const modalEl = document.getElementById('modalTips');
        if (!modalEl) return;

        if (!modalTipsInstance) {
            modalTipsInstance = new bootstrap.Modal(modalEl);
        }
        modalTipsInstance.show();
    }

    function nextTips() {
        indexTips++;
        const isiTips = document.getElementById("isiTips");

        if (indexTips < daftarTips.length) {
            if (isiTips) isiTips.innerText = daftarTips[indexTips];
        } else if (modalTipsInstance) {
            modalTipsInstance.hide();
        }
    }

    // ================= REALTIME AJAX =================
    function realtimeDashboard() {
        fetch('./ajax/dashboard_realtime.php')
            .then(response => response.json())
            .then(data => {
                const fields = {
                    totalUser: data.user,
                    totalAssessment: data.assessment,
                    totalNotif: data.notif,
                    totalDiary: data.diary,
                    totalLogin: data.login,
                    ringanRealtime: data.ringan,
                    sedangRealtime: data.sedang,
                    beratRealtime: data.berat
                };

                for (const [id, value] of Object.entries(fields)) {
                    const el = document.getElementById(id);
                    if (el) el.innerText = value;
                }
            })
            .catch(error => console.log(error));
    }

    // Poll every 5s, but pause while the tab is hidden to avoid wasting requests.
    function startRealtimePolling() {
        realtimeDashboard();
        realtimeTimer = setInterval(realtimeDashboard, 5000);

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(realtimeTimer);
            } else {
                realtimeDashboard();
                realtimeTimer = setInterval(realtimeDashboard, 5000);
            }
        });
    }
</script>