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
    <link rel="stylesheet" href="../../src/output.css">

</head>
<body>
    <h1>Edit Karyawan</h1>
    <p><a href="">Kembali ke daftar karyawan</a></p>    

    <?php if( !empty($errors) ) : ?>
        <div>
            <ul>
                <?php foreach($errors as $error) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div>
            <label for="nik">NIK</label>
            <input type="text" id="nik" name="nik" value="<?= htmlspecialchars($nik) ?>">
        </div>
        <div>
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>">
        </div>
        <div>
            <label for="jabatan">Jabatan</label>
            <input type="text" id="jabatan" name="jabatan" value="<?= htmlspecialchars($jabatan) ?>">
        </div>
        <div>
            <label>Status</label>
            <label>
                <input type="radio" name="status_aktif" value="1" <?= $statusAktif ? 'checked' : '' ?>>
                <span>Aktif</span>
            </label>
            <label>
                <input type="radio" name="status_aktif" value="0" <?= !$statusAktif ? 'checked' : '' ?>>
                <span>Nonaktif</span>
            </label>
        </div>

        <button type="submit">Simpan Perubahan</button>
    </form>


</body>
</html>