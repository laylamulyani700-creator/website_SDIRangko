<?php
require_once __DIR__ . '/koneksi.php';

// Jalur super prioritas: Jika mendeteksi halaman detail berita, langsung eksekusi tanpa babibu
if (isset($_GET['page']) && $_GET['page'] === 'detail_berita') {
    include __DIR__ . '/detail_berita.php';
    exit;
}

// Ambil parameter halaman dari URL (misal: index.php?page=berita)
$page = isset($_GET['page']) ? preg_replace('/[^a-z_]/', '', $_GET['page']) : '';

// Jika tidak ada halaman yang dipilih, arahkan ke halaman utama
if ($page === '') {
    header('Location: utama.php');
    exit;
}

// KHUSUS HALAMAN BERITA: Langsung panggil file berita.php tanpa lewat router lama
if ($page === 'berita') {
    include __DIR__ . '/berita.php'; 
    exit;
}

/* Sisa halaman lainnya tetap dilemparkan ke router bawaan template */
require_once __DIR__ . '/includes/router.php';
dispatchPortalPage($page);
exit;
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>PPDB Online - Portal Penerimaan Peserta Didik Baru</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
          tailwind.config = {
            darkMode: "class",
            theme: {
              extend: {
                "colors": {
                        "on-tertiary": "#ffffff",
                        "error": "#ba1a1a",
                        "tertiary-fixed": "#ffdea6",
                        "surface-container-highest": "#e0e3e5",
                        "outline-variant": "#c4c6cf",
                        "tertiary-fixed-dim": "#f7bd48",
                        "on-tertiary-container": "#ca9621",
                        "on-secondary": "#ffffff",
                        "on-tertiary-fixed": "#271900",
                        "outline": "#74777f",
                        "secondary-fixed-dim": "#8bd6b6",
                        "background": "#f7f9fb",
                        "primary-container": "#1a365d",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed-variant": "#00513b",
                        "surface-container-low": "#f2f4f6",
                        "on-surface-variant": "#43474e",
                        "on-tertiary-fixed-variant": "#5d4200",
                        "on-primary-fixed": "#001b3c",
                        "inverse-primary": "#adc7f7",
                        "primary": "#002045",
                        "primary-fixed": "#d6e3ff",
                        "surface-bright": "#f7f9fb",
                        "on-primary-fixed-variant": "#2d476f",
                        "on-error-container": "#93000a",
                        "secondary-fixed": "#a6f2d1",
                        "surface-dim": "#d8dadc",
                        "on-secondary-fixed": "#002116",
                        "on-primary-container": "#86a0cd",
                        "inverse-surface": "#2d3133",
                        "primary-fixed-dim": "#adc7f7",
                        "secondary-container": "#a6f2d1",
                        "on-secondary-container": "#237157",
                        "secondary": "#1b6b51",
                        "surface-container": "#eceef0",
                        "tertiary-container": "#483200",
                        "on-primary": "#ffffff",
                        "on-surface": "#191c1e",
                        "on-error": "#ffffff",
                        "tertiary": "#2d1e00",
                        "surface-container-high": "#e6e8ea",
                        "on-background": "#191c1e",
                        "inverse-on-surface": "#eff1f3",
                        "surface-tint": "#455f88",
                        "surface-container-lowest": "#ffffff",
                        "surface-variant": "#e0e3e5",
                        "surface": "#f7f9fb"
                },
                "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                },
                "spacing": {
                        "stack-sm": "8px",
                        "gutter": "24px",
                        "margin-mobile": "16px",
                        "container-max": "1200px",
                        "unit": "8px",
                        "stack-md": "16px",
                        "stack-lg": "32px",
                        "margin-desktop": "40px"
                },
                "fontFamily": {
                        "headline-xl-mobile": ["Montserrat"],
                        "headline-md": ["Montserrat"],
                        "body-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg-mobile": ["Montserrat"],
                        "headline-lg": ["Montserrat"],
                        "label-sm": ["Inter"],
                        "label-md": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-xl": ["Montserrat"]
                },
                "fontSize": {
                        "headline-xl-mobile": ["32px", {"lineHeight": "1.2", "fontWeight": "700"}],
                        "headline-md": ["24px", {"lineHeight": "1.4", "fontWeight": "600"}],
                        "body-sm": ["14px", {"lineHeight": "1.5", "fontWeight": "400"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "headline-lg-mobile": ["24px", {"lineHeight": "1.3", "fontWeight": "600"}],
                        "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                        "label-sm": ["12px", {"lineHeight": "1.2", "fontWeight": "500"}],
                        "label-md": ["14px", {"lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "headline-xl": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}]
                }
              },
            },
          }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .stats-card-border {
            border-left: 4px solid #002045;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
        }
        body {
            min-height: max(884px, 100dvh);
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col">