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

    $sqlAttendances = "SELECT tanggal, status, jam_masuk, jam_pulang FROM attendances 
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

            if( $attendance['status'] === 'HADIR' ) {
                $status = 'HADIR';
                $totalHadir++;

            } elseif( $attendance['status'] === 'IZIN' ) {
                $status ='IZIN';
                $totalIzin++;
            } elseif( $attendance['status'] === 'SAKIT' ) {
                $status = 'SAKIT';
                $totalSakit++;
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
    <title>Laporan Presensi Bulanan</title>
    <link rel="stylesheet" href="../../src/output.css">
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
            <h1 class="mt-2 text-xl font-semibold md:text-2xl text-slate-800">Presensi Bulanan</h1>
            <p class="mt-1 text-sm text-slate-500">Lihat rekap presensi per karyawan dalam satu bulan.</p>
        </section>

        <section class="mb-4">
            <form method="get" action="" class="grid gap-3 md:grid-cols-4 md:items-end">
                <div class="md:col-span-2">
                    <label for="employee_id" class="block mb-1 text-sm font-medium text-slate-700">Karyawan</label>
                    <select name="employee_id" id="employee_id" required class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Karyawan</option>
                        <?php foreach($employees as $employee) : ?>
                            <option value="<?= (int)$employee['id'] ?>" <?= ($employeeId == $employee['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($employee['nik'] . ' - ' . $employee['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="bulan" class="block mb-1 text-sm font-medium text-slate-700">Bulan</label>
                    <select name="bulan" id="bulan" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php foreach($namaBulan as $num => $label) : ?>
                            <option value="<?= $num ?>" <?= ($bulan == $num) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="tahun" class="block mb-1 text-sm font-medium text-slate-700">Tahun</label>
                    <input type="number" name="tahun" id="tahun" value="<?= htmlspecialchars($tahun) ?>" min="2000" max="2100" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                </div>

                <button type="submit" name="submit" value="1" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">Tampilkan</button>
            </form>
        </section>

        <?php if( !empty($errors) ) : ?>
            <div class="px-3 py-2 text-sm border rounded-lg border-rose-200 bg-rose-50 text-rose-700">
                <ul class="space-y-1 list-disc list-inside">
                    <?php foreach($errors as $error) : ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>


        <?php if( empty($errors) && $employeeId  > 0 ) : ?>
            <section class="mb-4">
                <div class="p-4 bg-white border shadow-sm border-salte-200 rounded-2xl">
                    <h2 class="mb-1 text-sm font-semibold text-slate-800">
                        Rekap Presensi : <?= htmlspecialchars($namaKaryawan) ?> <br>
                    </h2>
                    <p class="mb-3 text-xs text-slate-500">Bulan <?= $namaBulan[$bulan] ?? $bulan ?> <?= htmlspecialchars($tahun) ?></p>
                    <div class="grid gap-3 md:grid-cols-4">
                        <div class="p-3 text-center border bg-emerald-50 border-emerald-100 rounded-xl">
                            <div class="text-xs text-slate-500">Hadir</div>
                            <div class="text-xl font-semibold text-emerald-600"><?= $totalHadir ?></div>
                        </div>
                        <div class="p-3 text-center border bg-amber-50 border-amber-100 rounded-xl">
                            <div class="text-xs text-slate-500">Izin</div>
                            <div class="text-xl font-semibold text-amber-600"><?= $totalIzin ?></div>
                        </div>
                        <div class="p-3 text-center border bg-sky-50 border-sky-100 rounded-xl">
                            <div class="text-xs text-slate-500">Sakit</div>
                            <div class="text-xl font-semibold text-emerskyald-600"><?= $totalSakit ?></div>
                        </div>
                        <div class="p-3 text-center border bg-rose-50 border-rose-100 rounded-xl">
                            <div class="text-xs text-slate-500">Alpa</div>
                            <div class="text-xl font-semibold text-rose-600"><?= $totalAlpa ?></div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl ">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                <thead class="border-b bg-slate-50 border-slate-200">
                    <tr>
                        <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Tanggal</th>
                        <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Hari</th>
                        <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Status</th>
                        <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Jam Masuk</th>
                        <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Jam Pulang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($laporan as $row) : ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50/80">
                            <td class="px-4 py-2 text-slate-700"><?= htmlspecialchars($row['tanggal']) ?></td>
                            <td class="px-4 py-2 text-slate-700"><?= htmlspecialchars($row['hari']) ?></td>
                            <td class="px-4 py-2">
                                <?php if( $row['status'] === 'HADIR' ) : ?>
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-600 text-xs px-2 py-0.5 border border-emerald-100">Hadir</span>
                                <?php elseif( $row['status'] === 'IZIN' ) : ?>
                                    <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-600 text-xs px-2 py-0.5 border border-amber-100">Izin</span>
                                <?php elseif( $row['status'] === 'SAKIT' ) : ?>
                                    <span class="inline-flex items-center rounded-full bg-sky-50 text-sky-600 text-xs px-2 py-0.5 border border-sky-100">Sakit</span>
                                <?php else : ?>
                                    <span class="inline-flex items-center rounded-full bg-rose-50 text-rose-600 text-xs px-2 py-0.5 border border-rose-100">Alpa</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 text-slate-700"><?= htmlspecialchars($row['status']) ?></td>
                            <td class="px-4 py-2 text-slate-700">
                                <?php if( empty($row['jam_masuk']) ) : ?>
                                    -
                                <?php else : ?>
                                    <?= htmlspecialchars(substr($row['jam_masuk'], 11, 5)) ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 text-slate-700">
                                <?php if( empty($row['jam_pulang']) ) : ?>
                                    -
                                <?php else : ?>
                                    <?= htmlspecialchars(substr($row['jam_pulang'], 11, 5)) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
        
    </main>
   
    

    
    
</body>
</html>