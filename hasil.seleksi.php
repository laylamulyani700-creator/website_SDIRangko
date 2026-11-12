<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menyambungkan ke database dan komponen template
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout_head.php';

$siswa = null;
$error_msg = '';
$searched = false;

if (isset($_POST['cek_status'])) {
    $id_siswa = trim($_POST['id_siswa'] ?? '');
    $searched = true;

    if ($id_siswa === '') {
        $error_msg = 'Silakan masukkan Nomor Pendaftaran Anda.';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            'SELECT cs.nama_lengkap, cs.id_siswa, cs.jenis_kelamin, cs.tempat_lahir, cs.tanggal_lahir, p.status, p.tanggal_daftar 
             FROM calon_siswa cs
             LEFT JOIN pendaftaran p ON cs.id_siswa = p.id_siswa
             WHERE cs.id_siswa = ?'
        );
        mysqli_stmt_bind_param($stmt, 'i', $id_siswa);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        if ($res && mysqli_num_rows($res) > 0) {
            $siswa = mysqli_fetch_assoc($res);
        } else {
            $error_msg = 'Nomor pendaftaran tidak ditemukan. Periksa kembali nomor Anda.';
        }
        mysqli_stmt_close($stmt);
    }
}

layout_head('Pengumuman Hasil Seleksi PPDB — SDi rangko', 'pengumuman', true);
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap');
    
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: radial-gradient(circle at top right, #e0f2fe 0%, #f0f7ff 100%);
        min-height: 100vh;
    }

    .print-only {
        display: none;
    }

    /* FILTER DAN KONSISTENSI LAYOUT CETAK SURAT RESMI (A4) */
    @media print {
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
        }
        
        /* Hilangkan elemen global non-cetak */
        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 12pt;
        }

        header, form, .no-print, .instruction-text, .print-btn-container, footer {
            display: none !important;
        }

        /* Tampilkan elemen khusus cetak */
        .print-only {
            display: block !important;
        }

        /* Reset container kartu agar menyatu dengan kertas */
        .print-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            width: 100% !important;
        }

        /* Kop Surat & Garis Pembatas */
        .kop-line {
            border-top: 3px solid #000000 !important;
            border-bottom: 1px solid #000000 !important;
            height: 4px;
            margin-top: 8px !important;
            margin-bottom: 25px !important;
        }

        /* Pengaturan Tabel Data Saat Dicetak */
        .data-table-print {
            width: 100% !important;
            max-width: 100% !important;
            margin-top: 25px !important;
            border-collapse: collapse;
        }

        .data-table-print td {
            padding: 10px 6px !important;
            border-bottom: 1px solid #d1d5db !important;
            font-size: 11pt !important;
            color: #000000 !important;
        }

        /* Tanda Tangan */
        .print-signature {
            margin-top: 50px !important;
            page-break-inside: avoid;
        }
    }
</style>

<header class="no-print sticky top-0 z-40 bg-white/80 backdrop-blur-lg border-b border-sky-100 shadow-sm shadow-sky-600/5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-2">
        <a href="utama.php" class="font-display font-extrabold text-sky-950 tracking-tight text-base sm:text-lg no-underline flex items-center gap-2">
            <span class="w-2.5 h-6 bg-gradient-to-b from-sky-400 to-sky-500 rounded-full"></span> <span class="hidden sm:inline">SDi Rangko</span>
        </a>
        <a href="utama.php" class="text-xs font-bold text-sky-700 hover:text-white hover:bg-sky-500 transition-all no-underline px-3 sm:px-4 py-1.5 sm:py-2 bg-sky-50 rounded-lg sm:rounded-xl border border-sky-100">
            Kembali
        </a>
    </div>
</header>

