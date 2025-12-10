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
    <link rel="stylesheet" href="../../src/outpout.css">

</head>
<body>
    
    <h1>Laporan Presensi Harian</h1>
    <p>Halo, <?= htmlspecialchars($namaAdmin) ?></p>

    <p><a href="../index.php">Kembali ke Dashboard</a></p>

    <form method="get" action="">
        <label for="tanggal">Tanggal :</label>
        <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" required>
        <button type="submit">Tampilkan</button>
    </form>

    <br>
    <br>

    <h2>Presensi Tanggal : <?= htmlspecialchars($tanggal) ?></h2>

    <p>
        Total karyawan : <?= $total ?><br>
        Hadir : <?= $hadir ?> | Izin : <?= $izin ?> | Sakit : <?= $sakit ?> | Alpa : <?= $alpa ?>
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Status Karyawan</th>
                <th>Status Presensi</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
            </tr>
        </thead>
        <tbody>
            <?php if( empty($rows) ) : ?>
                <tr>
                    <td>Belum ada data karyawan.</td>
                </tr>
            <?php else : ?>
                <?php $no = 1; ?>
                <?php foreach( $rows as $row ) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nik']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['jabatan']) ?></td>
                        <td><?= $row['status_aktif'] ? 'Aktif' : 'Nonaktif' ?></td>
                        <td><?= htmlspecialchars($row['status_tampil']) ?></td>
                        <td>
                            <?php if( empty($row['jam_masuk']) ) : ?>
                                -
                            <?php else : ?>
                                <?= htmlspecialchars(substr($row['jam_masuk'], 11, 5)) ?>
                            <?php endif; ?>
                        </td>
                        <td>
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
</body>
</html>