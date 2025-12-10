<?php
require_once __DIR__ . '/../../includes/auth_admin.php';

require_once __DIR__ . '/../../config/db.php';

$employeeId = '';
$tanggal = date('Y-m-d');
$status = 'HADIR';
$keterangan = '';
$errors = [];
$success = '';

$sqlEmployee = "SELECT id, nik, nama FROM employees WHERE status_aktif = 1 ORDER BY nama ASC";
$resultEmployee = $conn->query($sqlEmployee);
$employees = [];
if( $resultEmployee ) {
    while($row = $resultEmployee->fetch_assoc()) {
        $employees[] = $row;
    }
}

if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $employeeId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;
    $tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $keterangan = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : '';

    if( $employeeId <= 0 ) {
        $errors[] = 'Pilih karyawan terlebih dahulu.';
    }

    if( $tanggal === '' ) {
        $errors[] = 'Tanggal wajib diisi.';
    }

    if( !in_array($status, ['HADIR', 'IZIN', 'SAKIT'], true) ) {
        $errors[] = 'Status presensi tidak valid.';
    }

    if( $tanggal !== '' ) {
        if( strlen($tanggal) !== 10 || substr_count($tanggal, '-') !== 2 ) {
            $errors[] = 'Format tanggal tidak valid.';
        }
    }

    if( empty($errors) ) {
        $tanggalEscaped = $conn->real_escape_string($tanggal);
        $keteranganEscaped = $conn->real_escape_string($keterangan);

        $sqlCheck = "SELECT id FROM attendances WHERE employee_id = $employeeId AND tanggal = '$tanggalEscaped' LIMIT 1";
        $resultCheck = $conn->query($sqlCheck);

        if( $resultCheck && $resultCheck->num_rows === 1 ) {
            $rowAttendance = $resultCheck->fetch_assoc();
            $attendanceId = (int)$rowAttendance['id'];

            $sqlUpdate = "UPDATE attendances SET
                            status = '$status',
                            keterangan = '$keteranganEscaped',
                            jam_masuk = CASE WHEN '$status' = 'HADIR' THEN jam_masuk ELSE NULL END,
                            jam_pulang = CASE WHEN '$status' = 'HADIR' THEN jam_pulang ELSE NULL END,
                            updated_at = NOW()
                            WHERE id = $attendanceId";
                        
            if( $conn->query($sqlUpdate) ) {
                $success = 'Presensi berhasil diperbarui untuk karyawan dan tanggal tersebut.';
            } else {
                $errors[] = 'Gagal memperbarui presensi : ' . $conn->error;
            }
                             
        } else {
            $sqlInsert = "INSERT INTO attendances (employee_id, tanggal, status, keterangan, created_at, updated_at)
                            VALUES ($employeeId, '$tanggalEscaped', '$status', '$keteranganEscaped', NOW(), NOW())";
            
            if( $conn->query($sqlInsert) ) {
                $success = 'Presensi baru berhasil dibuat.';
            } else {
                $errors[] = 'Gagal menambah presensi : ' . $conn->error;
            }
        }
    }
}

$namaAdmin = isset($_SESSION['admin_nama']) ?? $_SESSION['admin_username'] ?? 'Admin';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Pengaturan Presensi Manual</h1>
    <p>Halo, <?= htmlspecialchars($namaAdmin) ?></p>

    <p><a href="">Kembali ke dashboard</a> | <a href="">Lihat Laporan Harian</a></p>

    <?php if( !empty($errors) ) : ?>
        <div>
            <ul>
                <?php foreach( $errors as $error ) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if( $success !== '' ) : ?>
        <div>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div>
            <label for="">Karyawan</label>
            <select name="employee_id" id="employee_id" required>
                <option value="">Pilih Karyawan</option>
                <?php foreach( $employees as $employee ) : ?>
                    <option value="<?= (int)$employee['id'] ?>" <?= ($employeeId == $employee['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($employee['nik'] . ' - ' . $employee['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="tanggal">Tanggal : </label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" required>
        </div>

        <div>
            <label>Status Presensi</label>
            <label>
                <input type="radio" name="status" value="HADIR" <?= $status === 'HADIR' ? 'checked' : '' ?>>
                <span>HADIR</span>
            </label>
            <label>
                <input type="radio" name="status" value="IZIN" <?= $status === 'IZIN' ? 'checked' : '' ?>>
                <span>IZIN</span>
            </label>
            <label>
                <input type="radio" name="status" value="SAKIT" <?= $status === 'SAKIT' ? 'checked' : '' ?>>
                <span>SAKIT</span>
            </label>
            <div>
                <label for="">Keterangan</label>
                <textarea name="keterangan" id="keterangan"><?= htmlspecialchars($keterangan) ?></textarea>
            </div>
            <button type="submit">Simpan</button>
        </div>
    </form>
</body>
</html>