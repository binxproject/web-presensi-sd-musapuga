<?php
require_once __DIR__ . '/../../includes/auth_admin.php';

require_once __DIR__ . '/../../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if( $id <= 0 ) {
    header('Location: index.php?message=' . urlencode('ID karyawan tidak valid.'));
    exit;
}

$sql = "SELECT id, status_aktif FROM employees WHERE id = $id LIMIT 1";
$result = $conn->query($sql);

if( !$result || $result->num_rows === 0 ) {
    header('Location: index.php?message=' . urlencode('Karyawan tidak ditemukan.'));
    exit;
}

$employee = $result->fetch_assoc();

$statusBaru = $employee['status_aktif'] ? 0 : 1;

$sqlUpdate = "UPDATE employees SET status_aktif = $statusBaru, updated_at = NOW() WHERE id = $id";

if( $conn->query($sqlUpdate) ) {
    $pesan = $statusBaru ? 'Karyawan diaktifkan.' : 'Karyawan dinonaktifkan.';
    header('Location: index.php?message=' . urlencode($pesan));
    exit;
} else {
    header('Location: index.php?message=' . urlencode($pesan));
    exit;
}

?>