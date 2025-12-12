<?php
require_once __DIR__ . '/../../includes/auth_admin.php';

require_once __DIR__ . '/../../config/db.php';

$tanggal = date('Y-m-d');

if( isset($_GET['tanggal']) && $_GET['tanggal'] !== '' ) {
    $input = $_GET['tanggal'];

    if( strlen($input) === 10 && substr_count($input, '-') === 2 ) {
        $tanggal = $input;
    }
}

$namaAdmin = $_SESSION['admin_nama'] ?? $_SESSION['admin_username'] ?? 'Admin';

$tanggalEscaped = $conn->real_escape_string($tanggal);

$sql = "SELECT 
        e.id AS employee_id,
        e.nik,
        e.nama,
        e.jabatan,
        e.status_aktif,
        a.id AS attendance_id,
        a.jam_masuk,
        a.jam_pulang,
        a.status AS status_presensi 
        FROM employees e 
        LEFT JOIN attendances a 
        ON a.employee_id = e.id AND a.tanggal = '$tanggalEscaped' ORDER BY e.nama ASC";

        $result = $conn->query($sql);

        $rows = [];
        $total = 0;
        $hadir = 0;
        $izin = 0;
        $sakit = 0;
        $alpa = 0;

        if( $result ) {
            while( $row = $result->fetch_assoc() ) {
                $total++;

                if( is_null($row['attendance_id']) ) {
                    $statusTampil = 'ALPA';
                    $alpa++;
                } else {
                    if( $row['status_presensi'] === 'IZIN' ) {
                        $statusTampil = 'IZIN';
                        $izin++;
                    } elseif( $row['status_presensi'] === 'SAKIT' ) {
                        $statusTampil = 'SAKIT';
                        $sakit++;
                    } else {
                        $statusTampil = 'HADIR';
                        $hadir++;
                    }
                }

                $row['status_tampil'] = $statusTampil;
                $rows[] = $row;
            }
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi Harian</title>
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
        <section>
            <h1 class="text-xl font-semibold md:text-2xl text-slate-800">Presensi Harian</h1>
            <p class="mt-1 text-sm text-slate-500">Lihat status hadir/izin/sakit/alpa semua karyawan pada tanggal tertentu.</p>
        </section>

        <section class="mb-4">
            <form method="get" action="" class="flex flex-col gap-2 md:flex-row md:items-end md:gap-3">
                <div>
                    <label for="tanggal" class="block mb-1 text-sm font-medium text-slate-700">Tanggal :</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" required class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
                </div>
                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">Tampilkan</button>
                </div>
            </form>
        </section>

        <section class="grid gap-3 mb-4 md:grid-cols-4">
            <div class="p-3 text-center bg-white border shadow-sm border-emerald-100 rounded-xl">
                <div class="text-xs text-slate-500">Hadir</div>
                <div class="text-xl font-semibold text-emerald-600"><?= $hadir ?></div>
            </div>
            <div class="p-3 text-center bg-white border shadow-sm border-amber-100 rounded-xl">
                <div class="text-xs text-slate-500">Izin</div>
                <div class="text-xl font-semibold text-amber-600"><?= $izin ?></div>
            </div>
            <div class="p-3 text-center bg-white border shadow-sm border-sky-100 rounded-xl">
                <div class="text-xs text-slate-500">Sakit</div>
                <div class="text-xl font-semibold text-sky-600"><?= $sakit ?></div>
            </div>
            <div class="p-3 text-center bg-white border shadow-sm border-rose-100 rounded-xl">
                <div class="text-xs text-slate-500">Alpa</div>
                <div class="text-xl font-semibold text-rose-600"><?= $alpa ?></div>
            </div>
        </section>

        <section class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b bg-slate-50 border-slate-200">
                        <tr>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">No</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">NIK</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Nama</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Jabatan</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Status Karyawan</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Status Presensi</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Jam Masuk</th>
                            <th class="px-4 py-2 text-xs font-semibold tracking-wide text-left uppercase text-slate-500">Jam Pulang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if( empty($rows) ) : ?>
                            <tr>
                                <td colspan="7" class="px-4 py-4 text-sm text-center text-slate-500">Belum ada data karyawan.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach( $rows as $row ) : ?>
                                <tr class="border-t border-slate-100 hover:bg-slate-50/80">
                                    <td class="px-4 py-2 text-slate-700"><?= $no++ ?></td>
                                    <td class="px-4 py-2 text-slate-700"><?= htmlspecialchars($row['nik']) ?></td>
                                    <td class="px-4 py-2 text-slate-800"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="px-4 py-2 text-slate-700"><?= htmlspecialchars($row['jabatan']) ?></td>
                                    <td class="px-4 py-2">
                                        <?php if( $row['status_aktif']) : ?>
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-600 text-xs px-2 py-0.5 border border-emerald-100">Aktif</span>
                                        <?php else : ?>
                                            <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-500 text-xs px-2 py-0.5 border border-slate-200">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?php if( $row['status_tampil'] === 'HADIR') : ?>
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-600 text-xs px-2 py-0.5 border border-emerald-100">Hadir</span>
                                        <?php elseif( $row['status_tampil'] === 'IZIN' ) : ?>
                                            <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-600 text-xs px-2 py-0.5 border border-amber-100">Izin</span>
                                        <?php elseif( $row['status_tampil'] === 'SAKIT' ) : ?>
                                            <span class="inline-flex items-center rounded-full bg-sky-50 text-sky-600 text-xs px-2 py-0.5 border border-sky-100">Sakit</span>
                                        <?php else : ?>
                                            <span class="inline-flex items-center rounded-full bg-rose-50 text-rose-600 text-xs px-2 py-0.5 border border-rose-100">Alpa</span>
                                        <?php endif; ?>
                                    </td>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    
    
</body>
</html>