<?php
require_once __DIR__ . '/../includes/auth_admin.php';

$namaAdmin = isset($_SESSION['admin_nama']) ? $_SESSION['admin_nama'] : $_SESSION['admin_username'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../src/output.css">
</head>
<body class="min-h-screen bg-slate-100">
    <header class="bg-white border-b shadow-sm border-slate-200">
        <div class="flex items-center justify-between max-w-5xl px-4 py-3 mx-auto">
            <div class="flex items-center gap-2">
                <img src="../assets/images/logo.webp" alt="Logo SD Musapuga" class="w-[40px] h-[40px]">
                <div class="leading-tight">
                    <div class="text-base font-semibold text-slate-800">Panel Admin Presensi</div>
                    <div class="text-sm text-slate-500">SD Musapuga</div>
                </div>
                
            </div>
            <a href="logout.php" class="text-sm leading-tight text-rose-700 hover:underline">Logout</a>
        </div>
    </header>

    <main class="max-w-5xl px-4 py-6 mx-auto">
        <section class="mb-6">
            <div class="flex flex-col gap-4 p-5 bg-white border shadow-sm border-slate-200 rounded-2xl md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl text-slate-800">Selamat datang, <?= htmlspecialchars($namaAdmin) ?></h1>
                    <p class="mt-1 text-sm text-slate-500">Ini adalah dashboard untuk mengelola presensi karyawan di SD Musapuga.</p>
                </div>

                <div class="flex flex-col text-sm text-slate-600">
                    <span class="font-medium text-slate-700">Ringkasan singkat : </span>
                    <span>• Kelola data karyawan</span>
                    <span>• Lihat presensi harian & bulanan</span>
                    <span>• Atur presensi manual (izin/sakit)</span>
                </div>
            </div>
        </section>

        <section>
            <h2 class="mb-3 text-sm font-semibold text-slate-700">Menu Utama</h2>

            <div class="grid gap-4 md:grid-cols-2">
                <a href="karyawan/index.php" class="block p-4 transition-shadow bg-white border shadow-sm border-slate-200 rounded-xl hover:shadow-md hover:border-blue-400">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-slate-800">Kelola Karyawan</h3>
                        <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-600 text-xs px-2 py-0.5">Data Karyawan</span>
                    </div>
                    <p class="text-xs text-slate-500">Tambah, ubah, dan nonaktifkan karyawan yang terdaftar dalam sistem presensi.</p>
                </a>

                <a href="presensi/harian.php" class="block p-4 transition-shadow bg-white border shadow-sm border-slate-200 rounded-xl hover:shadow-md hover:border-blue-400">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-slate-800">Presensi Harian</h3>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-600 text-xs px-2 py-0.5">Rekap Harian</span>
                    </div>
                    <p class="text-xs text-slate-500">Lihat laporan presensi semua karyawan per tanggal : hadir, izin, sakit, atau alpa.</p>
                </a>

                <a href="presensi/bulanan.php" class="block p-4 transition-shadow bg-white border shadow-sm border-slate-200 rounded-xl hover:shadow-md hover:border-blue-400">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-slate-800">Presensi Bulanan</h3>
                        <span class="inline-flex items-center rounded-full bg-sky-50 text-sky-600 text-xs px-2 py-0.5">Rekap Bulanan</span>
                    </div>
                    <p class="text-xs text-slate-500">Lihat rekap presensi per karyawan dalam satu bulan lengkap dengan status tiap hari.</p>
                </a>

                <a href="presensi/pengaturan.php" class="block p-4 transition-shadow bg-white border shadow-sm border-slate-200 rounded-xl hover:shadow-md hover:border-blue-400">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-slate-800">Atur Presensi Manual</h3>
                        <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-600 text-xs px-2 py-0.5">Manual</span>
                    </div>
                    <p class="text-xs text-slate-500">Input atau koreksi presensi karyawan secara manual (izin, sakit, atau hadir tanpa presensi).</p>
                </a>
            </div>
        </section>
    </main>
    
    
</body>
</html>