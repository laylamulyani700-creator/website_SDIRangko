<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('PORTAL_PAGE')) {
    header('Location: index.php?page=cek_pendaftaran');
    exit;
}

require_once __DIR__ . '/koneksi.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = $GLOBALS['conn'] ?? null;
}
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/registration.php';

requireStaff();

$role         = $_SESSION['role'] ?? 'user';
$username     = $_SESSION['username'] ?? 'Admin';
$current_page = defined('PORTAL_PAGE') ? PORTAL_PAGE : 'cek_pendaftaran';
$baseIndex    = 'index.php';
$flash        = '';
$filter       = $_GET['filter'] ?? 'semua';
$cari         = trim($_GET['cari'] ?? '');

// --- VALIDASI SORTING DATA ---
$sort        = $_GET['sort'] ?? 'terbaru';
$allowedSort = ['terbaru', 'terlama', 'nama_az'];
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'terbaru';
}

$allowedFilter = ['semua', 'Menunggu', 'Diterima', 'Ditolak', 'Perbaikan'];
if (!in_array($filter, $allowedFilter, true)) {
    $filter = 'semua';
}

// --- PROSES UBAH STATUS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'ubah_status') {
    $id_pendaftaran = (int) ($_POST['id_pendaftaran'] ?? 0);
    $status_baru    = $_POST['status'] ?? '';
    $id_siswa_up    = (int) ($_POST['id_siswa'] ?? 0);
    $allowed        = ['Menunggu', 'Diterima', 'Ditolak', 'Perbaikan'];
    if ($id_pendaftaran > 0 && in_array($status_baru, $allowed, true)) {
        $stmtSt = mysqli_prepare($conn, 'UPDATE pendaftaran SET status = ? WHERE id_pendaftaran = ?');
        mysqli_stmt_bind_param($stmtSt, 'si', $status_baru, $id_pendaftaran);
        if (mysqli_stmt_execute($stmtSt)) {
            $flash = 'Status berhasil diperbarui.';
            if (tableHasColumn($conn, 'calon_siswa', 'status_verifikasi') && $id_siswa_up > 0) {
                $mapVerif = [
                    'Menunggu'  => 'Dalam Proses Review',
                    'Diterima'  => 'Diterima',
                    'Ditolak'   => 'Ditolak',
                    'Perbaikan' => 'Dalam Proses Review',
                ];
                $verif = $mapVerif[$status_baru] ?? $status_baru;
                $stmtV = mysqli_prepare($conn, 'UPDATE calon_siswa SET status_verifikasi = ? WHERE id_siswa = ?');
                mysqli_stmt_bind_param($stmtV, 'si', $verif, $id_siswa_up);
                mysqli_stmt_execute($stmtV);
                mysqli_stmt_close($stmtV);
            }
        } else {
            $flash = 'Gagal memperbarui status.';
        }
        mysqli_stmt_close($stmtSt);
    }
}

// --- LOGIKA QUERY DATA SISWA DAN FILTER ---
$gelombangCol = tableHasColumn($conn, 'pendaftaran', 'gelombang') ? ', p.gelombang' : '';
$sql = 'SELECT cs.*, p.id_pendaftaran, p.status, p.tanggal_daftar' . $gelombangCol . '
        FROM calon_siswa cs
        INNER JOIN pendaftaran p ON cs.id_siswa = p.id_siswa
        WHERE 1=1';
$params = [];
$types  = '';

if ($filter !== 'semua') {
    $sql     .= ' AND p.status = ?';
    $params[] = $filter;
    $types   .= 's';
}
if ($cari !== '') {
    $sql     .= ' AND (cs.nama_lengkap LIKE ? OR CAST(cs.id_siswa AS CHAR) LIKE ? OR cs.no_hp LIKE ?)';
    $like     = '%' . $cari . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}

// --- LOGIKA SORTING DATA ---
if ($sort === 'terlama') {
    $sql .= ' ORDER BY p.tanggal_daftar ASC, cs.id_siswa ASC';
} elseif ($sort === 'nama_az') {
    $sql .= ' ORDER BY cs.nama_lengkap ASC';
} else {
    $sql .= ' ORDER BY p.tanggal_daftar DESC, cs.id_siswa DESC';
}

$stmtList = mysqli_prepare($conn, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmtList, $types, ...$params);
}
mysqli_stmt_execute($stmtList);
$listPendaftar = mysqli_stmt_get_result($stmtList);

// --- COUNT DATA UNTUK ANGKA REALTIME DI CARD ---
$qCount = mysqli_query($conn, "SELECT p.status, COUNT(*) AS jml FROM pendaftaran p GROUP BY p.status");
$counts = ['semua' => 0, 'Menunggu' => 0, 'Diterima' => 0, 'Ditolak' => 0, 'Perbaikan' => 0];
$totalAll = mysqli_query($conn, 'SELECT COUNT(*) AS t FROM pendaftaran');
if ($totalAll) {
    $counts['semua'] = (int) (mysqli_fetch_assoc($totalAll)['t'] ?? 0);
}
if ($qCount) {
    while ($c = mysqli_fetch_assoc($qCount)) {
        $counts[$c['status']] = (int) $c['jml'];
    }
}
?>

