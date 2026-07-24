<?php ob_start();

$invalid = $invalid ?? false;
$error = $error ?? '';
$token = $token ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Sistem Informasi Manajemen Kesehatan Mental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            max-width: 420px;
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

        .back-home {
            display: block;
            margin-top: 12px;
            text-align: center;
            font-size: 14px;
        }

        .back-home a {
            color: #0ea5a4;
            text-decoration: none;
            font-weight: 600;
        }

        .back-home a:hover {
            text-decoration: underline;
        }

        .footer-note {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 14px;
        }
    </style>
</head>

<body>

    <div class="register-wrapper">
        <div class="container p-0" style="max-width:420px;">
            <?php if ($invalid): ?>
                <div class="card">
                    <h1>Tautan Tidak Valid</h1>
                    <p class="lead">Tautan reset password ini tidak valid, sudah digunakan, atau sudah kedaluwarsa.</p>

                    <a href="/forgot-password" class="btn btn-primary w-100">Minta Tautan Baru</a>

                    <div class="back-home">
                        <a href="/login">&larr; Kembali ke Halaman Masuk</a>
                    </div>

                    <div class="footer-note">&copy; <?= date('Y') ?> SIMKM</div>
                </div>
            <?php else: ?>
                <form method="POST" action="/reset-password/<?= htmlspecialchars($token) ?>" class="card" autocomplete="off">
                    <h1>Reset Password</h1>
                    <p class="lead">Buat password baru untuk akun Anda.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password Baru</label>
                        <input id="password" name="password" type="password" class="form-control" placeholder="Minimal 8 karakter" required minlength="8">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" placeholder="Ulangi password baru" required minlength="8">
                    </div>

                    <button type="submit" id="btnReset" class="btn btn-primary w-100">
                        <span id="btnText">Reset Password</span>
                        <span id="btnLoading" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                    </button>

                    <div class="back-home">
                        <a href="/login">&larr; Kembali ke Halaman Masuk</a>
                    </div>

                    <div class="footer-note">&copy; <?= date('Y') ?> SIMKM</div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.querySelector('form.card');
            var btn = document.getElementById('btnReset');
            var text = document.getElementById('btnText');
            var loading = document.getElementById('btnLoading');

            if (form) {
                form.addEventListener('submit', function() {
                    btn.disabled = true;
                    text.textContent = 'Memproses...';
                    loading.classList.remove('d-none');
                });
            }
        });
    </script>

</body>

</html>
