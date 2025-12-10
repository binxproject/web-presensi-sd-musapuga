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
<body>
    <h1>Kelola Karyawan</h1>
    <p>Halo, <?= htmlspecialchars($namaAdmin) ?></p>

    <p><a href="">Kembali ke Dashboard</a> | <a href="create.php">Tambah Karyawan</a></p>

    <?php if( $message !== '' ) : ?>
        <div><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table border="2" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Status</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($employees)) : ?>
                <tr>
                    <td>Belum ada data karyawan.</td>
                </tr>
            <?php else : ?>
                <?php foreach( $employees as $employee) : ?>
                    <tr>
                        <td><?= (int)$employee['id'] ?></td>
                        <td><?= htmlspecialchars($employee['nik']) ?></td>
                        <td><?= htmlspecialchars($employee['nama']) ?></td>
                        <td><?= htmlspecialchars($employee['jabatan']) ?></td>
                        <td><?= $employee['status_aktif'] ? 'Aktif' : 'Nonaktif' ?></td>
                        <td><?= htmlspecialchars($employee['created_at']) ?></td>
                        <td>
                            <a href="edit.php?id=<?= (int)$employee['id'] ?>">Edit</a>
                            <a href="toggle_status.php?id=<?= (int)$employee['id'] ?>" onclick="return confirm('Ubah status karyawan ini?')">Ubah Status</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
</body>
</html>