<?php
session_start();

// FIX PATH (WAJIB)
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex">
    <?php include __DIR__ . '/../layout/sidebar.php'; ?>

    <div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

        <nav>
            <small class="text-muted">Home / Laporan</small>
        </nav>

        <div class="card shadow-sm p-4 mt-3">
            <!-- ISI MASING-MASING LAPORAN -->

<h4>Export Data</h4>

<a href="../export_dashboard.php" class="btn btn-danger">Export Dashboard</a>
<a href="../export_all_pdf.php" class="btn btn-dark">Export Semua Data</a>

        </div>

<a href="../../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary">
    ← Kembali
</a>

    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>