<?php
if( session_status() === PHP_SESSION_NONE ) {
    session_start();
}

if( !isset($_SESSION['admin_id']) ) {
    header('Location: /web-presensi-sd-musapuga/admin/login.php');
    exit;
}


?>