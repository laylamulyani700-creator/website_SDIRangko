<?php
require_once __DIR__ . '/koneksi.php';

// 1. PROSES LOGIKA BACKEND SISI SERVER
$action = isset($_GET['action']) ? $_GET['action'] : 'tampil';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error_message = '';

// Catatan: Kita manfaatkan kolom 'created_at' atau jika di database kamu bertipe DATETIME, 
// lebih aman kita masukkan teks tanggalnya ke kolom tersendiri atau digabung. 
// Di sini kita asumsikan teks tanggal dimasukkan ke input khusus. 
// Agar tidak mengubah struktur tabel database kamu, kita akan simpan teks tanggal ("1 - 15 Juli 2026") ke dalam kolom 'created_at' dengan mengubah penanganannya, atau jika error, pastikan kolom di database bertipe VARCHAR/TEXT. Jika database Anda bertipe DATETIME, silakan ubah tipe data kolom 'created_at' menjadi VARCHAR(100) di PhpMyAdmin.

// --- AKSI: PROSES SIMPAN DATA BARU ---
if ($action === 'proses_tambah' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_berita = mysqli_real_escape_string($conn, $_POST['nama_berita']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $tanggal_custom = mysqli_real_escape_string($conn, $_POST['tanggal_custom']);
    $icon_name = mysqli_real_escape_string($conn, $_POST['icon_name']);

    $query = "INSERT INTO berita (nama_berita, img, deskripsi, created_at) VALUES ('$nama_berita', '$icon_name', '$deskripsi', '$tanggal_custom')";
    if (mysqli_query($conn, $query)) {
        header("Location: berita.php"); exit;
    } else { $error_message = "Gagal menambah data: " . mysqli_error($conn); $action = 'tambah'; }
}

// --- AKSI: PROSES PERBARUI DATA ---
if ($action === 'proses_edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_berita = mysqli_real_escape_string($conn, $_POST['nama_berita']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $tanggal_custom = mysqli_real_escape_string($conn, $_POST['tanggal_custom']);
    $icon_name = mysqli_real_escape_string($conn, $_POST['icon_name']);

    $query = "UPDATE berita SET nama_berita = '$nama_berita', img = '$icon_name', deskripsi = '$deskripsi', created_at = '$tanggal_custom' WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        header("Location: berita.php"); exit;
    } else { $error_message = "Gagal memperbarui data: " . mysqli_error($conn); $action = 'edit'; }
}

// --- AKSI: PROSES HAPUS DATA ---
if ($action === 'hapus' && $id > 0) {
    mysqli_query($conn, "DELETE FROM berita WHERE id = $id");
    header("Location: berita.php"); exit;
}

// --- AMBIL DATA UNTUK HALAMAN TAMPIL ATAU EDIT ---
$data_edit = null;
if ($action === 'edit' && $id > 0) {
    $res_edit = mysqli_query($conn, "SELECT * FROM berita WHERE id = $id");
    $data_edit = mysqli_fetch_assoc($res_edit);
}

