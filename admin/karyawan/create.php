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
    <link rel="stylesheet" href="../../src/output.css">

</head>
<body>
    
    <h1>Tambah Karyawan</h1>
    <p>Halo, <?= htmlspecialchars($namaAdmin) ?></p>

    <p><a href="index.php">Kembali ke daftar karyawan</a></p>

    <?php if( !empty($errors) ) : ?>
        <div>
            <ul>
                <?php foreach( $errors as $err ) : ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div>
            <label for="nik">NIK</label>
            <input type="text" id="nik" name="nik" value="<?= htmlspecialchars($nik) ?>" required>
        </div>

        <div>
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
        </div>

        <div>
            <label for="jabatan">Jabatan</label>
            <input type="text" id="jabatan" name="jabatan" value="<?= htmlspecialchars($jabatan) ?>" required>
        </div>

        <div>
            <label>Status</label>
            <label for="aktif">
                <input type="radio" id="aktif" name="status_aktif" value="1" <?= $statusAktif ? 'checked' : '' ?>>
                <span>Aktif</span>
            </label>
            <label for="nonaktif">
                <input type="radio" id="nonaktif" name="status_aktif" value="0" <?= !$statusAktif ? 'checked' : '' ?>>
                <span>Nonaktif</span>
            </label>
        </div>

        <button type="submit">Simpan</button>
    </form>



</body>
</html>