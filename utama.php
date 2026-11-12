<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/layout_head.php';

$namaSekolah = 'SDi Rangko';
$lokasi      = 'Rangko, Desa Tanjung Boleng';

// Pilih gambar dari folder uploads jika tersedia
$uploadsDir = __DIR__ . '/uploads';
$logoPath = 'sekolah.png';
$kapsekPath = 'kapsek.jpg';
if (file_exists($uploadsDir . '/logo.png')) {
    $logoPath = 'uploads/logo.png';
} elseif (file_exists($uploadsDir . '/sekolah.png')) {
    $logoPath = 'uploads/sekolah.png';
} elseif (file_exists(__DIR__ . '/sekolah.png')) {
    $logoPath = 'sekolah.png';
}
if (file_exists($uploadsDir . '/kapsek.jpg')) {
    $kapsekPath = 'uploads/kapsek.jpg';
} elseif (file_exists(__DIR__ . '/kapsek.jpg')) {
    $kapsekPath = 'kapsek.jpg';
}

// Ambil data berita langsung di sini untuk ditampilkan di bawah
$result = false;
try {
    $query = "SELECT id, nama_berita, img, deskripsi, created_at FROM berita ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
} catch (mysqli_sql_exception $e) {
    $result = false;
}

// Inisialisasi head bawaan sistem template
layout_head($namaSekolah . ' — Beranda', 'beranda');
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo $namaSekolah; ?> - Portal Penerimaan Peserta Didik Baru</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        'primary': '#0284c7',         // Sky 600
                        'primary-dark': '#0369a1',    // Sky 700
                        'primary-light': '#e0f2fe',   // Sky 100
                        'primary-slate': '#0f172a',   // Slate 900
                        'secondary': '#059669',       // Emerald 600
                        'secondary-dark': '#047857',  // Emerald 700
                        'bg-custom': '#f0f7ff',       // Sky 50 background
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        display: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f0f7ff;
        }
    </style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col pt-20">

<header class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-lg border-b border-sky-100 shadow-sm h-20">
    <div class="flex items-center justify-between px-6 md:px-12 h-20 max-w-7xl mx-auto w-full">
        <a href="#home" class="flex items-center gap-3 no-underline group">
            <div class="w-9 h-9 md:w-11 md:h-11 bg-transparent rounded-xl flex items-center justify-center shadow-md p-0 md:p-1 group-hover:scale-105 transition-all duration-300 border border-sky-100">
                <img src="<?php echo $logoPath; ?>" alt="Logo <?php echo $namaSekolah; ?>" class="w-full h-full object-contain" onerror="this.src='<?php echo $logoPath; ?>'">
            </div>
            <div>
                <h1 class="font-bold text-lg text-primary-slate leading-none tracking-tight"><?php echo $namaSekolah; ?></h1>
                <span class="text-[10px] font-semibold text-primary uppercase tracking-widest block mt-0.5">Kec. Boleng</span>
            </div>
        </a>
        
        <nav class="hidden md:flex items-center gap-8">
            <a class="text-primary font-bold py-1 border-b-2 border-primary transition-all" href="#home">Home</a>
            <a class="text-slate-600 font-semibold py-1 hover:text-primary transition-colors" href="#about">About</a>
            <a class="text-slate-600 font-semibold py-1 hover:text-primary transition-colors" href="#pendaftaran">Pendaftaran</a>
            <a class="text-slate-600 font-semibold py-1 hover:text-primary transition-colors" href="<?php echo asset('hasil.seleksi.php'); ?>">Hasil Seleksi</a>
            <a class="text-slate-600 font-semibold py-1 hover:text-primary transition-colors" href="#berita">Berita</a>
        </nav>
        
        <div class="flex items-center gap-4">
            <a href="<?php echo asset('login.php'); ?>" class="hidden sm:inline-block bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-xl font-bold text-xs transition-all shadow-md shadow-sky-100">Masuk Petugas</a>
            <button id="btn-menu-mobile" class="md:hidden p-2 rounded-xl hover:bg-sky-50 transition-colors" aria-label="Menu">
                <span class="material-symbols-outlined text-primary text-2xl">menu</span>
            </button>
        </div>
    </div>
    
    <div id="mobile-menu" class="hidden md:hidden border-t border-sky-100 bg-white px-6 py-4 flex flex-col gap-3 shadow-inner">
        <a href="#home" class="font-bold text-sm text-primary py-1">Home</a>
        <a href="#about" class="font-semibold text-sm text-slate-600 py-1">About</a>
        <a href="#pendaftaran" class="font-semibold text-sm text-slate-600 py-1">Pendaftaran</a>
        <a href="<?php echo asset('hasil.seleksi.php'); ?>" class="font-semibold text-sm text-slate-600 py-1">Hasil Seleksi</a>
        <a href="#berita" class="font-semibold text-sm text-slate-600 py-1">Berita</a>
        <a href="<?php echo asset('login.php'); ?>" class="text-sm font-bold text-primary border-t border-slate-100 pt-3 mt-1">Masuk </a>
    </div>
