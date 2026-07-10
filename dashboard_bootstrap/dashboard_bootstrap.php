<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// ================= DATA =================
$countUser = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as total FROM users"))['total'] ?? 0;

$q = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM assessment_results");
$countAssessment = $q ? mysqli_fetch_assoc($q)['total'] : 0;

$qNotif = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM notifications");
$countNotif = mysqli_fetch_assoc($qNotif)['total'] ?? 0;

$qDiary = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM diary_entries");
$countDiary = mysqli_fetch_assoc($qDiary)['total'] ?? 0;

$qLogin = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM log_login");
$countLogin = mysqli_fetch_assoc($qLogin)['total'] ?? 0;

// ================= CHART =================
$dataChart = [];
$queryChart = mysqli_query($mysqli, "
    SELECT DATE_FORMAT(assessment_date, '%b') as bulan, COUNT(*) as total
    FROM assessment_results GROUP BY bulan
");
while ($row = mysqli_fetch_assoc($queryChart)) {
    $dataChart[] = $row;
}

// ================= PIE =================
$ringan = $sedang = $berat = 0;
$qPie = mysqli_query($mysqli, "SELECT total_skor FROM assessment_results");
while ($d = mysqli_fetch_assoc($qPie)) {
    if ($d['total_skor'] >= 75) $berat++;
    elseif ($d['total_skor'] >= 50) $sedang++;
    else $ringan++;
}

// ================= DISTRIBUSI KONDISI MENTAL =================
$totalMental = $ringan + $sedang + $berat;

$persenRingan = 0;
$persenSedang = 0;
$persenBerat  = 0;

if($totalMental > 0){

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
    GROUP BY u.id
");

$role = $_SESSION['role'] ?? 'mahasiswa';

// ================= DATA MAHASISWA LOGIN =================
$userId = $_SESSION['user_id'] ?? 0;

// ================= FOTO PROFIL =================
$qUserProfile = mysqli_query($mysqli, "
    SELECT profile_image, nama
    FROM users
    WHERE id = '$userId'
");

$userProfile = mysqli_fetch_assoc($qUserProfile);

$fotoProfile = $userProfile['profile_image'] ?? '';
$namaProfile = $userProfile['nama'] ?? $_SESSION['username'];

// FOTO DEFAULT
if(empty($fotoProfile)){
    $fotoProfile = 'default.png';
}

// ================= STATUS ASSESSMENT TERAKHIR =================
$qLastAssessment = mysqli_query($mysqli, "
    SELECT total_skor 
    FROM assessment_results 
    WHERE user_id = '$userId'
    ORDER BY assessment_date DESC 
    LIMIT 1
");

$lastStatus = "Belum Ada";

if($qLastAssessment && mysqli_num_rows($qLastAssessment) > 0){
    $d = mysqli_fetch_assoc($qLastAssessment);

    if($d['total_skor'] >= 75){
        $lastStatus = "Berat";
    } elseif($d['total_skor'] >= 50){
        $lastStatus = "Sedang";
    } else {
        $lastStatus = "Ringan";
    }
}

// ================= DIARY TERBARU =================
$qLastDiary = mysqli_query($mysqli, "
    SELECT content 
    FROM diary_entries 
    WHERE user_id = '$userId'
    ORDER BY created_at DESC 
    LIMIT 1
");

$lastDiary = "Belum ada diary";

if($qLastDiary && mysqli_num_rows($qLastDiary) > 0){
    $dDiary = mysqli_fetch_assoc($qLastDiary);
    $lastDiary = substr($dDiary['content'], 0, 80) . "...";
}
?>

<?php include __DIR__ . '/layout/header.php'; ?>

<style>
#sidebar { transition: all 0.3s; }
#sidebar.active { margin-left: -250px; }
</style>

<div class="d-flex">
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<div class="content-wrapper w-100">

    <button id="sidebarCollapse" class="btn btn-primary">
        ☰
    </button>

    <div class="ms-auto d-flex align-items-center gap-3">

        <small class="text-muted me-3">
            Home / Dashboard
        </small>

        <!-- PROFILE + ROLE -->
        <div class="dropdown">

            <a href="#" class="d-flex align-items-center text-decoration-none"
               data-bs-toggle="dropdown">

                <!-- FOTO ADMIN -->
            <?php if($role == 'admin'): ?>

                <img src="../uploads/profile/<?= $fotoProfile ?>"
                     width="42"
                     height="42"
                     class="rounded-circle border shadow-sm"
                     style="object-fit:cover;">

            <?php else: ?>

                <!-- ICON USER -->
                <div class="rounded-circle border shadow-sm d-flex justify-content-center align-items-center"
                     style="width:42px;height:42px;background:#f1f1f1;">

                    👤

                </div>

                <?php endif; ?>

            <!-- NAMA -->
            <span class="ms-2 text-dark fw-bold">
                <?= htmlspecialchars($namaProfile) ?>
            </span>

        </a>

            <!-- DROPDOWN MENU -->
        <ul class="dropdown-menu dropdown-menu-end shadow">

            <li>
                <a class="dropdown-item"
                   href="../profile/edit_profile.php">

                    👤 Edit Profil

                </a>
            </li>

            <li>
                <hr class="dropdown-divider">
            </li>

            <li>
                <a class="dropdown-item text-danger"
                   href="../logout.php">

                    🚪 Logout

                </a>
            </li>

        </ul>

    </div>

</div>

</nav>

<div class="container-fluid p-4 py-3">

<div class="welcome-box mb-4">

    <h3 class="fw-bold mb-1">
        Halo, <?= $_SESSION['username'] ?> 👋
    </h3>

    <p class="text-muted mb-0">
        Selamat datang kembali di Dashboard SIMKM
    </p>

</div>

<!-- ================= INFO + TIPS (KHUSUS MAHASISWA) ================= -->
<?php if($role == 'mahasiswa'): ?>

<div class="row mt-4 g-3">

    <div class="col-md-4">
        <div class="card p-3 shadow-sm border-start border-success border-4">
            <h6>Status Assessment</h6>
            <p>Status terakhir: <b><?= $lastStatus ?></b></p>
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
        <p id="tipsText"><?= $tipsHariIni ?? 'Kurangi penggunaan media sosial jika mulai merasa cemas.' ?></p>

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
<?php if($role == 'admin'): ?>

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
        <h3 id="totalUser"><?= $countUser ?></h3>
        <p>Total User</p>
    </div>
    </div>
            <div class="col-md-2">
    <div class="card bg-warning p-3 shadow-sm">
        <h3 id="totalAssessment"><?= $countAssessment ?></h3>
        <p>Assessment</p>
    </div>
    </div>
            <div class="col-md-2">
    <div class="card bg-danger text-white p-3 shadow-sm">
        <h3 id="totalNotif"><?= $countNotif ?></h3>
        <p>Notifikasi</p>
    </div>
    </div>
            <div class="col-md-2">
    <div class="card bg-success text-white p-3 shadow-sm">
        <h3 id="totalDiary"><?= $countDiary ?></h3>
        <p>Diary</p>
    </div>
    </div>
            <div class="col-md-2">
    <div class="card bg-dark text-white p-3 shadow-sm">
        <h3 id="totalLogin"><?= $countLogin ?></h3>
        <p>Login</p>
    </div>
    </div>
        </div>

<?php endif; ?>

<!-- ================= KHUSUS NON MAHASISWA ================= -->
<?php if($role != 'mahasiswa'): ?>

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

    <!-- CARD DISTRIBUSI -->
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0 h-100">

            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📊 Distribusi Kondisi Mental</h5>
            </div>

            <div class="card-body">

                <!-- RINGAN -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <strong>Ringan</strong>
                        <span><?= $persenRingan ?>%</span>
                    </div>

                    <div class="progress mt-2" style="height:20px;">
                        <div class="progress-bar bg-success"
                             style="width: <?= $persenRingan ?>%">
                            <?= $ringan ?> Mahasiswa
                        </div>
                    </div>
                </div>

                <!-- SEDANG -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <strong>Sedang</strong>
                        <span><?= $persenSedang ?>%</span>
                    </div>

                    <div class="progress mt-2" style="height:20px;">
                        <div class="progress-bar bg-warning text-dark"
                             style="width: <?= $persenSedang ?>%">
                            <?= $sedang ?> Mahasiswa
                        </div>
                    </div>
                </div>

                <!-- BERAT -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>Berat</strong>
                        <span><?= $persenBerat ?>%</span>
                    </div>

                    <div class="progress mt-2" style="height:20px;">
                        <div class="progress-bar bg-danger"
                             style="width: <?= $persenBerat ?>%">
                            <?= $berat ?> Mahasiswa
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- GRAFIK DISTRIBUSI -->
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

            <h2 class="text-success" id="ringanRealtime">
                <?= $ringan ?>
            </h2>

        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">

            <h5>🟡 Kondisi Sedang</h5>

            <h2 class="text-warning" id="sedangRealtime">
                <?= $sedang ?>
            </h2>

        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">

            <h5>🔴 Kondisi Berat</h5>

            <h2 class="text-danger" id="beratRealtime">
                <?= $berat ?>
            </h2>

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

<?php if($qMonitoring && mysqli_num_rows($qMonitoring) > 0): ?>
    
    <?php while($m = mysqli_fetch_assoc($qMonitoring)): ?>

    <?php
    // LOGIKA RISIKO
    if($m['skor'] >= 75){
        $risiko = '<span class="badge bg-danger">Berat</span>';
    } elseif($m['skor'] >= 50){
        $risiko = '<span class="badge bg-warning text-dark">Sedang</span>';
    } else {
        $risiko = '<span class="badge bg-success">Ringan</span>';
    }
    ?>

    <tr>
        <td><?= $m['nama'] ?></td>
        <td>
            <?= $m['terakhir'] ? date('d F Y', strtotime($m['terakhir'])) : '-' ?>
        </td>
        <td><?= $risiko ?></td>
        <td>
            <a href="/AplikasiSkripsi/admin/detail_student.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-primary">
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

    </div>
</div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>

<script>
// ================= JS =================
const dataChart = <?= json_encode($dataChart); ?>;
let chartAssessmentInstance = null;

document.addEventListener("DOMContentLoaded", function(){

    loadDashboardAwal();

    const sidebar = document.getElementById('sidebar');
    const sidebarBtn = document.getElementById('sidebarCollapse');

    // LOAD STATUS SIDEBAR
    let state = localStorage.getItem("sidebar");

    if(state === "hide"){
        sidebar.classList.add("active");
    }

    // TOMBOL TOGGLE
    if(sidebarBtn){

        sidebarBtn.addEventListener('click', function(){

            sidebar.classList.toggle('active');

            // SIMPAN STATUS
            if(sidebar.classList.contains('active')){
                localStorage.setItem("sidebar", "hide");
            } else {
                localStorage.setItem("sidebar", "show");
            }

        });

    }

    const elAssessment = document.getElementById('chartAssessment');
    if(elAssessment){
        chartAssessmentInstance = new Chart(elAssessment, {
            type: 'line',
            data: {
                labels: dataChart.map(i => i.bulan),
                datasets: [{ label: 'Total Assessment', data: dataChart.map(i => i.total), borderColor: '#4e73df', backgroundColor: 'rgba(78, 115, 223, 0.1)', fill:true, tension:0.3 }]
            }
        });
    }

    const elPie = document.getElementById('pieChart');
    if(elPie){
        new Chart(elPie, {
            type: 'doughnut',
            data: { labels: ['Ringan','Sedang','Berat'], datasets:[{ data:[<?= $ringan ?>,<?= $sedang ?>,<?= $berat ?>], backgroundColor:['#1cc88a','#f6c23e','#e74a3b'] }] }
        });
    }
});

// ================= DISTRIBUSI KONDISI MENTAL =================
const mentalChart = document.getElementById('mentalChart');

if(mentalChart){

    new Chart(mentalChart, {

        type: 'bar',

        data: {

            labels: ['Ringan', 'Sedang', 'Berat'],

            datasets: [{

                label: 'Jumlah Mahasiswa',

                data: [
                    <?= $ringan ?>,
                    <?= $sedang ?>,
                    <?= $berat ?>
                ],

                backgroundColor: [
                    '#1cc88a',
                    '#f6c23e',
                    '#e74a3b'
                ],

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

function filterData(){
    let start = document.getElementById('startDate')?.value;
    let end   = document.getElementById('endDate')?.value;
    if(!start || !end) return;
    document.getElementById('loadingOverlay').style.display = 'block';
    fetch(`../ajax/dashboard_filter.php?from=${start}&to=${end}`)
    .then(res=>res.json())
    .then(data=>updateBox(data))
    .catch(err=>console.error(err))
    .finally(()=>document.getElementById('loadingOverlay').style.display='none');
}

function updateBox(data){
    if(document.getElementById('user')) document.getElementById('user').innerText = data.user || 0;
    if(document.getElementById('assessment')) document.getElementById('assessment').innerText = data.assessment || 0;
    if(document.getElementById('notif')) document.getElementById('notif').innerText = data.notif || 0;
    if(document.getElementById('diary')) document.getElementById('diary').innerText = data.diary || 0;
    if(document.getElementById('login')) document.getElementById('login').innerText = data.login || 0;
}

function loadDashboardAwal(){
    fetch('../ajax/dashboard_filter.php')
    .then(res=>res.json())
    .then(data=>updateBox(data))
    .catch(err=>console.error(err));
}

const startInput = document.getElementById('startDate');
const endInput   = document.getElementById('endDate');
if(startInput && endInput){
    let today = new Date().toISOString().split('T')[0];
    startInput.value = today;
    endInput.value   = today;
}

// ================= TIPS STEP BY STEP =================
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

// ✅ BUKA MODAL
function mulaiTips(){
    indexTips = 0;
    document.getElementById("isiTips").innerText = daftarTips[indexTips];

    const modalEl = document.getElementById('modalTips');

    if(!modalTipsInstance){
        modalTipsInstance = new bootstrap.Modal(modalEl);
    }

    modalTipsInstance.show();
}

// ✅ TOMBOL OK (NEXT STEP)
function nextTips(){
    indexTips++;

    if(indexTips < daftarTips.length){
        document.getElementById("isiTips").innerText = daftarTips[indexTips];
    } else {
        modalTipsInstance.hide(); // tutup modal kalau sudah habis
    }
}

// ✅ RANDOM TIPS HARIAN
document.addEventListener("DOMContentLoaded", function(){
    let random = Math.floor(Math.random() * daftarTips.length);
    const tipsEl = document.getElementById("tipsText");
    if(tipsEl){
        tipsEl.innerText = daftarTips[random];
    }
});

// ================= REALTIME AJAX =================
function realtimeDashboard(){

    fetch('../ajax/dashboard_realtime.php')

    .then(response => response.json())

    .then(data => {

        // ================= CARD =================
        const userEl = document.getElementById('totalUser');
        if(userEl){
            userEl.innerText = data.user;
        }

        const assessmentEl = document.getElementById('totalAssessment');
        if(assessmentEl){
            assessmentEl.innerText = data.assessment;
        }

        const notifEl = document.getElementById('totalNotif');
        if(notifEl){
            notifEl.innerText = data.notif;
        }

        const diaryEl = document.getElementById('totalDiary');
        if(diaryEl){
            diaryEl.innerText = data.diary;
        }

        const loginEl = document.getElementById('totalLogin');
        if(loginEl){
            loginEl.innerText = data.login;
        }

        // ================= DISTRIBUSI =================
        const ringanEl = document.getElementById('ringanRealtime');
        if(ringanEl){
            ringanEl.innerText = data.ringan;
        }

        const sedangEl = document.getElementById('sedangRealtime');
        if(sedangEl){
            sedangEl.innerText = data.sedang;
        }

        const beratEl = document.getElementById('beratRealtime');
        if(beratEl){
            beratEl.innerText = data.berat;
        }

    })

    .catch(error => console.log(error));

}

// ================= AUTO REFRESH =================
setInterval(function(){

    realtimeDashboard();

}, 5000);

// LOAD PERTAMA
realtimeDashboard();

</script>