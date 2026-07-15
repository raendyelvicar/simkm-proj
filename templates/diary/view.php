<div class="card p-4">
    <nav>
        <small class="text-muted">Home / Diary / Detail</small>
    </nav>

    <div class="card shadow-sm mt-3">

        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h5 class="mb-0">Detail Diary</h5>
            <span class="badge bg-light text-dark"><?= ucfirst($_SESSION['role']); ?></span>
        </div>

        <div class="card-body">

            <table class="table">
                <tr>
                    <th width="200">Nama Mahasiswa</th>
                    <td>nama_mahasiswa</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>entry_date</td>
                </tr>
                <tr>
                    <th>Mood</th>
                    <td>
                        <span class="badge bg-info">
                            mood_level
                        </span>
                    </td>
                </tr>
            </table>

            <hr>

            <h6>Isi Diary</h6>
            <div class="p-3 border rounded bg-light">
                content
            </div>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Dashboard';
require __DIR__ . '/../layouts/index.php';
