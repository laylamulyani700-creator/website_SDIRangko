<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('PORTAL_PAGE')) {
    header('Location: index.php?page=profil');
    exit;
}

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/koneksi.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = $GLOBALS['conn'] ?? null;
}

$role     = $_SESSION['role'] ?? 'user';
$username = $_SESSION['username'] ?? 'Pengguna';
$id_user  = (int) ($_SESSION['id_user'] ?? 0);
$email    = $_SESSION['email'] ?? '';
$baseIndex    = 'index.php';
$current_page = defined('PORTAL_PAGE') ? PORTAL_PAGE : 'profil';

$userRow = null;
if ($id_user > 0) {
    $stmt = mysqli_prepare($conn, 'SELECT id_user, username, email FROM user WHERE id_user = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id_user);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $userRow = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
    }
}

if ($userRow) {
    $email = $userRow['email'] ?? $email;
}

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout_head.php';
$portalTheme = isStaffRole($role) ? 'portal-admin' : 'portal-siswa';
layout_head('Profil Akun — SDi rangko', $portalTheme);
?>
<header class="portal-topbar">
    <span class="font-display font-bold">Profil akun</span>
    <?php include __DIR__ . '/includes/header_profile_dropdown.php'; ?>
</header>
<div class="portal-layout">
<?php if (isStaffRole($role)) : include __DIR__ . '/includes/admin_sidebar.php'; else : include __DIR__ . '/includes/student_sidebar.php'; endif; ?>
<main class="portal-main max-w-lg">
    <a href="<?= htmlspecialchars($baseIndex, ENT_QUOTES, 'UTF-8'); ?>?page=dashboard" class="text-sm font-semibold text-[var(--accent)] no-underline mb-6 inline-flex items-center gap-1">
        <span class="material-symbols-outlined text-lg">arrow_back</span> Dashboard
    </a>
    <div class="card overflow-hidden reveal">
        <div class="p-6 border-b border-[var(--border)]">
            <h2 class="font-display text-lg font-bold">Profil akun</h2>
            <p class="text-sm text-[var(--text-muted)]">Data login Anda</p>
        </div>
        <dl class="p-6 space-y-4 text-sm">
            <div><dt class="text-xs font-bold uppercase text-[var(--text-muted)]">ID</dt><dd class="font-mono mt-1"><?= (int) $id_user; ?></dd></div>
            <div><dt class="text-xs font-bold uppercase text-[var(--text-muted)]">Username</dt><dd class="mt-1 font-medium"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></dd></div>
            <div><dt class="text-xs font-bold uppercase text-[var(--text-muted)]">Email</dt><dd class="mt-1 break-all"><?= htmlspecialchars($email !== '' ? $email : '—', ENT_QUOTES, 'UTF-8'); ?></dd></div>
            <div><dt class="text-xs font-bold uppercase text-[var(--text-muted)]">Peran</dt><dd class="mt-1"><span class="stat-pill stat-pill-wait"><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?></span></dd></div>
        </dl>
    </div>
</main>
</div>
<?php layout_scripts(); ?>
