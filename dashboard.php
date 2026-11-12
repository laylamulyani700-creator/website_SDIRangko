<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('PORTAL_PAGE')) {
    header('Location: index.php?page=dashboard');
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
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/registration.php';

$role     = $_SESSION['role'] ?? 'user';
$username = $_SESSION['username'] ?? 'Pengguna';
$id_user  = $_SESSION['id_user'] ?? null;
$email    = $_SESSION['email'] ?? '';

$current_page = defined('PORTAL_PAGE') ? PORTAL_PAGE : 'dashboard';
$baseIndex    = 'index.php';

$insertStatus   = null;
$errorMsg       = '';
if (isStaffRole($role) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'ubah_status') {
    $id_pendaftaran = (int) ($_POST['id_pendaftaran'] ?? 0);
    $status_baru    = $_POST['status'] ?? '';
    $allowed        = ['Menunggu', 'Diterima', 'Ditolak', 'Perbaikan', 'Dalam Proses Review'];
    if ($id_pendaftaran > 0 && in_array($status_baru, $allowed, true)) {
        $stmtSt = mysqli_prepare($conn, 'UPDATE pendaftaran SET status = ? WHERE id_pendaftaran = ?');
        mysqli_stmt_bind_param($stmtSt, 'si', $status_baru, $id_pendaftaran);
        $insertStatus = mysqli_stmt_execute($stmtSt) ? 'status_ok' : 'gagal';
        mysqli_stmt_close($stmtSt);
        if (tableHasColumn($conn, 'calon_siswa', 'status_verifikasi')) {
            $id_siswa_up = (int) ($_POST['id_siswa'] ?? 0);
            if ($id_siswa_up > 0) {
                $mapVerif = [
                    'Menunggu' => 'Dalam Proses Review',
                    'Diterima' => 'Diterima',
                    'Ditolak'  => 'Ditolak',
                    'Perbaikan'=> 'Dalam Proses Review',
                ];
                $verif = $mapVerif[$status_baru] ?? $status_baru;
                $stmtV = mysqli_prepare($conn, 'UPDATE calon_siswa SET status_verifikasi = ? WHERE id_siswa = ?');
                mysqli_stmt_bind_param($stmtV, 'si', $verif, $id_siswa_up);
                mysqli_stmt_execute($stmtV);
                mysqli_stmt_close($stmtV);
            }
        }
    }
}

if (isAdminRole($role) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah_siswa') {
    $nama    = trim($_POST['nama_lengkap'] ?? '');
    $jk      = $_POST['jenis_kelamin'] ?? '';
    $tempat  = trim($_POST['tempat_lahir'] ?? '');
    $tanggal = $_POST['tanggal_lahir'] ?? '';
    $alamat  = trim($_POST['alamat'] ?? '') ?: 'Belum diisi';
    $agama   = trim($_POST['agama'] ?? '') ?: 'Islam';
    $ayah  = trim($_POST['nama_ayah'] ?? '') ?: '-';
    $ibu   = trim($_POST['nama_ibu'] ?? '') ?: '-';
    $no_hp = trim($_POST['no_hp'] ?? '') ?: '-';

    $id_siswa_baru = generateIdSiswa($conn);
    $cols          = getTableColumns($conn, 'calon_siswa');
    $row           = [
        'id_siswa'          => $id_siswa_baru,
        'nama_lengkap'      => $nama,
        'jenis_kelamin'     => $jk,
        'tempat_lahir'      => $tempat,
        'tanggal_lahir'     => $tanggal,
        'alamat'            => $alamat,
        'agama'             => $agama,
        'nama_ayah'         => $ayah,
        'nama_ibu'          => $ibu,
        'no_hp'             => $no_hp,
        'status_verifikasi' => 'Dalam Proses Review',
    ];
    $insertCols = [];
    $values     = [];
    $types      = '';
    foreach ($row as $col => $val) {
        if (in_array($col, $cols, true)) {
            $insertCols[] = "`{$col}`";
            $values[]     = $val;
            $types       .= $col === 'id_siswa' ? 'i' : 's';
        }
    }
    $sql       = 'INSERT INTO calon_siswa (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', array_fill(0, count($values), '?')) . ')';
    $stmt      = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        $statusBaru = 'Menunggu';
        $gelombangAdmin = (int) ($_POST['gelombang'] ?? 1);
        if (!in_array($gelombangAdmin, [1, 2], true)) {
            $gelombangAdmin = 1;
        }
        if (tableHasColumn($conn, 'pendaftaran', 'gelombang')) {
            $stmtDaftar = mysqli_prepare($conn, 'INSERT INTO pendaftaran (id_siswa, tanggal_daftar, status, gelombang) VALUES (?, CURDATE(), ?, ?)');
            mysqli_stmt_bind_param($stmtDaftar, 'isi', $id_siswa_baru, $statusBaru, $gelombangAdmin);
        } else {
            $stmtDaftar = mysqli_prepare($conn, 'INSERT INTO pendaftaran (id_siswa, tanggal_daftar, status) VALUES (?, CURDATE(), ?)');
            mysqli_stmt_bind_param($stmtDaftar, 'is', $id_siswa_baru, $statusBaru);
        }
        $insertStatus = mysqli_stmt_execute($stmtDaftar) ? 'sukses' : 'gagal';
        if ($insertStatus === 'gagal') {
            $errorMsg = mysqli_error($conn);
        }
        mysqli_stmt_close($stmtDaftar);
    } else {
        $insertStatus = 'gagal';
        $errorMsg     = mysqli_error($conn);
        mysqli_stmt_close($stmt);
    }
}

