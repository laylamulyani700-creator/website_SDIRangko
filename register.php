<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/registration.php';

$error_msg   = '';
$success     = null;

if (isset($_POST['daftar_ppdb'])) {
    $result = prosesPendaftaranCalon($conn, $_POST, $_FILES); 
    if ($result['ok']) {
        $success = $result;
    } else {
        $error_msg = $result['message'];
    }
}

require_once __DIR__ . '/includes/layout_head.php';
layout_head('Formulir PPDB — SDi rangko', 'daftar', true);
?>

<style>
    :root {
        --primary: #0f172a;
        --accent: #0284c7;
        --accent-hover: #0369a1;
        --accent-soft: #f0f9ff;
        --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
    }
    
    body { 
        background: var(--bg-gradient) !important; 
        min-height: 100vh;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .premium-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 24px;
        box-shadow: 0 10px 30px -5px rgba(2, 132, 199, 0.05), 0 20px 40px -10px rgba(15, 23, 42, 0.05);
    }

    input[type="text"], input[type="date"], select, textarea {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
        width: 100%;
        background-color: #f8fafc !important;
        transition: all 0.25s ease-in-out !important;
        color: #1e293b !important;
        font-size: 0.95rem !important;
    }

    input[type="text"]:focus, input[type="date"]:focus, select:focus, textarea:focus {
        border-color: var(--accent) !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15) !important;
        outline: none !important;
    }

    .btn-premium {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%) !important;
        color: #ffffff !important;
        border: none !important;
        padding: 14px 32px !important;
        border-radius: 14px !important;
        font-weight: 700 !important;
        letter-spacing: 0.025em;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3) !important;
        transition: all 0.2s ease !important;
    }

    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(2, 132, 199, 0.4) !important;
    }

    .upload-zone {
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 14px;
        padding: 24px 20px;
        text-align: center;
        transition: all 0.2s ease;
        position: relative;
    }

    .upload-zone:hover {
        border-color: var(--accent);
        background: var(--accent-soft);
    }

    .upload-zone input[type="file"] {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    @media print {
        header, .no-print, .btn-premium { display: none !important; }
        body { background: #fff !important; }
        .premium-card { border: none !important; box-shadow: none !important; background: #fff !important; }
        .print-area { border: 2px dashed #0f172a !important; padding: 30px !important; border-radius: 12px; }
    }
</style>

<header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm no-print">
    <div class="max-w-4xl mx-auto px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-sky-600 flex items-center justify-center text-white font-black text-sm shadow-md">SR</div>
            <a href="utama.php" class="font-extrabold text-slate-800 tracking-tight no-underline text-lg">SDi rangko</a>
        </div>
        <a href="login.php" class="text-xs font-bold text-sky-600 bg-sky-50 hover:bg-sky-100 px-4 py-2 rounded-xl transition-all no-underline">Gerbang Petugas</a>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-12">
    <?php if ($success) : ?>
        <div class="premium-card p-8 sm:p-10 text-center">
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2">Pendaftaran Berhasil Terkirim!</h1>
            <p class="text-sm text-slate-500 max-w-md mx-auto mb-8 no-print">Akun dan data pendaftaran calon siswa baru telah sukses dimasukkan ke dalam database sistem pendaftaran.</p>
            
            <div class="print-area text-left bg-slate-50 border border-slate-200 rounded-2xl p-6 sm:p-8 mb-8 space-y-4 text-slate-700 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-sky-500/5 rounded-full -mr-5 -mt-5"></div>
                <div class="text-center border-b border-slate-200 pb-4 mb-4">
                    <h2 class="font-black text-slate-800 text-xl tracking-wide">SDi RANGKO</h2>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-widest mt-0.5">Bukti Registrasi PPDB Elektronik</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="sm:col-span-2 bg-sky-50 border border-sky-100 p-4 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-xs text-sky-600 block font-bold uppercase tracking-wider">Nomor Pendaftaran (ID)</span>
                            <span class="font-mono font-black text-2xl text-sky-900"><?= htmlspecialchars($success['id_siswa']); ?></span>
                        </div>
                        <span class="px-3 py-1 bg-sky-200 text-sky-800 rounded-lg text-xs font-extrabold uppercase">Gelombang <?= htmlspecialchars($success['gelombang'] ?? '1'); ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Nama Lengkap Calon Siswa</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($success['nama_lengkap']); ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Jenis Kelamin</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($success['jenis_kelamin']); ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Waktu Registrasi</span>
                        <span class="font-medium text-slate-800"><?= date('d M Y - H:i'); ?> WIB</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Verifikasi Berkas</span>
                        <span class="text-amber-600 font-bold flex items-center gap-1 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 inline">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg> Menunggu Review
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 justify-center no-print">
                <button type="button" onclick="window.print();" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-3 rounded-xl text-sm font-bold shadow-md transition-all cursor-pointer">Cetak Bukti</button>
                <a href="utama.php" class="btn-premium no-underline flex items-center justify-center">Selesai & Beranda</a>
            </div>
        </div>
    <?php else : ?>

    <div class="mb-8 text-center sm:text-left">
        <span class="px-3 py-1 bg-sky-100 text-sky-800 rounded-full text-xs font-bold uppercase tracking-wider">Formulir Mandiri</span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight mt-2">Penerimaan Siswa Baru</h1>
        <p class="text-sm text-slate-500 mt-1">Lengkapi data anak di bawah ini. Tanda bintang (<span class="text-red-500">*</span>) wajib dilengkapi.</p>
    </div>

    <?php if ($error_msg !== '') : ?>
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-xl text-sm font-medium shadow-sm">
            <span class="font-bold block">Gagal mengirim data:</span>
            <?= htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="premium-card p-6 sm:p-10 space-y-8">
        <input type="hidden" name="daftar_ppdb" value="1"/>

        <div class="space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h2 class="text-base font-black text-slate-800 uppercase tracking-wider">Identitas Calon Siswa</h2>
            </div>
            
            <div>
                <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                <input type="text" name="nama_lengkap" required placeholder="Masukkan nama sesuai akta kelahiran" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>"/>
            </div>
            
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="jenis_kelamin" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Laki-laki" <?= ($_POST['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="Perempuan" <?= ($_POST['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Gelombang Pendaftaran <span class="text-red-500">*</span></label>
                    <select name="gelombang" required>
                        <option value="1" <?= ($_POST['gelombang'] ?? '') === '1' ? 'selected' : ''; ?>>Gelombang 1 (Reguler)</option>
                        <option value="2" <?= ($_POST['gelombang'] ?? '') === '2' ? 'selected' : ''; ?>>Gelombang 2 (Susulan)</option>
                    </select>
                </div>
            </div>
            
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
                    <input type="text" name="tempat_lahir" required placeholder="Kota tempat lahir" value="<?= htmlspecialchars($_POST['tempat_lahir'] ?? ''); ?>"/>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_lahir" required value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? ''); ?>"/>
                </div>
            </div>
            
            <div>
                <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Agama</label>
                <select name="agama">
                    <option value="Islam">Islam</option>
                    <option value="Kristen">Kristen</option>
                    <option value="Katolik">Katolik</option>
                    <option value="Hindu">Hindu</option>
                    <option value="Buddha">Buddha</option>
                </select>
            </div>
        </div>

        <div class="space-y-4 pt-2">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21.75h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21.825V13.5M15.75 21.825V13.5" />
                    </svg>
                </div>
                <h2 class="text-base font-black text-slate-800 uppercase tracking-wider">Kontak & Orang Tua</h2>
            </div>
            
            <div>
                <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Alamat Domisili Lengkap <span class="text-red-500">*</span></label>
                <textarea name="alamat" rows="3" required placeholder="Tulis alamat rumah lengkap (Jalan, RT/RW, Dusun, Desa)"><?= htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
            </div>
            
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Nama Ayah Kandung <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_ayah" required placeholder="Nama lengkap ayah" value="<?= htmlspecialchars($_POST['nama_ayah'] ?? ''); ?>"/>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Nama Ibu Kandung <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_ibu" required placeholder="Nama lengkap ibu" value="<?= htmlspecialchars($_POST['nama_ibu'] ?? ''); ?>"/>
                </div>
            </div>
            
            <div>
                <label class="text-xs font-bold uppercase text-slate-500 block mb-1.5">No. Handphone / WhatsApp Aktif <span class="text-red-500">*</span></label>
                <input type="text" name="no_hp" required placeholder="Contoh: 08XXXXXXXXXX" value="<?= htmlspecialchars($_POST['no_hp'] ?? ''); ?>"/>
            </div>
        </div>

        <div class="space-y-4 pt-2">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                    </svg>
                </div>
                <h2 class="text-base font-black text-slate-800 uppercase tracking-wider">Berkas Persyaratan</h2>
            </div>
            <p class="text-xs text-slate-400 -mt-2">Format berkas yang didukung: <strong>PNG, JPG, JPEG, atau PDF</strong> dengan ukuran file maksimal <strong>2MB</strong>.</p>
            
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold uppercase text-slate-600 block mb-2">Kartu Keluarga (KK) Asli <span class="text-red-500">*</span></label>
                    <div class="upload-zone">
                        <div class="text-slate-400 flex justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-600 block">Pilih Berkas KK</span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Klik untuk cari file</span>
                        <input type="file" name="berkas_kk" required accept=".pdf,.jpg,.jpeg,.png" onchange="updateFileName(this)"/>
                        <p class="text-[11px] text-sky-600 font-semibold mt-2 hidden file-label"></p>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-slate-600 block mb-2">Akta Kelahiran Anak <span class="text-red-500">*</span></label>
                    <div class="upload-zone">
                        <div class="text-slate-400 flex justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-600 block">Pilih Berkas Akta</span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Klik untuk cari file</span>
                        <input type="file" name="berkas_akta" required accept=".pdf,.jpg,.jpeg,.png" onchange="updateFileName(this)"/>
                        <p class="text-[11px] text-sky-600 font-semibold mt-2 hidden file-label"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="btn-premium w-full sm:w-auto cursor-pointer flex items-center justify-center gap-2">
                <span>Kirim Pendaftaran Resmi</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                </svg>
            </button>
        </div>
    </form>
    <?php endif; ?>
</main>

<script>
    function updateFileName(input) {
        const label = input.parentElement.querySelector('.file-label');
        if (input.files && input.files.length > 0) {
            label.innerHTML = `
                <span class="inline-flex items-center gap-1 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    \${input.files[0].name}
                </span>
            `;
            label.classList.remove('hidden');
        } else {
            label.classList.add('hidden');
        }
    }
</script>