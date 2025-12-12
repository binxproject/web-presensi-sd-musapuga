<?php
require_once __DIR__ . '/../../includes/auth_admin.php';

require_once __DIR__ . '/../../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if( $id <= 0 ) {
    header('Location: index.php?message=' . urlencode('ID karyawan tidak valid.'));
    exit;
}

$sql = "SELECT id, nik, nama, jabatan, status_aktif FROM employees WHERE id = $id LIMIT 1";
$result = $conn->query($sql);

if( !$result || $result->num_rows === 0 ) {
    header('Location: index.php?message=' . urlencode('Karyawan tidak ditemukan.'));
    exit;
}

$employee = $result->fetch_assoc();

$nik = $employee['nik'];
$nama = $employee['nama'];
$jabatan = $employee['jabatan'];
$statusAktif = (int)$employee['status_aktif'];

$errors = [];

if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $nik = isset($_POST['nik']) ? trim($_POST['nik']) : '';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';
    $statusAktif = isset($_POST['status_aktif']) ? (int)$_POST['status_aktif'] : 1;

    if( $nik === '' ) {
        $errors[] = 'NIK wajib diisi.';
    }

    if( $nama === '' ) {
        $errors[] = 'Nama wajib diisi.';
    }

    if( $nik !== '' ) {
        $nikEscaped = $conn->real_escape_string($nik);
        $sqlCheck = "SELECT id FROM employees WHERE nik = '$nikEscaped' AND id <> $id LIMIT 1";
        $resultCheck = $conn->query($sqlCheck);

        if( $resultCheck && $resultCheck->num_rows > 0 ) {
            $errors[] = 'NIK sudah dipakai karyawan lain.';
        }
    }

    if( empty($errors) ) {
        $nikEscaped = $conn->real_escape_string($nik);
        $namaEscaped = $conn->real_escape_string($nama);
        $jabatanEscaped = $conn->real_escape_string($jabatan);
        $statusAktif = $statusAktif ? 1 : 0;

        $sqlUpdate = "UPDATE employees SET
                        nik = '$nikEscaped',
                        nama = '$namaEscaped',
                        jabatan = '$jabatanEscaped',
                        status_aktif = $statusAktif,
                        updated_at = NOW() WHERE id = $id";
        
        if( $conn->query($sqlUpdate) ) {
            header('Location: index.php?message=' . urlencode('Data karyawan berhasil diperbarui.'));
            exit;
        } else {
            $errors[] = 'Gagal memperbarui data karyawan,';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karyawan</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/images/favicon.png">
    <link rel="stylesheet" href="../../assets/css/output.min.css">
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
        <section class="mb-4">
            <h1 class="mt-2 text-xl font-semibold md:text-2xl text-slate-800">Edit Karyawan</h1>
            <p class="mt-1 text-sm text-slate-500">Ubah data karyawan berikut sesuai kebutuhan.</p>
            <p class="mt-1 text-xs text-slate-400">ID Karyawan : <?= (int)$employee['id'] ?></p>
        </section>

        <?php if( !empty($errors) ) : ?>
            <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-rose-200 bg-rose-50 text-rose-700">
                <ul class="space-y-1 list-disc list-inside">
                    <?php foreach($errors as $error) : ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="p-5 bg-white border shadow-sm border-slate-200 rounded-2xl">
            <form method="post" action="" class="space-y-4">
                <div>
                    <label for="nik" class="block mb-1 text-sm font-medium text-slate-700">NIK</label>
                    <input type="text" id="nik" name="nik" value="<?= htmlspecialchars($nik) ?>" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                </div>
                <div>
                    <label for="nama" class="block mb-1 text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                </div>
                <div>
                    <label for="jabatan" class="block mb-1 text-sm font-medium text-slate-700">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" value="<?= htmlspecialchars($jabatan) ?>" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-slate-700">Status</label>
                    <div class="flex items-center gap-4 text-sm text-slate-700">
                        <label>
                            <input type="radio" name="status_aktif" value="1" <?= $statusAktif ? 'checked' : '' ?> class="inline-flex items-center gap-1">
                            <span>Aktif</span>
                        </label>
                        <label>
                            <input type="radio" name="status_aktif" value="0" <?= !$statusAktif ? 'checked' : '' ?> class="inline-flex items-center gap-1">
                            <span>Nonaktif</span>
                        </label>
                    </div>
                    
                </div>
                
                <div class="flex flex-wrap items-center gap-2 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>