<main class="max-w-2xl mx-auto px-4 sm:px-6 py-6 sm:py-12">
    <div class="text-center mb-6 sm:mb-10 no-print instruction-text">
        <span class="inline-flex items-center gap-2 px-3 py-1 sm:py-1.5 rounded-full text-xs font-bold bg-sky-50 text-sky-600 border border-sky-100 mb-2 sm:mb-4 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span> Portal Pengumuman PPDB
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-sky-950 tracking-tight">Hasil Seleksi Pendaftaran</h1>
        <p class="text-xs sm:text-sm text-sky-700/70 max-w-md mx-auto mt-2 leading-relaxed">
            Masukkan Nomor Pendaftaran atau ID Siswa untuk memvalidasi kelulusan administrasi berkas secara acuan resmi.
        </p>
    </div>

    <div class="no-print bg-white border border-sky-100 rounded-2xl shadow-[0_12px_40px_rgba(2,132,199,0.03)] p-4 sm:p-6 mb-6 sm:mb-8">
        <form method="POST" class="space-y-3 sm:space-y-4">
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-sky-950 uppercase tracking-widest pl-0.5">Nomor Pendaftaran / ID Siswa</label>
                <input type="number" name="id_siswa" required placeholder="Contoh: 20260013" 
                       class="w-full px-3 sm:px-4 py-2.5 sm:py-3.5 bg-sky-50/30 border border-sky-100 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10 rounded-xl text-sm font-medium transition-all outline-none text-sky-950 placeholder:text-sky-300" 
                       value="<?= htmlspecialchars($_POST['id_siswa'] ?? ''); ?>"/>
            </div>
            <button type="submit" name="cek_status" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-2.5 sm:py-3.5 px-4 rounded-xl text-sm transition-all duration-300 shadow-md shadow-sky-100">
                Cek Status Kelulusan
            </button>
        </form>
    </div>

    <?php if ($searched): ?>
        <?php if ($error_msg !== ''): ?>
            <div class="no-print p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-sm text-center font-medium shadow-sm">
                <?= htmlspecialchars($error_msg); ?>
            </div>
        <?php elseif ($siswa): ?>
            
            <div class="print-card bg-white border border-sky-100 rounded-2xl shadow-[0_20px_50px_rgba(2,132,199,0.06)] p-8 relative overflow-hidden">
                
                <div class="print-only">
                    <div class="text-center">
                        <h2 style="font-family: 'Playfair Display', serif; font-size: 20px; font-weight: bold; letter-spacing: 0.5px; margin: 0;">YAYASAN PENDIDIKAN SDI RANGKO</h2>
                        <h1 style="font-size: 14px; font-weight: 800; margin-top: 4px; margin-bottom: 0; letter-spacing: 0.5px;">PANITIA PENERIMAAN PESERTA DIDIK BARU (PPDB)</h1>
                        <p style="font-size: 11px; color: #4b5563; margin-top: 4px; margin-bottom: 0;">Jl. Raya Rangko No. 12, Manggarai Barat, NTT | Email: info@sdirangko.sch.id</p>
                    </div>
                    <div class="kop-line"></div>
                    <div class="text-center my-4">
                        <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; text-decoration: underline; margin: 0;">SURAT KETERANGAN HASIL SELEKSI</h3>
                        <p style="font-size: 11px; font-family: monospace; color: #64748b; margin-top: 4px;">Ref No: PPDB-2026/ID-<?= $siswa['id_siswa']; ?></p>
                    </div>
                </div>

                <div class="no-print flex justify-between items-center border-b border-sky-50 pb-5 mb-6 flex-wrap gap-3">
                    <div>
                        <span class="text-[10px] text-sky-500 uppercase tracking-widest font-extrabold">Lembar Hasil</span>
                        <h3 class="text-base font-bold text-sky-950">Status Verifikasi Calon Siswa</h3>
                    </div>
                    <div class="bg-sky-50/80 border border-sky-100 px-3.5 py-1.5 rounded-xl text-right">
                        <p class="text-[9px] text-sky-400 font-bold uppercase tracking-wider">ID Registrasi</p>
                        <p class="text-xs font-mono font-bold text-sky-700"><?= $siswa['id_siswa']; ?></p>
                    </div>
                </div>

                <div>
                    <?php 
                    $status = strtolower($siswa['status'] ?? 'menunggu');
                    if ($status === 'diterima' || $status === 'lulus'): 
                    ?>
                        <div class="text-center">
                            <div class="no-print inline-flex items-center justify-center w-16 h-16 bg-emerald-50 text-emerald-500 rounded-2xl mb-4 shadow-sm">
                                <span class="material-symbols-outlined text-4xl font-bold">verified</span>
                            </div>
                            
                            <p class="print-only text-sm text-slate-700 text-left leading-relaxed mb-4" style="text-align: justify;">
                                Berdasarkan hasil keputusan rapat pleno tim verifikator berkas PPDB SDi Rangko, dengan ini menerangkan secara resmi bahwa calon siswa di bawah ini:
                            </p>

                            <h2 class="text-2xl font-black text-emerald-600 tracking-tight uppercase print:text-xl print:text-black print:my-4">Dinyatakan: Lolos / Diterima</h2>
                            
                            <table class="data-table-print my-6 text-left mx-auto max-w-md w-full">
                                <tr>
                                    <td class="text-slate-400 font-medium w-1/3 print:text-black">Nama Lengkap</td>
                                    <td class="font-bold text-slate-900">: <?= htmlspecialchars($siswa['nama_lengkap']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-slate-400 font-medium print:text-black">Nomor Registrasi</td>
                                    <td class="font-mono font-bold text-sky-600 print:text-black">: <?= $siswa['id_siswa']; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-slate-400 font-medium print:text-black">Jenis Kelamin</td>
                                    <td class="font-semibold text-slate-800 print:text-black">: <?= htmlspecialchars($siswa['jenis_kelamin'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-slate-400 font-medium print:text-black">Tempat, Tanggal Lahir</td>
                                    <td class="font-semibold text-slate-800 print:text-black">: <?= htmlspecialchars(($siswa['tempat_lahir'] ?? '-') . ', ' . (!empty($siswa['tanggal_lahir']) ? date('d-m-Y', strtotime($siswa['tanggal_lahir'])) : '-')); ?></td>
                                </tr>
                            </table>

                            <p class="text-xs text-slate-500 leading-relaxed max-w-md mx-auto bg-slate-50/80 p-4 rounded-xl border border-slate-100 text-left mt-6 no-print">
                                <strong>Langkah Selanjutnya:</strong> Lembar digital ini merupakan dokumen bukti kelulusan sementara yang valid. Silakan cetak lembar ini dan serahkan ke loket sekolah bersama dokumen fisik asli untuk tahapan <strong>Daftar Ulang</strong>.
                            </p>

                            <div class="print-only print-signature mt-12 flex justify-end text-left">
                                <div class="text-center w-60 text-sm">
                                    <p>Manggarai Barat, <?= date('d F Y'); ?></p>
                                    <p class="font-bold mt-1">Ketua Panitia PPDB,</p>
                                    <div style="height: 70px;"></div> 
                                    <p class="font-bold underline">PANITIA SELEKSI PPDB</p>
                                    <p style="font-size: 11px; color: #4b5563;">SDi RANGKO</p>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($status === 'tidak diterima' || $status === 'ditolak'): ?>
                        <div class="text-center py-4">
                            <div class="no-print inline-flex items-center justify-center w-14 h-14 bg-rose-50 text-rose-500 rounded-xl mb-4">
                                <span class="material-symbols-outlined text-3xl">gpp_bad</span>
                            </div>
                            <h2 class="text-2xl font-extrabold text-rose-600 tracking-tight">BELUM LOLOS SELEKSI</h2>
                            <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto mt-4 bg-rose-50/40 p-4 rounded-xl border border-rose-100/50">
                                Mohon maaf, sehubungan dengan batasan akomodasi kuota rombongan belajar, berkas dengan nomor pendaftaran di atas belum dapat diloloskan tahun ini. Tetap semangat!
                            </p>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-6">
                            <div class="no-print inline-flex items-center justify-center w-14 h-14 bg-sky-50 text-sky-500 rounded-full mb-4 animate-spin">
                                <span class="material-symbols-outlined text-3xl">sync</span>
                            </div>
                            <h2 class="text-xl font-extrabold text-sky-700 tracking-tight">BERKAS SEDANG DIVERIFIKASI</h2>
                            <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto mt-3">
                                Berkas digital Anda sudah tersistem dan saat ini dalam antrean pengecekan dokumen oleh tim panitia. Harap segarkan halaman ini berkala.
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array($status, ['diterima', 'lulus'])): ?>
                        <div class="print-btn-container mt-8 pt-6 border-t border-slate-100 flex justify-center no-print">
                            <button type="button" onclick="window.print();" class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white font-bold px-6 py-3.5 rounded-xl text-xs transition-all duration-200 shadow-md shadow-sky-100">
                                <span class="material-symbols-outlined text-sm">print</span>
                                Cetak Surat Hasil Kelulusan (A4)
                            </button>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        <?php endif; ?>
    <?php endif; ?>
</main>

<?php layout_scripts(); ?>