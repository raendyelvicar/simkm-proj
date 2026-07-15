<?php ob_start(); ?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Informasi Manajemen Kesehatan Mental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            margin: 0;
            background: #f4f6f9;
        }

        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .card {
            width: 100%;
            max-width: 560px;
            padding: 2rem 2.5rem;
            border: none;
            border-radius: 14px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
        }

        .card h1 {
            font-size: 1.4rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.25rem;
        }

        .card .lead {
            text-align: center;
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<div class="register-wrapper">
    <div class="container p-0" style="max-width:560px;">


        <?php if (!empty($error)): ?>
            <p style="background:#fee2e2;color:#b91c1c;padding:14px 16px;border-radius:14px;margin-bottom:24px;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="/register" class="card">
            <h1>Sistem Informasi Manajemen Kesehatan Mental</h1>
            <p class="lead">Buat akun untuk memulai</p>

            <div style="display:grid;gap:16px;">
                <label style="font-weight:700;">Nama Panggilan</label>
                <input type="text" class="form-control" name="nama" required autocomplete="name" placeholder="Masukkan nama panggilan"
                    value="<?= htmlspecialchars($nama ?? '') ?>">

                <label style="font-weight:700;">Nama Lengkap</label>
                <input type="text" class="form-control" name="nama_lengkap" required autocomplete="name" placeholder="Masukkan nama lengkap"
                    value="<?= htmlspecialchars($nama_lengkap ?? '') ?>">

                <label style="font-weight:700;">Username</label>
                <input type="text" class="form-control" name="username" required autocomplete="username" placeholder="Pilih username"
                    value="<?= htmlspecialchars($username ?? '') ?>">

                <label style="font-weight:700;">Email</label>
                <input type="email" class="form-control" name="email" required autocomplete="email" placeholder="Masukkan alamat email"
                    value="<?= htmlspecialchars($email ?? '') ?>">

                <label style="font-weight:700;">Password</label>
                <input type="password" class="form-control" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter"
                    value="<?= htmlspecialchars($password ?? '') ?>">

                <label style="font-weight:700;">NPM</label>
                <input type="text" class="form-control" name="npm" autocomplete="off" placeholder="Masukkan NPM"
                    value="<?= htmlspecialchars($npm ?? '') ?>">

                <label style="font-weight:700;">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-control" required>
                    <option value="">Pilih jenis kelamin</option>
                    <option value="Laki-laki" <?= isset($jenis_kelamin) && $jenis_kelamin === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="Perempuan" <?= isset($jenis_kelamin) && $jenis_kelamin === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                </select>

                <label style="font-weight:700;">Fakultas</label>
                <select name="fakultas" required class="form-control">
                    <option value="">Pilih Fakultas</option>
                    <?php foreach ($fakultasList as $f): ?>
                        <option value="<?= (int) $f['id'] ?>" <?= (isset($fakultas) && $fakultas == $f['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label style="font-weight:700;">Jurusan</label>
                <select name="jurusan" required class="form-control">
                    <option value="">Pilih Jurusan</option>
                    <?php foreach ($jurusanList as $j): ?>
                        <option value=" <?= (int) $j['id'] ?>" <?= (isset($jurusan) && $jurusan == $j['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($j['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label style="font-weight:700;">No. HP</label>
                <input type="text" name="no_hp" autocomplete="tel" placeholder="Masukkan nomor HP"
                    style="width:100%;border:1px solid #cbd5e1;border-radius:14px;padding:14px 16px;background:#fff;color:#111;"
                    value="<?= htmlspecialchars($no_hp ?? '') ?>">

                <button type="submit"
                    style="width:100%;padding:14px 16px;background:#0ea5a4;color:#fff;border:none;border-radius:14px;font-weight:700;font-size:16px;cursor:pointer;transition:background .2s;">
                    Daftar
                </button>

                <hr>

                <div class="text-center">
                    <small>
                        Sudah punya akun?
                        <a href="/login">Login</a>
                    </small>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelector('select[name="fakultas"]').addEventListener('change', function() {
        const fakultasId = this.value;
        const jurusanSelect = document.querySelector('select[name="jurusan"]');
        jurusanSelect.innerHTML = '<option value="">Memuat...</option>';

        if (!fakultasId) {
            jurusanSelect.innerHTML = '<option value="">Pilih Jurusan</option>';
            return;
        }

        fetch('/jurusan?fakultas_id=' + encodeURIComponent(fakultasId))
            .then(res => res.json())
            .then(data => {
                jurusanSelect.innerHTML = '<option value="">Pilih Jurusan</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name;
                    jurusanSelect.appendChild(opt);
                });
            })
            .catch(() => {
                jurusanSelect.innerHTML = '<option value="">Gagal memuat jurusan</option>';
            });
    });
</script>

</html>