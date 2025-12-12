<?php
require_once __DIR__ . '/../../includes/auth_admin.php';

require_once __DIR__ . '/../../config/db.php';

$nik = '';
$nama = '';
$jabatan = '';
$statusAktif = '';
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
        $sqlCheckNik = "SELECT id FROM employees WHERE nik = '$nikEscaped' LIMIT 1";
        $resultCheckNik = $conn->query($sqlCheckNik);

        if( $resultCheckNik && $resultCheckNik->num_rows > 0 ) {
            $errors[] = 'NIK sudah digunakan oleh karyawan lain.';
        }
    }

    if( empty($errors) ) {
        $nikEscaped = $conn->real_escape_string($nik);
        $namaEscaped = $conn->real_escape_string($nama);
        $jabatanEscaped = $conn->real_escape_string($jabatan);
        $statusAktif = $statusAktif ? 1 : 0;

        $sqlInsert = "INSERT INTO 
        employees (nik, nama, jabatan, status_aktif, created_at, updated_at) 
        VALUES ('$nikEscaped', '$namaEscaped', '$jabatanEscaped', $statusAktif, NOW(), NOW()) ";

        if( $conn->query($sqlInsert) ) {
            header('Location: index.php?message=' . urlencode('Karyawan berhasil ditambahkan'));
            exit;
        } else {
            $errors[] = 'Gagal menambah karyawan: ' . $conn->error;
        }
    }
}

$namaAdmin = $_SESSION['admin_nama'] ?? $_SESSION['admin_username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan</title>
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
            <a href="index.php">Kembali ke daftar karyawan</a>
            <h1 class="mt-2 text-xl font-semibold md:text-2xl text-slate-800">Tambah Karyawan</h1>
            <p class="mt-1 text-sm text-slate-500">Isi data karyawan baru yang akan dimasukkan ke dalam sistem presensi.</p>
        </section>

        <?php if( !empty($errors) ) : ?>
            <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-rose-200 bg-rose-50 text-rose-700">
                <ul class="space-y-1 list-disc list-inside">
                    <?php foreach( $errors as $err ) : ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="p-5 bg-white border shadow-sm border-slate-200 rounded-xl">
            <form method="post" action="" class="space-y-4">
                <div>
                    <label for="nik" class="block mb-1 text-sm font-medium text-slate-700">NIK <span class="text-rose-500">*</span></label>
                    <input type="text" id="nik" name="nik" value="<?= htmlspecialchars($nik) ?>" required placeholder="Masukkan NIK" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                </div>

                <div>
                    <label for="nama" class="block mb-1 text-sm font-medium text-slate-700">Nama <span class="text-rose-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required placeholder="Masukkan Nama" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                </div>

                <div>
                    <label for="jabatan" class="block mb-1 text-sm font-medium text-slate-700">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" value="<?= htmlspecialchars($jabatan) ?>" required placeholder="Masukkan Jabatan" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-slate-700">Status</label>
                    <div class="flex items-center gap-4 text-sm text-slate-700">
                        <label for="aktif">
                            <input type="radio" id="aktif" name="status_aktif" value="1" <?= $statusAktif ? 'checked' : '' ?> class="inline-flex items-center gap-1">
                            <span>Aktif</span>
                        </label>
                        <label for="nonaktif">
                            <input type="radio" id="nonaktif" name="status_aktif" value="0" <?= !$statusAktif ? 'checked' : '' ?> class="inline-flex items-center gap-1">
                            <span>Nonaktif</span>
                        </label>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>