$result_berita = false;
try {
    $query = "SELECT id, nama_berita, img, deskripsi, created_at FROM berita ORDER BY id ASC";
    $result_berita = mysqli_query($conn, $query);
} catch (mysqli_sql_exception $e) {
    $result_berita = false;
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Manajemen Alur PPDB - SDI rangko</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght=600;700&family=Inter:wght=400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
</head>
<body class="bg-[#f7f9fb] text-[#191c1e] font-sans min-h-screen flex flex-col">

<header class="fixed top-0 w-full z-50 bg-white border-b border-gray-200 shadow-sm h-20">
    <div class="flex items-center justify-between px-10 h-20 max-w-[1200px] mx-auto">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[#002045] text-2xl">account_balance</span>
            <h1 class="text-xl font-bold text-[#002045]">SDI rangko</h1>
        </div>
        <nav class="hidden md:flex items-center gap-8">
            <a class="text-gray-600 font-medium py-1 hover:text-[#002045] transition-colors" href="index.php">Beranda</a>
            <a class="text-[#002045] font-bold border-b-2 border-[#002045] py-1" href="berita.php">Kelola Jadwal</a>
            <a class="text-gray-600 font-medium py-1 hover:text-[#002045] transition-colors" href="status.php">Panduan</a>
        </nav>
    </div>
</header>

<main class="mt-28 flex-grow max-w-[1200px] mx-auto px-6 w-full mb-12">

    <?php if(!empty($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if ($action === 'tambah'): ?>
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-2xl font-bold text-[#002045] mb-6 flex items-center gap-2"><span class="material-symbols-outlined">add_circle</span> Tambah Alur Pendaftaran</h2>
            <form action="berita.php?action=proses_tambah" method="POST" class="flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kegiatan</label>
                    <input type="text" name="nama_berita" placeholder="Contoh: Pendaftaran Online" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pelaksanaan (Bisa teks bebas)</label>
                    <input type="text" name="tanggal_custom" placeholder="Contoh: 1 - 15 Juli 2026" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Icon (Pilih kode Google Icon)</label>
                    <select name="icon_name" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="edit_note">Note / Edit (edit_note)</option>
                        <option value="verified">Centang / Verifikasi (verified)</option>
                        <option value="school">Sekolah / Ujian (school)</option>
                        <option value="campaign">Megaphone / Pengumuman (campaign)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Kegiatan</label>
                    <textarea name="deskripsi" rows="4" placeholder="Keterangan singkat mengenai kegiatan..." required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <a href="berita.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-5 py-2.5 rounded-lg text-sm transition-colors">Batal</a>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-lg text-sm transition-colors">Simpan Jadwal</button>
                </div>
            </form>
        </div>

    <?php elseif ($action === 'edit' && $data_edit): ?>
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-2xl font-bold text-[#002045] mb-6 flex items-center gap-2"><span class="material-symbols-outlined">edit</span> Ubah Tanggal & Detail Kegiatan</h2>
            <form action="berita.php?action=proses_edit&id=<?php echo $data_edit['id']; ?>" method="POST" class="flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kegiatan</label>
                    <input type="text" name="nama_berita" value="<?php echo htmlspecialchars($data_edit['nama_berita']); ?>" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pelaksanaan</label>
                    <input type="text" name="tanggal_custom" value="<?php echo htmlspecialchars($data_edit['created_at']); ?>" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Icon Kegiatan</label>
                    <select name="icon_name" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="edit_note" <?php echo $data_edit['img'] == 'edit_note' ? 'selected' : ''; ?>>Note / Edit (edit_note)</option>
                        <option value="verified" <?php echo $data_edit['img'] == 'verified' ? 'selected' : ''; ?>>Centang / Verifikasi (verified)</option>
                        <option value="school" <?php echo $data_edit['img'] == 'school' ? 'selected' : ''; ?>>Sekolah / Ujian (school)</option>
                        <option value="campaign" <?php echo $data_edit['img'] == 'campaign' ? 'selected' : ''; ?>>Megaphone / Pengumuman (campaign)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Kegiatan</label>
                    <textarea name="deskripsi" rows="4" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none"><?php echo htmlspecialchars($data_edit['deskripsi']); ?></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <a href="berita.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-5 py-2.5 rounded-lg text-sm transition-colors">Batal</a>
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-5 py-2.5 rounded-lg text-sm transition-colors">Perbarui Jadwal</button>
                </div>
            </form>
        </div>

    <?php else: ?>
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-[#002045] mb-2">Manajemen Jadwal & Alur PPDB</h2>
                <p class="text-sm sm:text-base text-gray-600">Kelola tanggal penting pendaftaran online, ujian seleksi, hingga pengumuman di bawah ini.</p>
            </div>
            <a href="berita.php?action=tambah" class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm shadow-md transition-all">
                <span class="material-symbols-outlined text-sm">add_circle</span> Tambah Jadwal Baru
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-700 uppercase tracking-wider text-xs font-semibold">
                            <th class="p-4 w-12 text-center">NO</th>
                            <th class="p-4 w-16 text-center">ICON</th>
                            <th class="p-4 w-48">NAMA KEGIATAN</th>
                            <th class="p-4">DESKRIPSI KETERANGAN</th>
                            <th class="p-4 w-44">TANGGAL PELAKSANAAN</th>
                            <th class="p-4 w-36 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php 
                        $no = 1;
                        if ($result_berita && mysqli_num_rows($result_berita) > 0) {
                            while ($row = mysqli_fetch_assoc($result_berita)) { 
                                $icon = !empty($row['img']) ? $row['img'] : 'calendar_today';
                        ?>
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="p-4 text-center font-medium text-gray-500"><?php echo $no++; ?></td>
                                    <td class="p-4 text-center">
                                        <span class="material-symbols-outlined text-blue-900 bg-blue-50 p-2 rounded-full"><?php echo htmlspecialchars($icon); ?></span>
                                    </td>
                                    <td class="p-4 font-bold text-[#002045]"><?php echo htmlspecialchars($row['nama_berita']); ?></td>
                                    <td class="p-4 text-gray-600"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                                    <td class="p-4 text-emerald-700 font-semibold"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="berita.php?action=edit&id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                                <span class="material-symbols-outlined text-sm">edit</span> Ubah
                                            </a>
                                            <a href="berita.php?action=hapus&id=<?php echo $row['id']; ?>" onclick="return confirm('Hapus kegiatan ini?')" class="inline-flex items-center gap-1 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                                <span class="material-symbols-outlined text-sm">delete</span> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php 
                            }
                        } else {
                            // JIKA DATABASE KOSONG, INI DUMMY DATA COCOK DENGAN REQUES-AN KAMU
                            $dummy_data = [
                                ['judul' => 'Pendaftaran Online', 'isi' => 'Mengisi formulir data diri dan unggah berkas awal melalui portal resmi PPDB.', 'waktu' => '1 - 15 Juli 2026', 'icon' => 'edit_note'],
                                ['judul' => 'Verifikasi Berkas', 'isi' => 'Tim panitia melakukan validasi keaslian dokumen yang telah diunggah oleh pendaftar.', 'waktu' => '16 - 20 Juli 2026', 'icon' => 'verified'],
                                ['judul' => 'Ujian Seleksi', 'isi' => 'Tes Academic, Wawancara, dan Tes Potensi Minat Bakat bagi calon siswa.', 'waktu' => '22 - 25 Juli 2026', 'icon' => 'school'],
                                ['judul' => 'Pengumuman Hasil', 'isi' => 'Pengumuman kelulusan akhir dapat dilihat secara mandiri melalui akun pendaftaran.', 'waktu' => '30 Juli 2026', 'icon' => 'campaign']
                            ];
                            foreach ($dummy_data as $index => $dummy) {
                        ?>
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="p-4 text-center font-medium text-gray-500"><?php echo $index + 1; ?></td>
                                    <td class="p-4 text-center">
                                        <span class="material-symbols-outlined text-blue-900 bg-blue-50 p-2 rounded-full"><?php echo $dummy['icon']; ?></span>
                                    </td>
                                    <td class="p-4 font-bold text-[#002045]"><?php echo $dummy['judul']; ?></td>
                                    <td class="p-4 text-gray-600"><?php echo $dummy['isi']; ?></td>
                                    <td class="p-4 text-emerald-700 font-semibold"><?php echo $dummy['waktu']; ?></td>
                                    <td class="p-4 text-center">
                                        <p class="text-xs text-gray-400 italic">Tekan Tambah Berita di atas untuk mengaktifkan database</p>
                                    </td>
                                </tr>
                        <?php 
                            }
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</main>

<footer class="w-full py-8 bg-[#002045] text-white mt-auto">
    <div class="max-w-[1200px] mx-auto px-10 text-center text-sm opacity-80">
        <p>© 2026 Portal Penerimaan Peserta Didik Baru (PPDB). Hak Cipta Dilindungi Undang-Undang.</p>
    </div>
</footer>

</body>
</html>