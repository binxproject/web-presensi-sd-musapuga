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
    <title>Pengaturan Presensi Manual</title>
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
                <a href="../logout.php" class="text-sm leading-tight text-rose-700 hover:underline">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-5xl px-4 py-6 mx-auto">
        <section class="mb-4">
            <h1 class="text-xl font-semibold md:text-2xl text-slate-800">Atur Presensi Manual</h1>
            <p class="mt-1 text-sm text-slate-500">Gunakan halaman ini untuk memperbaiki atau menginput presensi secara manual (hadir, izin, sakit).</p>
        </section>

        <?php if( !empty($errors) ) : ?>
            <section class="mb-3">
                 <div class="px-3 py-2 text-sm border rounded-lg border-rose-200 bg-rose-50 text-rose-700">
                    <ul class="space-y-1 list-disc list-inside">
                        <?php foreach( $errors as $error ) : ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>
        
        <?php if( $success !== '' ) : ?>
            <section class="mb-3">
                <div class="px-3 py-2 text-sm border rounded-lg border-emerald-200 bg-emerald-50 text-emerald-700">
                    <?= htmlspecialchars($success) ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="p-5 bg-white border shadow-sm border-slate-200 rounded-2xl">
            <form method="post" action="" class="space-y-4">
                <div>
                    <label for="employee_id" class="block mb-1 text-sm font-medium text-slate-700">Karyawan</label>
                    <select name="employee_id" id="employee_id" required class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                        <option value="">Pilih Karyawan</option>
                        <?php foreach( $employees as $employee ) : ?>
                            <option value="<?= (int)$employee['id'] ?>" <?= ($employeeId == $employee['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($employee['nik'] . ' - ' . $employee['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="tanggal" class="block mb-1 text-sm font-medium text-slate-700">Tanggal : </label>
                    <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" required class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-slate-700">Status Presensi</label>
                    <div class="flex items-center gap-4 text-sm text-slate-700">
                        <label class="inline-flex items-center gap-1">
                            <input type="radio" name="status" value="HADIR" <?= $status === 'HADIR' ? 'checked' : '' ?>>
                            <span>HADIR</span>
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="radio" name="status" value="IZIN" <?= $status === 'IZIN' ? 'checked' : '' ?>>
                            <span>IZIN</span>
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="radio" name="status" value="SAKIT" <?= $status === 'SAKIT' ? 'checked' : '' ?>>
                            <span>SAKIT</span>
                        </label>
                    </div>
                    
                    <div class="mt-2">
                        <label for="keterangan" class="block mb-1 text-sm font-medium text-slate-700">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($keterangan) ?></textarea>
                    </div>
                    <button type="submit" class="mt-2 px-4 py-2.5 text-white text-sm rounded-lg transition-colors bg-blue-600 hover:bg-blue-700 active:bg-blue-800">Simpan</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>