<div class="content">

    <h3>Dashboard Admin</h3>
    <p>Selamat datang, Test</p>

    <!-- MENU ADMIN -->
    <div class="row mt-4">

        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h5>Kelola Pengguna</h5>
                <a href="admin/manage_users.php" class="btn btn-primary btn-sm">Kelola</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h5>Tambah User</h5>
                <a href="admin/add_user.php" class="btn btn-success btn-sm">Tambah</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h5>Data Mahasiswa</h5>
                <a href="admin/view_students.php" class="btn btn-info btn-sm">Lihat</a>
            </div>
        </div>

        <!-- 🔽 TARUH DI SINI -->
        <div class="card mb-3">
            <div class="card-body">
                <form id="filterForm" class="row g-2">
                    <div class="col-md-4">
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- 🔼 SAMPAI SINI -->
    </div>

    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <h5>Export Assessment</h5>
            <a href="admin/export_all_assessments_dompdf.php" target="_blank" class="btn btn-warning btn-sm">
                Export PDF
            </a>
        </div>
    </div>

</div>

<!-- INFO TAMBAHAN -->
<div class="row mt-4">

    <div class="col-md-6">
        <div class="card p-3 shadow-sm">
            <h5>Notifikasi</h5>
            <h3><?= $notifCount ?></h3>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3 shadow-sm">
            <h5>Status Sistem</h5>
            <p>Sistem berjalan normal</p>
        </div>
    </div>

</div>

</div>

<script>
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'ajax/filter_dashboard.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                let data = JSON.parse(res);

                pieChart.data.datasets[0].data = [
                    data.ringan,
                    data.sedang,
                    data.berat
                ];
                pieChart.update();
            }
        });
    });
</script>