<?php
require_once __DIR__ . '/includes/layout_head.php';
layout_head('Cek Data Pendaftaran — SDi rangko', 'portal-admin', true);
?>

<header class="portal-topbar">
    <span class="font-display font-bold text-[var(--text)]">Cek Pendaftaran</span>
    <?php include __DIR__ . '/includes/header_profile_dropdown.php'; ?>
</header>

<div class="portal-layout">
    <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
    <main class="portal-main">
        <div class="max-w-6xl mx-auto space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-primary font-['Montserrat']">Cek Data Pendaftaran</h2>
                    <p class="text-sm text-on-surface-variant mt-1">Data calon siswa dari formulir PPDB online — dipantau admin dan petugas.</p>
                </div>
                <a href="<?= $baseIndex; ?>?page=dashboard" class="text-sm font-medium text-primary hover:underline inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-lg">arrow_back</span> Dashboard
                </a>
            </div>

            <?php if ($flash !== '') : ?>
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">
                <?= htmlspecialchars($flash); ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <?php
                $tabs = [
                    'semua' => ['label' => 'Semua', 'icon' => 'group', 'color' => 'bg-slate-100 text-slate-700 border-slate-200'],
                    'Menunggu' => ['label' => 'Menunggu', 'icon' => 'hourglass_empty', 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
                    'Diterima' => ['label' => 'Diterima', 'icon' => 'check_circle', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                    'Ditolak' => ['label' => 'Ditolak', 'icon' => 'cancel', 'color' => 'bg-red-50 text-red-700 border-red-200'],
                    'Perbaikan' => ['label' => 'Perbaikan', 'icon' => 'build', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                ];

                foreach ($tabs as $key => $info) :
                    $active = $filter === $key;
                    $countValue = $counts[$key] ?? 0;
                    $cardHref = $baseIndex . '?page=cek_pendaftaran&filter=' . urlencode($key) . '&sort=' . urlencode($sort) . ($cari !== '' ? '&cari=' . urlencode($cari) : '');
                ?>
                <a href="<?= $cardHref; ?>" 
                   class="p-4 rounded-xl border flex flex-col justify-between transition-all duration-200 hover:shadow-md <?= $info['color']; ?> <?= $active ? 'ring-2 ring-primary border-transparent font-bold scale-[1.02]' : 'opacity-80 hover:opacity-100'; ?>">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs uppercase font-semibold tracking-wider opacity-80"><?= $info['label']; ?></span>
                        <span class="material-symbols-outlined text-lg opacity-70"><?= $info['icon']; ?></span>
                    </div>
                    <div class="text-2xl font-bold font-mono"><?= $countValue; ?></div>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="bg-white p-4 rounded-xl border border-outline-variant/60 shadow-sm">
                <form method="GET" action="<?= $baseIndex; ?>" class="flex flex-col md:flex-row gap-3 items-center">
                    <input type="hidden" name="page" value="cek_pendaftaran"/>
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter); ?>"/>
                    
                    <div class="w-full md:w-auto flex items-center gap-2 whitespace-nowrap text-sm font-semibold text-primary">
                        <span class="inline-block w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        Mengelola Tab: <span class="underline uppercase tracking-wide"><?= $filter; ?></span>
                    </div>

                    <div class="w-full flex-1 relative">
                        <input type="search" name="cari" value="<?= htmlspecialchars($cari); ?>" 
                            placeholder="Cari nama, ID, atau HP di tab <?= htmlspecialchars($filter); ?>..."
                            class="w-full rounded-lg border border-outline-variant text-sm focus:border-primary focus:ring-primary pl-9 pr-3 py-2.5 bg-slate-50/50"/>
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    </div>
                    
                    <div class="w-full md:w-auto flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-500 whitespace-nowrap hidden lg:inline">Urutkan:</label>
                        <select name="sort" onchange="this.form.submit()" 
                            class="w-full md:w-auto rounded-lg border border-outline-variant text-sm focus:border-primary focus:ring-primary p-2.5 bg-white font-medium text-slate-700 min-w-[150px] shadow-sm cursor-pointer">
                            <option value="terbaru" <?= $sort === 'terbaru' ? 'selected' : ''; ?>>🕒 Pendaftaran Terbaru</option>
                            <option value="terlama" <?= $sort === 'terlama' ? 'selected' : ''; ?>>⏳ Pendaftaran Terlama</option>
                            <option value="nama_az" <?= $sort === 'nama_az' ? 'selected' : ''; ?>>🔤 Nama Siswa (A-Z)</option>
                        </select>
                    </div>

                    <div class="w-full md:w-auto">
                        <button type="submit" class="w-full md:w-auto px-5 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-semibold whitespace-nowrap transition-colors shadow-sm cursor-pointer">
                            Terapkan
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-outline-variant/60 uppercase tracking-wider text-slate-600 font-bold">
                                <th class="px-4 py-3 w-10 text-center">#</th>
                                <th class="px-4 py-3">ID / Nama</th>
                                <th class="px-4 py-3 hidden md:table-cell">Gelombang</th>
                                <th class="px-4 py-3 hidden lg:table-cell">L/P &amp; Lahir</th>
                                <th class="px-4 py-3 hidden sm:table-cell">Kontak</th>
                                <th class="px-4 py-3 hidden lg:table-cell">Orang Tua</th>
                                <th class="px-4 py-3 hidden sm:table-cell">Tgl Daftar</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php
                            $no = 0;
                            if ($listPendaftar && mysqli_num_rows($listPendaftar) > 0) :
                                while ($row = mysqli_fetch_assoc($listPendaftar)) :
                                    $no++;
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-4 text-center font-medium text-slate-400"><?= $no; ?></td>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-slate-900"><?= htmlspecialchars($row['nama_lengkap']); ?></p>
                                    <p class="text-[11px] font-mono text-slate-400 mt-0.5">ID: <?= (int) $row['id_siswa']; ?></p>
                                </td>
                                <td class="px-4 py-4 hidden md:table-cell font-medium">
                                    <?php
                                    $gRow = isset($row['gelombang']) ? (int) $row['gelombang'] : 0;
                                    if ($gRow === 0 && !empty($row['tanggal_daftar'])) {
                                        $gRow = (int) date('n', strtotime($row['tanggal_daftar'])) <= 6 ? 1 : 2;
                                    }
                                    ?>
                                    <span class="px-2 py-1 bg-slate-100 text-slate-800 rounded text-xs font-semibold">
                                        <?= htmlspecialchars(labelGelombang($gRow ?: 1)); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 hidden lg:table-cell text-xs leading-relaxed">
                                    <p class="font-semibold text-slate-800"><?= htmlspecialchars(formatJenisKelamin($row['jenis_kelamin'])); ?></p>
                                    <p class="text-slate-500"><?= htmlspecialchars($row['tempat_lahir']); ?>, <?= formatTanggalIndo($row['tanggal_lahir']); ?></p>
                                </td>
                                <td class="px-4 py-4 hidden sm:table-cell font-mono text-xs text-slate-600"><?= htmlspecialchars($row['no_hp'] ?? '-'); ?></td>
                                <td class="px-4 py-4 hidden lg:table-cell text-xs leading-relaxed text-slate-600">
                                    <p><span class="text-slate-400 font-medium">Ayah:</span> <?= htmlspecialchars($row['nama_ayah'] ?? '-'); ?></p>
                                    <p><span class="text-slate-400 font-medium">Ibu:</span> <?= htmlspecialchars($row['nama_ibu'] ?? '-'); ?></p>
                                </td>
                                <td class="px-4 py-4 hidden sm:table-cell text-xs text-slate-500 whitespace-nowrap"><?= formatTanggalIndo($row['tanggal_daftar']); ?></td>
                                <td class="px-4 py-4 whitespace-nowrap"><?= badgeStatusPendaftaran(statusTampilanSiswa($row)); ?></td>
                                <td class="px-4 py-4 text-center">
                                    <form method="POST" class="inline-block w-full">
                                        <input type="hidden" name="aksi" value="ubah_status"/>
                                        <input type="hidden" name="id_pendaftaran" value="<?= (int) $row['id_pendaftaran']; ?>"/>
                                        <input type="hidden" name="id_siswa" value="<?= (int) $row['id_siswa']; ?>"/>
                                        <select name="status" class="w-full text-xs rounded-lg border border-slate-200 bg-white p-1.5 font-medium shadow-xs cursor-pointer focus:border-primary focus:ring-1 focus:ring-primary" onchange="this.form.submit()">
                                            <?php foreach (['Menunggu', 'Diterima', 'Ditolak', 'Perbaikan'] as $st) : ?>
                                            <option value="<?= $st; ?>" <?= ($row['status'] ?? '') === $st ? 'selected' : ''; ?>><?= $st; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; else : ?>
                            <tr>
                                <td colspan="9" class="px-6 py-16 text-center text-slate-400 font-medium">
                                    Belum ada data pendaftaran<?= $filter !== 'semua' ? ' dengan status ini' : ''; ?>.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-xs text-on-surface-variant">
                <span class="material-symbols-outlined text-sm align-middle">info</span>
                Admin dan petugas <strong>tidak</strong> mendaftar di halaman register — akun petugas ditambahkan lewat menu Tambah Petugas.
            </p>
        </div>
    </main>
</div>

<?php layout_scripts(); ?>