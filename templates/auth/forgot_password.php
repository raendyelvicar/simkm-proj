<?php ob_start();

$error = $error ?? '';
$success = $success ?? '';
$old = $old ?? [];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Sistem Informasi Manajemen Kesehatan Mental</title>
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
            <form method="POST" action="/forgot-password" class="card" autocomplete="off">
                <h1>Lupa Password</h1>
                <p class="lead">Masukkan email akun Anda, kami akan mengirimkan tautan untuk reset password.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control" placeholder="Masukkan email akun Anda"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                </div>

                <button type="submit" id="btnSend" class="btn btn-primary w-100">
                    <span id="btnText">Kirim Tautan Reset</span>
                    <span id="btnLoading" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                </button>

                <hr>

                <div class="text-center">
                    <small>
                        Sudah ingat password?
                        <a href="/login">Masuk di sini</a>
                    </small>
                </div>

                <div class="back-home">
                    <a href="/">&larr; Kembali ke Beranda</a>
                </div>

                <div class="footer-note">&copy; <?= date('Y') ?> SIMKM</div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.querySelector('form.card');
            var btn = document.getElementById('btnSend');
            var text = document.getElementById('btnText');
            var loading = document.getElementById('btnLoading');

            if (form) {
                form.addEventListener('submit', function() {
                    btn.disabled = true;
                    text.textContent = 'Mengirim...';
                    loading.classList.remove('d-none');
                });
            }
        });
    </script>

</body>

</html>