if (isStaffRole($role)) {
    $queryTotal = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM calon_siswa');
    $dataTotal  = mysqli_fetch_assoc($queryTotal);
    $totalPendaftar = (int) ($dataTotal['total'] ?? 0);

    $queryLaki = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM calon_siswa WHERE jenis_kelamin IN ('L', 'Laki-laki')"
    );
    $totalLaki = (int) (mysqli_fetch_assoc($queryLaki)['total'] ?? 0);

    $queryPerempuan = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM calon_siswa WHERE jenis_kelamin IN ('P', 'Perempuan')"
    );
    $totalPerempuan = (int) (mysqli_fetch_assoc($queryPerempuan)['total'] ?? 0);

    $queryMenunggu = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM pendaftaran WHERE status = 'Menunggu'"
    );
    $totalMenunggu = (int) (mysqli_fetch_assoc($queryMenunggu)['total'] ?? 0);

    $queryPerbaikan = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM pendaftaran WHERE status = 'Perbaikan'"
    );
    $totalPerbaikan = (int) (mysqli_fetch_assoc($queryPerbaikan)['total'] ?? 0);

    $queryPending = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM pendaftaran WHERE status IN ('Menunggu', 'Perbaikan')"
    );
    $pendingVerification = (int) (mysqli_fetch_assoc($queryPending)['total'] ?? 0);

    $statsGelombang = hitungPendaftaranPerGelombang($conn);

    $hasGelombang = tableHasColumn($conn, 'pendaftaran', 'gelombang');
    $sqlList      = 'SELECT cs.*, p.status, p.tanggal_daftar, p.id_pendaftaran'
        . ($hasGelombang ? ', p.gelombang' : '')
        . ' FROM calon_siswa cs
         LEFT JOIN pendaftaran p ON cs.id_siswa = p.id_siswa
         ORDER BY cs.id_siswa DESC
         LIMIT 20';
    $listSiswa = mysqli_query($conn, $sqlList);
} else {
    $totalPendaftar = $totalLaki = $totalPerempuan = $totalMenunggu = 0;
    $statsGelombang = [1 => 0, 2 => 0];

    $stmtUser = mysqli_prepare(
        $conn,
        'SELECT cs.*, p.status, p.tanggal_daftar, p.id_pendaftaran
         FROM calon_siswa cs
         LEFT JOIN pendaftaran p ON cs.id_siswa = p.id_siswa
         WHERE cs.id_user = ?
         ORDER BY cs.id_siswa DESC
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtUser, 'i', $id_user);
    mysqli_stmt_execute($stmtUser);
    $listSiswa = mysqli_stmt_get_result($stmtUser);
}
?>

<?php
require_once __DIR__ . '/includes/layout_head.php';
$portalTheme = isStaffRole($role) ? 'portal-admin' : 'portal-siswa';
layout_head(isStaffRole($role) ? 'Dashboard Admin — SDi rangko' : 'Portal Siswa — SDi rangko', $portalTheme, true);
?>

