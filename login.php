<?php
// Pastikan session sudah dimulai di bagian paling atas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/helpers.php';

$error_msg = ""; 

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    require_once __DIR__ . '/includes/auth.php';

    $stmt = mysqli_prepare($conn, 'SELECT * FROM user WHERE username = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // --- PERUBAHAN DI SINI: Menggunakan md5() untuk mencocokkan password ---
        if ($row && md5($password) === $row['password']) {
            $role = resolveSessionRole($conn, $row);

            $_SESSION['login']    = true;
            $_SESSION['id_user']  = (int) $row['id_user'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $role;
            $_SESSION['email']    = $row['email'] ?? '';

            header('Location: index.php?page=dashboard');
            exit;
        }

        $checkExists = mysqli_prepare($conn, 'SELECT 1 FROM user WHERE username = ? LIMIT 1');
        mysqli_stmt_bind_param($checkExists, 's', $username);
        mysqli_stmt_execute($checkExists);
        $exists = mysqli_stmt_get_result($checkExists);
        $hasUser = $exists && mysqli_num_rows($exists) > 0;
        mysqli_stmt_close($checkExists);

        $error_msg = $hasUser
            ? 'Password yang Anda masukkan salah!'
            : 'Username tidak terdaftar di database!';
    } else {
        $error_msg = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    }
}

require_once __DIR__ . '/includes/layout_head.php';
layout_head('Masuk — SDi rangko', 'masuk', true);
?>
<div class="auth-wrap min-h-screen">
    <div class="auth-panel hidden md:flex">
        <a href="<?= asset('utama.php'); ?>" class="text-white/80 text-xs sm:text-sm no-underline mb-6 sm:mb-8 hover:text-white">&larr; Beranda</a>
        <p class="text-white/50 text-xs font-bold uppercase tracking-widest mb-2 sm:mb-3">Portal internal</p>
        <h2 class="font-display text-2xl md:text-3xl font-bold leading-snug mb-3 sm:mb-4">Masuk ke sistem PPDB</h2>
        <p class="text-white/75 text-sm leading-relaxed max-w-sm mb-8">
            Halaman ini hanya untuk admin dan petugas. Calon siswa mengisi formulir di halaman Daftar — tanpa akun login.
        </p>
        <ul class="space-y-3 text-sm text-white/70">
            <li class="flex gap-2"><span class="text-[var(--accent-soft)]">●</span> Calon siswa → <a href="<?= asset('register.php'); ?>" class="text-white underline">formulir PPDB</a> (tanpa login)</li>
            <li class="flex gap-2"><span class="text-[var(--accent-soft)]">●</span> Admin → <code class="bg-white/10 px-1 rounded">Role = admin</code></li>
            <li class="flex gap-2"><span class="text-[var(--accent-soft)]">●</span> Petugas → <code class="bg-white/10 px-1 rounded">Role = petugas</code></li>
        </ul>
    </div>
    <div class="auth-form-side flex-1">
        <div class="w-full max-w-md px-4 sm:px-0">
            <a href="<?= asset('utama.php'); ?>" class="md:hidden text-xs sm:text-sm font-semibold text-[var(--text-muted)] no-underline mb-4 sm:mb-6 inline-block">&larr; Beranda</a>
            <h1 class="font-display text-xl sm:text-2xl font-bold text-[var(--text)] mb-1">Masuk akun</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] mb-4 sm:mb-6">SDi rangko · PPDB Online</p>

            <?php if (!empty($error_msg)) : ?>
                <div class="alert alert-error mb-4"><?= htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>

            <form method="POST" class="card p-4 sm:p-6 md:p-8 space-y-3 sm:space-y-4">
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" required placeholder="admin atau username siswa" autocomplete="username"/>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required placeholder="••••••••" autocomplete="current-password"/>
                        <button type="button" data-toggle-password="password" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-[var(--text-muted)] bg-transparent border-0 cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                        </button>
                    </div>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-full mt-2">Masuk</button>
            </form>

            <p class="text-center text-xs sm:text-sm text-[var(--text-muted)] mt-4 sm:mt-6">
                Calon siswa? <a href="<?= asset('register.php'); ?>" class="font-bold text-[var(--accent)] no-underline hover:underline">Formulir pendaftaran</a>
            </p>
        </div>
    </div>
</div>
<?php layout_scripts(); ?>