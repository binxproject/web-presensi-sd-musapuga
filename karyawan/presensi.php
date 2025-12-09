<?php
require_once __DIR__ . '/../config/db.php';

$errors = [];
$success = '';

if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $nik = isset($_POST['nik']) ? trim($_POST['nik']) : '';
    $tipe = isset($_POST['tipe']) ? trim($_POST['tipe']) : '';

    if( $nik === '' ) {
        $errors[] = 'NIK wajib diisi.';
    }

    if( !in_array($tipe, ['masuk', 'pulang'], true) ) {
        $errors[] = 'Jenis presensi tidak valid.';
    }

    if( empty($errors) ) {
        $nik_escaped = $conn->real_escape_string($nik);
        $sqlEmployee = "SELECT id, nama FROM employees WHERE nik = '$nik_escaped' AND status_aktif = 1 LIMIT 1";
        $resultEmployee = $conn->query($sqlEmployee);

        if( $resultEmployee && $resultEmployee->num_rows === 1 ) {
            $employee = $resultEmployee->fetch_assoc();
            $employeeId = (int)$employee['id'];

            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');

            $sqlAttendance = "SELECT id, jam_masuk, jam_pulang, status FROM attendances WHERE employee_id = $employeeId AND tanggal = '$today' LIMIT 1";
            $resultAttendance = $conn->query($sqlAttendance);

            if( $tipe === 'masuk' ) {
                if( $resultAttendance && $resultAttendance->num_rows > 0 ) {
                    $attendance = $resultAttendance->fetch_assoc();

                    if( !is_null($attendance['jam_masuk']) ) {
                        $errors[] = 'Kamu sudah presensi MASUK hari ini.';
                    } else {
                        $attendanceId = (int)$attendance['id'];
                        $sqlUpdateMasuk = "UPDATE attendances SET jam_masuk = '$now', status = 'HADIR', update_at = NOW() WHERE id =$attendanceId";

                        if( $conn->query($sqlUpdateMasuk) ) {
                            $success = 'Presensi MASUK berhasil diperbarui.';
                        } else {
                            $errors[] = 'Gagal menyimpan presensi MASUK (error update).';
                        }
                    }
                } else {
                    $sqlInsertMasuk = "INSERT INTO 
                    attendances (employee_id, tanggal, jam_masuk, status, created_at, updated_at) 
                    VALUES ($employeeId, '$today', '$now', 'HADIR', NOW(), NOW())";

                    if( $conn->query($sqlInsertMasuk) ) {
                        $success = 'Presensi MASUK berhasil dicatat.';
                    } else {
                        $errors[] = 'Gagal menyimpan presensi MASUK (error insert).';
                    }
                }
            } elseif( $tipe === 'pulang' ) {
                if( !$resultAttendance || $resultAttendance->num_rows === 0 ) {
                    $errors[] = 'Kamu belum presensi MASUK hari ini, jadi belum bisa presensi PULANG.';
                } else {
                    $attendance = $resultAttendance->fetch_assoc();

                    if( !is_null($attendance['jam_pulang']) ) {
                        $errors[] = 'Kamu sudah presensi PULANG hari ini.';
                    } else {
                        $attendanceId = (int)$attendance['id'];
                        $sqlUpdatePulang = "UPDATE attendances SET jam_pulang = '$now', updated_at = NOW() WHERE id = $attendanceId";

                        if( $conn->query($sqlUpdatePulang) ) {
                            $success = 'Presensi PULANG berhasil dicatat.';
                        } else {
                            $errors[] = 'Gagal menyimpan presensi PULANG.';
                        }
                    }
                }
            }
        } else {
            $errors[] = 'NIK tidak ditemukan atau karyawan tidak aktif.';
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi</title>
    <link rel="stylesheet" href="../src/output.css">


</head>
<body>
    <h1>Presensi Karyawan</h1>

    <?php if( !empty($errors) ) : ?>
        <div>
            <ul>
                <?php foreach( $errors as $err) : ?>
                    <li><?= htmlspecialchars($err) ?></li>
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
            <label for="nik">NIK</label>
            <input type="text" name="nik" id="nik" required>
        </div>

        <div>
            <label for="">Jenis Presensi</label>
            <label for="masuk">
                <input type="radio" id="masuk" name="tipe" value="masuk" checked>
                <span>Masuk</span>
            </label>
            <label for="pulang">
                <input type="radio" id="pulang" name="tipe" value="pulang">
                <span>Pulang</span>
            </label>
        </div>

        <div>
            <button type="submit">Kirim Presensi</button>
        </div>
    </form>


</body>
</html>