<div id="toast-container" class="fixed top-20 right-4 z-[60] flex flex-col gap-2"></div>

<header class="portal-topbar">
    <div class="flex items-center gap-2">
        <span class="font-display font-bold text-[var(--text)] text-base">
            <?= isStaffRole($role) ? 'SDi rangko · Panel Admin' : 'SDi rangko · Portal Siswa'; ?>
        </span>
    </div>
    <?php include __DIR__ . '/includes/header_profile_dropdown.php'; ?>
</header>

<div class="portal-layout">
    <?php if (isStaffRole($role)) : ?>
        <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
    <?php else : ?>
        <?php include __DIR__ . '/includes/student_sidebar.php'; ?>
    <?php endif; ?>

    <main class="portal-main">
        <div class="max-w-6xl mx-auto space-y-6">
            
            <div class="space-y-stack-lg">
                <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-xl border border-outline-variant/60 shadow-sm">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2.5 h-2.5 bg-secondary rounded-full"></span>
                            <span class="text-xs text-secondary font-bold uppercase tracking-wider">Sistem Berjalan Normal</span>
                        </div>
                        <h2 class="text-2xl font-bold text-primary font-['Montserrat']">
                            <?= isStaffRole($role) ? 'Ringkasan Manajemen PPDB' : 'Selamat Datang di Portal PPDB'; ?>
                        </h2>
                    </div>
                    
                    <?php if (isStaffRole($role)) : ?>
                    <div class="flex flex-wrap gap-2 self-start sm:self-auto">
                        <a href="<?= $baseIndex; ?>?page=cek_pendaftaran" class="flex items-center gap-2 bg-secondary text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:opacity-90 shadow-md">
                            <span class="material-symbols-outlined text-sm">fact_check</span> Cek Data Pendaftaran
                        </a>
                        <?php if (isAdminRole($role)) : ?>
                        <button id="btn-buka-modal" type="button" class="flex items-center gap-2 bg-primary text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-opacity-90 shadow-md">
                            <span class="material-symbols-outlined text-sm">add_circle</span> Tambah Calon Siswa
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </section>

                <?php if (isStaffRole($role)) : ?>
                <section class="card p-6 bg-white rounded-xl shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-bold text-lg text-primary">Tautan Cepat Admin / Petugas</h3>
                            <p class="text-sm text-on-surface-variant">Klik tombol untuk langsung buka halaman yang Anda butuhkan.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-secondary">Menunggu tindakan</p>
                            <p class="text-2xl font-bold text-primary mt-1"><?= number_format($pendingVerification); ?></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                        <a href="<?= $baseIndex; ?>?page=cek_pendaftaran" class="group block rounded-2xl border border-outline-variant p-4 hover:border-primary hover:bg-primary/5 transition">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="material-symbols-outlined text-primary">fact_check</span>
                                <div>
                                    <p class="font-semibold">Cek Data Pendaftaran</p>
                                    <p class="text-xs text-on-surface-variant">Lihat semua pendaftaran siswa</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-secondary">Buka sekarang</span>
                        </a>
                        <a href="<?= $baseIndex; ?>?page=cek_berkas" class="group block rounded-2xl border border-outline-variant p-4 hover:border-primary hover:bg-primary/5 transition">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="material-symbols-outlined text-primary">folder</span>
                                <div>
                                    <p class="font-semibold">Verifikasi Berkas</p>
                                    <p class="text-xs text-on-surface-variant">Periksa berkas calon siswa</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-secondary">Buka sekarang</span>
                        </a>
                        <a href="<?= $baseIndex; ?>?page=minta_perbaikan" class="group block rounded-2xl border border-outline-variant p-4 hover:border-primary hover:bg-primary/5 transition">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="material-symbols-outlined text-primary">assignment_late</span>
                                <div>
                                    <p class="font-semibold">Minta Perbaikan</p>
                                    <p class="text-xs text-on-surface-variant">Kirim permintaan perbaikan data</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-secondary">Buka sekarang</span>
                        </a>
                        <a href="<?= $baseIndex; ?>?page=verifikasi_keputusan" class="group block rounded-2xl border border-outline-variant p-4 hover:border-primary hover:bg-primary/5 transition">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="material-symbols-outlined text-primary">gavel</span>
                                <div>
                                    <p class="font-semibold">Verifikasi Keputusan</p>
                                    <p class="text-xs text-on-surface-variant">Setujui atau tolak calon siswa</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-secondary">Buka sekarang</span>
                        </a>
                        <?php if (isAdminRole($role)) : ?>
                        <a href="<?= $baseIndex; ?>?page=input_pengumuman" class="group block rounded-2xl border border-outline-variant p-4 hover:border-primary hover:bg-primary/5 transition">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="material-symbols-outlined text-primary">campaign</span>
                                <div>
                                    <p class="font-semibold">Input Berita</p>
                                    <p class="text-xs text-on-surface-variant">Tambah pengumuman dan informasi</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-secondary">Buka sekarang</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div class="card p-4 sm:p-5 flex flex-col justify-between card-lift">
                        <div class="w-10 sm:w-12 h-10 sm:h-12 bg-primary/10 rounded-xl flex items-center justify-center"><span class="material-symbols-outlined text-primary text-2xl sm:text-3xl" style="font-variation-settings: 'FILL' 1">groups</span></div>
                        <div class="mt-3 sm:mt-4">
                            <p class="text-on-surface-variant text-[9px] sm:text-[11px] uppercase font-bold tracking-wider">Total Calon Siswa</p>
                            <h3 class="text-2xl sm:text-3xl font-bold text-primary mt-1"><?= number_format($totalPendaftar); ?> <span class="text-xs sm:text-sm font-normal text-on-surface-variant">Siswa</span></h3>
                        </div>
                    </div>
                    <div class="card p-4 sm:p-5 flex flex-col justify-between card-lift border-l-4 border-l-amber-500">
                        <div class="w-10 sm:w-12 h-10 sm:h-12 bg-amber-100 rounded-xl flex items-center justify-center"><span class="material-symbols-outlined text-amber-700 text-2xl sm:text-3xl">hourglass_top</span></div>
                        <div class="mt-3 sm:mt-4">
                            <p class="text-on-surface-variant text-[9px] sm:text-[11px] uppercase font-bold tracking-wider">Status Menunggu</p>
                            <h3 class="text-2xl sm:text-3xl font-bold text-yellow-700 mt-1"><?= number_format($totalMenunggu); ?></h3>
                        </div>
                    </div>
                    <div class="card p-4 sm:p-5 flex flex-col justify-between card-lift">
                        <div class="w-10 sm:w-12 h-10 sm:h-12 bg-emerald-100 rounded-xl flex items-center justify-center"><span class="material-symbols-outlined text-emerald-700 text-2xl sm:text-3xl">man</span></div>
                        <div class="mt-3 sm:mt-4">
                            <p class="text-on-surface-variant text-[9px] sm:text-[11px] uppercase font-bold tracking-wider">Laki-Laki</p>
                            <h3 class="text-2xl sm:text-3xl font-bold text-secondary mt-1"><?= number_format($totalLaki); ?></h3>
                        </div>
                    </div>
                    <div class="card p-4 sm:p-5 flex flex-col justify-between card-lift">
                        <div class="w-10 sm:w-12 h-10 sm:h-12 bg-violet-100 rounded-xl flex items-center justify-center"><span class="material-symbols-outlined text-violet-700 text-2xl sm:text-3xl">woman</span></div>
                        <div class="mt-3 sm:mt-4">
                            <p class="text-on-surface-variant text-[9px] sm:text-[11px] uppercase font-bold tracking-wider">Perempuan</p>
                            <h3 class="text-2xl sm:text-3xl font-bold text-on-tertiary-container mt-1"><?= number_format($totalPerempuan); ?></h3>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                    <div class="card p-4 sm:p-5">
                        <h4 class="font-bold text-primary font-['Montserrat'] text-xs sm:text-sm mb-1">Pendaftaran Gelombang 1</h4>
                        <p class="text-[11px] sm:text-xs text-on-surface-variant mb-3 sm:mb-4">Jumlah calon siswa terdaftar gelombang 1</p>
                        <div class="relative h-40 sm:h-48">
                            <canvas id="chart-gelombang-1" aria-label="Grafik gelombang 1"></canvas>
                        </div>
                        <p class="text-center mt-2 sm:mt-3 text-xl sm:text-2xl font-bold text-primary"><?= number_format($statsGelombang[1] ?? 0); ?> <span class="text-xs sm:text-sm font-normal text-on-surface-variant">pendaftar</span></p>
                    </div>
                    <div class="card p-4 sm:p-5">
                        <h4 class="font-bold text-primary font-['Montserrat'] text-xs sm:text-sm mb-1">Pendaftaran Gelombang 2</h4>
                        <p class="text-[11px] sm:text-xs text-on-surface-variant mb-3 sm:mb-4">Jumlah calon siswa terdaftar gelombang 2</p>
                        <div class="relative h-40 sm:h-48">
                            <canvas id="chart-gelombang-2" aria-label="Grafik gelombang 2"></canvas>
                        </div>
                        <p class="text-center mt-2 sm:mt-3 text-xl sm:text-2xl font-bold text-primary"><?= number_format($statsGelombang[2] ?? 0); ?> <span class="text-xs sm:text-sm font-normal text-on-surface-variant">pendaftar</span></p>
                    </div>
                </section>
                <?php else: ?>
                <section class="bg-gradient-to-r from-primary to-blue-900 text-white p-4 sm:p-6 rounded-xl shadow-sm">
                    <h3 class="text-base sm:text-lg font-bold font-['Montserrat'] mb-1">Halo, <?= htmlspecialchars($username); ?>!</h3>
                    <p class="text-xs sm:text-sm text-white/80">Silakan gunakan menu di sebelah kiri untuk mengisi formulir pendaftaran serta mengunggah berkas penunjang PPDB secara berkala.</p>
                </section>
                <?php endif; ?>

                <section class="card overflow-hidden">
                    <div class="p-3 sm:p-5 border-b border-outline-variant bg-white">
                        <h4 class="font-bold text-primary font-['Montserrat'] text-sm sm:text-base">
                            <?= isStaffRole($role) ? 'Data Calon Siswa (formulir PPDB online)' : 'Status Data Formulir Anda'; ?>
                        </h4>
                    </div>
                    <div class="overflow-x-auto text-xs sm:text-sm">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-surface-container-low border-b border-outline-variant text-[10px] sm:text-[11px] text-on-surface-variant uppercase tracking-wider font-semibold">
                                    <th class="px-3 sm:px-6 py-3 sm:py-4">ID &amp; Nama</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 hidden sm:table-cell">Jenis Kelamin</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell">Tempat, Tgl Lahir</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 hidden lg:table-cell">Alamat &amp; Agama</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 hidden lg:table-cell">Orang Tua</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 hidden sm:table-cell">No. HP</th>
                                    <?php if (isStaffRole($role)) : ?>
                                        <th class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell">Gelombang</th>
                                        <th class="px-3 sm:px-6 py-3 sm:py-4">Status PPDB</th>
                                        <th class="px-3 sm:px-6 py-3 sm:py-4 text-center">Kelola</th>
                                    <?php else: ?>
                                        <th class="px-3 sm:px-6 py-3 sm:py-4 text-center">Status</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/60 text-sm">
                                <?php if ($listSiswa && mysqli_num_rows($listSiswa) > 0): ?>
                                    <?php while ($siswa = mysqli_fetch_assoc($listSiswa)): ?>
                                    <tr class="hover:bg-surface-container-low/40 transition-colors border-b border-outline-variant/60">
                                        <td class="px-3 sm:px-6 py-3 sm:py-4">
                                            <div class="flex items-center gap-2 sm:gap-3">
                                                <div class="w-8 sm:w-9 h-8 sm:h-9 rounded-lg bg-primary text-white flex items-center justify-center font-bold text-[10px] sm:text-xs shadow-sm flex-shrink-0">
                                                    <?= strtoupper(substr($siswa['nama_lengkap'] ?? '??', 0, 2)); ?>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-primary"><?= htmlspecialchars($siswa['nama_lengkap'] ?? '-'); ?></p>
                                                    <p class="text-[11px] text-on-surface-variant">ID: <span class="font-mono font-medium"><?= $siswa['id_siswa'] ?? '-'; ?></span></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-surface-container-low text-primary rounded-md text-xs font-medium border border-outline-variant/30">
                                                <?= htmlspecialchars(formatJenisKelamin($siswa['jenis_kelamin'] ?? '')); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-on-surface"><?= htmlspecialchars($siswa['tempat_lahir'] ?? '-'); ?></p>
                                            <p class="text-xs text-on-surface-variant"><?= formatTanggalIndo($siswa['tanggal_lahir'] ?? null); ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-xs">
                                            <p class="text-on-surface max-w-[200px] truncate" title="<?= htmlspecialchars($siswa['alamat'] ?? '-'); ?>"><?= htmlspecialchars($siswa['alamat'] ?? '-'); ?></p>
                                            <p class="text-on-surface-variant mt-0.5"><?= htmlspecialchars($siswa['agama'] ?? '-'); ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-on-surface">
                                            <p><span class="text-on-surface-variant">Ayah:</span> <?= htmlspecialchars($siswa['nama_ayah'] ?? '-'); ?></p>
                                            <p class="mt-0.5"><span class="text-on-surface-variant">Ibu:</span> <?= htmlspecialchars($siswa['nama_ibu'] ?? '-'); ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-xs font-mono text-on-surface-variant">
                                            <?= htmlspecialchars($siswa['no_hp'] ?? '-'); ?>
                                        </td>
                                        <?php if (isStaffRole($role)) : ?>
                                        <td class="px-6 py-4">
                                            <?php
                                            $g = isset($siswa['gelombang']) ? (int) $siswa['gelombang'] : 0;
                                            if ($g === 0 && !empty($siswa['tanggal_daftar'])) {
                                                $g = (int) date('n', strtotime($siswa['tanggal_daftar'])) <= 6 ? 1 : 2;
                                            }
                                            ?>
                                            <span class="px-2 py-1 rounded-md text-xs font-semibold <?= $g === 2 ? 'bg-violet-100 text-violet-800' : 'bg-sky-100 text-sky-800'; ?>">
                                                <?= htmlspecialchars(labelGelombang($g ?: 1)); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?= badgeStatusPendaftaran(statusTampilanSiswa($siswa)); ?>
                                            <?php if (!empty($siswa['tanggal_daftar'])) : ?>
                                            <p class="text-[10px] text-on-surface-variant mt-1">Daftar: <?= formatTanggalIndo($siswa['tanggal_daftar']); ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <td class="px-6 py-4 text-center">
                                            <?php if (isStaffRole($role)) : ?>
                                                <?php if (!empty($siswa['id_pendaftaran'])) : ?>
                                                <form method="POST" action="<?= $baseIndex; ?>?page=dashboard" class="inline-flex flex-col gap-1 items-center">
                                                    <input type="hidden" name="aksi" value="ubah_status"/>
                                                    <input type="hidden" name="id_pendaftaran" value="<?= (int) $siswa['id_pendaftaran']; ?>"/>
                                                    <input type="hidden" name="id_siswa" value="<?= (int) $siswa['id_siswa']; ?>"/>
                                                    <select name="status" class="status-select text-xs rounded border py-1 px-2 max-w-[140px]" onchange="this.form.submit()">
                                                        <option value="Menunggu" <?= ($siswa['status'] ?? '') === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                                        <option value="Diterima" <?= ($siswa['status'] ?? '') === 'Diterima' ? 'selected' : ''; ?>>Diterima</option>
                                                        <option value="Ditolak" <?= ($siswa['status'] ?? '') === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                                        <option value="Perbaikan" <?= ($siswa['status'] ?? '') === 'Perbaikan' ? 'selected' : ''; ?>>Perbaikan</option>
                                                    </select>
                                                </form>
                                                <?php else : ?>
                                                <span class="text-xs text-on-surface-variant">—</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?= badgeStatusPendaftaran(statusTampilanSiswa($siswa)); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= isStaffRole($role) ? '9' : '7'; ?>" class="px-6 py-12 text-center text-on-surface-variant">
                                            <?= isStaffRole($role) ? 'Belum ada data calon siswa terdaftar.' : 'Anda belum mengisi kelengkapan formulir pendaftaran.'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

        </div>
    </main>
