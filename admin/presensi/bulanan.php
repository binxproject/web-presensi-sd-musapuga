<?php
require_once __DIR__ . '/../../includes/auth_admin.php';

require_once __DIR__ . '/../../config/db.php';

$sqlEmployee = "SELECT id, nik, nama FROM employees WHERE status_aktif = 1 ORDER BY nama ASC";
$resultEmployee = $conn->query($sqlEmployee);
$employees = [];
if( $resultEmployee ) {
    while($row = $resultEmployee->fetch_assoc()) {
        $employees[] = $row;
    }
}

$employeeId = '';
$bulan = (int)date('n');
$tahun = (int)date('Y');

$errors = [];

if( isset($_GET['submit']) ) {
    $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
    $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
    $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

    if( $employeeId <= 0 ) {
        $errors[] = 'Pilih karyawan terlebih dahulu.';
    }

    if( $bulan < 1 || $bulan > 12 ) {
        $errors[] = 'Bulan tidak valid.';
    }

    if( $tahun < 2000 || $tahun > 2100 ) {
        $errors[] = 'Tahun tidak valid.';
    }
}

$laporan = [];
$totalHadir = 0;
$totalIzin = 0;
$totalSakit = 0;
$totalAlpa = 0;
$namaKaryawan = '';

if( empty($errors) && $employeeId > 0 ) {
    foreach($employees as $employee) {
        if( (int)$employee['id'] === $employeeId ) {
            $namaKaryawan = $employee['nama'] . ' (' . $employee['nik'] . ')';
            break;
        }
    }

    $dayInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
    $endDate = sprintf('%04d-%02d-%02d', $tahun, $bulan, $dayInMonth);

    $startEscaped = $conn->real_escape_string($startDate);
    $endEscaped = $conn->real_escape_string($endDate);

    $sqlAttendances = "SELECT tanggal, status, jam_masuk, jam_pulang, FROM attendances 
                        WHERE employee_id = $employeeId AND tanggal BETWEEN '$startEscaped' AND '$endEscaped' ORDER BY tanggal ASC";
    
    $resultAttendaces = $conn->query($sqlAttendances);

    $mapPresensi = [];
    if( $resultAttendaces ) {
        while($row = $resultAttendaces->fetch_assoc()) {
            $mapPresensi[$row['tanggal']] = $row;
        }
    }

    for( $hari = 1; $hari <= $dayInMonth; $hari++ ) {
        $tanggalString = sprintf('%04d-%02d-%02d', $tahun, $bulan, $hari);

        $timeStamp = strtotime($tanggalString);
        $namaHari = date('l', $timeStamp);

        $hariIndoMap = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        $namaHariIndo = isset($hariIndoMap[$namaHari]) ? $hariIndoMap[$namaHari] : $namaHari;

        if( isset($mapPresensi[$tanggalString]) ) {
            $attendance = $mapPresensi[$tanggalString];

            if( $attendance['status'] === 'IZIN' ) {
                $status ='IZIN';
                $totalIzin++;
            } elseif( $attendance['status'] === 'SAKIT' ) {
                $status = 'SAKIT';
                $totalSakit++;
            } else {
                $status = 'HADIR';
                $totalHadir++;
            }

            $jamMasuk = $attendance['jam_masuk'];
            $jamPulang = $attendance['jam_pulang'];

        } else {
            $status = 'ALPA';
            $jamMasuk = null;
            $jamPulang = null;
            $totalAlpa++;
        }

        $laporan[] = [
            'tanggal' => $tanggalString,
            'hari' => $namaHariIndo,
            'status' => $status,
            'jam_masuk' => $jamMasuk,
            'jam_pulang' => $jamPulang
        ];
    }
}

$namaAdmin = $_SESSION['admin_nama'] ?? $_SESSION['admin_username'] ?? 'Admin';

$namaBulan = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <h1>Laporan Presensi Bulanan</h1>
    <p>Halo, <?= htmlspecialchars($namaAdmin) ?></p>

    <p><a href="">Kembali ke Dashboard</a> | <a href="">Laporan Harian</a> | <a href="">Atur Presensi Manual</a></p>

    <form action="">
        <div>
            <label for="employee_id">Karyawan</label>
            <select name="employee_id" id="employee_id" required>
                <option value="">Pilih Karyawan</option>
                <?php foreach($employees as $employee) : ?>
                    <option value="<?= (int)$employee['id'] ?>" <?= ($employeeId == $employee['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($employee['nik'] . ' - ' . $employee['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="bulan">Bulan</label>
            <select name="bulan" id="bulan">
                <?php foreach($namaBulan as $num => $label) : ?>
                    <option value="<?= $num ?>" <?= ($bulan == $num) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="tahun">Tahun</label>
            <input type="number" name="tahun" id="tahun" value="<?= htmlspecialchars($tahun) ?>" min="2000" max="2100">
        </div>

        <button type="submit" name="submit" value="1">Tampilkan</button>
    </form>

    <?php if( !empty($errors) ) : ?>
        <div>
            <ul>
                <?php foreach($errors as $error) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if( empty($errors) && $employeeId  > 0 ) : ?>
    
    <?php endif; ?>
    
</body>
</html>