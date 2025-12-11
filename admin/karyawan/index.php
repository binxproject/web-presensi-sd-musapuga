<?php
require_once __DIR__ . '/../../includes/auth_admin.php';

require_once __DIR__ . '/../../config/db.php';

$sql = "SELECT id, nik, nama, jabatan, status_aktif, created_at FROM employees ORDER BY created_at DESC";

$result = $conn->query($sql);

$employees = [];
if( $result ) {
    while( $row = $result->fetch_assoc() ) {
        $employees[] = $row;
    }
}

$namaAdmin = $_SESSION['admin_nama'] ?? $_SESSION['admin_username'] ?? 'Admin';

$message = isset($_GET['message']) ? $_GET['message'] : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Karyawan</title>
    <link rel="stylesheet" href="../../src/output.css">

</head>
<body class="min-h-screen bg-slate-100">
    <header class="bg-white border-b shadow-sm border-slate-200">
        <div class="flex items-center justify-between max-w-5xl px-4 py-3 mx-auto">
            <div class="flex items-center gap-2">
                <img src="../../assets/images/logo.webp" alt="Logo SD Musapuga" class="w-[40px] h-[40px]">
                <div class="leading-tight">
                    <div class="text-base font-semibold text-slate-800">Panel Admin Presensi</div>
                    <div class="text-sm text-slate-500">SD Musapuga</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="../index.php" class="text-sm leading-tight text-blue-700 hover:underline">Dashboard</a>
                <a href="logout.php" class="text-sm leading-tight text-rose-700 hover:underline">Logout</a>
            </div>
            
        </div>
    </header>
    
    <main class="max-w-5xl px-4 py-6 mx-auto">
        <section class="flex flex-col gap-3 mb-4 md:flex-row md:items-center md:justify-between ">
            <div>
                <h1 class="text-xl font-semibold md:text-2xl text-slate-800">Kelola Karyawan</h1>
                <p class="mt-1 text-sm text-slate-500">Tambah, ubah, dan atur status karyawan yang terdaftar di sistem presensi.</p>
            </div>

            <div class="flex md:justify-end">
                <a href="create.php" class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
                    <span>+ Tambah Karyawan</span>
                </a>
            </div>
        </section>

        <?php if( $message !== '' ) : ?>
            <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-emerald-200 bg-emerald-50 text-emerald-700"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <section class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b bg-slate-50 border-slate-200">
                        <tr>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">ID</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">NIK</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Nama</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Jabatan</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Status</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Tanggal Dibuat</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($employees)) : ?>
                            <tr>
                                <td colspan="7" class="px-4 py-4 text-sm text-center text-slate-500">Belum ada data karyawan.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach( $employees as $employee) : ?>
                                <tr class="border-t border-slate-100 hover:bg-slate-50/80">
                                    <td class="px-4 py-2 text-slate-700"><?= (int)$employee['id'] ?></td>
                                    <td class="px-4 py-2 text-slate-700"><?= htmlspecialchars($employee['nik']) ?></td>
                                    <td class="px-4 py-2 text-slate-800"><?= htmlspecialchars($employee['nama']) ?></td>
                                    <td class="px-4 py-2 text-slate-700"><?= htmlspecialchars($employee['jabatan']) ?></td>
                                    <td class="px-4 py-2">
                                        <?php if( $employee['status_aktif']) : ?>
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-600 text-xs px-2 py-0.5 border border-emerald-100">Aktif</span>
                                        <?php else : ?>
                                            <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-500 text-xs px-2 py-0.5 border border-slate-200">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 text-slate-700"><?= htmlspecialchars($employee['created_at']) ?></td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="edit.php?id=<?= (int)$employee['id'] ?>" class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 border border-blue-200 rounded-md hover:bg-blue-50">Edit</a>
                                            <a href="toggle_status.php?id=<?= (int)$employee['id'] ?>" onclick="return confirm('Ubah status karyawan ini?')" class="inline-flex items-center px-2 py-1 text-xs font-medium border rounded-md text-amber-700 border-amber-200 hover:bg-amber-50">Ubah Status</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </section>
        
    </main>
    

    

    
    
</body>
</html>