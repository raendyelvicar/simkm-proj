<?php ob_start();

$error = $error ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Sistem Informasi Manajemen Kesehatan Mental</title>
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
            <form method="POST" action="/login" class="card" autocomplete="off">
                <h1>Sistem Informasi Manajemen Kesehatan Mental</h1>
                <p class="lead">Masuk untuk mengakses fitur</p>

                <!-- ERROR -->
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input id="username" name="username" type="text" class="form-control" placeholder="Masukkan username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" name="password" type="password" class="form-control" placeholder="Masukkan password" required>
                    <div class="text-end mt-1">
                        <a href="/forgot-password" style="font-size:13px;color:#0ea5a4;text-decoration:none;font-weight:600;">Lupa password?</a>
                    </div>
                </div>

                <button type="submit" id="btnLogin" class="btn btn-primary w-100">
                    <span id="btnText">Masuk</span>
                    <span id="btnLoading" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                </button>

                <hr>

                <div class="text-center">
                    <small>
                        Belum punya akun?
                        <a href="/register">Daftar di sini</a>
                    </small>
                </div>

                <div class="back-home">
                    <a href="/">&larr; Kembali ke Beranda</a>
                </div>

                <div class="footer-note">&copy; <?= date('Y') ?> SIMKM</div>
            </form>
        </div>
    </div>

    <!-- TOAST SUCCESS (shown once, right after a successful registration) -->
    <?php if (!empty($successRegister)): ?>
        <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
            <div id="toastSuccess" class="toast text-bg-success border-0">
                <div class="d-flex">
                    <div class="toast-body">
                        <?= htmlspecialchars($successRegister) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- TOAST ERROR -->
    <?php if ($error): ?>
        <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
            <div id="toastError" class="toast text-bg-danger border-0">
                <div class="d-flex">
                    <div class="toast-body">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ['toastSuccess', 'toastError'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    new bootstrap.Toast(el, {
                        delay: 5000
                    }).show();
                }
            });

            var form = document.querySelector('form.card');
            var btn = document.getElementById('btnLogin');
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