</header>

<main class="flex-grow">
    
    <section id="home" class="relative w-full h-[420px] md:h-[640px] flex items-center justify-center overflow-hidden text-center">
        <div class="absolute inset-0 z-0">
            <img class="w-full h-full object-cover object-center scale-105" src="<?php echo $logoPath; ?>" alt="Gedung <?php echo $namaSekolah; ?>" onerror="this.src='<?php echo $logoPath; ?>'"/>
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/80 via-slate-900/75 to-slate-950/80"></div>
        </div>
        
        <div class="relative z-10 max-w-4xl mx-auto px-6 w-full flex flex-col items-center justify-center">
            <span class="bg-secondary/20 text-white border border-secondary/30 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-6 backdrop-blur-md inline-flex items-center gap-2 shadow-sm animate-fade-in">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> PPDB Tahun <?php echo date('Y'); ?> · Kab. Manggarai Barat
            </span>
            
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-5 text-white leading-tight tracking-tight max-w-3xl">
                Selamat Datang di <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-sky-300 to-emerald-300"><?php echo $namaSekolah; ?></span>
            </h2>
            
            <p class="text-base md:text-lg lg:text-xl mb-6 text-slate-200/90 font-medium max-w-2xl leading-relaxed">
                Membentuk generasi berkarakter unggul, cerdas, kreatif, dan berakhlak mulia di tengah lingkungan belajar yang asri dan bersahabat.
            </p>
            
            <a href="https://www.google.com/maps/search/?api=1&query=SDi+Rangko+Desa+Tanjung+Boleng+Manggarai+Barat" target="_blank" class="text-xs text-sky-200 flex items-center gap-2 mb-10 bg-white/10 border border-white/20 px-4 py-2 rounded-xl backdrop-blur-sm shadow-inner hover:bg-white/20 hover:text-white transition-all group no-underline">
                <span class="material-symbols-outlined text-sm text-sky-400 group-hover:scale-110 transition-transform">location_on</span>
                <span class="hover:underline"><?php echo $lokasi; ?>, NTT</span>
            </a>
            
            <div class="flex flex-wrap justify-center items-center gap-4 w-full">
                <a href="<?php echo asset('register.php'); ?>" class="bg-primary hover:bg-primary-dark text-white px-7 py-3.5 rounded-xl font-bold text-sm transition-all duration-300 shadow-lg shadow-sky-500/20 hover:shadow-sky-500/30 no-underline flex items-center gap-2 hover:-translate-y-0.5">
                    <span class="material-symbols-outlined text-sm">app_registration</span> Mulai Pendaftaran Baru
                </a>
                <a href="<?php echo asset('hasil.seleksi.php'); ?>" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-7 py-3.5 rounded-xl font-bold text-sm transition-all duration-300 backdrop-blur-sm no-underline flex items-center gap-2 hover:-translate-y-0.5">
                    <span class="material-symbols-outlined text-sm">assignment_turned_in</span> Hasil Kelulusan
                </a>
            </div>
        </div>
    </section>

    <section id="about" class="py-16 px-6 md:px-12 max-w-7xl mx-auto w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <div class="lg:col-span-8 bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-sky-100 flex flex-col md:flex-row gap-6 md:gap-8 items-center md:items-start">
                <div class="shrink-0 w-28 h-36 md:w-36 md:h-44 rounded-2xl overflow-hidden border-4 border-sky-50 bg-sky-100 shadow-md relative group">
                    <img class="w-full h-full object-cover object-top" src="<?php echo $kapsekPath; ?>" alt="Kepala Sekolah <?php echo $namaSekolah; ?>" onerror="this.src='<?php echo $kapsekPath; ?>'"/>
                    <div class="absolute bottom-0 inset-x-0 bg-slate-900/70 py-1 text-center backdrop-blur-xs">
                        <span class="text-[10px] font-bold text-white tracking-widest uppercase">Kepala Sekolah</span>
                    </div>
                </div>
                
                <div class="flex-grow text-center md:text-left">
                    <span class="text-xs font-bold uppercase tracking-widest text-primary mb-1 inline-block">Sambutan Pengantar</span>
                    <h3 class="text-2xl font-extrabold text-primary-slate mb-3">Pendidikan Bermutu Untuk Semua</h3>
                    <p class="text-sm text-slate-600 mb-4 leading-relaxed">
                        Selamat datang di portal pendaftaran online <?php echo $namaSekolah; ?>. Kami berkomitmen menyediakan ekosistem pendidikan inklusif di <?php echo $lokasi; ?> untuk mendidik buah hati Anda secara personal, berkarakter, aman, dan berdaya saing global.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-semibold text-slate-700">
                        <div class="flex items-center justify-center md:justify-start gap-2"><span class="text-emerald-500 font-bold text-base">✓</span> Tenaga Pengajar Pengalaman</div>
                        <div class="flex items-center justify-center md:justify-start gap-2"><span class="text-emerald-500 font-bold text-base">✓</span> Fasilitas Kelas Kondusif</div>
                        <div class="flex items-center justify-center md:justify-start gap-2"><span class="text-emerald-500 font-bold text-base">✓</span> Kurikulum Karakter & Keagamaan</div>
                        <div class="flex items-center justify-center md:justify-start gap-2"><span class="text-emerald-500 font-bold text-base">✓</span> Pendekatan Belajar Aktif</div>
                    </div>
                </div>
            </div>
            
            <div class="lg:col-span-4 grid grid-cols-2 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-primary border-y border-r border-sky-100 flex flex-col justify-center">
                    <span class="material-symbols-outlined text-primary text-2xl mb-1">groups</span>
                    <p class="text-2xl font-extrabold text-primary-slate">120+</p>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">Siswa Aktif</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-secondary border-y border-r border-sky-100 flex flex-col justify-center">
                    <span class="material-symbols-outlined text-secondary text-2xl mb-1">diversity_3</span>
                    <p class="text-2xl font-extrabold text-primary-slate">15:1</p>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">Rasio Kelas Nyaman</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-primary border-y border-r border-sky-100 flex flex-col justify-center">
                    <span class="material-symbols-outlined text-primary text-2xl mb-1">verified_user</span>
                    <p class="text-2xl font-extrabold text-primary-slate">Akred. </p>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">Standar Mutu</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-secondary border-y border-r border-sky-100 flex flex-col justify-center">
                    <span class="material-symbols-outlined text-secondary text-2xl mb-1">menu_book</span>
                    <p class="text-2xl font-extrabold text-primary-slate">Unggul</p>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">Disiplin & Kreatif</p>
                </div>
            </div>
        </div>
    </section>

    <section id="pendaftaran" class="py-16 bg-gradient-to-b from-sky-900 to-slate-950 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at top right, #059669, transparent 50%);"></div>
        <div class="relative max-w-7xl mx-auto px-6 md:px-12 text-center z-10 w-full">
            <span class="text-xs font-bold uppercase tracking-widest text-sky-400 mb-2 inline-block">Sistem Penerimaan Digital</span>
            <h2 class="text-3xl font-extrabold mb-4 tracking-tight">Alur Pendaftaran Peserta Didik</h2>
            <p class="max-w-xl mx-auto mb-12 text-sm text-slate-300 leading-relaxed">
                Pengisian berkas dilakukan sepenuhnya secara daring tanpa perlu pembuatan akun rumit. Simak tiga langkah mudah berikut:
            </p>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-3xl mx-auto mb-12 text-left">
                <div class="rounded-2xl bg-white/5 p-5 border border-white/10 backdrop-blur-md">
                    <span class="text-xs font-bold text-sky-400 bg-sky-500/10 px-2.5 py-1 rounded-md">Tahap 01</span>
                    <p class="font-bold mt-4 text-sm text-white">Isi Formulir</p>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Masukkan rincian identitas calon anak didik secara lengkap dan teliti.</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-5 border border-white/10 backdrop-blur-md">
                    <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-md">Tahap 02</span>
                    <p class="font-bold mt-4 text-sm text-white">Simpan ID Daftar</p>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Gunakan nomor pendaftaran unik hasil cetak sistem untuk pelacakan berkas.</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-5 border border-white/10 backdrop-blur-md">
                    <span class="text-xs font-bold text-sky-400 bg-sky-500/10 px-2.5 py-1 rounded-md">Tahap 03</span>
                    <p class="font-bold mt-4 text-sm text-white">Cek Kelulusan</p>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Pantau halaman Hasil Seleksi secara berkala sesuai instruksi panitia resmi.</p>
                </div>
            </div>
            
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo asset('register.php'); ?>" class="bg-secondary hover:bg-secondary-dark text-white px-8 py-4 rounded-xl font-bold text-sm transition-all shadow-lg shadow-emerald-950/20 no-underline">
                    Isi Formulir PPDB Sekarang
                </a>
            </div>
        </div>
    </section>

    <section id="berita" class="py-16 px-6 md:px-12 max-w-7xl mx-auto w-full">
        <div class="mb-12 text-center">
            <span class="text-xs font-bold uppercase tracking-widest text-primary mb-2 inline-block">Kabar Sekolah</span>
            <h2 class="text-3xl font-extrabold text-primary-slate mb-3">Informasi &amp; Agenda Terkini</h2>
            <p class="text-slate-500 max-w-xl mx-auto text-sm leading-relaxed">
                Pantau dinamika kegiatan belajar mengajar, pengumuman, dan berita terbaru seputar civitas akademika <?php echo $namaSekolah; ?>.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php 
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) { 
                    $gambar = !empty($row['img']) ? 'uploads/' . htmlspecialchars($row['img']) : 'sekolah.png';
                    $tanggal = isset($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : date('d M Y');
                    $detailUrl = 'detail_berita.php?id=' . $row['id'];
            ?>
                    <a href="<?php echo $detailUrl; ?>" class="bg-white rounded-2xl p-5 shadow-sm border border-sky-100 flex flex-col justify-between hover:-translate-y-1 hover:shadow-md transition-all duration-300 group no-underline text-inherit">
                        <div>
                            <div class="aspect-video w-full rounded-xl overflow-hidden mb-4 bg-sky-50">
                                <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="<?php echo $gambar; ?>" alt="Gambar Berita" onerror="this.onerror=null; this.src='sekolah.png';" />
                            </div>
                            <span class="text-[11px] font-bold text-primary uppercase tracking-wider"><?php echo $tanggal; ?></span>
                            <h3 class="text-base text-primary-slate font-bold mt-2 mb-2 line-clamp-2 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($row['nama_berita'] ?? 'Berita Sekolah'); ?></h3>
                            <p class="text-slate-600 text-xs leading-relaxed line-clamp-3"><?php echo htmlspecialchars(strip_tags($row['deskripsi'] ?? '')); ?></p>
                        </div>
                    </a>
            <?php 
                }
            } else {
                for ($i = 1; $i <= 3; $i++) {
            ?>
                    <article class="bg-white rounded-2xl p-5 shadow-sm border border-sky-100 flex flex-col justify-between hover:-translate-y-1 transition-all duration-300">
                        <div>
                            <div class="aspect-video w-full rounded-xl overflow-hidden mb-4 bg-sky-50">
                                <img class="w-full h-full object-cover" src="sekolah.png" alt="Simulasi Berita" onerror="this.src='https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=600'"/>
                            </div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider"><?php echo date('d M Y'); ?></span>
                            <h3 class="text-base text-primary-slate font-bold mt-2 mb-2">Pendaftaran PPDB Berkas Gelombang Resmi Dibuka</h3>
                            <p class="text-slate-600 text-xs leading-relaxed mb-4">Bagi seluruh calon wali murid baru di wilayah Manggarai Barat, pengisian formulir sistem berkas digital telah dibuka secara terpusat.</p>
                        </div>
                        <span class="inline-block text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-md self-start">Simulasi Sistem</span>
                    </article>
            <?php 
                }
            }
            ?>
        </div>
    </section>
