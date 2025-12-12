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
                        $sqlUpdateMasuk = "UPDATE attendances SET jam_masuk = '$now', status = 'HADIR', updated_at = NOW() WHERE id =$attendanceId";

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
<body class="flex items-center justify-center min-h-screen bg-slate-100">

    <div class="w-full max-w-md p-6 bg-white shadow-lg rounded-xl">
        <div class="flex items-center justify-center">
            <img src="../assets/images/logo.webp" alt="Logo SD Musapuga" class="w-[120px] h-[120px]">
        </div>
        
        <h1 class="mb-1 text-2xl font-semibold text-center text-slate-800">Presensi Karyawan</h1>
        <p class="mb-6 text-sm text-center text-slate-500">Silahkan masukkan NIK dan pilih jenis presensi.</p>

        <?php if( !empty($errors) ) : ?>
            <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-rose-200 bg-rose-50 text-rose-700">
                <ul class="list-disc list-inside">
                    <?php foreach( $errors as $err) : ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if( $success !== '' ) : ?>
            <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-emerald-200 bg-emerald-50 text-emerald-700">
                <ul class="list-disc list-inside">
                    <li><?= htmlspecialchars($success) ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="space-y-5">
            <div>
                <label for="nik" class="block mb-1 text-sm font-medium text-slate-700">NIK</label>
                <input type="text" name="nik" id="nik" required placeholder="Masukkan NIK anda" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
            </div>

            <div>
                <span class="block mb-1 text-sm font-medium text-slate-700">Jenis Presensi</span>
                <div class="flex items-center gap-4 text-sm text-slate-700">
                    <label for="masuk" class="inline-flex items-center gap-1">
                        <input type="radio" id="masuk" name="tipe" value="masuk" checked>
                        <span>Masuk</span>
                    </label>
                    <label for="pulang" class="inline-flex items-center gap-1">
                        <input type="radio" id="pulang" name="tipe" value="pulang">
                        <span>Pulang</span>
                    </label>
                </div>
                
            </div>

            <button type="submit" class="px-4 py-2.5 text-white text-sm rounded-lg transition-colors bg-blue-600 hover:bg-blue-700 active:bg-blue-800">Kirim Presensi</button>
            
        </form>
        <p class="mt-4 text-xs text-center text-slate-400">Presensi hanya dapat dilakukan sekali untuk MASUK dan sekali untuk PULANG setiap hari.</p>
    </div>
    


</body>
</html>