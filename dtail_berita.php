<?php
// Mengaktifkan laporan eror internal untuk mempermudah pelacakan jika ada kendala database
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/koneksi.php';

$namaSekolah = 'SDI rangko';

// 1. Ambil ID Berita/Jadwal dari URL secara aman
$id_berita = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Ambil data spesifik dari database
$berita = null;
if ($id_berita > 0) {
    $query = "SELECT * FROM berita WHERE id = $id_berita";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $berita = mysqli_fetch_assoc($result);
    }
}

// Jika data tidak ditemukan di database, kembalikan otomatis ke index utama
if (!$berita) {
    header("Location: index.php");
    exit;
}

// Cek ketersediaan icon atau gambar kustom
$icon = !empty($berita['img']) ? htmlspecialchars($berita['img']) : 'calendar_today';
$tanggal_custom = isset($berita['created_at']) ? date('d M Y', strtotime($berita['created_at'])) : date('d M Y'); 
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo htmlspecialchars($berita['nama_berita']); ?> - <?php echo $namaSekolah; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }
    </style>
</head>
<body class="text-[#191c1e] min-h-screen flex flex-col pt-12 pb-12">

    <div class="max-w-2xl mx-auto w-full px-6">
        <div class="mb-6">
            <a href="index.php" class="inline-flex items-center gap-2 text-sm font-semibold text-[#002045] hover:opacity-80 transition-opacity bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-200 no-underline">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Beranda
            </a>
        </div>

        <article class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-200 text-center">
            
            <div class="inline-flex items-center justify-center w-20 h-20 text-[#002045] bg-blue-50 rounded-full mb-6 mx-auto border border-blue-100">
                <?php if(strpos($icon, '.') === false): ?>
                    <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1"><?php echo $icon; ?></span>
                <?php else: ?>
                    <img src="uploads/<?php echo $icon; ?>" class="w-full h-full object-cover rounded-full" onerror="this.src='sekolah.png'">
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 uppercase tracking-wide">
                    Waktu: <?php echo htmlspecialchars($tanggal_custom); ?>
                </span>
            </div>
            
            <h1 class="text-2xl md:text-3xl font-bold text-[#002045] mb-6 leading-tight">
                <?php echo htmlspecialchars($berita['nama_berita']); ?>
            </h1>

            <hr class="border-gray-100 my-6">

            <div class="text-gray-600 text-base leading-relaxed text-left whitespace-pre-line bg-gray-50 p-5 rounded-xl border border-gray-100">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Deskripsi Kegiatan:</h3>
                <?php echo nl2br(htmlspecialchars($berita['deskripsi'])); ?>
            </div>
        </article>
    </div>

</body>
</html>