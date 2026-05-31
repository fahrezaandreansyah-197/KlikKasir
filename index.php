<?php
session_start();

if (!empty($_SESSION['username'])) {
    header('Location: kasir.php');
    exit;
}

$error = isset($_GET['error']) ? (string) $_GET['error'] : '';
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — Kasir Toko</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-icon">
                <i data-lucide="shopping-bag"></i>
            </div>
            <h2 style="font-weight: 800; font-size: 1.5rem">Selamat Datang</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem">
                Silakan masuk ke akun kasir Anda
            </p>
        </div>

        <?php if ($error === '1'): ?>
            <div style="margin-bottom: 1rem; padding: 0.85rem 1rem; border-radius: 12px; background: #fee2e2; color: #b91c1c; font-size: 0.9rem;">
                Username atau password salah.
            </div>
        <?php endif; ?>

        <form id="loginForm" action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username">USERNAME</label>
                <div class="input-wrapper">
                    <i data-lucide="user"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="login-input"
                        placeholder="admin"
                        required />
                </div>
            </div>

            <div class="form-group">
                <label for="password">PASSWORD</label>
                <div class="input-wrapper">
                    <i data-lucide="lock"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="login-input"
                        placeholder="••••••••"
                        required />
                </div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 1rem">
                MASUK SEKARANG
            </button>
        </form>

        <div class="login-footer">
            Belum punya akses?
            <a
                href="#"
                style="color: var(--primary); font-weight: 600; text-decoration: none">Hubungi Admin</a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>