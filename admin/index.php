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
<body>

    <h1>Dashboard Admin</h1>
    <p>Selamat datang, <?= htmlspecialchars($namaAdmin) ?></p>

    <p>
        <a href="karyawan/index.php">Kelola karyawan</a>
        <a href="">Presensi harian</a>
        <a href="">Presensi bulanan</a>
        <a href="logout.php">Logout</a>
    </p>
    
</body>
</html>