</div>

<?php if (isAdminRole($role)) : ?>
<div id="modal-siswa" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden opacity-0 transition-opacity duration-300 p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]">
        <div class="p-5 border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
            <h3 class="font-bold text-primary font-['Montserrat'] text-lg">Tambah Calon Siswa Baru</h3>
            <button id="btn-tutup-modal" class="text-on-surface-variant hover:text-error"><span class="material-symbols-outlined">close</span></button>
        </div>
        
        <form action="<?= $baseIndex; ?>?page=dashboard" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
            <input type="hidden" name="aksi" value="tambah_siswa">
            
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" required class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Jenis Kelamin *</label>
                <select name="jenis_kelamin" required class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                    <option value="L">Laki-laki (L)</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Tempat Lahir *</label>
                    <input type="text" name="tempat_lahir" required class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Tanggal Lahir *</label>
                    <input type="date" name="tanggal_lahir" required max="<?= date('Y-m-d'); ?>" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Alamat</label>
                <textarea name="alamat" rows="2" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm" placeholder="Jl. ..., RT/RW, Kelurahan"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Agama</label>
                    <select name="agama" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Nama Ayah</label>
                    <input type="text" name="nama_ayah" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Nama Ibu</label>
                    <input type="text" name="nama_ibu" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">No. HP</label>
                <input type="text" name="no_hp" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm" placeholder="08xxxxxxxxxx">
            </div>

            <div class="pt-4 border-t border-outline-variant/60 flex justify-end gap-3 sticky bottom-0 bg-white">
                <button type="button" id="btn-batal-modal" class="px-4 py-2 text-sm font-medium text-on-surface-variant hover:bg-surface-container-low rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2 text-sm font-semibold bg-primary text-white rounded-lg hover:bg-opacity-90 shadow-md">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (isStaffRole($role)) : ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const g1 = <?= (int) ($statsGelombang[1] ?? 0); ?>;
    const g2 = <?= (int) ($statsGelombang[2] ?? 0); ?>;
    const navy = '#002045';
    const gold = '#c9a227';

    function makeBarChart(canvasId, label, value, color) {
        const el = document.getElementById(canvasId);
        if (!el || typeof Chart === 'undefined') return;
        new Chart(el, {
            type: 'bar',
            data: {
                labels: [label],
                datasets: [{
                    label: 'Jumlah pendaftar',
                    data: [value],
                    backgroundColor: color,
                    borderRadius: 8,
                    maxBarThickness: 72
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    makeBarChart('chart-gelombang-1', 'Gelombang 1', g1, navy);
    makeBarChart('chart-gelombang-2', 'Gelombang 2', g2, gold);
})();
</script>
<?php endif; ?>

<script>
    // JS Modal Box (Hanya dipanggil jikalau user adalah admin)
    const modal = document.getElementById('modal-siswa');
    const btnBuka = document.getElementById('btn-buka-modal');
    const btnTutup = document.getElementById('btn-tutup-modal');
    const btnBatal = document.getElementById('btn-batal-modal');

    function bukaModal() {
        if(!modal) return;
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function tutupModal() {
        if(!modal) return;
        modal.classList.add('opacity-0');
        modal.firstElementChild.classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    if (btnBuka) btnBuka.addEventListener('click', bukaModal);
    if (btnTutup) btnTutup.addEventListener('click', tutupModal);
    if (btnBatal) btnBatal.addEventListener('click', tutupModal);

    // Fungsi Notifikasi Toast
    function createToast(message, icon = 'check_circle', isError = false) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm transition-all duration-300 transform translate-x-10 opacity-0 bg-white border-outline-variant`;
        
        toast.innerHTML = `
            <span class="material-symbols-outlined ${isError ? 'text-error' : 'text-secondary'}">${icon}</span>
            <p class="text-primary font-medium">${message}</p>
        `;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.remove('translate-x-10', 'opacity-0'); }, 10);
        setTimeout(() => {
            toast.classList.add('translate-x-10', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Panggil toast jika ada status dari PHP
    <?php if ($insertStatus === 'sukses'): ?>
        createToast('Data calon siswa berhasil disimpan!');
    <?php elseif($insertStatus === 'status_ok'): ?>
        createToast('Status pendaftaran berhasil diperbarui.');
    <?php elseif($insertStatus === 'gagal'): ?>
        createToast('Gagal menyimpan data: <?= addslashes($errorMsg); ?>', 'error', true);
    <?php endif; ?>
</script>
<?php layout_scripts(); ?>