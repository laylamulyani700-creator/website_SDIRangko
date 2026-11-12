<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('PORTAL_PAGE')) {
    header('Location: index.php?page=tambah_petugas');
    exit;
}

require_once __DIR__ . '/koneksi.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = $GLOBALS['conn'] ?? null;
}
require_once __DIR__ . '/includes/helpers.php';

requireAdminOnly();

$role         = $_SESSION['role'] ?? 'admin';
$username     = $_SESSION['username'] ?? 'Admin';
$current_page = defined('PORTAL_PAGE') ? PORTAL_PAGE : 'tambah_petugas';
$baseIndex    = 'index.php';
$error_msg    = '';
$success_msg  = '';

// --- PROSES TAMBAH PETUGAS ---
if (isset($_POST['tambah_petugas'])) {
    $result = prosesTambahPetugas($conn, $_POST);
    if ($result['ok']) {
        $success_msg = $result['message'];
    } else {
        $error_msg = $result['message'];
    }
}

// --- PROSES HAPUS PETUGAS ---
if (isset($_POST['hapus_petugas'])) {
    $id_hapus = $_POST['id_user'] ?? '';
    // Fungsi ini perlu ditambahkan di helpers.php
    $result = prosesHapusPetugas($conn, $id_hapus); 
    if ($result['ok']) {
        $success_msg = $result['message'];
    } else {
        $error_msg = $result['message'];
    }
}

// --- PROSES EDIT PETUGAS ---
if (isset($_POST['edit_petugas'])) {
    // Fungsi ini perlu ditambahkan di helpers.php
    $result = prosesEditPetugas($conn, $_POST); 
    if ($result['ok']) {
        $success_msg = $result['message'];
    } else {
        $error_msg = $result['message'];
    }
}

// Mengambil data untuk form edit (jika sedang dalam mode edit)
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_edit = mysqli_real_escape_string($conn, $_GET['id']);
    $res_edit = mysqli_query($conn, "SELECT id_user, username, email FROM user WHERE id_user = '$id_edit' AND LOWER(username) LIKE 'petugas%'");
    if ($res_edit && mysqli_num_rows($res_edit) > 0) {
        $edit_data = mysqli_fetch_assoc($res_edit);
    }
}

$listPetugas = mysqli_query(
    $conn,
    "SELECT id_user, username, email FROM user WHERE LOWER(username) LIKE 'petugas%' ORDER BY id_user DESC"
);
?>
<?php
require_once __DIR__ . '/includes/layout_head.php';
layout_head('Kelola Petugas — SDi rangko', 'portal-admin', true);
?>
<header class="portal-topbar">
    <span class="font-display font-bold text-[var(--text)]">Kelola Petugas</span>
    <?php include __DIR__ . '/includes/header_profile_dropdown.php'; ?>
</header>
<div class="portal-layout">
    <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
    <main class="portal-main">
        <div class="max-w-xl mx-auto space-y-6 reveal">
            <div>
                <h2 class="text-2xl font-bold text-primary font-['Montserrat']">
                    <?= $edit_data ? 'Ubah Petugas' : 'Tambah Petugas'; ?>
                </h2>
                <p class="text-sm text-on-surface-variant mt-1">Petugas login lewat <strong>login.php</strong>, bukan register. Hanya admin yang bisa mengelola akun petugas.</p>
            </div>

            <?php if ($success_msg) : ?><div class="alert alert-success"><?= htmlspecialchars($success_msg); ?></div><?php endif; ?>
            <?php if ($error_msg) : ?><div class="alert alert-error"><?= htmlspecialchars($error_msg); ?></div><?php endif; ?>

            <form method="POST" class="card p-6 space-y-4">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="edit_petugas" value="1"/>
                    <input type="hidden" name="id_user" value="<?= htmlspecialchars($edit_data['id_user']); ?>"/>
                <?php else: ?>
                    <input type="hidden" name="tambah_petugas" value="1"/>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-bold uppercase text-on-surface-variant mb-1">Username *</label>
                    <input type="text" name="username" required pattern="petugas.*" title="Harus diawali petugas"
                        class="w-full rounded-lg border text-sm p-2" placeholder="petugas_nama"
                        value="<?= htmlspecialchars($edit_data['username'] ?? ''); ?>"/>
                    <p class="text-[11px] text-on-surface-variant mt-1">Wajib diawali <code>petugas</code> (contoh: petugas_siti)</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-on-surface-variant mb-1">Email *</label>
                    <input type="email" name="email" required class="w-full rounded-lg border text-sm p-2"
                        value="<?= htmlspecialchars($edit_data['email'] ?? ''); ?>"/>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-on-surface-variant mb-1">
                        <?= $edit_data ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password awal *'; ?>
                    </label>
                    <input type="password" name="password" <?= $edit_data ? '' : 'required'; ?> minlength="5" class="w-full rounded-lg border text-sm p-2"/>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1">
                        <?= $edit_data ? 'Simpan Perubahan' : 'Simpan Petugas'; ?>
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="index.php?page=tambah_petugas" class="btn border px-4 py-2 text-sm rounded-lg flex align-center justify-center items-center">Batal</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="card overflow-hidden">
                <div class="px-4 py-3 border-b font-bold text-primary text-sm">Daftar akun petugas</div>
                <ul class="divide-y text-sm">
                    <?php if ($listPetugas && mysqli_num_rows($listPetugas) > 0) : ?>
                        <?php while ($p = mysqli_fetch_assoc($listPetugas)) : ?>
                        <li class="px-4 py-3 flex justify-between items-center gap-2">
                            <div class="flex flex-col truncate">
                                <span class="font-medium"><?= htmlspecialchars($p['username']); ?></span>
                                <span class="text-on-surface-variant text-xs truncate"><?= htmlspecialchars($p['email']); ?></span>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="index.php?page=tambah_petugas&action=edit&id=<?= $p['id_user']; ?>" 
                                   class="text-blue-600 hover:text-blue-800 text-xs font-semibold px-2 py-1 border border-blue-200 rounded bg-blue-50">
                                   Edit
                                </a>
                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus petugas ini?');" style="display:inline;">
                                    <input type="hidden" name="id_user" value="<?= $p['id_user']; ?>"/>
                                    <button type="submit" name="hapus_petugas" 
                                            class="text-red-600 hover:text-red-800 text-xs font-semibold px-2 py-1 border border-red-200 rounded bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <li class="px-4 py-6 text-center text-on-surface-variant">Belum ada petugas ditambahkan.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </main>
</div>
<?php layout_scripts(); ?>