</main>

<footer class="w-full py-12 mt-auto bg-slate-950 text-slate-400 border-t border-slate-900">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 px-6 md:px-12 max-w-7xl mx-auto w-full text-sm">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2 text-white">
                <img src="<?php echo $logoPath; ?>" alt="Logo <?php echo $namaSekolah; ?>" class="w-7 h-7 object-contain bg-white rounded p-0.5">
                <h2 class="text-lg font-bold tracking-tight"><?php echo $namaSekolah; ?></h2>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">Sistem PPDB daring resmi terintegrasi guna memberikan layanan pendaftaran prima yang akuntabel dan transparan bagi masyarakat.</p>
        </div>
        <div>
            <h4 class="font-bold text-xs text-white mb-4 uppercase tracking-wider opacity-80">Tautan Utama</h4>
            <ul class="flex flex-col gap-2.5 list-none p-0 m-0 text-xs">
                <li><a class="hover:text-white no-underline transition-colors" href="#home">Home</a></li>
                <li><a class="hover:text-white no-underline transition-colors" href="#about">About</a></li>
                <li><a class="hover:text-white no-underline transition-colors" href="#pendaftaran">Pendaftaran</a></li>
                <li><a class="hover:text-white no-underline transition-colors" href="<?php echo asset('hasil.seleksi.php'); ?>">Hasil Seleksi</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold text-xs text-white mb-4 uppercase tracking-wider opacity-80">Layanan</h4>
            <ul class="flex flex-col gap-2.5 list-none p-0 m-0 text-xs">
                <li><a class="hover:text-white no-underline transition-colors" href="<?php echo asset('status.php'); ?>">Cek Status Berkas</a></li>
                <li><a class="hover:text-white no-underline transition-colors" href="<?php echo asset('login.php'); ?>">Login Operator</a></li>
            </ul>
        </div>
        <div class="flex flex-col gap-3 text-xs">
            <h4 class="font-bold text-xs text-white mb-4 uppercase tracking-wider opacity-80">Kontak Sekolah</h4>
            <div class="flex items-start gap-2.5">
                <a href="https://www.google.com/maps/search/?api=1&query=SDi+Rangko+Desa+Tanjung+Boleng+Manggarai+Barat" target="_blank" class="flex items-start gap-2.5 hover:text-white no-underline text-inherit">
                    <span class="material-symbols-outlined text-sm text-primary mt-0.5">location_on</span>
                    <span><?php echo $lokasi; ?>, Kab. Manggarai Barat, NTT.</span>
                </a>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-sm text-primary">mail</span>
                <span>info@sdirangko.sch.id</span>
            </div>
        </div>
    </div>
    <div class="mt-12 pt-6 border-t border-slate-900 text-center px-6">
        <p class="text-xs text-slate-600">© <?php echo date('Y'); ?> <?php echo $namaSekolah; ?>. Hak Cipta Dilindungi Undang-Undang.</p>
    </div>
</footer>

<script>
    // Mobile Navigation Toggle
    const btnMenuMobile = document.getElementById('btn-menu-mobile');
    const mobileMenu = document.getElementById('mobile-menu');

    btnMenuMobile.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });

    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
        });
    });

    // Intersection Observer untuk animasi halus saat scroll
    const observerOptions = { threshold: 0.1 };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('opacity-100', 'translate-y-0');
                entry.target.classList.remove('opacity-0', 'translate-y-6');
            }
        });
    }, observerOptions);

    document.querySelectorAll('section').forEach(section => {
        section.classList.add('transition-all', 'duration-700', 'opacity-0', 'translate-y-6');
        observer.observe(section);
    });
</script>
</body>
</html>